<?php

namespace BricksExtras;

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * BricksExtras Bunny Stream Integration
 * 
 * Handles integration with Bunny Stream API for captions and other functionality
 */
class BricksExtrasBunnyStream {
    
    /**
     * Initialize the class
     */
    public function init() {
        add_action('rest_api_init', array($this, 'register_rest_routes'));
    }

    /**
     * Register REST API routes
     */
    public function register_rest_routes() {
        // Endpoint to get caption metadata
        register_rest_route( 'bricksextras/v1', '/bunny-captions/(?P<library_id>[\w-]+)/(?P<video_id>[\w-]+)', [
            'methods'  => 'GET',
            'callback' => [ $this, 'get_bunny_captions' ],
            'permission_callback' => [ $this, 'verify_bunny_captions_request' ],
        ] );
    } 
    
    /**
     * Verify that the request for captions is legitimate
     * 
     * @param WP_REST_Request $request Request object
     * @return bool Whether the request is valid
     */
    public function verify_bunny_captions_request( $request ) { 

        $nonce = $request->get_header( 'X-WP-Nonce' );

        if ( empty( $nonce ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $nonce ) ), 'wp_rest' ) ) {
            return false; 
        }
        
        // Verify request comes from our site
        $referer = $request->get_header( 'Referer' );
        if ( $referer && strpos( $referer, get_site_url() ) === 0 ) {
            return true;
        } 
        
        return false;
    } 

    /**
     * Get captions for a Bunny Stream video
     * 
     * @param WP_REST_Request $request Request object
     * @return WP_REST_Response|WP_Error Response object or error
     */
    public function get_bunny_captions( $request ) {
        // Get parameters from request
        $cdn_library_id = $request->get_param( 'library_id' );
        $video_id = $request->get_param( 'video_id' );

        // Validate required parameters
        if (empty($cdn_library_id) || empty($video_id)) {
            return new \WP_Error(
                'missing_parameters',
                'Missing required parameters: library_id and video_id are required',
                ['status' => 400]
            );
        }
        
        // Validate parameter format
        if (!preg_match('/^[\w\-]+$/', $video_id) || !preg_match('/^[\w\-]+$/', $cdn_library_id)) {
            return new \WP_Error(
                'invalid_parameters',
                'Invalid parameter format',
                ['status' => 400]
            );
        }

        $client_ip = '';

        if ( ! empty( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ) {
            $forwarded_ips = explode( ',', wp_unslash( $_SERVER['HTTP_X_FORWARDED_FOR'] ) );
            $client_ip     = trim( $forwarded_ips[0] );
        }

        if ( '' === $client_ip && ! empty( $_SERVER['REMOTE_ADDR'] ) ) {
            $client_ip = wp_unslash( $_SERVER['REMOTE_ADDR'] );
        } 

        if ( '' !== $client_ip ) {
            $client_ip = sanitize_text_field( $client_ip );

            if ( ! rest_is_ip_address( $client_ip ) ) {
                $client_ip = '';
            }
        } 

        if ( '' !== $client_ip ) { 
            $rate_limit_window = (int) apply_filters( 'bricksextras/bunny_rate_window', MINUTE_IN_SECONDS * 1 );
            $rate_limit_max    = (int) apply_filters( 'bricksextras/bunny_rate_max', 20 );

            if ( $rate_limit_window > 0 && $rate_limit_max > 0 ) {
                $rate_key       = 'bricksextras_bunny_rate_' . md5( $client_ip );
                $request_count  = (int) get_transient( $rate_key );
                $request_count++;

                set_transient( $rate_key, $request_count, absint( $rate_limit_window ) );

                if ( $request_count > absint( $rate_limit_max ) ) {
                    return new \WP_Error(
                        'rate_limited',
                        __( 'Too many requests', 'bricksextras' ),
                        [ 'status' => 429 ]
                    );
                }
            }
        } 

        // Get the numeric library ID from settings for API calls
        $library_id = $this->get_bunny_library_id();
        
        if (empty($library_id)) {
            return new \WP_Error(
                'missing_library_id',
                'Bunny Stream library ID is not configured in settings',
                ['status' => 400]
            );
        }
        
        // Get API key from settings
        $api_key = $this->get_bunny_api_key();
        
        if (empty($api_key)) {
            return new \WP_Error( 'missing_api_key', 'Bunny Stream API key is not configured in settings', [ 'status' => 400 ] );
        }

        $cache_key = 'bricksextras_bunny_captions_' . md5( $cdn_library_id . '|' . $video_id );
        $cached_response = get_transient( $cache_key ); 

        if ( is_array( $cached_response ) ) {
            return new \WP_REST_Response( $cached_response );
        }
        
        // Get video details from Bunny Stream API
        $video_data = $this->get_video_details( $library_id, $video_id, $api_key );
        
        if ( is_wp_error( $video_data ) ) {
            return $video_data;
        }
        
        // Extract captions from video data - use the CDN library ID for the CDN hostname
        $captions = $this->extract_captions_from_video_data( $video_data, $cdn_library_id, $video_id ); 

        $response_data = [
            'success'  => true,
            'video'    => [
                'id'    => $video_id,
                'title' => $video_data['title'] ?? 'Unknown',
            ],
            'captions' => $captions,
            'hasAiGeneratedCaptions' => isset( $video_data['hasAiGeneratedCaptions'] ) ? $video_data['hasAiGeneratedCaptions'] : false,
        ];

        $cache_ttl = apply_filters( 'bricksextras/bunny_captions_cache_ttl', MINUTE_IN_SECONDS * 20 ); 
        set_transient( $cache_key, $response_data, absint( $cache_ttl ) ); 

        return new \WP_REST_Response( $response_data ); 
    }

    /**
     * Get Bunny Stream API key from settings
     * 
     * @return string API key or empty string if not set
     */
    private function get_bunny_api_key() {
        return get_option( 'bricksextras_bunny_api_key', '' );
    }
    
    /**
     * 
     * Get Bunny Stream library ID from settings
     * 
     * @return string Library ID or empty string if not set
     */
    private function get_bunny_library_id() {
        return get_option( 'bricksextras_bunny_library_id', '' );
    }

    /**
     * Get video details from Bunny Stream API
     * 
     * @param string $library_id Bunny Stream library ID
     * @param string $video_id Bunny Stream video ID
     * @param string $api_key Bunny Stream API key
     * @return array|WP_Error Video data or error
     */
    private function get_video_details( $library_id, $video_id, $api_key ) {
        // API endpoint to get video details - library_id should be numeric
        $endpoint = "https://video.bunnycdn.com/library/{$library_id}/videos/{$video_id}";
        
        $response = wp_remote_get( $endpoint, [
            'headers' => [
                'AccessKey' => $api_key,
                'Accept'    => 'application/json',
            ],
        ] );
        
        if ( is_wp_error( $response ) ) {
            $error_message = $response->get_error_message();
            return new \WP_Error( 'api_error', 'Error fetching video data: ' . $error_message, [ 'status' => 500 ] );
        }
        
        $status_code = wp_remote_retrieve_response_code( $response );
        
        if ($status_code !== 200) {
            $body = wp_remote_retrieve_body($response);
            return new \WP_Error( 'api_error', 'Error from Bunny Stream API: ' . $status_code, [ 'status' => $status_code ] );
        }
        
        $body = wp_remote_retrieve_body($response);
        
        $data = json_decode($body, true);
        if (!is_array($data)) {
            return new \WP_Error('invalid_response', 'Invalid response from Bunny Stream API', ['status' => 500]);
        }
        
        return $data;
    }

    /**
     * Get native language name from language code
     * 
     * @param string $lang_code Language code (e.g., 'en', 'fr', 'en-auto')
     * @return string Native language name
     */
    private function get_native_language_name( $lang_code ) {
        // Remove the -auto suffix if present
        $base_lang = preg_replace('/-auto$/', '', $lang_code);
        
        $native_names = [
            'en' => 'English',
            'fr' => 'Français',
            'de' => 'Deutsch',
            'es' => 'Español',
            'it' => 'Italiano',
            'pt' => 'Português',
            'nl' => 'Nederlands',
            'ru' => 'Русский',
            'ja' => '日本語',
            'zh' => '中文',
            'ar' => 'العربية',
            'hi' => 'हिन्दी',
            'ko' => '한국어',
            'sv' => 'Svenska',
            'no' => 'Norsk',
            'da' => 'Dansk',
            'fi' => 'Suomi',
            'pl' => 'Polski',
            'tr' => 'Türkçe',
            'cs' => 'Čeština',
            'hu' => 'Magyar',
            'el' => 'Ελληνικά',
            'he' => 'עברית',
            'th' => 'ไทย',
            'vi' => 'Tiếng Việt',
            'id' => 'Bahasa Indonesia',
            'ms' => 'Bahasa Melayu',
            'ro' => 'Română',
            'uk' => 'Українська',
            'bg' => 'Български',
            'hr' => 'Hrvatski',
            'sr' => 'Српски',
            'sk' => 'Slovenčina',
            'sl' => 'Slovenščina',
            'et' => 'Eesti',
            'lv' => 'Latviešu',
            'lt' => 'Lietuvių',
            'fa' => 'فارسی',
            'af' => 'Afrikaans',
            'sq' => 'Shqip',
            'hy' => 'Հայերեն',
            'az' => 'Azərbaycan',
            'eu' => 'Euskara',
            'be' => 'Беларуская',
            'bn' => 'বাংলা',
            'bs' => 'Bosanski',
            'ca' => 'Català',
            'cy' => 'Cymraeg',
            'ka' => 'ქართული',
            'gl' => 'Galego',
            'gu' => 'ગુજરાતી',
            'ha' => 'Hausa',
            'is' => 'Íslenska',
            'ga' => 'Gaeilge',
            'kk' => 'Қазақ',
            'km' => 'ខ្មែរ',
            'lo' => 'ລາວ',
            'mk' => 'Македонски',
            'mn' => 'Монгол',
            'mr' => 'मराठी',
            'ne' => 'नेपाली',
            'pa' => 'ਪੰਜਾਬੀ',
            'si' => 'සිංහල',
            'ta' => 'தமிழ்',
            'te' => 'తెలుగు',
            'ur' => 'اردو',
            'uz' => 'Oʻzbek',
            'zu' => 'isiZulu',
        ];
        
        return isset($native_names[$base_lang]) ? $native_names[$base_lang] : $lang_code;
    }
    
    /**
     * Extract captions from video data
     * 
     * @param array $video_data Video data from Bunny Stream API
     * @param string $library_id Bunny Stream library ID
     * @param string $video_id Bunny Stream video ID
     * @return array Captions data
     */
    private function extract_captions_from_video_data( $video_data, $library_id, $video_id ) {
        $captions = [];
        
        // For the CDN hostname, we use the library ID directly
        $cdn_hostname = "vz-{$library_id}.b-cdn.net";
        
        // Check for captions in direct captions property
        if ( isset( $video_data['captions'] ) && is_array( $video_data['captions'] ) ) {
            foreach ( $video_data['captions'] as $caption ) {
                if ( isset( $caption['srclang'] ) ) {
                    $lang = $caption['srclang'];
                    $native_name = $this->get_native_language_name($lang);
                    
                    $captions[ $lang ] = [
                        'language' => $lang,
                        'label'    => $native_name,
                        'url'      => esc_url("https://{$cdn_hostname}/{$video_id}/captions/{$lang}.vtt"),
                    ];
                }
            }
        }

        return $captions;
    }
    

}