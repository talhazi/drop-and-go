<?php

namespace DebugToolkit;

/**
 * Admin-specific functionality 
 */
class Admin {
    /**
     * The single instance of the class
     *
     * @var Admin
     */
    protected static $instance = null;

    /**
     * Main Admin Instance
     */
    public static function get_instance() {
        if (is_null(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Prevent cloning
     */
    private function __clone() {

    }

    /**
     * Prevent unserializing
     */
    public function __wakeup() {

    }

    /**
     * Constructor.
     */
    protected function __construct() {
        $this->define_admin_hooks();
    }

    /**
     * Register all of the hooks 
     */
    private function define_admin_hooks() {

        add_action('admin_menu', [$this, 'add_admin_menu']);
        
        add_action('admin_enqueue_scripts', [$this, 'enqueue_admin_assets']);
    }

    /**
     * Add menu items 
     */
    public function add_admin_menu() {
        add_menu_page(
            __('Debug Toolkit', 'wpdebugtoolkit'),
            __('Debug Toolkit', 'wpdebugtoolkit'),
            'manage_options',
            'wpdebugtoolkit',
            [$this, 'render_admin_page'],
            'dashicons-admin-tools',
            100
        );
    }

    /**
     * Render the admin page 
     */
    public function render_admin_page() {
        echo '<div id="wpdebugtoolkit-admin"></div>';
    }

    /**
     * Register js
     */
    public function enqueue_admin_assets($hook) {

        if ('toplevel_page_wpdebugtoolkit' !== $hook) {
            return;
        }

        wp_dequeue_script('svg-painter');

        wp_enqueue_script(
            'wpdebugtoolkit-admin',
            Constants::DBTK_URL . 'admin/react-build/static/js/main.js',
            ['wp-element', 'wp-components', 'wp-api-fetch'],
            Constants::DBTK_VERSION,
            true
        );

        wp_enqueue_style(
            'wpdebugtoolkit-admin',
            Constants::DBTK_URL . 'admin/react-build/static/css/main.css',
            [],
            Constants::DBTK_VERSION
        );

        wp_localize_script('wpdebugtoolkit-admin', 'debugToolkitSettings', [
            'root' => esc_url_raw(rest_url()),
            'nonce' => wp_create_nonce('wp_rest'),
            'homeUrl' => home_url(),
            'pluginUrl' => Constants::DBTK_URL,
            'version' => Constants::DBTK_VERSION,
            'isDebugEnabled' => defined('WP_DEBUG') && WP_DEBUG,
            'isDebugDisplayEnabled' => defined('WP_DEBUG_DISPLAY') && WP_DEBUG_DISPLAY,
            'isDebugLogEnabled' => defined('WP_DEBUG_LOG') && WP_DEBUG_LOG,
            'isUserLoggedIn' => is_user_logged_in(),
            'currentUserId' => get_current_user_id(),
            'viewer_installed' => $this->is_viewer_installed()
        ]);

        add_filter('admin_body_class', function($classes) {
            return "$classes wpdebugtoolkit-admin";
        });
    }

    /**
     * Check if viewer is installed
     * 
     * @return bool 
     */
    private function is_viewer_installed() {
        try {

            $viewer_manager = Core::get_service('viewer_manager');
            if ($viewer_manager) {
                return $viewer_manager->is_installed();
            }
        } catch (\Exception $e) {
            error_log('Debug Toolkit: Error checking viewer installation status: ' . $e->getMessage());
        }
        
        return file_exists(ABSPATH . Constants::DBTK_VIEWER_DIR . '/index.php');
    }
} 