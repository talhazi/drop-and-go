<?php
namespace RightPlace;

/**
 * LoginTokenService
 * 
 * Handles secure token-based authentication for internal WordPress requests.
 * Supports both long-term and one-time tokens.
 * Tokens are stored in user meta for better security and organization.
 */
class LoginTokenService {
    const TOKEN_HEADER = 'x-rightplace-login-token';
    const TOKEN_HEADER_NAME = 'HTTP_X_RIGHTPLACE_LOGIN_TOKEN';
    const TOKEN_TYPE_LONG_TERM = 'long_term';
    const TOKEN_TYPE_ONCE = 'once';
    const TOKEN_EXPIRY_LONG_TERM = 30 * DAY_IN_SECONDS; // 30 days
    const TOKEN_EXPIRY_ONCE = 5 * MINUTE_IN_SECONDS;    // 5 minutes
    const USER_META_KEY = '_rightplace_auth_tokens';

    /**
     * Generate a new token
     * 
     * @param string $type Token type (TOKEN_TYPE_LONG_TERM or TOKEN_TYPE_ONCE)
     * @param int $user_id User ID to associate with the token
     * @return string|false Generated token or false on failure
     */
    public function generate_token($type, $user_id) {
        if (!in_array($type, [self::TOKEN_TYPE_LONG_TERM, self::TOKEN_TYPE_ONCE])) {
            return false;
        }

        // Generate a cryptographically secure random token
        $token = wp_generate_password(32, false);
        
        // Store token in user meta with metadata
        $token_data = [
            'token' => wp_hash($token), // Hash the token before storing
            'type' => $type,
            'created_at' => time(),
            'expires_at' => time() + ($type === self::TOKEN_TYPE_LONG_TERM ? self::TOKEN_EXPIRY_LONG_TERM : self::TOKEN_EXPIRY_ONCE),
            'last_used' => null
        ];

        $tokens = get_user_meta($user_id, self::USER_META_KEY, true);
        if (!is_array($tokens)) {
            $tokens = [];
        }
        
        $tokens[$token_data['token']] = $token_data;
        update_user_meta($user_id, self::USER_META_KEY, $tokens);

        return $token; // Return the unhashed token for the client
    }

    /**
     * Validate a token and return associated user ID
     * 
     * @param string $token Token to validate
     * @return int|false User ID if valid, false otherwise
     */
    public function validate_token($token) {
        global $wpdb;
        
        // Hash the incoming token
        $hashed_token = wp_hash($token);
        
        // Find user with this token
        $user_id = $wpdb->get_var($wpdb->prepare(
            "SELECT user_id FROM {$wpdb->usermeta} 
            WHERE meta_key = %s 
            AND meta_value LIKE %s",
            self::USER_META_KEY,
            '%' . $wpdb->esc_like($hashed_token) . '%'
        ));

        if (!$user_id) {
            return false;
        }

        $tokens = get_user_meta($user_id, self::USER_META_KEY, true);
        if (!is_array($tokens) || !isset($tokens[$hashed_token])) {
            return false;
        }

        $token_data = $tokens[$hashed_token];
        
        // Check if token is expired
        if (time() > $token_data['expires_at']) {
            unset($tokens[$hashed_token]);
            update_user_meta($user_id, self::USER_META_KEY, $tokens);
            return false;
        }

        // Update last used timestamp
        $token_data['last_used'] = time();
        $tokens[$hashed_token] = $token_data;
        update_user_meta($user_id, self::USER_META_KEY, $tokens);

        // For one-time tokens, delete after use
        if ($token_data['type'] === self::TOKEN_TYPE_ONCE) {
            unset($tokens[$hashed_token]);
            update_user_meta($user_id, self::USER_META_KEY, $tokens);
        }

        return $user_id;
    }

    /**
     * Clean up expired tokens for all users
     */
    public function cleanup_expired_tokens() {
        global $wpdb;
        
        $now = time();
        $users = get_users(['fields' => 'ID']);
        
        foreach ($users as $user_id) {
            $tokens = get_user_meta($user_id, self::USER_META_KEY, true);
            if (!is_array($tokens)) {
                continue;
            }
            
            $changed = false;
            foreach ($tokens as $token => $data) {
                if ($now > $data['expires_at']) {
                    unset($tokens[$token]);
                    $changed = true;
                }
            }
            
            if ($changed) {
                update_user_meta($user_id, self::USER_META_KEY, $tokens);
            }
        }
    }

    /**
     * Initialize the service
     */
    public function init() {
        // Add cleanup hook
        add_action('rightplace_cleanup_tokens', [$this, 'cleanup_expired_tokens']);
        
        // Schedule cleanup if not already scheduled
        if (!wp_next_scheduled('rightplace_cleanup_tokens')) {
            wp_schedule_event(time(), 'daily', 'rightplace_cleanup_tokens');
        }

        // Add authentication filter with higher priority
        add_filter('determine_current_user', [$this, 'authenticate_token'], 5);

        // Add init hook to ensure session is properly set up
        add_action('init', [$this, 'setup_session'], 0);
    }

    /**
     * Set up the WordPress session if we have a valid token
     */
    public function setup_session() {
        // Skip if already logged in
        if (is_user_logged_in()) {
            return;
        }

        // Check for token in header
        $token = isset($_SERVER[self::TOKEN_HEADER_NAME]) ? $_SERVER[self::TOKEN_HEADER_NAME] : null;
        if (!$token) {
            return;
        }

        // Validate token
        $token_user_id = $this->validate_token($token);
        if (!$token_user_id) {
            return;
        }

        // Get user and verify it exists
        $user = get_user_by('id', $token_user_id);
        if (!$user) {
            return;
        }

        rp_dev_log('RightPlace: Setting up session for user: ' . $user->user_login);

        // Clear any existing auth cookies
        wp_clear_auth_cookie();

        // Set up the session
        wp_set_current_user($user->ID);
        wp_set_auth_cookie($user->ID, true);

        // Set the user's roles and capabilities
        $user->set_role($user->roles[0]);

        // Trigger login action
        do_action('wp_login', $user->user_login, $user);
    }

    /**
     * WordPress authentication filter
     * 
     * @param int|false $user_id Current user ID
     * @return int|false User ID if authenticated, false otherwise
     */
    public function authenticate_token($user_id) {
        // Skip if already authenticated
        if ($user_id) {
            return $user_id;
        }

        // Check for token in header
        $token = isset($_SERVER[self::TOKEN_HEADER_NAME]) ? $_SERVER[self::TOKEN_HEADER_NAME] : null;
        if (!$token) {
            return false;
        }

        // Validate token
        $token_user_id = $this->validate_token($token);
        if (!$token_user_id) {
            return false;
        }

        // Get user and verify it exists
        $user = get_user_by('id', $token_user_id);
        if (!$user) {
            return false;
        }

        rp_dev_log( 'RightPlace: -------------------------------> Authenticated user: ' . $user->user_login );

        return $user->ID;
    }
} 