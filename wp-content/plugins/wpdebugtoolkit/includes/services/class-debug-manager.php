<?php

namespace DebugToolkit\Services;

use DebugToolkit\Error_Handler;
use DebugToolkit\Filesystem_Utils;
use DebugToolkit\Traits\Config_Utils;

require_once __DIR__ . '/../utils.php';

/**
 * Class Debug_Manager -- debugging operations
 */
class Debug_Manager {
    use Config_Utils;

    /**
     * File write operation in CGI env
     * 
     * @param string $file_path
     * @param string $content
     * @return bool
     * @throws \Exception
     */
    private function cgi_safe_file_write($file_path, $content) {
        $result = @file_put_contents($file_path, $content);
        
        if ($result === false) {

            $handle = @fopen($file_path, 'w');
            if ($handle === false) {
                throw new \Exception(__('Failed to open file for writing in CGI mode.', 'wpdebugtoolkit'));
            }
            
            $write_result = @fwrite($handle, $content);
            @fclose($handle);
            
            if ($write_result === false) {
                throw new \Exception(__('Failed to write file in CGI mode.', 'wpdebugtoolkit'));
            }
            
            return true;
        }
        
        return $result !== false;
    }

    /**
     * Get current debug settings
     *
     * @return array
     */
    public function get_settings() {
        return Error_Handler::execute_with_error_handling(
            function() {
                clear_caching_plugins_cache();
                $original_error_reporting = error_reporting();
                $original_display_errors = ini_get('display_errors');
                error_reporting(0);
                ini_set('display_errors', 0);

                if (ob_get_level()) {
                    ob_clean();
                }

                $data = [
                    'debug_enabled' => defined('WP_DEBUG') && WP_DEBUG,
                    'debug_display' => defined('WP_DEBUG_DISPLAY') && WP_DEBUG_DISPLAY,
                    'debug_log' => defined('WP_DEBUG_LOG') && WP_DEBUG_LOG,
                ];

                error_reporting($original_error_reporting);
                ini_set('display_errors', $original_display_errors);

                return $data;
            },
            'Get Debug Settings'
        );
    }

