<?php

namespace DebugToolkit;

use DebugToolkit\Services\Debug_Manager;
use DebugToolkit\Filesystem_Utils;

/**
 * Plugin activation
 */
class Activator {

    /**
     * Activate the plugin
     */
    public static function activate() {

        self::create_directories();
        
        self::set_default_options();

        self::configure_debug_settings();
    }

    /**
     * Configure debug settings
     */
    private static function configure_debug_settings() {
        try {

            $config_file = ABSPATH . 'wp-config.php';
            
            if (!file_exists($config_file)) {
                return;
            }
            
            // Create a backup
            $backup_file = $config_file . '.wpdebugtoolkit-backup';
            if (!file_exists($backup_file) && is_readable($config_file)) {
                Error_Handler::fs_operation(
                    function() use ($config_file, $backup_file) {
                        return copy($config_file, $backup_file);
                    },
                    __('Failed to create backup of wp-config.php during activation.', 'wpdebugtoolkit'),
                    true 
                );
            }
            
            // Store original values of debug constants
            $debug_constants = ['WP_DEBUG', 'WP_DEBUG_DISPLAY', 'WP_DEBUG_LOG'];
            foreach ($debug_constants as $constant) {
                $option_key = 'debug_toolkit_' . strtolower($constant) . '_original';
                
                // Only store if we haven't stored it
                if (get_option($option_key) === false) {
                    if (defined($constant)) {
                        update_option($option_key, constant($constant));
                    } else {
                        update_option($option_key, false);
                    }
                }
            }
        } catch (\Exception $e) {
            Error_Handler::log_exception($e, 'Activation');
        }
    }

    /**
     * Create directories
     */
    private static function create_directories() {
        $upload_dir = wp_upload_dir();
        $plugin_upload_dir = $upload_dir['basedir'] . '/wpdebugtoolkit';
        
        try {
            Filesystem_Utils::ensure_directory($plugin_upload_dir);
        } catch (\Exception $e) {
            Error_Handler::log_exception($e, 'Plugin Directory Creation');
        }
    }

    /**
     * Set default options
     */
    private static function set_default_options() {
        $defaults = [
            'debug_enabled' => false,
            'debug_display' => false,
            'debug_log' => false,
            'viewer_password' => '',
            'viewer_installed' => false,
        ];

        foreach ($defaults as $key => $value) {
            if (get_option('debug_toolkit_' . $key) === false) {
                add_option('debug_toolkit_' . $key, $value);
            }
        }
    }
} 