<?php
error_reporting(0);
ini_set('display_errors', 0);

define('DBTK_VIEWER_LOG_FILE', sys_get_temp_dir() . '/debug_toolkit_viewer.log');

/**
 * Generate CSRF token
 * 
 * @return string 
 */
function dbtk_generate_csrf_token() {
    return bin2hex(random_bytes(32));
}

/**
 * Set a CSRF token cookie 
 * 
 * @return string 
 */
function dbtk_set_csrf_cookie() {
    $token = dbtk_generate_csrf_token();
    
    $secure = isSecure();
    
    setcookie('dbtk_csrf', $token, [
        'expires' => time() + 86400, 
        'path' => '/',
        'httponly' => true,
        'secure' => $secure, 
        'samesite' => 'Lax' 
    ]);
    
    return $token;
}

/**
 * Validate CSRF 
 * 
 * @return bool 
 */
function dbtk_validate_csrf_token() {

    $cookie_token = isset($_COOKIE['dbtk_csrf']) ? $_COOKIE['dbtk_csrf'] : '';
    
    $header_token = isset($_SERVER['HTTP_X_CSRF_TOKEN']) ? $_SERVER['HTTP_X_CSRF_TOKEN'] : '';
    $post_token = isset($_POST['csrf_token']) ? $_POST['csrf_token'] : '';
    $get_token = isset($_GET['csrf_token']) ? $_GET['csrf_token'] : '';
    
    $submitted_token = $header_token ?: $post_token ?: $get_token;
    
    if (empty($cookie_token) || empty($submitted_token)) {
        error_log("CSRF Validation Failed - Missing token(s). Cookie: " . 
                 (empty($cookie_token) ? "Missing" : "Present") . ", Submitted: " . 
                 (empty($submitted_token) ? "Missing" : "Present"), 3, DBTK_VIEWER_LOG_FILE);
        return false;
    }
    
    $is_valid = hash_equals($cookie_token, $submitted_token);
    
    if (!$is_valid) {
        error_log("CSRF Validation Failed - Token mismatch", 3, DBTK_VIEWER_LOG_FILE);
    }
    
    return $is_valid;
}

/**
 * Standarize error handling
 * This is a standalone version that mimics the Error_Handler class functionality
 * but doesn't depend on WordPress
 * 
 * @param Exception 
 * @param string 
 * @return void 
 */
function dbtk_handle_error($e, $context = '') {
    error_log("Debug Toolkit Viewer Error" . ($context ? " [$context]" : '') . ": " . $e->getMessage() . PHP_EOL, 3, DBTK_VIEWER_LOG_FILE);
    
    header('Content-Type: application/json');
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'An error occurred',
        'message' => $e->getMessage(),
        'context' => $context
    ]);
    exit;
}

/**
 * Custom error handler
 * 
 * @param int $errno Error number
 * @param string $errstr Error message
 * @param string $errfile File where the error occurred
 * @param int $errline Line where the error occurred
 * @return bool 
 */
function dbtk_viewer_error_handler($errno, $errstr, $errfile, $errline) {
    $error_message = date('[Y-m-d H:i:s]') . " [$errno] $errstr in $errfile:$errline";
    error_log($error_message . PHP_EOL, 3, DBTK_VIEWER_LOG_FILE);
    return true; 
}

/**
 * Custom exception handler
 * 
 * @param Exception 
 * @return void
 */
function dbtk_viewer_exception_handler($e) {
    dbtk_handle_error($e, 'Uncaught Exception');
}

set_error_handler('dbtk_viewer_error_handler');
set_exception_handler('dbtk_viewer_exception_handler');

/**
 * Standard viewer API response format
 * 
 * @param mixed 
 * @param bool 
 * @param int $status HTTP status code
 * @param string $error Optional error message
 * @return void Outputs JSON response and exits
 */
function dbtk_viewer_api_response($data, $success = true, $status = 200, $error = '') {
    header('Content-Type: application/json');
    http_response_code($status);
    
    $response = [
        'success' => $success
    ];
    
    if ($success) {
        $response['data'] = $data;
    } else {
        $response['error'] = $error;
    }
    
    echo json_encode($response);
    exit;
}