    /**
     * Update debug settings in wp-config.php
     *
     * @param array $settings Settings to update
     * @return bool
     * @throws \Exception
     */
    public function update_settings($settings) {
        return Error_Handler::execute_with_error_handling(
            function() use ($settings) {
                if (!is_array($settings)) {
                    throw new \Exception(__('Settings must be provided as an array.', 'wpdebugtoolkit'));
                }

                if (empty($settings)) {
                    throw new \Exception(__('No settings provided.', 'wpdebugtoolkit'));
                }

                $validation_rules = [
                    'debug_enabled' => [
                        'type' => 'boolean',
                        'required' => false,
                        'description' => __('WP_DEBUG setting', 'wpdebugtoolkit')
                    ],
                    'debug_display' => [
                        'type' => 'boolean',
                        'required' => false,
                        'description' => __('WP_DEBUG_DISPLAY setting', 'wpdebugtoolkit')
                    ],
                    'debug_log' => [
                        'type' => 'boolean',
                        'required' => false,
                        'description' => __('WP_DEBUG_LOG setting', 'wpdebugtoolkit')
                    ]
                ];

                $allowed_settings = array_keys($validation_rules);
                $invalid_settings = array_diff(array_keys($settings), $allowed_settings);
                if (!empty($invalid_settings)) {
                    throw new \Exception(
                        sprintf(
                            __('Invalid settings provided: %s. Allowed settings are: %s', 'wpdebugtoolkit'),
                            implode(', ', $invalid_settings),
                            implode(', ', $allowed_settings)
                        )
                    );
                }

                $validated_settings = [];
                foreach ($settings as $key => $value) {
                    $rule = $validation_rules[$key];
                    
                    if ($rule['type'] === 'boolean') {
                        $validated_settings[$key] = filter_var($value, FILTER_VALIDATE_BOOLEAN);
                    } else {
                        $validated_settings[$key] = $value;
                    }
                }

                if (isset($validated_settings['debug_enabled']) && $validated_settings['debug_enabled'] === false) {
                    if ((isset($validated_settings['debug_display']) && $validated_settings['debug_display'] === true) ||
                        (isset($validated_settings['debug_log']) && $validated_settings['debug_log'] === true)) {
                        Error_Handler::log(
                            __('Warning: Enabling debug display or logging while WP_DEBUG is disabled may not work as expected.', 'wpdebugtoolkit'),
                            'Debug Settings Validation'
                        );
                    }
                }

                $config_file = ABSPATH . 'wp-config.php';
                
                Filesystem_Utils::ensure_file_exists_and_writable($config_file);

                // Create a backup before making changes
                $backup_file = $config_file . '.wpdebugtoolkit-backup';
                if (!file_exists($backup_file)) {
                    Error_Handler::fs_operation(
                        function() use ($config_file, $backup_file) {
                            if (!is_readable($config_file)) {
                                return false;
                            }
                            return copy($config_file, $backup_file);
                        },
                        __('Failed to create backup of wp-config.php before modifying debug settings.', 'wpdebugtoolkit'),
                        true 
                    );
                }

                $config_content = Error_Handler::fs_operation(
                    function() use ($config_file) {
                        return file_get_contents($config_file);
                    },
                    __('Failed to read wp-config.php file content.', 'wpdebugtoolkit')
                );
                
                $constant_map = [
                    'debug_enabled' => 'WP_DEBUG',
                    'debug_display' => 'WP_DEBUG_DISPLAY',
                    'debug_log' => 'WP_DEBUG_LOG'
                ];
                
                // Clean up any incorrect constants that might have been added by previous versions
                $config_content = $this->cleanup_incorrect_constants($config_content);

                foreach ($allowed_settings as $setting) {
                    if (isset($validated_settings[$setting])) {
                        $constant = $constant_map[$setting];
                        $config_content = $this->update_config_constant(
                            $constant,
                            $validated_settings[$setting],
                            $config_content
                        );
                    }
                }

                if (is_cgi_environment()) {
                    
                    try {

                        if ($this->cgi_safe_file_write($config_file, $config_content)) {
                            return true;
                        }
                        throw new \Exception(__('CGI-compatible write failed.', 'wpdebugtoolkit'));
                    } catch (\Exception $e) {
                        Error_Handler::log_exception($e, 'CGI Write Operation');
                        throw new \Exception(__('Failed to update wp-config.php in CGI mode.', 'wpdebugtoolkit'));
                    }
                }
                
                // Atomic write operation
                try {
                    $result = Error_Handler::fs_operation(
                        function() use ($config_file, $config_content) {
                            $temp_file = $config_file . '.tmp';
                            $write_result = file_put_contents($temp_file, $config_content, LOCK_EX);
                            
                            if ($write_result === false) {
                                return false;
                            }
                            
                            $written_content = file_get_contents($temp_file);
                            if ($written_content !== $config_content) {
                                @unlink($temp_file);
                                throw new \Exception(__('Written config file content does not match expected content.', 'wpdebugtoolkit'));
                            }
                            
                            if (function_exists('exec')) {
                                try {
                                    $output = [];
                                    $return_var = 0;
                                
                                    $php_binary = PHP_BINARY ?: 'php';
                                    @exec($php_binary . ' -l ' . escapeshellarg($temp_file) . ' 2>&1', $output, $return_var);
                                    
                                    if ($return_var !== 0 && !empty($output)) {
                                        $error_output = implode("\n", $output);
                                        if (strpos($error_output, 'Syntax error') !== false) {
                                            @unlink($temp_file);
                                            throw new \Exception(__('Generated wp-config.php contains syntax errors. Changes not applied.', 'wpdebugtoolkit'));
                                        }
                                        
                                        Error_Handler::log('PHP syntax check warning (proceeding anyway): ' . $error_output, 'Config Update');
                                    }
                                } catch (\Exception $e) {
                                    Error_Handler::log('PHP syntax check failed (proceeding anyway): ' . $e->getMessage(), 'Config Update');
                                }
                            }
                            
                            if (!rename($temp_file, $config_file)) {
                                @unlink($temp_file);
                                throw new \Exception(__('Failed to apply changes to wp-config.php.', 'wpdebugtoolkit'));
                            }
                            
                            return true;
                        },
                        __('Failed to write to wp-config.php. Please check file permissions.', 'wpdebugtoolkit')
                    );
                    
                    if ($result) {
                        return true;
                    }
                    
                    throw new \Exception(__('Failed to update wp-config.php with atomic write operation.', 'wpdebugtoolkit'));
                } 
                catch (\Exception $atomic_error) {
                    Error_Handler::log_exception($atomic_error, 'Atomic Write Operation');
                    
                    Error_Handler::log('Attempting direct write fallback for wp-config.php...', 'Config Update');
                    
                    $direct_result = Error_Handler::fs_operation(
                        function() use ($config_file, $config_content) {
                            return file_put_contents($config_file, $config_content, LOCK_EX);
                        },
                        __('Failed to write directly to wp-config.php. Please check file permissions.', 'wpdebugtoolkit')
                    );
                    
                    if ($direct_result !== false) {
                        Error_Handler::log('Direct write fallback succeeded', 'Config Update');
                        return true;
                    }
                    
                    throw new \Exception(__('All attempts to write to wp-config.php failed.', 'wpdebugtoolkit'));
                }
            },
            'Update Debug Settings'
        );
    }

