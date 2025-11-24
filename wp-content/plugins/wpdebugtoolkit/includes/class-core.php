<?php

namespace DebugToolkit;

use DebugToolkit\Traits\Rest_Controller;
use DebugToolkit\Services;
use DebugToolkit\Constants;
use DebugToolkit\Service_Container;

require_once __DIR__ . '/utils.php';

/**
 * The core plugin class
 */
class Core {
    use Rest_Controller;

    /**
     * @var Core
     */
    private static $instance = null;

    /**
     * @var Services\Health_Check
     */
    private $health_check;
    
    /**
     * @var Services\Viewer_Manager
     */
    private $viewer_manager;
    
    /**
     * @var Services\Debug_Manager
     */
    private $debug_manager;
    
    /**
     * @var Services\License_Manager
     */
    private $license_manager;
    
    /**
     * @var Updater
     */
    private $updater;
    
    /**
     * @var Service_Container
     */
    private static $container = null;

    /**
     * Core instance management
     * 
     * @return Core
     */
    public static function get_instance() {
        if (is_null(self::$instance)) {

            self::init_container();
            
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Initialize the service container
     */
    private static function init_container() {
        if (is_null(self::$container)) {
            self::$container = new Service_Container();
            
            self::$container->register('health_check', function() {
                try {

                    if (!class_exists('WP_Site_Health') && !class_exists('\\WP_Site_Health')) {
                        error_log('Debug Toolkit - Core: WP_Site_Health class not found during service registration');
                    }
                    
                    if (!function_exists('wp_convert_hr_to_bytes')) {
                        error_log('Debug Toolkit - Core: wp_convert_hr_to_bytes function not available during service registration');
                    }
                    
                    if (defined('ABSPATH')) {
                        require_once ABSPATH . 'wp-admin/includes/misc.php';
                        require_once ABSPATH . 'wp-admin/includes/class-wp-site-health.php';
                    }
                    
                    return new Services\Health_Check();
                } catch (\Throwable $e) {
                    error_log('Debug Toolkit - Core: Error creating Health_Check service: ' . $e->getMessage());
                    throw $e;
                }
            });
            
            self::$container->register('viewer_manager', function() {
                return new Services\Viewer_Manager();
            });
            
            self::$container->register('debug_manager', function() {
                return new Services\Debug_Manager();
            });
            
            self::$container->register('license_manager', function() {
                return new Services\License_Manager();
            });
        }
    }
    
    /**
     * Get a service
     * 
     * @param string $id 
     * @return mixed 
     */
    public static function get_service($id) {
        if (is_null(self::$container)) {
            self::init_container();
        }
        
        return self::$container->get($id);
    }

    public function register_rest_routes() {
        if (defined('REST_REQUEST') && REST_REQUEST) {
            error_reporting(0);
            ini_set('display_errors', 0);
            
            if (ob_get_level()) {
                ob_clean();
            }
        }

        remove_filter('rest_pre_serve_request', 'rest_send_cors_headers');
        add_filter('rest_pre_serve_request', function($value) {
            if (!headers_sent()) {
                header('Content-Type: application/json; charset=utf-8');
                header('Access-Control-Allow-Origin: ' . esc_url_raw(site_url()));
                header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
                header('Access-Control-Allow-Credentials: true');
                header('Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept, Authorization, X-WP-Nonce');
            }
            return $value;
        });

        $this->register_core_routes(Constants::DBTK_REST_NAMESPACE);
    }
    
    /**
     * Register core API routes
     * 
     * @param string $namespace 
     */
    private function register_core_routes($namespace) {
        register_rest_route($namespace, '/settings', [
            [
                'methods' => \WP_REST_Server::READABLE,
                'callback' => [$this, 'get_settings'],
                'permission_callback' => [$this, 'check_admin_permissions'],
            ],
            [
                'methods' => \WP_REST_Server::EDITABLE,
                'callback' => [$this, 'update_settings'],
                'permission_callback' => [$this, 'check_admin_permissions'],
                'args' => [
                    'debug_enabled' => [
                        'type' => 'boolean',
                        'required' => false,
                        'description' => __('Whether to enable WP_DEBUG', 'wpdebugtoolkit'),
                        'validate_callback' => function($param) {
                            return is_bool($param) || in_array($param, [0, 1, '0', '1'], true);
                        },
                        'sanitize_callback' => function($param) {
                            return filter_var($param, FILTER_VALIDATE_BOOLEAN);
                        }
                    ],
                    'debug_display' => [
                        'type' => 'boolean',
                        'required' => false,
                        'description' => __('Whether to display errors on the screen (WP_DEBUG_DISPLAY)', 'wpdebugtoolkit'),
                        'validate_callback' => function($param) {
                            return is_bool($param) || in_array($param, [0, 1, '0', '1'], true);
                        },
                        'sanitize_callback' => function($param) {
                            return filter_var($param, FILTER_VALIDATE_BOOLEAN);
                        }
                    ],
                    'debug_log' => [
                        'type' => 'boolean',
                        'required' => false,
                        'description' => __('Whether to log errors to debug.log (WP_DEBUG_LOG)', 'wpdebugtoolkit'),
                        'validate_callback' => function($param) {
                            return is_bool($param) || in_array($param, [0, 1, '0', '1'], true);
                        },
                        'sanitize_callback' => function($param) {
                            return filter_var($param, FILTER_VALIDATE_BOOLEAN);
                        }
                    ]
                ],
            ],
        ]);

        register_rest_route($namespace, '/setup-viewer', [
            [
                'methods' => \WP_REST_Server::CREATABLE,
                'callback' => [$this, 'setup_viewer'],
                'permission_callback' => [$this, 'check_admin_permissions'],
                'args' => [],
            ],
        ]);

        register_rest_route($namespace, '/remove-viewer', [
            [
                'methods' => \WP_REST_Server::CREATABLE,
                'callback' => [$this, 'remove_viewer'],
                'permission_callback' => [$this, 'check_admin_permissions'],
                'args' => [],
            ],
        ]);

        register_rest_route($namespace, '/health-check', [
            [
                'methods' => \WP_REST_Server::READABLE,
                'callback' => [$this, 'get_health_check'],
                'permission_callback' => [$this, 'check_admin_permissions'],
                'args' => [],
            ],
        ]);

        register_rest_route($namespace, '/license-status', [
            [
                'methods' => 'GET',
                'callback' => [$this, 'get_license_status'],
                'permission_callback' => [$this, 'check_admin_permissions'],
                'args' => [],
            ],
        ]);

        register_rest_route($namespace, '/activate-license', [
            [
                'methods' => 'POST',
                'callback' => [$this, 'activate_license'],
                'permission_callback' => [$this, 'check_admin_permissions'],
                'args' => [
                    'license_key' => [
                        'type' => 'string',
                        'required' => true,
                        'description' => __('License key to activate', 'wpdebugtoolkit'),
                        'validate_callback' => function($param) {
                            return is_string($param) && !empty(trim($param));
                        },
                        'sanitize_callback' => 'sanitize_text_field'
                    ]
                ],
            ],
        ]);

        register_rest_route($namespace, '/license-details', [
            [
                'methods' => 'GET',
                'callback' => [$this, 'get_license_details'],
                'permission_callback' => [$this, 'check_admin_permissions'],
                'args' => [],
            ],
        ]);

        register_rest_route($namespace, '/deactivate-license', [
            [
                'methods' => 'POST',
                'callback' => [$this, 'deactivate_license'],
                'permission_callback' => [$this, 'check_admin_permissions'],
                'args' => [],
            ],
        ]);
        
        register_rest_route($namespace, '/viewer-password', [
            [
                'methods' => \WP_REST_Server::READABLE,
                'callback' => [$this, 'get_viewer_password_status'],
                'permission_callback' => [$this, 'check_admin_permissions'],
                'args' => [],
            ],
            [
                'methods' => \WP_REST_Server::EDITABLE,
                'callback' => [$this, 'set_viewer_password'],
                'permission_callback' => [$this, 'check_admin_permissions'],
                'args' => [
                    'password' => [
                        'type' => 'string',
                        'required' => false,
                        'description' => __('Password for the viewer app', 'wpdebugtoolkit'),
                        'validate_callback' => function($param) {
                            return is_string($param);
                        },
                        'sanitize_callback' => 'sanitize_text_field'
                    ],
                    'is_password_protection_enabled' => [
                        'type' => 'boolean',
                        'required' => false,
                        'description' => __('Whether password protection is enabled', 'wpdebugtoolkit'),
                        'validate_callback' => function($param) {
                            return is_bool($param) || in_array($param, [0, 1, '0', '1'], true);
                        },
                        'sanitize_callback' => function($param) {
                            return filter_var($param, FILTER_VALIDATE_BOOLEAN);
                        }
                    ]
                ],
            ],
        ]);
    }

    public function check_admin_permissions($request) {
        try {
            $nonce = $request->get_header('X-WP-Nonce');

            if (!$nonce) {
                $params = $request->get_params();
                $nonce = isset($params['_wpnonce']) ? $params['_wpnonce'] : '';
            }

            if (!wp_verify_nonce($nonce, 'wp_rest')) {
                return $this->handle_error(
                    new \Exception(__('Invalid nonce. Please refresh the page and try again.', 'wpdebugtoolkit')),
                    'Admin Permissions',
                    403
                );
            }
            
            if (!is_user_logged_in()) {
                return $this->handle_error(
                    new \Exception(__('You must be logged in to access this endpoint.', 'wpdebugtoolkit')),
                    'Admin Permissions',
                    401
                );
            }

            if (!current_user_can('manage_options')) {
                return $this->handle_error(
                    new \Exception(__('You do not have permission to access this resource.', 'wpdebugtoolkit')),
                    'Admin Permissions',
                    403
                );
            }

            return true;
        } catch (\Exception $e) {
            return $this->handle_error($e, 'Check Admin Permissions', 500);
        }
    }

    /**
     * Get debug settings
     */
    public function get_settings($request) {
        try {
            if ($this->debug_manager) {
                $data = $this->debug_manager->get_settings();
            } else {
                $data = [
                    'debug_enabled' => false,
                    'debug_display' => false,
                    'debug_log' => false
                ];
                Error_Handler::log('Debug manager not initialized in get_settings', 'Core');
            }
            
            if ($this->viewer_manager) {
                $data['viewer_installed'] = $this->viewer_manager->is_installed();
            } else {
                $data['viewer_installed'] = false;
                Error_Handler::log('Viewer manager not initialized in get_settings', 'Core');
            }

            return $this->handle_response(['data' => $data]);
        } catch (\Exception $e) {
            return $this->handle_error($e, 'Failed to get settings');
        }
    }

    /**
     * @param \WP_REST_Request $request 
     * @return \WP_REST_Response 
     */
    public function update_settings($request) {
        try {
            $settings = [];
            
            $allowed_settings = ['debug_enabled', 'debug_display', 'debug_log'];
            
            foreach ($allowed_settings as $setting) {
                if ($request->has_param($setting)) {
                    $settings[$setting] = $request->get_param($setting);
                }
            }
            
            if (empty($settings)) {
                return $this->handle_error(
                    new \Exception(__('No valid settings provided to update', 'wpdebugtoolkit')),
                    'Update Settings',
                    400
                );
            }

            $this->debug_manager->update_settings($settings);

            $data = [
                'message' => __('Settings updated successfully', 'wpdebugtoolkit'),
                'settings' => $this->debug_manager->get_settings()
            ];
            
            return $this->handle_response($data);
        } catch (\Exception $e) {
            return $this->handle_error($e, 'Failed to update settings', 400);
        }
    }

    public function setup_viewer($request) {
        try {
            if (ob_get_level()) {
                ob_clean();
            }

            $result = $this->viewer_manager->setup();
            
            clear_caching_plugins_cache();

            return $this->handle_response([
                'message' => __('Viewer setup successfully', 'wpdebugtoolkit'),
                'success' => $result
            ]);
        } catch (\Exception $e) {
            if (!headers_sent()) {
                header('Content-Type: application/json');
            }
            
            return $this->handle_error($e, 'Failed to setup viewer', 500);
        }
    }

    /**
     * Remove viewer
     * @param \WP_REST_Request $request The REST request
     * @return \WP_REST_Response|\WP_Error
     */
    public function remove_viewer($request = null) {
        try {
            if (ob_get_level()) {
                ob_clean();
            }

            $result = $this->viewer_manager->remove();
            
            if ($result && $this->debug_manager) {
                $this->debug_manager->update_settings([
                    'debug_enabled' => false,
                    'debug_log' => false
                ]);
            }
            
            clear_caching_plugins_cache();
            
            return $this->handle_response([
                'message' => __('Viewer removed successfully', 'wpdebugtoolkit'),
                'success' => $result
            ]);
        } catch (\Exception $e) {
            if (!headers_sent()) {
                header('Content-Type: application/json');
            }
            
            return $this->handle_error($e, 'Failed to remove viewer', 500);
        }
    }

    public function get_health_check() {
        try {
            if (!$this->health_check) {
                return $this->handle_response($this->create_basic_health_data());
            }
            
            delete_transient(Constants::DBTK_CACHE_KEYS['HEALTH_CHECK']);
            
            try {
                $data = $this->health_check->get_simplified_health_data();
                
                $response_data = [
                    'success' => true,
                    'wp_health' => $data['wp_health'] ?? [
                        'good' => 0,
                        'recommended' => 0,
                        'critical' => 0,
                        'test_results' => []
                    ],
                    'debug_checks' => $data['debug_checks'] ?? [],
                    'test_results' => $data['wp_health']['test_results'] ?? []
                ];
                
                return new \WP_REST_Response($response_data, 200);
            } catch (\Throwable $e) {
                return $this->handle_response($this->create_basic_health_data());
            }
        } catch (\Throwable $e) {
            return $this->handle_response($this->create_basic_health_data());
        }
    }
    
    /**
     * Create basic health data 
     *
     * @return array
     */
    private function create_basic_health_data() {
        return [
            'wp_health' => [
                'good' => 0,
                'recommended' => 0,
                'critical' => 0,
                'test_results' => [
                    'basic_check' => [
                        'status' => 'warning',
                        'label' => __('Health Check', 'wpdebugtoolkit'),
                        'message' => __('Basic health check - limited functionality', 'wpdebugtoolkit')
                    ]
                ]
            ],
            'debug_checks' => [
                'debug_mode' => [
                    'status' => defined('WP_DEBUG') && WP_DEBUG ? 'good' : 'warning',
                    'badge' => __('Debug Mode', 'wpdebugtoolkit'),
                    'message' => defined('WP_DEBUG') && WP_DEBUG 
                        ? __('Debug mode is enabled', 'wpdebugtoolkit')
                        : __('Debug mode is disabled', 'wpdebugtoolkit'),
                ],
                'error_logging' => [
                    'status' => defined('WP_DEBUG_LOG') && WP_DEBUG_LOG ? 'good' : 'warning',
                    'badge' => __('Error Logging', 'wpdebugtoolkit'),
                    'message' => defined('WP_DEBUG_LOG') && WP_DEBUG_LOG
                        ? __('Error logging is enabled', 'wpdebugtoolkit')
                        : __('Error logging is disabled', 'wpdebugtoolkit'),
                ]
            ]
        ];
    }

    /**
     * Get license status
     */
    public function get_license_status($request) {
        try {
            $result = $this->license_manager->get_license_status();
            return $this->handle_response($result);
        } catch (\Exception $e) {
            return $this->handle_error($e, 'Get License Status');
        }
    }

    /**
     * Activate license
     * 
     * @param \WP_REST_Request $request The request
     * @return \WP_REST_Response The response
     */
    public function activate_license($request) {
        try {

            $license_key = $request->get_param('license_key');
            
            $result = $this->license_manager->activate_license($license_key);
            
            if (!$result['success']) {
                return $this->handle_error(
                    new \Exception($result['error'] ?? __('License activation failed', 'wpdebugtoolkit')),
                    'License Activation',
                    400
                );
            }
            
            return $this->handle_response($result);
        } catch (\Exception $e) {
            return $this->handle_error($e, 'License Activation');
        }
    }

    /**
     * Get license details
     */
    public function get_license_details($request) {
        try {
            $result = $this->license_manager->get_license_details();
            
            if (!$result['success']) {
                return $this->handle_error(
                    new \Exception($result['error'] ?? __('Failed to retrieve license details', 'wpdebugtoolkit')),
                    'Get License Details',
                    404
                );
            }

            return $this->handle_response([
                'details' => $result['details']
            ]);
        } catch (\Exception $e) {
            return $this->handle_error($e, 'Get License Details');
        }
    }

    /**
     * Deactivate license
     */
    public function deactivate_license() {
        try {
            $result = $this->license_manager->deactivate_license();
            
            if (!$result['success']) {
                return $this->handle_error(
                    new \Exception($result['error'] ?? __('License deactivation failed', 'wpdebugtoolkit')),
                    'License Deactivation',
                    400
                );
            }

            return $this->handle_response([
                'message' => $result['message']
            ]);
        } catch (\Exception $e) {
            return $this->handle_error($e, 'License Deactivation', 400);
        }
    }

    /**
     * Register Site Health tests
     */
    public function register_site_health_tests($tests) {
        $tests['direct']['debug_toolkit_debug_mode'] = [
            'label' => __('Debug Mode', 'wpdebugtoolkit'),
            'test'  => [$this->health_check, 'check_debug_mode'],
        ];

        $tests['direct']['debug_toolkit_error_logging'] = [
            'label' => __('Error Logging', 'wpdebugtoolkit'),
            'test'  => [$this->health_check, 'check_error_logging'],
        ];

        $tests['direct']['debug_toolkit_error_display'] = [
            'label' => __('Error Display', 'wpdebugtoolkit'),
            'test'  => [$this->health_check, 'check_error_display'],
        ];

        $tests['direct']['debug_toolkit_file_permissions'] = [
            'label' => __('Debug File Permissions', 'wpdebugtoolkit'),
            'test'  => [$this->health_check, 'check_file_permissions'],
        ];

        return $tests;
    }

    // -------------------------------------------------------------------------
    // PROTECTED METHODS
    // -------------------------------------------------------------------------

    /**
     * Constructor.
     */
    protected function __construct() {
        try {
            $this->health_check = self::get_service('health_check');
            $this->viewer_manager = self::get_service('viewer_manager');
            $this->debug_manager = self::get_service('debug_manager');
            $this->license_manager = self::get_service('license_manager');
        } catch (\Exception $e) {

            Error_Handler::log_exception($e, 'Failed to initialize services in Core constructor');
            
            $this->health_check = null;
            $this->viewer_manager = null;
            $this->debug_manager = null;
            $this->license_manager = null;
        }

        $this->updater = new Updater(Constants::DBTK_FILE);

        add_action('rest_api_init', [$this, 'register_rest_routes']);
        add_filter('site_health_tests', [$this, 'register_site_health_tests']);
    }

    // -------------------------------------------------------------------------
    // PRIVATE METHODS
    // -------------------------------------------------------------------------

    /**
     * Prevent cloning
     */
    public function __clone() {

    }

    /**
     * Prevent unserializing
     */
    public function __wakeup() {

    }

    /**
     * Check if feature requires license
     */
    private function requires_license() {
        return true; 
    }
   
    /**
     * Get viewer password status
     *   
     * @return \WP_REST_Response 
     */
    public function get_viewer_password_status() {
        try {
            $has_password = get_option('debug_toolkit_viewer_password_hash') ? true : false;
            $is_password_protection_enabled = get_option('debug_toolkit_viewer_password_protection_enabled', false);
            
            return $this->handle_response([
                'has_password' => $has_password,
                'is_password_protection_enabled' => $is_password_protection_enabled
            ]);
        } catch (\Exception $e) {
            return $this->handle_error($e, 'Failed to get viewer password status');
        }
    }
    
    /**
     * Set viewer password
     *
     * @param \WP_REST_Request $request 
     * @return \WP_REST_Response 
     */
    public function set_viewer_password($request) {
        try {
            $password = $request->get_param('password');
            $is_password_protection_enabled = $request->get_param('is_password_protection_enabled');
            
            // Preserve existing hash
            if (empty($password) && $is_password_protection_enabled !== null) {

                update_option('debug_toolkit_viewer_password_protection_enabled', (bool)$is_password_protection_enabled);
                
                $this->update_auth_file();
                
                return $this->handle_response([
                    'message' => __('Password protection settings updated successfully', 'wpdebugtoolkit'),
                    'success' => true
                ]);
            }
            
            // If password is provided, update both password and protection status
            if (!empty($password)) {
                $password_hash = password_hash($password, PASSWORD_DEFAULT);
                update_option('debug_toolkit_viewer_password_hash', $password_hash);
                
                if ($is_password_protection_enabled !== null) {
                    update_option('debug_toolkit_viewer_password_protection_enabled', (bool)$is_password_protection_enabled);
                }
                
                $this->update_auth_file();
                
                return $this->handle_response([
                    'message' => __('Viewer password set successfully', 'wpdebugtoolkit'),
                    'success' => true
                ]);
            }
            
            return $this->handle_error(
                new \Exception(__('No password or protection status provided', 'wpdebugtoolkit')),
                'Set Viewer Password',
                400
            );
            
        } catch (\Exception $e) {
            return $this->handle_error($e, 'Failed to set viewer password', 400);
        }
    }

    /**
     * Get auth file path
     *
     * @return string 
     */
    private function get_auth_file_path() {
        return ABSPATH . Constants::DBTK_VIEWER_DIR . '/auth.php';
    }

    /**
     * Create or update auth file
     *
     * @return bool
     */
    private function update_auth_file() {
        try {
            $auth_file_path = $this->get_auth_file_path();
            $viewer_dir = dirname($auth_file_path);
            
            if (!file_exists($viewer_dir)) {
                if (!wp_mkdir_p($viewer_dir)) {
                    throw new \Exception('Failed to create viewer directory');
                }
            }
            
            $password_hash = get_option('debug_toolkit_viewer_password_hash', '');
            $is_password_protection_enabled = get_option('debug_toolkit_viewer_password_protection_enabled', false);
            
            $auth_data = [
                'password_protection_enabled' => (bool)$is_password_protection_enabled,
                'password_hash' => $password_hash,
                'created_at' => gmdate('Y-m-d\TH:i:s\Z'),
                'updated_at' => gmdate('Y-m-d\TH:i:s\Z')
            ];
            
            if (file_exists($auth_file_path)) {
                $existing_data = $this->read_auth_file();
                if ($existing_data && isset($existing_data['created_at'])) {
                    $auth_data['created_at'] = $existing_data['created_at'];
                }
            }
            
            $php_content = "<?php\n";
            $php_content .= "if (!defined('DBTK_VIEWER_CONTEXT') || DBTK_VIEWER_CONTEXT !== 'api') {\n";
            $php_content .= "    header('HTTP/1.1 403 Forbidden');\n";
            $php_content .= "    exit('Access denied');\n";
            $php_content .= "}\n\n";
            $php_content .= "return " . var_export($auth_data, true) . ";\n";
            
            $result = @file_put_contents($auth_file_path, $php_content);
            if ($result === false) {
                $handle = @fopen($auth_file_path, 'w');
                
                if ($handle === false) {
                    throw new \Exception('Failed to create auth file');
                }
                
                $write_result = @fwrite($handle, $php_content);
                
                @fclose($handle);
                if ($write_result === false) {
                    throw new \Exception('Failed to write auth file');
                }
            }
            
            if (!chmod($auth_file_path, 0644)) {
                Error_Handler::log('Failed to set auth file permissions', 'Auth File');
            }
            
            return true;
            
        } catch (\Exception $e) {
            Error_Handler::log_exception($e, 'Update Auth File');
            return false;
        }
    }

    /**
     * Read auth file data
     *
     * @return array|null
     */
    private function read_auth_file() {
        try {
            $auth_file_path = $this->get_auth_file_path();
            
            if (!file_exists($auth_file_path)) {
                return null;
            }
            
            define('DBTK_VIEWER_CONTEXT', 'api');
            $data = include $auth_file_path;
            
            if (!is_array($data)) {
                throw new \Exception('Auth file did not return an array');
            }
            
            return $data;
            
        } catch (\Exception $e) {
            Error_Handler::log_exception($e, 'Read Auth File');
            return null;
        }
    }

    /**
     * Standard response handler
     *
     * @param mixed $data 
     * @param int $status 
     * @return \WP_REST_Response
     */
    private function handle_response($data, $status = 200) {
        $response = [
            'success' => true
        ];

        if (is_array($data)) {
            $response = array_merge($response, $data);
        } else {
            $response['data'] = $data;
        }

        return new \WP_REST_Response($response, $status);
    }

    /**
     * Standard error handler
     *
     * @param \Exception $e 
     * @param string $context 
     * @param int $status 
     * @return \WP_REST_Response
     */
    private function handle_error($e, $context = '', $status = 500) {
        try {
            if (class_exists('\\DebugToolkit\\Error_Handler')) {
                Error_Handler::log_exception($e, $context);
            } else {

                error_log('Debug Toolkit Error: ' . $e->getMessage() . ' [' . $context . ']');
            }
            
            return new \WP_REST_Response([
                'success' => false,
                'error' => $e->getMessage(),
                'context' => $context
            ], $status);
        } catch (\Exception $fatal_error) {

            error_log('Debug Toolkit Critical Error in error handler: ' . $fatal_error->getMessage());
            
            return new \WP_REST_Response([
                'success' => false,
                'error' => 'An unexpected error occurred. Please check server logs.',
                'fatal' => true
            ], 500);
        }
    }
} 