if (!defined('ABSPATH')) {
    define('ABSPATH', dirname(dirname(__FILE__)) . '/');
}

if (!defined('WP_CONTENT_DIR')) {
    define('WP_CONTENT_DIR', ABSPATH . 'wp-content');
}

$config = require __DIR__ . '/config.php';

/**
 * Load auth settings
 */
function dbtk_load_auth_settings() {
    $auth_file_path = __DIR__ . '/auth.php';
    
    if (!file_exists($auth_file_path)) {
        return [
            'password_protection_enabled' => false,
            'password_hash' => ''
        ];
    }
    
    if (!defined('DBTK_VIEWER_CONTEXT')) {
        define('DBTK_VIEWER_CONTEXT', 'api');
    }
    
    $auth_data = @include $auth_file_path;
    
    if (!is_array($auth_data)) {
        return [
            'password_protection_enabled' => false,
            'password_hash' => ''
        ];
    }
    
    return [
        'password_protection_enabled' => isset($auth_data['password_protection_enabled']) ? (bool)$auth_data['password_protection_enabled'] : false,
        'password_hash' => isset($auth_data['password_hash']) ? $auth_data['password_hash'] : ''
    ];
}

$auth_settings = dbtk_load_auth_settings();
$password_hash = $auth_settings['password_hash'];
$is_password_protection_enabled = $auth_settings['password_protection_enabled'];

if ($is_password_protection_enabled) {
    error_log("Debug Toolkit Viewer: Password protection enabled, hash present: " . (!empty($password_hash) ? 'Yes' : 'No'), 3, DBTK_VIEWER_LOG_FILE);
} else {
    error_log("Debug Toolkit Viewer: Password protection disabled", 3, DBTK_VIEWER_LOG_FILE);
}

$allowed_actions = [
    'get_logs',
    'clear_logs',
    'get_file_content',
    'get_modules_status',
    'disable_plugins',
    'enable_plugins',
    'disable_themes',
    'enable_themes',
    'get_csrf_token', 
    'authenticate',   
    'check_auth'      
];

function isSecure(){
    $isLocal = (
        strpos($_SERVER['HTTP_HOST'] ?? '', 'localhost') !== false ||
        strpos($_SERVER['HTTP_HOST'] ?? '', '.local') !== false ||
        strpos($_SERVER['HTTP_HOST'] ?? '', '.test') !== false ||
        strpos($_SERVER['HTTP_HOST'] ?? '', '.dev') !== false ||
        strpos($_SERVER['HTTP_HOST'] ?? '', '.docker') !== false ||
        strpos($_SERVER['HTTP_HOST'] ?? '', '127.0.0.1') !== false ||
        strpos($_SERVER['HTTP_HOST'] ?? '', '192.168.') !== false
        // Any other... probably?
    );
    $isSecure = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';
    
    return $isSecure && !$isLocal;
}
/**
 * Initialize session
 */
function dbtk_init_session() {
    $secure = isSecure();
    
    $sessionSavePath = session_save_path();
    if (!empty($sessionSavePath) && !is_writable($sessionSavePath)) {
        error_log("Warning: Session save path is not writable: $sessionSavePath", 3, DBTK_VIEWER_LOG_FILE);
        
        $tempDir = sys_get_temp_dir();
        if (is_writable($tempDir)) {
            session_save_path($tempDir);
            error_log("Using alternate session save path: $tempDir", 3, DBTK_VIEWER_LOG_FILE);
        }
    }
    
    $currentSavePath = session_save_path();
    if (!empty($currentSavePath)) {
        error_log("Current session save path: $currentSavePath, Writable: " . (is_writable($currentSavePath) ? 'Yes' : 'No'), 3, DBTK_VIEWER_LOG_FILE);
    }
    
    session_set_cookie_params([
        'lifetime' => 86400, 
        'path' => '/',
        'domain' => '',
        'secure' => $secure,
        'httponly' => true,
        'samesite' => 'Lax'
    ]);
    
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
        error_log("Session started with ID: " . session_id(), 3, DBTK_VIEWER_LOG_FILE);
    } else {
        error_log("Session already active with ID: " . session_id(), 3, DBTK_VIEWER_LOG_FILE);
    }
}

