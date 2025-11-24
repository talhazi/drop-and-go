<?php

namespace DebugToolkit;

use DebugToolkit\Error_Handler;
use DebugToolkit\Filesystem_Utils;

/**
 * Fired during plugin uninstallation.
 */
class Uninstaller {
    /**
     * Uninstall the plugin.
     */
    public static function uninstall() {

        Deactivator::deactivate();
        
        self::delete_options();
        
        self::remove_upload_directory();
    }

    /**
     * Delete all plugin options
     */
    private static function delete_options() {
        $options = [
            'debug_enabled',
            'debug_display',
            'debug_log',
            'viewer_password',
            'viewer_installed',
        ];

        foreach ($options as $option) {
            delete_option('debug_toolkit_' . $option);
        }
    }

    /**
     * Remove plugin upload directory
     */
    private static function remove_upload_directory() {
        $upload_dir = wp_upload_dir();
        $plugin_upload_dir = $upload_dir['basedir'] . '/wpdebugtoolkit';
        
        if (file_exists($plugin_upload_dir)) {
            Filesystem_Utils::recursively_delete_directory($plugin_upload_dir, true);
        }
    }
} 