    /**
     * Update wp-config.php constants
     *
     * @param string $constant 
     * @param bool $value 
     * @param string $content 
     * @return string
     * @throws \Exception
     */
    private function update_config_constant($constant, $value, $content) {
        $allowed_constants = ['WP_DEBUG', 'WP_DEBUG_DISPLAY', 'WP_DEBUG_LOG'];
        if (!in_array($constant, $allowed_constants, true)) {
            throw new \Exception(sprintf(
                __('Invalid constant name: %s. Allowed constants are: %s', 'wpdebugtoolkit'),
                $constant,
                implode(', ', $allowed_constants)
            ));
        }
        
        $is_boolean_value = is_bool($value);
        if (!$is_boolean_value) {
            if ($value === 'true' || $value === '1' || $value === 1) {
                $value = true;
            } elseif ($value === 'false' || $value === '0' || $value === 0) {
                $value = false;
            } else {
                throw new \Exception(sprintf(
                    __('Invalid value for constant %s. Only boolean values are supported.', 'wpdebugtoolkit'),
                    $constant
                ));
            }
        }
        
        $value_string = $value ? 'true' : 'false';
        
        $this->store_constant_original_value($constant);
        $this->track_constant_change($constant, $value);
        
        $updated_content = $this->replace_existing_constant($constant, $value_string, $content);
        if ($updated_content !== $content) {
            return $updated_content;
        }
        
        return $this->add_new_constant($constant, $value_string, $content);
    }

    /**
     * Store original value of a constant
     *
     * @param string $constant 
     */
    private function store_constant_original_value($constant) {
        if (!get_option('debug_toolkit_' . strtolower($constant) . '_original', false)) {
            if (defined($constant)) {
                update_option('debug_toolkit_' . strtolower($constant) . '_original', constant($constant));
            }
        }
    }

    /**
     * Track constant change
     *
     * @param string $constant 
     * @param bool $value 
     */
    private function track_constant_change($constant, $value) {
        update_option('debug_toolkit_' . str_replace('wp_', '', strtolower($constant)), $value);
    }

    /**
     * Try to find and replace an existing constant
     *
     * @param string $constant 
     * @param string $value_string 
     * @param string $content 
     * @return string 
     */
    private function replace_existing_constant($constant, $value_string, $content) {
        $patterns = $this->get_constant_patterns($constant);
        
        $sanitized_constant = $this->sanitize_constant_name($constant);
        $sanitized_value = $this->sanitize_constant_value($value_string);
        
        $replacement = sprintf("define('%s', %s);", $sanitized_constant, $sanitized_value);
        
        if (preg_match($patterns['active'], $content, $matches)) {
            $full_match = $matches[0]; 
            
            $whitespace = '';
            if (preg_match('/^(\s+)/', $full_match, $ws_match)) {
                $whitespace = $ws_match[1];
            }
            
            $formatted_replacement = $whitespace . $replacement;
            
            return preg_replace(
                $patterns['active'],
                $formatted_replacement,
                $content,
                1 
            );
        }
        
        foreach (['commented_double', 'commented_hash'] as $type) {
            if (preg_match($patterns[$type], $content, $matches)) {
                $full_match = $matches[0]; 
                
                $whitespace = '';
                if (preg_match('/^(\s+)/', $full_match, $ws_match)) {
                    $whitespace = $ws_match[1];
                }
                
                $formatted_replacement = $whitespace . $replacement;
                
                return preg_replace(
                    $patterns[$type],
                    $formatted_replacement,
                    $content,
                    1 
                );
            }
        }
        
        return $content;
    }

