<?php
namespace RightPlace;
/**
 * Plugin_Code_Validator
 *
 * Assume you have already verified NONCE and the incoming parameters.
 * $file        = sanitize_text_field( $_POST['file'] );        // e.g. 'my-plugin/includes/foo.php'
 * $new_content = wp_unslash( $_POST['code'] );                 // raw PHP source
 *
 * $errors = Plugin_Code_Validator::validate( $file, $new_content );
 *
 * if ( empty( $errors ) ) {
 *     wp_send_json_success();               // safe to save – file already written
 * } else {
 *     wp_send_json_error( $errors );        // validation failed – file rolled back
 * }
 *
 * Validates edited PHP files in a WordPress plugin before they are permanently saved.
 * This mirrors the sandbox / loop-back approach used by WordPress core.
 */
class Plugin_Code_Validator {

	/**
	 * Public entry point.
	 *
	 * @param string $file        Path to the file *relative to WP_PLUGIN_DIR*.
	 * @param string $new_content The edited PHP code to validate.
	 * @return array              Empty array if safe; otherwise an array of error hashes.
	 */
	public static function validate( $file, $new_content ) {
		$validator = new self;
		return $validator->run_validation( $file, $new_content );
	}

	/* ---------------------------------------------------------------------
	 * INTERNALS
	 * -------------------------------------------------------------------*/

	/**
	 * Orchestrates write → sandbox test → rollback.
	 */
	private function run_validation( $file, $new_content ) {

		if ( ! current_user_can( 'edit_plugins' ) ) {
			return array( 'success' => false, 'error' => $this->error( 'permission_error', 'Unauthorized to edit plugins.' ) );
		}

		// Extract the plugin directory name from the path
		$file_relative_path = $file;
		if ( strpos( $file, 'wp-content/plugins/' ) !== false) {
			$file_relative_path = substr( $file, strpos( $file, 'wp-content/plugins/' ) + 19 );
		}

		if ( 0 !== validate_file( $file_relative_path ) ) {
			return array( 'success' => false, 'error' => $this->error( 'file_error', 'Invalid file path.' ) );
		}

		$real_path = WP_PLUGIN_DIR . '/' . ltrim( $file_relative_path, '/' );
		if ( ! file_exists( $real_path ) ) {
			return array( 'success' => false, 'error' => $this->error( 'file_error', 'File does not exist.' ) );
		}
		if ( ! is_writable( $real_path ) ) {
			return array( 'success' => false, 'error' => $this->error( 'file_error', 'File is not writable.' ) );
		}

		// Backup & write.
		$original = file_get_contents( $real_path );
		if ( false === file_put_contents( $real_path, $new_content ) ) {
			return array( 'success' => false, 'error' => $this->error( 'file_error', 'Failed to write new content on file: ' . $real_path ) );
		}
		if ( function_exists( 'opcache_invalidate' ) ) {
			@opcache_invalidate( $real_path, true );
		}

		// Create scrape key and nonce used by WP core sandbox.
		list( $scrape_key, $scrape_nonce ) = $this->create_scrape_tokens();

		$errors = array();
		// Admin-context check.
		$admin_url = add_query_arg(
			array_merge(
				array(
					'plugin' => plugin_basename( $real_path ),
					'file'   => $file,
				),
				$this->scrape_args( $scrape_key, $scrape_nonce )
			),
			admin_url( 'plugin-editor.php' )
		);

		rp_dev_log( 'RightPlace: Admin URL: ' . $admin_url );

		$result = $this->scrape_url( $admin_url, $scrape_key );
		$this->collect_errors( $result, $errors );

		// Front-end check (only if admin passed).
		if ( empty( $errors ) ) {
			$front_url = add_query_arg(
				$this->scrape_args( $scrape_key, $scrape_nonce ),
				home_url( '/' )
			);
			$result = $this->scrape_url( $front_url, $scrape_key );
			$this->collect_errors( $result, $errors );
		}

		delete_transient( 'scrape_key_' . $scrape_key );

		// Roll back on failure.
		if ( ! empty( $errors ) ) {
			file_put_contents( $real_path, $original );
			if ( function_exists( 'opcache_invalidate' ) ) {
				@opcache_invalidate( $real_path, true );
			}
			return array( 'success' => false, 'errors' => $errors );
		}

		return array( 'success' => true );
	}