/**
 * Check if user is authenticated
 * 
 * @return bool 
 */
function dbtk_is_authenticated() {

    dbtk_init_session();
    
    $is_authenticated = isset($_SESSION['dbtk_authenticated']) && $_SESSION['dbtk_authenticated'] === true;
    
    error_log("Session ID: " . session_id() . ", Auth Check Result: " . ($is_authenticated ? 'true' : 'false'), 3, DBTK_VIEWER_LOG_FILE);
    if (!$is_authenticated) {
        error_log("Session data: " . print_r($_SESSION, true), 3, DBTK_VIEWER_LOG_FILE);
    }
    
    return $is_authenticated;
}

/**
 * Authenticate user
 * 
 * @param string 
 * @return bool 
 */
function dbtk_authenticate($password) {
    global $password_hash;
    
    if (empty($password_hash)) {
        error_log("Authentication failed: No password hash configured", 3, DBTK_VIEWER_LOG_FILE);
        return false;
    }
    
    $authenticated = password_verify($password, $password_hash);
    error_log("Password verification result: " . ($authenticated ? "Success" : "Failed"), 3, DBTK_VIEWER_LOG_FILE);
    
    if ($authenticated) {
        dbtk_init_session();
        
        $_SESSION['dbtk_authenticated'] = true;
        $_SESSION['dbtk_auth_time'] = time();
        
        session_write_close();
        
        session_start();
        $verification = isset($_SESSION['dbtk_authenticated']) && $_SESSION['dbtk_authenticated'] === true;
        error_log("Authentication successful. Session verification: " . ($verification ? "Confirmed" : "Failed"), 3, DBTK_VIEWER_LOG_FILE);
        
        return true;
    } else {
        error_log("Authentication failed: Invalid password", 3, DBTK_VIEWER_LOG_FILE);
        return false;
    }
}

$action = isset($_GET['action']) ? trim(htmlspecialchars($_GET['action'])) : '';

if (!in_array($action, $allowed_actions, true)) {
    dbtk_viewer_api_response(null, false, 400, 'Invalid action');
}

header('Content-Type: application/json');

// Skip auth check if password protection is disabled
if (!in_array($action, ['authenticate', 'check_auth', 'get_csrf_token']) && 
    $is_password_protection_enabled && !dbtk_is_authenticated()) {
    dbtk_viewer_api_response(null, false, 401, 'Authentication required');
    exit;
}

