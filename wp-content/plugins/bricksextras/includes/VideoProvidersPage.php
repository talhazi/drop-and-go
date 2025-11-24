<?php

namespace BricksExtras;

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class VideoProvidersPage {
	static $prefix = '';
	static $title = '';
	static $version = '';
	
	/**
	 * Register settings
	 */
	public static function register_settings() {
		// Register Bunny Stream settings
		register_setting( self::$prefix . 'video_providers', self::$prefix . 'bunny_library_id', [
			'type' => 'string',
			'sanitize_callback' => 'sanitize_text_field',
			'default' => '',
		]);
		
		register_setting( self::$prefix . 'video_providers', self::$prefix . 'bunny_api_key', [
			'type' => 'string',
			'sanitize_callback' => 'sanitize_text_field',
			'default' => '',
		]);
		
		register_setting( self::$prefix . 'video_providers', self::$prefix . 'bunny_token', [
			'type' => 'string',
			'sanitize_callback' => 'sanitize_text_field',
			'default' => '',
		]);
	}
	
	/**
	 * Save settings
	 */
	public static function save_settings() {
		// Save Bunny Stream settings
		if ( isset( $_POST[self::$prefix . 'bunny_library_id'] ) ) {
			$library_id = sanitize_text_field( $_POST[self::$prefix . 'bunny_library_id'] );
			
			// Check if input is a single space (used to clear the setting)
			if ( $_POST[self::$prefix . 'bunny_library_id'] === ' ' ) {
				delete_option( self::$prefix . 'bunny_library_id' );
			}
			// Only update library ID if it's not empty, otherwise keep existing value
			elseif ( !empty($library_id) ) {
				// Validate library ID format - allow numbers, letters, dashes, and underscores
				if ( !preg_match('/^[\w\-]+$/', $library_id) ) {
					add_action('admin_notices', function() {
						?>
						<div class="notice notice-error is-dismissible">
							<p><?php _e('Library ID can only contain letters, numbers, dashes, and underscores.'); ?></p>
						</div>
						<?php
					});
					return;
				}
				
				update_option( self::$prefix . 'bunny_library_id', $library_id );
			}
		}
		
		// Handle API key - update if not empty, delete if space, otherwise keep existing value
		if ( isset( $_POST[self::$prefix . 'bunny_api_key'] ) ) {
			if ( $_POST[self::$prefix . 'bunny_api_key'] === ' ' ) {
				delete_option( self::$prefix . 'bunny_api_key' );
			} elseif ( !empty( $_POST[self::$prefix . 'bunny_api_key'] ) ) {
				update_option( self::$prefix . 'bunny_api_key', sanitize_text_field( $_POST[self::$prefix . 'bunny_api_key'] ) );
			}
		}
		
		// Handle token - update if not empty, delete if space, otherwise keep existing value
		if ( isset( $_POST[self::$prefix . 'bunny_token'] ) ) {
			if ( $_POST[self::$prefix . 'bunny_token'] === ' ' ) {
				delete_option( self::$prefix . 'bunny_token' );
			} elseif ( !empty( $_POST[self::$prefix . 'bunny_token'] ) ) {
				update_option( self::$prefix . 'bunny_token', sanitize_text_field( $_POST[self::$prefix . 'bunny_token'] ) );
			}
		}
		
		// Add a success message
		add_action('admin_notices', function() {
			?>
			<div class="notice notice-success is-dismissible">
				<p><?php _e('Settings saved successfully!'); ?></p>
			</div>
			<?php 
		});
	}
	
	/**
	 * Mask an API key for display, showing only first and last parts
	 * and masking approximately 60% of the middle characters
	 *
	 * @param string $key The API key to mask
	 * @return string The masked API key
	 */
	public static function mask_api_key($key) {
		if (empty($key)) {
			return '';
		}
		
		$length = strlen($key);
		
		// For very short keys (less than 6 chars), show the same number of bullets as characters
		if ($length < 6) {
			return str_repeat('•', $length);
		}
		
		// For short keys (less than 8 chars), show first 2 and last 2
		if ($length < 8) {
			$first_part = substr($key, 0, 2);
			$last_part = substr($key, -2);
			$masked = $first_part . str_repeat('•', $length - 4) . $last_part;
			return $masked;
		}
		
		// For longer keys, mask approximately 60% of the middle
		$visible_percent = 0.3; // 40% visible, 60% masked
		$visible_chars = (int)($length * $visible_percent);
		
		// Ensure even distribution between first and last part
		$first_chars = (int)($visible_chars / 2);
		$last_chars = $visible_chars - $first_chars;
		
		$first_part = substr($key, 0, $first_chars);
		$last_part = substr($key, -$last_chars);
		$masked_length = $length - $first_chars - $last_chars;
		
		$masked = $first_part . str_repeat('•', $masked_length) . $last_part;
		
		return $masked;
	}

	public static function init( $prefix, $title, $version ) {
		self::$prefix = $prefix;
		self::$title = $title; 
		self::$version = $version;
		
		// Register settings
		self::register_settings();
		
		// Handle form submission
		if ( isset( $_POST['submit'] ) && isset( $_POST[self::$prefix . 'bunny_settings_nonce'] ) ) {
			if ( wp_verify_nonce( $_POST[self::$prefix . 'bunny_settings_nonce'], self::$prefix . 'save_bunny_settings' ) ) {
				self::save_settings();
			} else {
				wp_die( __( 'Security check failed. Please try again.' ) );
			}
		}

		// Get saved values
		$bunny_token = get_option( self::$prefix . 'bunny_token', '' );
		$bunny_library_id = get_option( self::$prefix . 'bunny_library_id', '' );
		$bunny_api_key = get_option( self::$prefix . 'bunny_api_key', '' );
		
		// Mask the sensitive values for display
		$bunny_token_display = self::mask_api_key($bunny_token);
		$bunny_api_key_display = self::mask_api_key($bunny_api_key);
		$bunny_library_id_display = self::mask_api_key($bunny_library_id);
		?>
		
		

			<div class="form-plugin-links" style="display: flex;">
				<form method="post" action="" style="width: 100%;">
					<?php wp_nonce_field( self::$prefix . 'save_bunny_settings', self::$prefix . 'bunny_settings_nonce' ); ?>
					
					<h2>Bunny Stream</h2>
					<table id="bricks-settings">
						<tbody>
							<tr>
								<th scope="row">
									<label><?php echo __( 'Caption Settings' ); ?></label>
									<p class="description"><?php echo __( 'Optional (for auto-populating captions from Bunny Stream videos)' ); ?></p>
								</th>
								<td>
									<label for="<?php echo self::$prefix; ?>bunny_library_id"><?php echo __( 'Video Library ID' ); ?></label>
									<div class="api-key-wrapper" style="position: relative;">
										<input type="text" id="<?php echo self::$prefix; ?>bunny_library_id" 
											name="<?php echo self::$prefix; ?>bunny_library_id" 
											value="" 
											placeholder="<?php echo !empty($bunny_library_id) ? esc_attr($bunny_library_id_display) : 'Enter your library ID'; ?>" 
											class="regular-text">
									</div>
										<p class="description"><?php echo __( 'Find this in your Bunny Stream dashboard under API.' ); ?></p
									<br>
									<p class="description"></p>
									<label for="<?php echo self::$prefix; ?>bunny_api_key"><?php echo __( 'API Key' ); ?></label>
									<div class="api-key-wrapper" style="position: relative;">
										<input type="text" id="<?php echo self::$prefix; ?>bunny_api_key" 
											name="<?php echo self::$prefix; ?>bunny_api_key" 
											value="" 
											placeholder="<?php echo !empty($bunny_api_key) ? esc_attr($bunny_api_key_display) : 'Enter your API key'; ?>" 
											class="regular-text">
										<?php if (!empty($bunny_api_key)): ?>
										<div class="api-key-notice" style="font-size: 12px; color: #666; margin-top: 5px;">
										</div>
										<?php endif; ?>
									</div>
									<p class="description"><?php echo __( 'Find this in your Bunny Stream dashboard under API.' ); ?></p
								</td>
							</tr>
							
							<tr>
								<th scope="row">
									<label><?php echo __( 'Security Settings' ); ?></label>
									<p class="description"><?php echo __( 'Optional for token-protected videos' ); ?></p>
								</th>
								<td>
									<label for="<?php echo self::$prefix; ?>bunny_token"><?php echo __( 'CDN Token Key' ); ?></label>
									<input type="text" id="<?php echo self::$prefix; ?>bunny_token" 
										name="<?php echo self::$prefix; ?>bunny_token" 
										value="" 
										placeholder="<?php echo !empty($bunny_token) ? esc_attr($bunny_token_display) : 'Enter your CDN token key (optional)'; ?>" 
										class="regular-text">
									<p class="description"><?php echo __( 'Find this in your Bunny Stream dashboard under Security settings.' ); ?></p>
								</td>
							</tr>
						</tbody>
					</table>
					
					<?php submit_button( null, $type = 'primary large' ); ?>
				</form>
			</div>
		<?php
	}
}