	/**
	 * Perform a loop-back request and parse the sandbox markers.
	 *
	 * @return array|WP_Error|true
	 */
	private function scrape_url( $url, $scrape_key ) {
		$args = array(
			'timeout' => 15,
			'sslverify' => apply_filters( 'https_local_ssl_verify', false ),
			'user-agent' => 'WordPress/' . get_bloginfo( 'version' ) . '; ' . get_bloginfo( 'url' ),
			'redirection' => 0,
		);

		rp_dev_log( 'RightPlace: Attempting scrape URL: ' . $url );
		rp_dev_log( 'RightPlace: Scrape key: ' . $scrape_key );

		$response = wp_remote_get( $url, $args );
		if ( is_wp_error( $response ) ) {
			rp_dev_log( 'RightPlace: Loopback request failed: ' . $response->get_error_message() );
			return new \WP_Error( 'loopback_failed', $response->get_error_message() );
		}

		$body = wp_remote_retrieve_body( $response );
		$response_code = wp_remote_retrieve_response_code( $response );
		rp_dev_log( 'RightPlace: Response code: ' . $response_code );
		rp_dev_log( 'RightPlace: Response body length: ' . strlen( $body ) );

		return $this->parse_scrape_response( $body, $scrape_key );
	}

	/**
	 * Extract result JSON wrapped by WP_SANDBOX markers.
	 */
	private function parse_scrape_response( $body, $scrape_key ) {
		$start = "###### wp_scraping_result_start:$scrape_key ######";
		$end   = "###### wp_scraping_result_end:$scrape_key ######";
		
		rp_dev_log( 'RightPlace: Looking for markers with key: ' . $scrape_key );
		rp_dev_log( 'RightPlace: Start marker: ' . $start );
		rp_dev_log( 'RightPlace: End marker: ' . $end );

		$s_pos = strpos( $body, $start );
		$e_pos = strpos( $body, $end );

		if ( $s_pos === false || $e_pos === false ) {
			rp_dev_log( 'RightPlace: Marker positions - Start: ' . ($s_pos === false ? 'not found' : $s_pos) . ', End: ' . ($e_pos === false ? 'not found' : $e_pos) );
			rp_dev_log( 'RightPlace: First 500 chars of response: ' . substr( $body, 0, 500 ) );
			return new \WP_Error( 'scrape_error', 'Sandbox markers not found.' );
		}
		$json = trim( substr( $body, $s_pos + strlen( $start ), $e_pos - $s_pos - strlen( $start ) ) );
		$data = json_decode( $json, true );

		rp_dev_log( 'RightPlace: Scrape response data: ' . print_r( $data, true ) );

		if ( null === $data ) {
			return new \WP_Error( 'scrape_error', 'Invalid sandbox JSON.' );
		}
		return $data;
	}

	/**
	 * Convert scrape result into our unified $errors array.
	 */
	private function collect_errors( $result, &$errors ) {
		if ( is_wp_error( $result ) ) {
			$errors[] = $this->error( 'sandbox_error', $result->get_error_message() );
		} elseif ( is_array( $result ) ) {
			if ( isset( $result['message'] ) ) {
				$error = array(
					'type'    => 'fatal_error',
					'message' => $result['message'],
					'file'    => isset( $result['file'] ) ? $result['file'] : null,
					'line'    => isset( $result['line'] ) ? $result['line'] : null,
				);

				// Parse PHP syntax error details
				if ( isset( $result['code'] ) && $result['code'] === 'php_syntax_error' ) {
					$error['type'] = 'syntax_error';
					
					// Extract line number from the error message if not already provided
					if ( ! isset( $error['line'] ) && preg_match( '/on line (\d+)/', $result['message'], $matches ) ) {
						$error['line'] = (int) $matches[1];
					}

					// Try to extract the problematic code snippet
					if ( isset( $result['snippet'] ) ) {
						$error['snippet'] = $result['snippet'];
					}

					// Extract column position if available
					if ( isset( $result['column'] ) ) {
						$error['column'] = $result['column'];
					}
				}

				$errors[] = $error;
			}
		}
	}

	/**
	 * Helpers
	 * ----------------------------------------------------------------- */

	private function error( $type, $msg ) {
		return array( 'type' => $type, 'message' => $msg );
	}

	private function create_scrape_tokens() {
		$key   = md5( wp_generate_password( 20, true, true ) );
		$nonce = wp_generate_password( 6, false );
		set_transient( 'scrape_key_' . $key, $nonce, 60 );
		return array( $key, $nonce );
	}

	private function scrape_args( $key, $nonce ) {
		return array(
			'wp_scrape_key'   => $key,
			'wp_scrape_nonce' => $nonce,
		);
	}

	private function current_cookies() {
		$cookies = array();
		foreach ( $_COOKIE as $name => $value ) {
			$cookies[] = new \WP_Http_Cookie( compact( 'name', 'value' ) );
		}
		return $cookies;
	}

	private function auth_header() {
		if ( isset( $_SERVER['PHP_AUTH_USER'], $_SERVER['PHP_AUTH_PW'] ) ) {
			$auth = base64_encode( $_SERVER['PHP_AUTH_USER'] . ':' . $_SERVER['PHP_AUTH_PW'] );
			return array( 'Authorization' => 'Basic ' . $auth );
		}
		return array();
	}
}

