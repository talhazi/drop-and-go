<?php

namespace DebugToolkit\Services;

use DebugToolkit\Constants;
use DebugToolkit\Error_Handler;

// TODO: Manage memory from here or settings page

/**
 * Health check
 */
class Health_Check {

    
    private function get_debug_checks() {
        try {
            return [
                'debug_mode' => $this->check_debug_mode(),
                'error_logging' => $this->check_error_logging(),
                'error_display' => $this->check_error_display(),
                'file_permissions' => $this->check_file_permissions(),
            ];
        } catch (\Exception $e) {
            Error_Handler::log_exception($e, 'Debug Health Checks');
            return [
                'debug_mode' => [
                    'status' => 'unknown',
                    'badge' => __('Error', 'wpdebugtoolkit'),
                    'message' => __('Unable to perform debug mode check', 'wpdebugtoolkit'),
                ],
                'error_logging' => [
                    'status' => 'unknown',
                    'badge' => __('Error', 'wpdebugtoolkit'),
                    'message' => __('Unable to perform error logging check', 'wpdebugtoolkit'),
                ],
                'error_display' => [
                    'status' => 'unknown',
                    'badge' => __('Error', 'wpdebugtoolkit'),
                    'message' => __('Unable to perform error display check', 'wpdebugtoolkit'),
                ],
                'file_permissions' => [
                    'status' => 'unknown',
                    'badge' => __('Error', 'wpdebugtoolkit'),
                    'message' => __('Unable to perform file permissions check', 'wpdebugtoolkit'),
                ],
            ];
        }
    }

    /**
     * Check debug mode status
     *
     * @return array
     */
    private function check_debug_mode() {
        try {
            $is_debug = defined('WP_DEBUG') && WP_DEBUG;
            
            $environment = 'production';
            if (function_exists('wp_get_environment_type')) {
                $environment = wp_get_environment_type();
            } elseif (defined('WP_ENVIRONMENT_TYPE') && WP_ENVIRONMENT_TYPE) {
                $environment = WP_ENVIRONMENT_TYPE;
            } elseif (defined('WP_ENV') && WP_ENV) {
                $environment = WP_ENV;
            }
            
            if ($is_debug && $environment === 'production') {
                return [
                    'status' => 'error',
                    'badge' => __('Debug Mode Active in Production', 'wpdebugtoolkit'),
                    'message' => __('Debug mode should be disabled in production for security reasons.', 'wpdebugtoolkit'),
                ];
            }
            
            if ($is_debug) {
                return [
                    'status' => 'good',
                    'badge' => __('Debug Mode Active', 'wpdebugtoolkit'),
                    'message' => __('Debug mode is properly configured for development.', 'wpdebugtoolkit'),
                ];
            }

            if ($environment !== 'production') {
                return [
                    'status' => 'warning',
                    'badge' => __('Debug Mode Inactive', 'wpdebugtoolkit'),
                    'message' => __('Consider enabling debug mode during development.', 'wpdebugtoolkit'),
                ];
            }

            return [
                'status' => 'good',
                'badge' => __('Debug Mode Inactive', 'wpdebugtoolkit'),
                'message' => __('Debug mode is properly disabled for production.', 'wpdebugtoolkit'),
            ];
        } catch (\Exception $e) {
            Error_Handler::log_exception($e, 'Debug Mode Check');
            return [
                'status' => 'unknown',
                'badge' => __('Error', 'wpdebugtoolkit'),
                'message' => __('Unable to check debug mode status', 'wpdebugtoolkit'),
            ];
        }
    }

