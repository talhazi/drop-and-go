<?php
/**
 * BricksExtras Video Helper Functions
 *
 * @package BricksExtras
 */

namespace BricksExtras;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Video Helper Class
 * 
 * Provides functionality for video-related features including
 * secure video playback for various providers
 */
class BricksExtrasVideo {
    /**
     * Bunny.net Stream token authentication key
     * 
     * @var string
     */
    private static $bunny_token_key = '';

    /**
     * Initialize the video helper class
     */
    public static function init() {
        // Load settings from options
        self::$bunny_token_key = get_option('bricksextras_bunny_token', '');
        
        // Add filter to modify video URLs in the media player
        add_filter('bricksextras/video/src', [__CLASS__, 'maybe_secure_video_url'], 10, 2);
    }

    /**
     * Check if a URL is from Bunny Stream
     * 
     * @param string $url The URL to check
     * @return boolean True if the URL is from Bunny Stream
     */
    public static function is_bunny_stream_url($url) {
        return (strpos($url, 'vz-') !== false || 
                strpos($url, 'b-cdn.net') !== false);
    }
    

    /**
     * Extract Bunny Stream video ID and library ID from URL
     * 
     * @param string $url The Bunny Stream URL
     * @return array|false Array with 'video_id' and 'library_id' or false if not found
     */
    public static function extract_bunny_stream_ids($url) {
        // First, check for UUID pattern in the URL to extract video ID
        // This will work for both regular and pre-signed URLs
        if (preg_match('#([a-f0-9]{8}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{12})#', $url, $uuid_matches) && 
            preg_match('#vz-([^.]+)\.b-cdn\.net#', $url, $domain_matches)) {
            
            $video_id = $uuid_matches[1];
            $library_id = $domain_matches[1];
            
            // Determine if this is an HLS playlist URL
            $is_hls = (strpos($url, 'playlist.m3u8') !== false);
            
            return [
                'library_id' => $library_id,
                'video_id' => $video_id,
                'path' => '/' . $video_id . '/' . ($is_hls ? 'playlist.m3u8' : ''),
                'type' => $is_hls ? 'hls_playlist' : 'direct'
            ];
        }
        
        // Pattern for regular HLS playlist URLs ending with .m3u8 on Bunny CDN
        if (preg_match('#vz-([^.]+)\.b-cdn\.net/([^/]+)/([^\?]*\.m3u8)#', $url, $matches)) {
            return [
                'library_id' => $matches[1],
                'video_id' => $matches[2],
                'path' => '/' . $matches[2] . '/' . $matches[3],
                'type' => 'hls_playlist'
            ];
        }
        
        return false;
    }

    
    /**
     * Generate a signed URL for Bunny.net Stream using Bunny's official implementation
     * 
     * @param string $video_id The Bunny.net video ID
     * @param string $library_id The Bunny.net library ID
     * @param int $expiry Expiry time in seconds from now
     * @param string $url_type The URL type (direct, hls_playlist, etc.)
     * @param string $path Optional custom path
     * @return string|false The signed URL or false if validation fails
     */
    public static function get_bunny_stream_signed_url($video_id, $library_id, $expiry = 3600, $url_type = 'direct', $path = '') {
        $security_key = self::$bunny_token_key;
        
        // If token key is empty or invalid (should be a UUID format), return false
        if (empty($security_key) || empty($video_id) || empty($library_id)) {
            return false;
        }
        
        // Validate that the token key is in the correct format (UUID)
        if (!preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i', $security_key)) {
            error_log('Bunny Stream token key is not in the correct format (UUID)');
            return false;
        }
        
        // Determine the path based on URL type
        if (empty($path)) {
            if ($url_type === 'hls_playlist') {
                $path = "/{$video_id}/playlist.m3u8"; // Default to playlist.m3u8 for HLS
            } else {
                $path = "/{$video_id}"; // Default path for other types
            }
        }
        
        // Construct the base URL
        $url = "https://vz-{$library_id}.b-cdn.net{$path}";
        $expiration_time = $expiry; // Time in seconds from now
        $is_directory_token = true; // Use path-based token format for video delivery
        $path_allowed = dirname($path) . '/'; // Allow access to all files in this directory
        
        // Parse URL components
        $url_scheme = parse_url($url, PHP_URL_SCHEME);
        $url_host = parse_url($url, PHP_URL_HOST);
        $url_path = parse_url($url, PHP_URL_PATH);
        $url_query = parse_url($url, PHP_URL_QUERY);

        // Set up parameters
        $parameters = array();
        if ($url_query !== null) {
            parse_str($url_query, $parameters);
        }

        // Set the signature path
        $signature_path = $url_path;
        if ($path_allowed) {
            $signature_path = $path_allowed;
            $parameters["token_path"] = $signature_path;
        }

        // Set expiration time
        $expires = time() + $expiration_time;

        // Construct parameter data
        ksort($parameters); // Sort alphabetically, very important
        $parameter_data = "";
        $parameter_data_url = "";
        
        if (count($parameters) > 0) {
            foreach ($parameters as $key => $value) {
                if (strlen($parameter_data) > 0)
                    $parameter_data .= "&";

                $parameter_data_url .= "&";

                $parameter_data .= "{$key}=" . $value;
                $parameter_data_url .= "{$key}=" . urlencode($value);
            }
        }

        // Generate the token
        $hashableBase = $security_key . $signature_path . $expires . $parameter_data;

        // Generate the token using Bunny's exact method
        $token = hash('sha256', $hashableBase, true);
        $token = base64_encode($token);
        $token = strtr($token, '+/', '-_');
        $token = str_replace('=', '', $token);

        // Construct the final URL with path-based token
        if ($is_directory_token) {
            return "{$url_scheme}://{$url_host}/bcdn_token={$token}&expires={$expires}{$parameter_data_url}{$url_path}";
        } else {
            return "{$url_scheme}://{$url_host}{$url_path}?token={$token}{$parameter_data_url}&expires={$expires}";
        }
    }
    