try {
    switch ($action) {

        case 'authenticate':
            $password = isset($_POST['password']) ? $_POST['password'] : '';
            if (empty($password)) {
                dbtk_viewer_api_response(null, false, 400, 'Password is required');
            }
            
            $authenticated = dbtk_authenticate($password);
            if ($authenticated) {
                dbtk_viewer_api_response([
                    'authenticated' => true
                ]);
            } else {
                dbtk_viewer_api_response([
                    'authenticated' => false
                ], false, 401, 'Invalid password');
            }
            break;
            
        case 'check_auth':
            $requires_auth = $is_password_protection_enabled;
            $is_authenticated = $requires_auth ? dbtk_is_authenticated() : true;
            
            dbtk_viewer_api_response([
                'requires_auth' => $requires_auth,
                'authenticated' => $is_authenticated
            ]);
            break;
            

            case 'get_csrf_token':
            $token = dbtk_set_csrf_cookie();
            dbtk_viewer_api_response([
                'token' => $token
            ]);
            break;
            
        case 'get_logs':
            $logs = dbtk_get_logs($config['log_file']);
            if ($logs['success']) {
                echo json_encode($logs['data']);
                exit;
            } else {
                dbtk_viewer_api_response(null, false, 400, $logs['error']);
            }
            break;
            
        case 'clear_logs':

            if (!dbtk_validate_csrf_token()) {
                dbtk_viewer_api_response(null, false, 403, 'Invalid or missing CSRF token');
            }
            
            $result = dbtk_clear_logs($config['log_file']);
            if ($result['success']) {
                dbtk_viewer_api_response($result);
            } else {
                dbtk_viewer_api_response(null, false, 400, $result['error']);
            }
            break;
            
        case 'get_file_content':
            if (!isset($_GET['file']) || !isset($_GET['line'])) {
                dbtk_viewer_api_response(null, false, 400, 'Missing file or line parameter');
            }
            
            $file = trim(htmlspecialchars($_GET['file']));
            $line = (int)$_GET['line'];
            $original_log_entry = isset($_GET['log_entry']) ? urldecode(trim(htmlspecialchars($_GET['log_entry']))) : null;
            
            if (!dbtk_validate_file_path($file)) {

                dbtk_viewer_api_response([
                    'content' => ['Invalid file path'],
                    'start_line' => 1,
                    'error_line' => $line,
                    'file' => $file,
                    'total_lines' => 1,
                    'original_log_entry' => $original_log_entry,
                    'logEntry' => $original_log_entry
                ], false, 403, 'Invalid file path');
                exit;
            }
            
            $content = dbtk_get_file_content($file, $line, $original_log_entry);
            
            if (!$content['success']) {
                echo json_encode($content);
                exit;
            } else {
                echo json_encode($content);
                exit;
            }
            
        case 'get_modules_status':
            $status = dbtk_get_modules_status();
            dbtk_viewer_api_response($status);
            break;
            
        case 'disable_plugins':
            if (!dbtk_validate_csrf_token()) {
                dbtk_viewer_api_response(null, false, 403, 'Invalid or missing CSRF token');
            }
            
            $result = dbtk_toggle_modules('plugins', false);
            if ($result['success']) {
                dbtk_viewer_api_response($result);
            } else {
                dbtk_viewer_api_response(null, false, 400, $result['error']);
            }
            break;
            
        case 'enable_plugins':

            if (!dbtk_validate_csrf_token()) {
                dbtk_viewer_api_response(null, false, 403, 'Invalid or missing CSRF token');
            }
            
            $result = dbtk_toggle_modules('plugins', true);
            if ($result['success']) {
                dbtk_viewer_api_response($result);
            } else {
                dbtk_viewer_api_response(null, false, 400, $result['error']);
            }
            break;
            
        case 'disable_themes':

            if (!dbtk_validate_csrf_token()) {
                dbtk_viewer_api_response(null, false, 403, 'Invalid or missing CSRF token');
            }
            
            $result = dbtk_toggle_modules('themes', false);
            if ($result['success']) {
                dbtk_viewer_api_response($result);
            } else {
                dbtk_viewer_api_response(null, false, 400, $result['error']);
            }
            break;
            
        case 'enable_themes':

            if (!dbtk_validate_csrf_token()) {
                dbtk_viewer_api_response(null, false, 403, 'Invalid or missing CSRF token');
            }
            
            $result = dbtk_toggle_modules('themes', true);
            if ($result['success']) {
                dbtk_viewer_api_response($result);
            } else {
                dbtk_viewer_api_response(null, false, 400, $result['error']);
            }
            break;
    }
} catch (Exception $e) {
    dbtk_handle_error($e, 'API Action: ' . $action);
}

/**
 * Validates if a path is safe and allowed
 * 
 * @param string 
 * @param array 
 * @return bool 
 */
function dbtk_is_path_allowed($path, $allowed_roots = []) {
    $real_path = realpath($path);
    if (!$real_path) return false;
    
    if (empty($allowed_roots)) {
        $allowed_roots = [realpath(ABSPATH)];
        
        $wp_content_real = realpath(WP_CONTENT_DIR);
        if ($wp_content_real) {
            $allowed_roots[] = $wp_content_real;
        }
    }
    
    foreach ($allowed_roots as $root) {
        $real_root = realpath($root);
        if ($real_root && strpos($real_path, $real_root) === 0) {
            return true;
        }
    }
    
    return false;
}

/**
 * Validates a file path
 * 
 * @param string 
 * @return bool 
 */