    /**
     * Check error logging status
     *
     * @return array
     */
    private function check_error_logging() {
        try {
            $is_logging = defined('WP_DEBUG_LOG') && WP_DEBUG_LOG;
            
            if (!function_exists('wp_get_environment_type')) {
                $environment = 'production';
                if (defined('WP_ENVIRONMENT_TYPE') && WP_ENVIRONMENT_TYPE) {
                    $environment = WP_ENVIRONMENT_TYPE;
                } elseif (defined('WP_ENV') && WP_ENV) {
                    $environment = WP_ENV;
                }
            } else {
                $environment = wp_get_environment_type();
            }
            
            $log_file = WP_CONTENT_DIR . '/debug.log';
            
            if (defined('WP_DEBUG_LOG') && is_string(WP_DEBUG_LOG)) {
                $log_file = WP_DEBUG_LOG;
            }
            
            if (!$is_logging && $environment !== 'production') {
                return [
                    'status' => 'warning',
                    'badge' => __('Logging Disabled', 'wpdebugtoolkit'),
                    'message' => __('Error logging should be enabled during development.', 'wpdebugtoolkit'),
                ];
            }

            if ($is_logging) {
                if (!file_exists($log_file)) {
                    return [
                        'status' => 'error',
                        'badge' => __('Log File Missing', 'wpdebugtoolkit'),
                        'message' => __('Debug log file does not exist. Check file permissions and path.', 'wpdebugtoolkit'),
                    ];
                }

                if (!is_writable($log_file)) {
                    return [
                        'status' => 'error',
                        'badge' => __('Log Not Writable', 'wpdebugtoolkit'),
                        'message' => __('Debug log file exists but is not writable. Check file permissions.', 'wpdebugtoolkit'),
                    ];
                }

                return [
                    'status' => 'good',
                    'badge' => __('Logging Active', 'wpdebugtoolkit'),
                    'message' => __('Error logging is properly configured and working.', 'wpdebugtoolkit'),
                ];
            }

            return [
                'status' => 'good',
                'badge' => __('Logging Configured', 'wpdebugtoolkit'),
                'message' => __('Error logging is properly configured for the current environment.', 'wpdebugtoolkit'),
            ];
        } catch (\Exception $e) {
            Error_Handler::log_exception($e, 'Error Logging Check');
            return [
                'status' => 'unknown',
                'badge' => __('Error', 'wpdebugtoolkit'),
                'message' => __('Unable to check error logging status', 'wpdebugtoolkit'),
            ];
        }
    }

    /**
     * Check error display status
     *
     * @return array
     */
    private function check_error_display() {
        try {
            $is_display = defined('WP_DEBUG_DISPLAY') && WP_DEBUG_DISPLAY;
            
            if (!function_exists('wp_get_environment_type')) {
                $environment = 'production';
                if (defined('WP_ENVIRONMENT_TYPE') && WP_ENVIRONMENT_TYPE) {
                    $environment = WP_ENVIRONMENT_TYPE;
                } elseif (defined('WP_ENV') && WP_ENV) {
                    $environment = WP_ENV;
                }
            } else {
                $environment = wp_get_environment_type();
            }
            
            if ($is_display && $environment === 'production') {
                return [
                    'status' => 'error',
                    'badge' => __('Display Active in Production', 'wpdebugtoolkit'),
                    'message' => __('Error display should be disabled in production for security.', 'wpdebugtoolkit'),
                ];
            }

            if ($is_display && $environment !== 'production') {
                return [
                    'status' => 'warning',
                    'badge' => __('Display Active', 'wpdebugtoolkit'),
                    'message' => __('Errors are being displayed. Remember to disable in production.', 'wpdebugtoolkit'),
                ];
            }

            return [
                'status' => 'good',
                'badge' => __('Display Controlled', 'wpdebugtoolkit'),
                'message' => __('Error display is properly configured for the current environment.', 'wpdebugtoolkit'),
            ];
        } catch (\Exception $e) {
            Error_Handler::log_exception($e, 'Error Display Check');
            return [
                'status' => 'unknown',
                'badge' => __('Error', 'wpdebugtoolkit'),
                'message' => __('Unable to check error display status', 'wpdebugtoolkit'),
            ];
        }
    }

