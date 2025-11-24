<?php

namespace DebugToolkit;

use DebugToolkit\Error_Handler;
use DebugToolkit\Filesystem_Utils;
use DebugToolkit\Constants;
use DebugToolkit\Services;
use DebugToolkit\Services\Debug_Manager;

/**
 * Fired during plugin deactivation
 */
class Deactivator {

    /**
     * Deactivate the plugin
     */
    public static function deactivate() {
        self::remove_viewer();
        self::reset_debug_settings();
    }

    /**
     * Remove the external viewer if it exists
     */
    private static function remove_viewer() {
        try {
            $viewer_manager = new Services\Viewer_Manager();
            $viewer_manager->remove();
        } catch (\Exception $e) {
            Error_Handler::log_exception($e, 'Deactivator Remove Viewer');
            
            $viewer_dir = ABSPATH . Constants::DBTK_VIEWER_DIR;
            if (file_exists($viewer_dir)) {
                Filesystem_Utils::recursively_delete_directory($viewer_dir, true);
            }
        }
        
        update_option('debug_toolkit_viewer_installed', false);
    }

    /**
     * Reset debug settings in wp-config.php
     */
    private static function reset_debug_settings() {
        try {
            $config_file = ABSPATH . 'wp-config.php';
            if (!file_exists($config_file) || !is_writable($config_file)) {
                return;
            }
            
            $backup_file = $config_file . '.wpdebugtoolkit-backup';
            if (file_exists($backup_file) && is_readable($backup_file)) {
                Error_Handler::fs_operation(
                    function() use ($backup_file, $config_file) {
                        return copy($backup_file, $config_file);
                    },
                    __('Failed to restore backup of wp-config.php during deactivation.', 'wpdebugtoolkit'),
                    true 
                );
                
                Error_Handler::fs_operation(
                    function() use ($backup_file) {
                        return unlink($backup_file);
                    },
                    __('Failed to remove backup file after restoration.', 'wpdebugtoolkit'),
                    true 
                );
                
                self::clean_up_options();
                return;
            }
            
            // If no backup file, manually clean up the constants
            self::cleanup_debug_constants($config_file);
            self::clean_up_options();
            
        } catch (\Exception $e) {
            Error_Handler::log_exception($e, 'Deactivation');
        }
    }
    
    /**
     * Clean up debug constants 
     * 
     * @param string $config_file Path to wp-config.php
     */
    private static function cleanup_debug_constants($config_file) {
        try {
            $config_content = Error_Handler::fs_operation(
                function() use ($config_file) {
                    return file_get_contents($config_file);
                },
                __('Failed to read wp-config.php file content during cleanup.', 'wpdebugtoolkit')
            );
            
            $debug_constants = [
                'WP_DEBUG' => get_option('debug_toolkit_wp_debug_original', false),
                'WP_DEBUG_DISPLAY' => get_option('debug_toolkit_wp_debug_display_original', false),
                'WP_DEBUG_LOG' => get_option('debug_toolkit_wp_debug_log_original', false)
            ];
            
            // Remove all instances of debug constants
            foreach (array_keys($debug_constants) as $constant) {
                $config_content = self::remove_all_constant_definitions($config_content, $constant);
            }
            
            // Add back the original values
            foreach ($debug_constants as $constant => $original_value) {
                if ($original_value !== false) {
                    $config_content = self::add_constant_definition($config_content, $constant, $original_value);
                }
            }
            
            // Write the cleaned content
            Error_Handler::fs_operation(
                function() use ($config_file, $config_content) {
                    $result = @file_put_contents($config_file, $config_content);
                    
                    // we may be in CGI environment, try again
                    if ($result === false) {

                        $handle = @fopen($config_file, 'w');
                        if ($handle !== false) {
                            $write_result = @fwrite($handle, $config_content);
                            @fclose($handle);
                            return $write_result !== false;
                        }
                        return false;
                    }
                    return true;
                },
                __('Failed to write cleaned wp-config.php during deactivation.', 'wpdebugtoolkit'),
                true
            );
            
        } catch (\Exception $e) {
            Error_Handler::log_exception($e, 'Debug Constants Cleanup');
        }
    }
    
    /**
     * Remove all definitions of a constant
     * 
     * @param string $content The config file content
     * @param string $constant The constant name to remove
     * @return string The cleaned content
     */
    private static function remove_all_constant_definitions($content, $constant) {
        $patterns = [
            // Active define statements
            "/^\s*define\s*\(\s*['\"]" . preg_quote($constant, '/') . "['\"]\s*,\s*[^)]+\s*\)\s*;\s*$/m",
            // Commented define statements (double slash)
            "/^\s*\/\/\s*define\s*\(\s*['\"]" . preg_quote($constant, '/') . "['\"]\s*,\s*[^)]+\s*\)\s*;\s*$/m",
            // Commented define statements (hash)
            "/^\s*#\s*define\s*\(\s*['\"]" . preg_quote($constant, '/') . "['\"]\s*,\s*[^)]+\s*\)\s*;\s*$/m"
        ];
        
        foreach ($patterns as $pattern) {
            $content = preg_replace($pattern, '', $content);
        }
        
        // Clean up excessive blank lines
        $content = preg_replace("/\n{3,}/", "\n\n", $content);
        
        return $content;
    }
    
    /**
     * Add a constant definition to the config content
     * 
     * @param string $content The config file content
     * @param string $constant The constant name
     * @param mixed $value The constant value
     * @return string The updated content
     */
    private static function add_constant_definition($content, $constant, $value) {
        $value_string = is_bool($value) ? ($value ? 'true' : 'false') : $value;
        $definition = sprintf("define('%s', %s);", $constant, $value_string);
        
        // Find the best insertion point
        $insertion_markers = [
            "/* That's all",
            "/* That's It. Pencils down",
            "require_once(ABSPATH . 'wp-settings.php');",
            "require_once ABSPATH . 'wp-settings.php';",
            "?>"
        ];
        
        foreach ($insertion_markers as $marker) {
            $pos = strpos($content, $marker);
            if ($pos !== false) {
                return substr_replace($content, $definition . "\n", $pos, 0);
            }
        }
        
        // Fallback: add to the end
        if (substr($content, -1) !== "\n") {
            $content .= "\n";
        }
        return $content . $definition . "\n";
    }
    
    /**
     * Clean up options 
     */
    private static function clean_up_options() {
        $options_to_delete = [
            'debug_toolkit_wp_debug_original',
            'debug_toolkit_wp_debug_display_original',
            'debug_toolkit_wp_debug_log_original',
        ];
        
        foreach ($options_to_delete as $option) {
            delete_option($option);
        }
    }
} 