function dbtk_validate_file_path($file_path) {

    if (empty($file_path) || !is_string($file_path)) {
        error_log("Debug Toolkit Viewer: Empty or non-string file path provided", 3, DBTK_VIEWER_LOG_FILE);
        return false;
    }
    
    $file_path = urldecode($file_path);
    
    $file_path = str_replace('\\', '/', $file_path);

    if (basename($file_path) === 'wp-config.php') {
        error_log("Debug Toolkit Viewer: Direct access to wp-config.php is forbidden.", 3, DBTK_VIEWER_LOG_FILE);
        return false;
    }
    
    if (strpos($file_path, '..') !== false || 
        preg_match('#/\.+/#', $file_path) ||
        preg_match('#^\.+/#', $file_path) ||
        preg_match('#\\0#', $file_path) ||  
        preg_match('#/(proc|etc|sys|dev|bin|sbin|var|tmp)/|^/(proc|etc|sys|dev|bin|sbin|var|tmp)/#', $file_path)) { // Common system directories
        
        error_log("Debug Toolkit Viewer: Potentially malicious file path detected: " . $file_path, 3, DBTK_VIEWER_LOG_FILE);
        return false;
    }
    
    $dangerous_extensions = [
        'php', 'phtml', 'php3', 'php4', 'php5', 'php7', 'pht', 'phar', 'inc',
        'sh', 'bash', 'pl', 'py', 'cgi', 'exe', 'bat', 'cmd', 'dll', 'so', 'com'
    ];

    if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $extension = strtolower(pathinfo($file_path, PATHINFO_EXTENSION));
        if (in_array($extension, $dangerous_extensions, true)) {
            error_log("Debug Toolkit Viewer: Potentially dangerous file extension detected: " . $extension, 3, DBTK_VIEWER_LOG_FILE);
            return false;
        }
    }
    
    if (file_exists($file_path)) {
        return dbtk_is_path_allowed($file_path);
    }
    
    $possible_paths = [
        $file_path,                                
        ABSPATH . $file_path,                      
        WP_CONTENT_DIR . '/' . $file_path,         
        ABSPATH . 'wp-content/plugins/' . $file_path 
    ];
    
    if (strpos($file_path, 'wp-content/') === 0) {
        $possible_paths[] = ABSPATH . $file_path;
    }
    
    foreach ($possible_paths as $path) {
        if (file_exists($path) && dbtk_is_path_allowed($path)) {
            return true;
        }
    }

    return true;
}

function dbtk_get_logs($file) {
    if (empty($file)) {
        return ['success' => false, 'error' => 'Log file path is empty'];
    }
    
    if (!file_exists($file)) {
        // Try to create an empty log file
        @touch($file);
        
        if (!file_exists($file)) {
            return ['success' => false, 'error' => 'Log file does not exist: ' . $file];
        }
    }
    
    if (!is_readable($file)) {
        return ['success' => false, 'error' => 'Log file is not readable: ' . $file];
    }
    
    $filesize = filesize($file);
    if ($filesize === 0) {
        return ['success' => true, 'data' => ['logs' => []]];
    }
    
    $lines = @file($file, FILE_IGNORE_NEW_LINES);
    if ($lines === false) {
        return ['success' => false, 'error' => 'Failed to read log file: ' . $file];
    }
    
    return ['success' => true, 'data' => ['logs' => $lines]];
}

function dbtk_clear_logs($file) {
    if (empty($file)) {
        return ['success' => false, 'error' => 'Empty file path'];
    }
    
    if (file_exists($file)) {
        if (!is_writable($file)) {
            return ['success' => false, 'error' => 'Log file is not writable'];
        }
        
        $result = @file_put_contents($file, '');
        if ($result !== false) {
            return ['success' => true];
        } else {
            return ['success' => false, 'error' => 'Failed to clear log file'];
        }
    }
    return ['success' => false, 'error' => 'Log file does not exist'];
}