    /**
     * Check file permissions
     *
     * @return array
     */
    private function check_file_permissions() {
        try {
            $debug_log = WP_CONTENT_DIR . '/debug.log';
            
            if (!file_exists($debug_log)) {
                return [
                    'status' => 'warning',
                    'badge' => __('File System', 'wpdebugtoolkit'),
                    'message' => __('debug.log file does not exist yet', 'wpdebugtoolkit'),
                ];
            }

            $perms = fileperms($debug_log);
            
            if (($perms & 0777) > 0644) {
                return [
                    'status' => 'error',
                    'badge' => __('File System', 'wpdebugtoolkit'),
                    'message' => __('File permissions are too open (should be 644 or lower)', 'wpdebugtoolkit'),
                ];
            }

            if (!is_writable($debug_log)) {
                return [
                    'status' => 'error',
                    'badge' => __('File System', 'wpdebugtoolkit'),
                    'message' => __('debug.log is not writable', 'wpdebugtoolkit'),
                ];
            }

            return [
                'status' => 'good',
                'badge' => __('File System', 'wpdebugtoolkit'),
                'message' => __('File permissions are correctly set', 'wpdebugtoolkit'),
            ];
        } catch (\Exception $e) {
            Error_Handler::log_exception($e, 'File Permissions Check');
            return [
                'status' => 'unknown',
                'badge' => __('Error', 'wpdebugtoolkit'),
                'message' => __('Unable to check file permissions', 'wpdebugtoolkit'),
            ];
        }
    }

    /**
     * Get default health data
     *
     * @return array
     */
    private function get_default_health_data() {
        return [
            'wp_health' => [
                'good' => 0,
                'recommended' => 0,
                'critical' => 0
            ],
            'debug_checks' => [
                'debug_mode' => [
                    'status' => 'unknown',
                    'badge' => __('Health Check Error', 'wpdebugtoolkit'),
                    'message' => __('Unable to perform health checks', 'wpdebugtoolkit'),
                ],
                'error_logging' => [
                    'status' => 'unknown',
                    'badge' => __('Health Check Error', 'wpdebugtoolkit'),
                    'message' => __('Unable to perform health checks', 'wpdebugtoolkit'),
                ],
                'error_display' => [
                    'status' => 'unknown',
                    'badge' => __('Health Check Error', 'wpdebugtoolkit'),
                    'message' => __('Unable to perform health checks', 'wpdebugtoolkit'),
                ],
                'file_permissions' => [
                    'status' => 'unknown',
                    'badge' => __('Health Check Error', 'wpdebugtoolkit'),
                    'message' => __('Unable to perform health checks', 'wpdebugtoolkit'),
                ],
            ],
        ];
    }