    /**
     * Check if a video URL should be secured and if so, generate a secure URL
     * 
     * @param string $src The original video URL
     * @param array $settings The element settings
     * @return string The potentially modified video URL
     */
    public static function maybe_secure_video_url($src, $settings = []) {
        // Check if security is enabled in settings
        $enable_security = isset($settings['enableSecurity']) && $settings['enableSecurity'];
        
        // If security is not enabled, return the original URL
        if (!$enable_security) {
            return $src;
        }
        
        // Get expiry time from settings or use default
        $expiry = isset($settings['securityExpiry']) ? intval($settings['securityExpiry']) : 3600;
        
        // Check if this is a Bunny Stream URL
        if (self::is_bunny_stream_url($src)) {
            $ids = self::extract_bunny_stream_ids($src);
            
            if ($ids) {

                /**
                 * Filter to allow developers to add extra conditions for token generation
                 * 
                 * @param bool   $should_generate_token Whether to generate the secure token
                 * @param string $src                  The original video URL
                 * @param array  $ids                  The extracted Bunny Stream IDs
                 * @return bool  Whether to generate the secure token
                 */
                $should_generate_token = apply_filters('bricksextras/mediaplayer/generate_token', true, $src, $ids);
                
                // If the filter returns false, return an empty URL to prevent video playback
                if (!$should_generate_token) {
                    return '';
                }
                
                $secured_url = self::get_bunny_stream_signed_url(
                    $ids['video_id'], 
                    $ids['library_id'], 
                    $expiry,
                    isset($ids['type']) ? $ids['type'] : 'direct',
                    isset($ids['path']) ? $ids['path'] : ''
                );
                
                return $secured_url;
            } 
        } 
        
        return $src;
    }
    
}

// Initialize the class
BricksExtrasVideo::init();