function dbtk_get_file_content($file_path, $line_number, $original_log_entry = null) {

    
    $debug_info = [
        'original_path' => $file_path,
        'abspath' => ABSPATH,
        'wp_content_dir' => WP_CONTENT_DIR
    ];
    
    $possible_paths = [
        $file_path,                                
        ABSPATH . $file_path,                      
        WP_CONTENT_DIR . '/' . $file_path,    
        ABSPATH . 'wp-content/plugins/' . $file_path 
    ];
    
    if (strpos($file_path, 'wp-content/') === 0) {
        $possible_paths[] = ABSPATH . $file_path;
    }
    
    $debug_info['possible_paths'] = $possible_paths;
    
    $file_found = false;
    $file_path = '';
    
    foreach ($possible_paths as $path) {
        if (file_exists($path)) {
            if (!dbtk_is_path_allowed($path)) {
                continue; 
            }
            
            $file_path = $path;
            $file_found = true;
            $debug_info['found_path'] = $path;
            break;
        }
    }
    
    if (!$file_found || empty($file_path)) {
        return [
            'success' => false,
            'error' => 'File does not exist or is outside allowed directories',
            'data' => [
                'content' => ['File not found or inaccessible'],
                'start_line' => 1,
                'error_line' => $line_number ? $line_number : 1,
                'file' => $file_path ? $file_path : 'File not found',
                'total_lines' => 1,
                'original_log_entry' => $original_log_entry,
                'debug' => $debug_info
            ]
        ];
    }
    
    if (!is_readable($file_path)) {
        return [
            'success' => false,
            'error' => 'File is not readable',
            'data' => [
                'content' => ['File is not readable'],
                'start_line' => 1,
                'error_line' => $line_number ? $line_number : 1,
                'file' => $file_path,
                'total_lines' => 1,
                'original_log_entry' => $original_log_entry,
                'debug' => $debug_info
            ]
        ];
    }
    
    $lines = @file($file_path, FILE_IGNORE_NEW_LINES);
    if ($lines === false) {
        return [
            'success' => false,
            'error' => 'Failed to read file',
            'data' => [
                'content' => ['Failed to read file'],
                'start_line' => 1,
                'error_line' => $line_number ? $line_number : 1,
                'file' => $file_path,
                'total_lines' => 1,
                'original_log_entry' => $original_log_entry,
                'debug' => $debug_info
            ]
        ];
    }
    
    $total_lines = count($lines);
    
    $safe_error_line = max(1, min($total_lines, intval($line_number)));
    
    return [
        'success' => true,
        'data' => [
            'content' => $lines,
            'start_line' => 1, 
            'error_line' => $safe_error_line,
            'file' => $file_path,
            'total_lines' => $total_lines,
            'original_log_entry' => $original_log_entry,
            'logEntry' => $original_log_entry, 
            'debug' => $debug_info
        ]
    ];
}

/**
 * Get status of plugins and themes
 * 
 * @return array 
 */
function dbtk_get_modules_status() {
    $plugins_dir = WP_CONTENT_DIR . '/plugins';
    $themes_dir = WP_CONTENT_DIR . '/themes';
    $plugins_disabled_dir = WP_CONTENT_DIR . '/plugins-disabled';
    $themes_disabled_dir = WP_CONTENT_DIR . '/themes-disabled';
    
    $plugins_exist = file_exists($plugins_dir) && is_dir($plugins_dir);
    $themes_exist = file_exists($themes_dir) && is_dir($themes_dir);
    
    $plugins_disabled = file_exists($plugins_disabled_dir) && is_dir($plugins_disabled_dir);
    $themes_disabled = file_exists($themes_disabled_dir) && is_dir($themes_disabled_dir);
    
    $plugins_count = $plugins_exist ? count(array_filter(scandir($plugins_dir), fn($item) => 
        $item !== '.' && $item !== '..' && is_dir($plugins_dir . '/' . $item))) : 0;
    
    $themes_count = $themes_exist ? count(array_filter(scandir($themes_dir), fn($item) => 
        $item !== '.' && $item !== '..' && is_dir($themes_dir . '/' . $item))) : 0;
    
    $root_url = dbtk_get_wp_root_url();
    
    if (substr($root_url, -1) !== '/') {
        $root_url .= '/';
    }
    
    $admin_url = $root_url . 'wp-admin';
    
    return [
        'plugins' => [
            'enabled' => $plugins_exist,
            'disabled' => $plugins_disabled,
            'count' => $plugins_count,
            'admin_url' => $admin_url . '/plugins.php'
        ],
        'themes' => [
            'enabled' => $themes_exist,
            'disabled' => $themes_disabled,
            'count' => $themes_count,
            'admin_url' => $admin_url . '/themes.php'
        ],
        'wp_admin_url' => $admin_url . '/'
    ];
}