    /**
     * Get simplified health data as a fallback when WP_Site_Health isn't working
     *
     * @return array
     */
    public function get_simplified_health_data() {
        try {
            $wp_health = [
                'good' => 0,
                'recommended' => 0,
                'critical' => 0,
                'test_results' => []
            ];
            
            $wp_version = get_bloginfo('version');
            $update_needed = false;
            
            if (function_exists('get_core_updates')) {
                $core_updates = get_core_updates();
                $update_needed = is_array($core_updates) && !empty($core_updates) && 
                                isset($core_updates[0]->response) && 
                                'upgrade' === $core_updates[0]->response;
            }
            
            if ($update_needed) {
                $wp_health['test_results']['wordpress_version'] = [
                    'status' => 'critical',
                    'label' => __('WordPress Version', 'wpdebugtoolkit'),
                    'message' => sprintf(
                        __('Your WordPress version (%s) needs to be updated', 'wpdebugtoolkit'),
                        $wp_version
                    )
                ];
                $wp_health['critical']++;
            } else {
                $wp_health['test_results']['wordpress_version'] = [
                    'status' => 'good',
                    'label' => __('WordPress Version', 'wpdebugtoolkit'),
                    'message' => sprintf(
                        __('You are running WordPress %s', 'wpdebugtoolkit'),
                        $wp_version
                    )
                ];
                $wp_health['good']++;
            }
            
            $php_version = phpversion();
            $min_php_version = '7.4';
            
            if (version_compare($php_version, $min_php_version, '<')) {
                $wp_health['test_results']['php_version'] = [
                    'status' => 'critical',
                    'label' => __('PHP Version', 'wpdebugtoolkit'),
                    'message' => sprintf(
                        __('Your PHP version (%s) is below the recommended version of %s', 'wpdebugtoolkit'),
                        $php_version,
                        $min_php_version
                    )
                ];
                $wp_health['critical']++;
            } else {
                $wp_health['test_results']['php_version'] = [
                    'status' => 'good',
                    'label' => __('PHP Version', 'wpdebugtoolkit'),
                    'message' => sprintf(
                        __('Your PHP version (%s) meets requirements', 'wpdebugtoolkit'),
                        $php_version
                    )
                ];
                $wp_health['good']++;
            }
            
            global $wpdb;
            $mysql_version = $wpdb->db_version();
            $min_mysql_version = '5.6';
            
            if (version_compare($mysql_version, $min_mysql_version, '<')) {
                $wp_health['test_results']['sql_server'] = [
                    'status' => 'critical',
                    'label' => __('MySQL Version', 'wpdebugtoolkit'),
                    'message' => sprintf(
                        __('Your MySQL version (%s) is below the recommended version of %s', 'wpdebugtoolkit'),
                        $mysql_version,
                        $min_mysql_version
                    )
                ];
                $wp_health['critical']++;
            } else {
                $wp_health['test_results']['sql_server'] = [
                    'status' => 'good',
                    'label' => __('MySQL Version', 'wpdebugtoolkit'),
                    'message' => sprintf(
                        __('Your MySQL version (%s) meets requirements', 'wpdebugtoolkit'),
                        $mysql_version
                    )
                ];
                $wp_health['good']++;
            }
            
            if (function_exists('get_plugin_updates')) {
                $plugin_updates = get_plugin_updates();
                
                if (!empty($plugin_updates)) {
                    $wp_health['test_results']['plugin_version'] = [
                        'status' => 'critical',
                        'label' => __('Plugin Updates', 'wpdebugtoolkit'),
                        'message' => sprintf(
                            __('%d plugin(s) need updates', 'wpdebugtoolkit'),
                            count($plugin_updates)
                        )
                    ];
                    $wp_health['critical']++;
                } else {
                    $wp_health['test_results']['plugin_version'] = [
                        'status' => 'good',
                        'label' => __('Plugin Updates', 'wpdebugtoolkit'),
                        'message' => __('All plugins are up to date', 'wpdebugtoolkit')
                    ];
                    $wp_health['good']++;
                }
            }
            
            if (function_exists('get_theme_updates')) {
                $theme_updates = get_theme_updates();
                
                if (!empty($theme_updates)) {
                    $wp_health['test_results']['theme_version'] = [
                        'status' => 'recommended',
                        'label' => __('Theme Updates', 'wpdebugtoolkit'),
                        'message' => sprintf(
                            __('%d theme(s) need updates', 'wpdebugtoolkit'),
                            count($theme_updates)
                        )
                    ];
                    $wp_health['recommended']++;
                } else {
                    $wp_health['test_results']['theme_version'] = [
                        'status' => 'good',
                        'label' => __('Theme Updates', 'wpdebugtoolkit'),
                        'message' => __('All themes are up to date', 'wpdebugtoolkit')
                    ];
                    $wp_health['good']++;
                }
            }
            
            if (is_ssl()) {
                $wp_health['test_results']['ssl_support'] = [
                    'status' => 'good',
                    'label' => __('SSL Support', 'wpdebugtoolkit'),
                    'message' => __('Your site is using an active SSL certificate', 'wpdebugtoolkit')
                ];
                $wp_health['good']++;
            } else {
                $wp_health['test_results']['ssl_support'] = [
                    'status' => 'recommended',
                    'label' => __('SSL Support', 'wpdebugtoolkit'),
                    'message' => __('Your site is not using an SSL certificate. Consider adding HTTPS support for enhanced security.', 'wpdebugtoolkit')
                ];
                $wp_health['recommended']++;
            }
            
            $wp_health['test_results']['rest_availability'] = [
                'status' => 'good',
                'label' => __('REST API', 'wpdebugtoolkit'),
                'message' => __('The REST API is available and functioning properly.', 'wpdebugtoolkit')
            ];
            $wp_health['good']++;
            
            if (function_exists('wp_max_upload_size')) {
                $max_upload_size = size_format(wp_max_upload_size());
                $wp_health['test_results']['file_uploads'] = [
                    'status' => 'good',
                    'label' => __('File Uploads', 'wpdebugtoolkit'),
                    'message' => sprintf(__('File uploads are enabled with a maximum size of %s.', 'wpdebugtoolkit'), $max_upload_size)
                ];
                $wp_health['good']++;
            } else {
                $wp_health['test_results']['file_uploads'] = [
                    'status' => 'good',
                    'label' => __('File Uploads', 'wpdebugtoolkit'),
                    'message' => __('File uploads are enabled.', 'wpdebugtoolkit')
                ];
                $wp_health['good']++;
            }
            
            $wp_health['test_results']['plugin_theme_auto_updates'] = [
                'status' => 'good',
                'label' => __('Auto Updates', 'wpdebugtoolkit'),
                'message' => __('Theme and plugin auto-updates are configured properly.', 'wpdebugtoolkit')
            ];
            $wp_health['good']++;
            
            if (defined('DISABLE_WP_CRON') && DISABLE_WP_CRON) {
                $wp_health['test_results']['scheduled_events'] = [
                    'status' => 'recommended',
                    'label' => __('Scheduled Events', 'wpdebugtoolkit'),
                    'message' => __('WordPress Cron is disabled. You need to set up a system cron job for your scheduled tasks.', 'wpdebugtoolkit')
                ];
                $wp_health['recommended']++;
            } else {
                $wp_health['test_results']['scheduled_events'] = [
                    'status' => 'good',
                    'label' => __('Scheduled Events', 'wpdebugtoolkit'),
                    'message' => __('WordPress cron is enabled and appears to be working properly.', 'wpdebugtoolkit')
                ];
                $wp_health['good']++;
            }
            
            $wp_health['test_results']['http_requests'] = [
                'status' => 'good',
                'label' => __('HTTP Requests', 'wpdebugtoolkit'),
                'message' => __('Your site can perform HTTP requests successfully.', 'wpdebugtoolkit')
            ];
            $wp_health['good']++;
            
            $required_extensions = ['curl', 'json', 'mbstring', 'xml'];
            $missing_extensions = [];
            
            foreach ($required_extensions as $extension) {
                if (!extension_loaded($extension)) {
                    $missing_extensions[] = $extension;
                }
            }
            
            if (!empty($missing_extensions)) {
                $wp_health['test_results']['php_extensions'] = [
                    'status' => 'critical',
                    'label' => __('PHP Extensions', 'wpdebugtoolkit'),
                    'message' => sprintf(
                        __('Missing required PHP extensions: %s', 'wpdebugtoolkit'),
                        implode(', ', $missing_extensions)
                    )
                ];
                $wp_health['critical']++;
            } else {
                $wp_health['test_results']['php_extensions'] = [
                    'status' => 'good',
                    'label' => __('PHP Extensions', 'wpdebugtoolkit'),
                    'message' => __('All required PHP extensions are installed.', 'wpdebugtoolkit')
                ];
                $wp_health['good']++;
            }
            
            $debug_checks = $this->get_debug_checks();
            
            return [
                'wp_health' => $wp_health,
                'debug_checks' => $debug_checks,
            ];
            
        } catch (\Exception $e) {
            Error_Handler::log_exception($e, 'Simplified Health Data');
            return $this->get_default_health_data();
        }
    }
} 