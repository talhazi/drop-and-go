<?php

namespace BricksExtras;

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class BricksExtrasMedia {
    
    /**
     * Initialize the poster handler
     */
    public function init_poster_handler() {
        add_action('wp_ajax_check_poster', array($this, 'check_media'));
    }
    
    /**
     * Initialize the waveform handler
     */
    public function init_waveform_handler() {
        add_action('wp_ajax_save_waveform_data', array($this, 'ajax_save_waveform_data'));
        add_action('wp_ajax_get_waveform_data', array($this, 'ajax_get_waveform_data'));
        add_action('wp_ajax_nopriv_get_waveform_data', array($this, 'ajax_get_waveform_data'));
    }
    
    /**
     * Get local poster if exists, create one if not. 
     */
    public function check_media() {

         // Verify nonce for security
         check_ajax_referer( 'media-nonce', 'sec' );
         
         if (!current_user_can('edit_posts')) {
             wp_send_json_error(array('message' => 'You do not have permission to perform this action'));
             wp_die();
         } 

         $user_id = get_current_user_id();
         $rate_limit_key = 'bricksextras_poster_rate_limit_' . $user_id;
         $current_count = get_transient($rate_limit_key);
         
         if ($current_count === false) {
             set_transient($rate_limit_key, 1, 60);
         } elseif ($current_count >= 20) {
             wp_die();
         } else {
             set_transient($rate_limit_key, $current_count + 1, 60);
         } 

         // Sanitize and validate inputs
         $externalPosterSrc = isset( $_POST['poster_src'] ) ? esc_url_raw( sanitize_url( $_POST['poster_src'] ) ) : false;
         $videoSrc = isset( $_POST['video_src'] ) ? esc_url_raw( sanitize_url( $_POST['video_src'] ) ) : false;
         $videoID = $videoSrc ? sanitize_file_name( \BricksExtras\Helpers::get_video_id_from_url( $videoSrc ) ) : false;
 
         // Validate required inputs
         if ( !$externalPosterSrc || !$videoID ) {
             wp_send_json_error( array( 'message' => 'Missing required parameters' ) );
             wp_die();
         }
 
         $is_thumbnail_url = false;
         
         $url_parts = parse_url($externalPosterSrc);
         
         if ($url_parts && isset($url_parts['host'])) {
             $host = strtolower($url_parts['host']);
             $path = isset($url_parts['path']) ? $url_parts['path'] : '';
             
             if ($host === 'i.ytimg.com' && preg_match('#^/vi(?:_webp)?/[a-zA-Z0-9_-]+/[a-zA-Z0-9_-]+\.(jpg|jpeg|png|webp)$#i', $path)) {
                 $is_thumbnail_url = true;
             }
             
             if ($host === 'i.vimeocdn.com' && preg_match('#^/video/[0-9]+(?:-[a-zA-Z0-9]+)*(?:-d)?_[0-9]+x[0-9]+\.(jpg|jpeg|png|webp)$#i', $path)) {
                 $is_thumbnail_url = true;
             }
         }
         
         if (!$is_thumbnail_url) {
             wp_send_json_error(array('message' => 'Invalid format'));
             wp_die();
         }
 
         // Validate URL scheme (only allow http and https)
         $scheme = parse_url( $externalPosterSrc, PHP_URL_SCHEME );
         if ( !in_array( $scheme, array( 'http', 'https' ) ) ) {
             wp_send_json_error( array( 'message' => 'Invalid URL scheme' ) );
             wp_die();
         }
 
         // Get upload directory information
         $thumbnail_dir_info = wp_get_upload_dir();
         if ( is_wp_error( $thumbnail_dir_info ) ) {
             wp_send_json_error( array( 'message' => 'Could not determine upload directory' ) );
             wp_die();
         }
 
         $mediaPlayerDir = $thumbnail_dir_info['basedir'] . '/bricksextras/posters';
         $mediaPlayerUrl = $thumbnail_dir_info['baseurl'] . '/bricksextras/posters';
 
         // Create main directory with proper permissions if it doesn't exist
         if ( !file_exists( $mediaPlayerDir ) ) {
             $dir_created = wp_mkdir_p( $mediaPlayerDir );
             if ( !$dir_created ) {
                 wp_send_json_error( array( 'message' => 'Failed to create media directory' ) );
                 wp_die();
             }
         }
 
         // Create video-specific directory
         $newVideoDir = $thumbnail_dir_info['basedir'] . '/bricksextras/posters/' . $videoID;
         $newVideoURL = $videoID;
 
         if ( !file_exists( $newVideoDir ) ) {
             $dir_created = wp_mkdir_p( $newVideoDir );
             if ( !$dir_created ) {
                 wp_send_json_error( array( 'message' => 'Failed to create video directory' ) );
                 wp_die();
             }
         }
 
         // Get filename from URL path
         $path = parse_url( $externalPosterSrc, PHP_URL_PATH );
         $filename = basename( $path );
         
 
         $filename = sanitize_file_name( $filename );
         if ( empty( $filename ) ) {
             $filename = 'poster-' . $videoID . '.jpg'; // Fallback to a safe filename
         }
         
         $target_file = $newVideoDir . '/' . $filename;
 
         // Download file using WordPress HTTP API
         $response = wp_safe_remote_get( $externalPosterSrc, array(
             'timeout'     => 30,
             'redirection' => 5,
             'sslverify'   => true,
             'stream'      => true,
             'filename'    => $target_file,
         ) );
 
         // Check for errors
         if ( is_wp_error( $response ) ) {
             wp_send_json_error( array( 'message' => 'Failed to download poster image: ' . $response->get_error_message() ) );
             wp_die();
         }
 
         // Verify HTTP response code
         $http_code = wp_remote_retrieve_response_code( $response );
         if ( $http_code !== 200 ) {
             wp_send_json_error( array( 'message' => 'Failed to download poster image: HTTP error ' . $http_code ) );
             wp_die();
         }
 
         // Verify file size (limit to 1MB)
         $filesize = filesize( $target_file );
         if ( $filesize > 1024 * 1024 ) {
             @unlink( $target_file ); // Delete the oversized file
             wp_send_json_error( array( 'message' => 'Poster image too large (max 1MB)' ) );
             wp_die();
         }
 
         // Verify file is an image
         $image_info = @getimagesize( $target_file );
         if ( !$image_info ) {
             @unlink( $target_file ); // Delete the non-image file
             wp_send_json_error( array( 'message' => 'Downloaded file is not a valid image' ) );
             wp_die();
         }
 
         // Only allow common image formats
         $allowed_mime_types = array( 'image/jpeg', 'image/png', 'image/gif', 'image/webp' );
         if ( !in_array( $image_info['mime'], $allowed_mime_types ) ) {
             @unlink( $target_file ); // Delete the file with disallowed mime type
             wp_send_json_error( array( 'message' => 'Invalid image format' ) );
             wp_die();
         }
 
         // Update options to store poster URL
         $currentPosters = array();
 
         if ( !get_option( 'bricksextras_media_poster_url' ) ) {
             // Create new option if it doesn't exist
             $currentPosters[$videoID] = $newVideoURL . '/' . $filename;
             $updatedPosters = wp_json_encode( $currentPosters );
             add_option( 'bricksextras_media_poster_url', $updatedPosters );
         } else {
             // Update existing option
             $currentPosters = json_decode( get_option( 'bricksextras_media_poster_url' ), TRUE );
             if ( !is_array( $currentPosters ) ) {
                 $currentPosters = array(); // Ensure we have a valid array
             }
             
             $currentPosters[$videoID] = $newVideoURL . '/' . $filename;
             $updatedPosters = wp_json_encode( $currentPosters );
 
             // Only update if external source (not from this site)
             if ( !str_contains( $externalPosterSrc, get_home_url() ) ) {
                 update_option( 'bricksextras_media_poster_url', $updatedPosters );
             }
         }
 
         // Return success response
         wp_send_json_success( array(
             'newVideoURL' => $newVideoURL,
         ) );
 
         wp_die();

    }

    /**
	 * Save waveform data to a JSON file
	 * 
	 * @param string $audio_url URL of the audio file
	 * @param array $waveform_data Array of waveform peaks
	 * @param string $player_id Optional player ID
	 * @return array Response with success/error status and message
	 */
	public static function save_waveform_data($audio_url, $waveform_data, $player_id = '') {
		
		// Validate inputs
		if (empty($audio_url) || empty($waveform_data)) {
			return array(
				'success' => false,
				'message' => 'Missing required data'
			);
		}
		
		// Validate waveform data structure
		if (!self::is_valid_waveform_data($waveform_data)) {
			return array(
				'success' => false,
				'message' => 'Invalid waveform data structure'
			);
		}

		// Strip URL fragments (anything after #) to ensure same audio files share waveform data
		$clean_audio_url = strtok($audio_url, '#');
		
		// Create a unique filename based on the URL only (not player ID)
		// This allows sharing waveform data between players with the same audio
		$url_hash = md5($clean_audio_url);
		$filename = sanitize_file_name($url_hash . '.json');
		
		// Get the upload directory
		$upload_dir = wp_upload_dir();
		$waveform_dir = $upload_dir['basedir'] . '/bricksextras/waveforms';
		
		// Create directory if it doesn't exist
		if (!file_exists($waveform_dir)) {
			wp_mkdir_p($waveform_dir);
			
			// Create an index.php file to prevent directory listing
			file_put_contents($waveform_dir . '/index.php', '<?php // Silence is golden');
		}
		
		// Get additional data from POST if available
		$full_duration = isset($_POST['full_duration']) ? floatval($_POST['full_duration']) : null;
		$data_resolution = isset($_POST['data_resolution']) ? intval($_POST['data_resolution']) : 2;
		
		// Create the data to save
		$data_to_save = array(
			'audio_url' => $audio_url,
			'waveform_data' => $waveform_data, // Full audio waveform data
			'full_duration' => $full_duration,
			'data_resolution' => $data_resolution,
			'timestamp' => time()
		);
		
		// Save the file
		$file_path = $waveform_dir . '/' . $filename;
		
		$json_data = json_encode($data_to_save);
		
		// Check file size limit (50KB)
		$max_size = 51200; // 50KB in bytes
		if (strlen($json_data) > $max_size) {
			return array(
				'success' => false,
				'message' => 'Waveform data exceeds maximum allowed size (50KB)'
			);
		} 
		
		$saved = file_put_contents($file_path, $json_data);
		
		if ($saved) {
			return array(
				'success' => true,
				'message' => 'Waveform data saved successfully',
				'file' => $filename,
				'url' => $upload_dir['baseurl'] . '/bricksextras/waveforms/' . $filename,
				'file_size' => strlen($json_data)
			);
		} else {
			return array(
				'success' => false,
				'message' => 'Failed to save waveform data'
			);
		}
	}

	/**
	 * Validate waveform data structure
	 *
	 * @param array $data The waveform data to validate
	 * @return bool True if data is valid, false otherwise
	 */
	public static function is_valid_waveform_data($data) {
		// Check if data is an array
		if (!is_array($data)) {
			return false;
		}
		
		// Check required properties
		if (!isset($data['scale']) || !is_numeric($data['scale'])) {
			return false;
		}
		
		// Check min/max arrays
		if (!isset($data['min']) || !isset($data['max']) || !is_array($data['min']) || !is_array($data['max'])) {
			return false;
		}
		
		// Check that arrays have same length and are not empty
		if (empty($data['min']) || count($data['min']) !== count($data['max'])) {
			return false;
		}
		
		// Check that arrays contain only numbers
		foreach ($data['min'] as $val) {
			if (!is_numeric($val)) {
				return false;
			}
		}
		
		foreach ($data['max'] as $val) {
			if (!is_numeric($val)) {
				return false;
			}
		}
		
		return true;
	}

	/**
	 * Get saved audio waveform data
	 *
	 * @param string $audio_url URL of the audio file
	 * @param string $player_id Optional player identifier to make the cache file unique
	 * @return array Waveform data or error message
	 */
	public static function get_waveform_data($audio_url, $player_id = '') {
		// Validate input
		if (empty($audio_url)) {
			return array(
				'success' => false,
				'message' => 'Missing audio URL'
			);
		}
		
		// Strip URL fragments (anything after #) to ensure same audio files share waveform data
		$clean_audio_url = strtok($audio_url, '#');
		
		// Create the filename based on the URL only (not player ID)
		// This allows sharing waveform data between players with the same audio
		$url_hash = md5($clean_audio_url);
		$filename = $url_hash . '.json';
		
		// Get the upload directory
		$upload_dir = wp_upload_dir();
		$file_path = $upload_dir['basedir'] . '/bricksextras/waveforms/' . $filename;
		
		// Check if file exists
		if (file_exists($file_path)) {
			$file_content = file_get_contents($file_path);
			$waveform_data = json_decode($file_content, true);
			
			// Validate the waveform data structure
			if ($waveform_data === null) {
				return array(
					'success' => false,
					'message' => 'Invalid JSON format in waveform data file'
				);
			}
			
			// Check if we have the waveform_data property and validate it
			if (!isset($waveform_data['waveform_data']) || !self::is_valid_waveform_data($waveform_data['waveform_data'])) {
				return array(
					'success' => false,
					'message' => 'Invalid waveform data structure'
				);
			}
			
			return array(
				'success' => true,
				'data' => $waveform_data
			);
		} else {
			return array(
				'success' => false,
				'message' => 'Waveform data not found'
			);
		}
	}

	

	/**
	 * AJAX handler for saving waveform data
	 */
	public static function ajax_save_waveform_data() {
		// Verify nonce for security
		if (!isset($_POST['security']) || !wp_verify_nonce($_POST['security'], 'media-nonce')) {
			wp_send_json_error('Security check failed');
		}
		
		if (!current_user_can('edit_posts')) {
			wp_send_json_error('Authentication required');
		}
		
		$user_id = get_current_user_id();
		$rate_limit_key = 'bricksextras_waveform_rate_limit_' . $user_id;
		$current_count = get_transient($rate_limit_key);
		
		if ($current_count === false) {
			set_transient($rate_limit_key, 1, 60);
		} elseif ($current_count >= 20) {
			wp_die();
		} else {
			set_transient($rate_limit_key, $current_count + 1, 60);
		} 
		
		$audio_url = isset($_POST['audio_url']) ? esc_url_raw($_POST['audio_url']) : '';
		if (empty($audio_url)) {
			wp_send_json_error('Missing audio URL');
		}

		$player_id = isset($_POST['player_id']) ? sanitize_text_field($_POST['player_id']) : '';
		$full_duration = isset($_POST['full_duration']) ? floatval($_POST['full_duration']) : null;
		$data_resolution = isset($_POST['data_resolution']) ? intval($_POST['data_resolution']) : 2;
		
		// Get waveform data from POST
		if (isset($_POST['waveform_data']) && substr($_POST['waveform_data'], 0, 1) === '{' && substr($_POST['waveform_data'], -1) === '}') {
			$waveform_data = wp_unslash($_POST['waveform_data']);
		} else {
			wp_send_json_error('Invalid waveform data format');
			return;
		}
		
		// Decode the JSON string to get the waveform peaks array
		$waveform_peaks = null;
		if (!empty($waveform_data)) {
			$waveform_peaks = json_decode($waveform_data, true);
			
			// Check if JSON decode was successful
			if ($waveform_peaks === null) {
				wp_send_json_error('Invalid JSON format in waveform data');
				return;
			}
			
			// Validate the waveform data structure
			if (!self::is_valid_waveform_data($waveform_peaks)) {
				wp_send_json_error('Invalid waveform data structure');
				return;
			}
			
			// Log a sample of the decoded data
			if (count($waveform_peaks['min']) > 0) {
				$sample = array(
					'scale' => $waveform_peaks['scale'],
					'min_sample' => array_slice($waveform_peaks['min'], 0, 3),
					'max_sample' => array_slice($waveform_peaks['max'], 0, 3)
				);
			}
		} else {
			wp_send_json_error('Empty waveform data');
			return;
		}
		
		// Create the data to save
		$data_to_save = array(
			'audio_url' => $audio_url,
			'waveform_data' => $waveform_peaks,
			'full_duration' => $full_duration,
			'data_resolution' => $data_resolution,
			'timestamp' => time()
		);
		
		// Save the file
		$result = self::save_waveform_data($audio_url, $waveform_peaks, $player_id);
		
		if ($result['success']) {
			wp_send_json_success($result);
		} else {
			wp_send_json_error($result['message']);
		}
	}

	/**
	 * AJAX handler for retrieving waveform data
	 */
	public static function ajax_get_waveform_data() {
		// Verify nonce for security
		if (!isset($_GET['security']) || !wp_verify_nonce($_GET['security'], 'media-nonce')) {
			wp_send_json_error('Security check failed');
		}
		
		$audio_url = isset($_GET['audio_url']) ? esc_url_raw($_GET['audio_url']) : '';
		$player_id = isset($_GET['player_id']) ? sanitize_text_field($_GET['player_id']) : '';
		
		$result = self::get_waveform_data($audio_url, $player_id);
		
		if ($result['success']) {
			wp_send_json_success($result['data']);
		} else {
			wp_send_json_error($result['message']);
		}
	}
}