/**
 * Toggle plugins or themes (enable/disable)
 * 
 * @param string 
 * @param bool 
 * @return array 
 */

function dbtk_toggle_modules($module_type, $enable) {
    if ($module_type !== 'plugins' && $module_type !== 'themes') {
        return ['success' => false, 'error' => 'Invalid module type'];
    }
    
    $source_dir = $enable 
        ? WP_CONTENT_DIR . "/{$module_type}-disabled" 
        : WP_CONTENT_DIR . "/{$module_type}";
    
    $target_dir = $enable 
        ? WP_CONTENT_DIR . "/{$module_type}" 
        : WP_CONTENT_DIR . "/{$module_type}-disabled";
    
    if (!file_exists($source_dir) || !is_dir($source_dir)) {
        if ($enable) {
            return ['success' => false, 'error' => "No disabled {$module_type} found"];
        } else {
            return ['success' => false, 'error' => "No {$module_type} directory found"];
        }
    }
    
    if (!dbtk_is_path_allowed($source_dir) || !dbtk_is_path_allowed(dirname($target_dir))) {
        return ['success' => false, 'error' => "Invalid directory paths detected"];
    }
    
    if (file_exists($target_dir)) {
        if (is_dir($target_dir)) {
            return ['success' => false, 'error' => "Both enabled and disabled {$module_type} directories exist"];
        } else {
            return ['success' => false, 'error' => "Target directory path exists but is not a directory"];
        }
    }
    
    if (!is_writable(dirname($source_dir)) || !is_writable(dirname($target_dir))) {
        return ['success' => false, 'error' => "Insufficient permissions to rename directories"];
    }
    
    $source_contents = array_diff(scandir($source_dir), ['.', '..']);
    if (count($source_contents) === 0) {
        return ['success' => false, 'error' => "Source directory is empty"];
    }
    
    $result = rename($source_dir, $target_dir);
    
    if ($result) {
        $action = $enable ? 'enabled' : 'disabled';
        $root_url = dbtk_get_wp_root_url();
        
        if (substr($root_url, -1) !== '/') {
            $root_url .= '/';
        }
        
        $admin_url = $root_url . 'wp-admin';
        
        return [
            'success' => true, 
            'message' => "Successfully {$action} all {$module_type}",
            'wp_admin_url' => $admin_url . '/'
        ];
    } else {
        return ['success' => false, 'error' => "Failed to toggle {$module_type}"];
    }
}

/**
 * Get WordPress root URL 
 * 
 * @return string 
 */
function dbtk_get_wp_root_url() {
    if (function_exists('home_url')) {
        return home_url();
    }
    
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
    
    $host = $_SERVER['HTTP_HOST'] ?? '';
    if (empty($host)) {
        $host = $_SERVER['SERVER_NAME'] ?? 'localhost';
    }
    
    $path = dirname(__FILE__);
    $root_path = '';
    
    while (!file_exists($path . '/wp-config.php') && dirname($path) !== $path) {
        $path = dirname($path);
    }
    
    if (file_exists($path . '/wp-config.php')) {
        $root_path = $path;
    }
    
    if ($root_path) {
        $document_root = $_SERVER['DOCUMENT_ROOT'] ?? '';
        if ($document_root && strpos($root_path, $document_root) === 0) {
            $relative_path = substr($root_path, strlen($document_root));
            return $protocol . '://' . $host . str_replace('\\', '/', $relative_path);
        }
    }
    
    $current_path = $_SERVER['REQUEST_URI'] ?? '';
    $current_path = strtok($current_path, '?');
    
    if (strpos($current_path, '/wpdebugtoolkit') !== false) {
        $current_path = preg_replace('#(/wpdebugtoolkit)(?:/.*)?$#', '', $current_path);
    }
    
    if (preg_match('#(.*?)(?:/wp-content|/wp-admin|/wp-includes)(?:/.*)?$#', $current_path, $matches)) {
        return $protocol . '://' . $host . $matches[1];
    }
    
    return $protocol . '://' . $host;
} 