    /**
     * Get regex patterns
     *
     * @param string $constant 
     * @return array 
     */
    private function get_constant_patterns($constant) {
        return [
            // Match active define(...) with optional whitespace
            'active' => "/^(\s*)define\s*\(\s*['\"]" . preg_quote($constant, '/') . "['\"]\s*,\s*(.*?)\s*\)\s*;/m",
            // Match //define(...) with optional whitespace
            'commented_double' => "/^(\s*)\/\/\s*define\s*\(\s*['\"]" . preg_quote($constant, '/') . "['\"]\s*,\s*(.*?)\s*\)\s*;/m",
            // Match #define(...) with optional whitespace
            'commented_hash' => "/^(\s*)#\s*define\s*\(\s*['\"]" . preg_quote($constant, '/') . "['\"]\s*,\s*(.*?)\s*\)\s*;/m"
        ];
    }

    /**
     * Add new constant (if it doesn't exist)
     *
     * @param string $constant 
     * @param string $value_string 
     * @param string $content 
     * @return string 
     */
    private function add_new_constant($constant, $value_string, $content) {
        $insertion_markers = [
            "/* That's all",
            "/* That's It. Pencils down",
            "require_once(ABSPATH . 'wp-settings.php');",
            "require_once ABSPATH . 'wp-settings.php';",
            "?>"
        ];
        
        $sanitized_constant = $this->sanitize_constant_name($constant);
        $sanitized_value = $this->sanitize_constant_value($value_string);
        
        $definition = sprintf("\ndefine('%s', %s);\n", $sanitized_constant, $sanitized_value);
        
        foreach ($insertion_markers as $marker) {
            $pos = strpos($content, $marker);
            if ($pos !== false) {
                return substr_replace(
                    $content,
                    $definition,
                    $pos,
                    0
                );
            }
        }
        
        if (substr($content, -1) !== "\n") {
            $content .= "\n"; 
        }
        
        return $content . $definition;
    }
    
    /**
     * Sanitize constant name
     *
     * @param string $constant 
     * @return string 
     */
    private function sanitize_constant_name($constant) {
        $sanitized = preg_replace('/[^a-zA-Z0-9_]/', '', $constant);
        
        $allowed_constants = ['WP_DEBUG', 'WP_DEBUG_DISPLAY', 'WP_DEBUG_LOG'];
        if (!in_array($sanitized, $allowed_constants, true)) {
            $sanitized = 'WP_DEBUG'; 
            Error_Handler::log(
                sprintf(__('Invalid constant name sanitized: %s', 'wpdebugtoolkit'), $constant),
                'Config Sanitization'
            );
        }
        
        return $sanitized;
    }
    
    /**
     * Sanitize constant value
     *
     * @param string $value 
     * @return string 
     */
    private function sanitize_constant_value($value) {
        $lower_value = strtolower(trim($value));
        if ($lower_value === 'true') {
            return 'true';
        } else if ($lower_value === 'false') {
            return 'false';
        }
        
        Error_Handler::log(
            sprintf(__('Invalid constant value sanitized: %s', 'wpdebugtoolkit'), $value),
            'Config Sanitization'
        );
        
        return 'false';
    }

    /**
     * Get debug log path
     *
     * @return string
     */
    public function get_log_path() {
        return Filesystem_Utils::get_debug_log_path();
    }

    /**
     * Check if debug log exists and is writable
     *
     * @return array
     */
    public function check_log_file() {
        $log_status = Filesystem_Utils::check_debug_log_file(false);
        
        return [
            'exists' => $log_status['exists'],
            'writable' => $log_status['writable'],
            'message' => $log_status['exists'] 
                ? ($log_status['writable'] 
                    ? __('Debug log file is writable.', 'wpdebugtoolkit')
                    : __('Debug log file exists but is not writable.', 'wpdebugtoolkit'))
                : __('Debug log file does not exist.', 'wpdebugtoolkit'),
        ];
    }

    /**
     * Clear debug log file
     *
     * @return bool
     * @throws \Exception
     */
    public function clear_log() {
        $log_path = $this->get_log_path();
        
        Filesystem_Utils::ensure_file_exists_and_writable($log_path);

        return Error_Handler::fs_operation(
            function() use ($log_path) {
                return file_put_contents($log_path, '');
            },
            __('Failed to clear debug log file.', 'wpdebugtoolkit')
        );
    }
} 
