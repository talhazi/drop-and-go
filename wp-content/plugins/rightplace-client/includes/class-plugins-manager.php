<?php
namespace RightPlace;

use Rightplace_Client;
/**
 * WordPress REST API Plugin Manager
 * 
 * Provides REST API endpoints for uploading and managing WordPress plugins.
 * 
 * API Endpoint: /wp-json/rightplace/v1/{salt}/plugins/upload-plugin
 * 
 * Usage:
 * Method: POST
 * Endpoint: /wp-json/rightplace/v1/{salt}/plugins/upload-plugin
 * 
 * Headers:
 * - Authorization: Basic base64(username:password) or Bearer token
 * - Content-Type: multipart/form-data
 * 
 * Parameters:
 * - pluginfile: (Required) The ZIP file containing the plugin
 * - status: (Optional) Either 'activated' or 'deactivated' to set plugin state after upload
 * - overwrite: (Optional) Whether to overwrite an existing plugin with the same name
 * 
 * Example cURL request:
 * ```
 * curl -X POST \
 *   -H "Authorization: Basic base64(username:password)" \
 *   -F "pluginfile=@/path/to/plugin.zip" \
 *   -F "status=activated" \
 *   https://your-site.com/wp-json/rightplace/v1/{salt}plugins/upload-plugin
 * ```
 * 
 * Response:
 * ```json
 * {
 *   "message": "Plugin \"plugin-name\" uploaded and installed successfully. Plugin has been activated.",
 *   "action": "install",
 *   "plugin_folder": "plugin-name",
 *   "status_changed": "activated"
 * }
 * ```
 * 
 * Required Capabilities:
 * - User must have 'install_plugins' capability
 */
class Class_Plugins_Manager {

    const TEXTDOMAIN = 'wp-rest-api-plugin-uploader';

    public function __construct() {
        add_action('rest_api_init', array($this, 'register_routes'));
    }

    /** 
     * Register the single REST API route for uploading plugins.
     */
    public function register_routes() {

        $namespace = 'rightplace/v1';
        $salt = Rightplace_Client::get_url_salt();

        register_rest_route($namespace, '/' . $salt . '/plugins/upload-plugin', array(
            'methods' => 'POST',
            'callback' => array($this, 'upload_plugin'),
            'permission_callback' => array($this, 'permissions_check_install'),
            'args' => array(
                'status' => array(
                    'required' => true,
                    'type' => 'string',
                    'enum' => array('activated', 'deactivated'),
                    'default' => 'deactivated',
                    'description' => __('Whether to activate or deactivate the plugin after upload.', self::TEXTDOMAIN),
                ),
                'overwrite' => array(
                    'required' => false,
                    'type' => 'boolean',
                    'default' => false,
                    'description' => __('Whether to overwrite an existing plugin with the same name.', self::TEXTDOMAIN),
                ),
            ),
        ));
    }

    /**
     * Permission check for uploading plugins.
     */
    public function permissions_check_install($request) {
        return current_user_can('install_plugins');
    }

    /**
     * Upload and install a plugin, optionally activate/deactivate it.
     */
    public function upload_plugin($request) {
        $files = $request->get_file_params();
        $status = $request->get_param('status') ?: 'deactivated';
        $overwrite = $request->get_param('overwrite');

        // Check if a file was submitted
        if (empty($files['pluginfile'])) {
            return new \WP_Error('no_plugin_file', __('No file submitted.', self::TEXTDOMAIN), array('status' => 400));
        }

        $file = $files['pluginfile'];

        // Validate the uploaded file
        $file_check_result = $this->check_uploaded_file($file);
        if (is_wp_error($file_check_result)) {
            return $file_check_result;
        }

        // Handle plugin upload
        $result = $this->handle_plugin_upload($file, $overwrite);
        if (is_wp_error($result)) {
            return $result;
        }

        // FINAL VERIFICATION - Make sure the plugin folder and main file actually exist
        if (!file_exists(WP_PLUGIN_DIR . '/' . $result['plugin_folder'])) {
            return new \WP_Error('plugin_not_installed', __('Plugin installation failed - plugin folder does not exist.', self::TEXTDOMAIN));
        }
        
        if (!file_exists(WP_PLUGIN_DIR . '/' . $result['main_file'])) {
            return new \WP_Error('main_file_not_found', __('Plugin installation failed - main plugin file does not exist.', self::TEXTDOMAIN));
        }

        // Convert 'activated'/'deactivated' to 'activate'/'deactivate'
        $action = $status === 'activated' ? 'activate' : 'deactivate';

        // Always handle activation/deactivation if main_file exists
        $activation_result = $this->handle_plugin_activation($result['main_file'], $action);
        if (is_wp_error($activation_result)) {
            // If activation fails, CLEAN UP the plugin installation
            if (file_exists(WP_PLUGIN_DIR . '/' . $result['plugin_folder'])) {
                $this->recursive_rmdir(WP_PLUGIN_DIR . '/' . $result['plugin_folder']);
            }
            return new \WP_REST_Response(array(
                'code' => $activation_result->get_error_code(),
                'message' => $activation_result->get_error_message(),
                'data' => null
            ), 400);
        }
        $result['status_changed'] = $activation_result;

        // Determine the response message
        $message = $this->get_response_message($result);

        // Return success response
        return new \WP_REST_Response(array(
            'message' => $message,
            'action' => $result['action'],
            'plugin_folder' => $result['plugin_folder'],
            'status_changed' => isset($result['status_changed']) ? $result['status_changed'] : null,
        ), 200);
    }

    /**
     * Check uploaded file validity.
     */
    private function check_uploaded_file($file) {
        if ($file['error'] !== UPLOAD_ERR_OK) {
            return new \WP_Error('upload_error', __('An error occurred during file upload.', self::TEXTDOMAIN), array('status' => 500));
        }

        $max_upload_size = wp_max_upload_size();
        if ($file['size'] > $max_upload_size) {
            return new \WP_Error('file_too_large', sprintf(__('File size exceeds the maximum upload size of %s.', self::TEXTDOMAIN), size_format($max_upload_size)), array('status' => 400));
        }

        $wp_filetype = wp_check_filetype_and_ext($file['tmp_name'], $file['name']);
        if ($wp_filetype['ext'] !== 'zip' || $wp_filetype['type'] !== 'application/zip') {
            return new \WP_Error('invalid_file_type', __('Invalid file type. Please upload a ZIP file.', self::TEXTDOMAIN), array('status' => 400));
        }

        return true;
    }

    /**
     * Handle plugin upload and installation.
     */
    private function handle_plugin_upload($file, $overwrite = false) {
        global $wp_filesystem;
        if (!function_exists('WP_Filesystem')) {
            require_once ABSPATH . 'wp-admin/includes/file.php';
        }
        WP_Filesystem();

        $upload_dir = wp_upload_dir();
        $plugin_dir = WP_PLUGIN_DIR;

        $temp_dir = $upload_dir['basedir'] . '/temp_plugin_' . uniqid();
        if (!$wp_filesystem->mkdir($temp_dir)) {
            return new \WP_Error('fs_error', __('Could not create temporary directory.', self::TEXTDOMAIN));
        }

        $unzip_result = unzip_file($file['tmp_name'], $temp_dir);
        if (is_wp_error($unzip_result)) {
            $wp_filesystem->delete($temp_dir, true);
            return $unzip_result;
        }

        // Log contents of temp directory for debugging
        rp_dev_log('Plugin upload - temp dir contents: ' . print_r(scandir($temp_dir), true));
        
        $plugin_folders = glob($temp_dir . '/*', GLOB_ONLYDIR);
        if (empty($plugin_folders)) {
            $wp_filesystem->delete($temp_dir, true);
            return new \WP_Error('invalid_plugin', __('No plugin found in the ZIP file.', self::TEXTDOMAIN));
        }

        $plugin_folder = basename($plugin_folders[0]);
        $plugin_path = $plugin_dir . '/' . $plugin_folder;

        $action = 'install';
        if ($wp_filesystem->exists($plugin_path)) {
            if (!$overwrite) {
                $wp_filesystem->delete($temp_dir, true);
                return new \WP_Error('plugin_exists', __('Plugin already exists. Use "overwrite" parameter to replace.', self::TEXTDOMAIN));
            }
            $wp_filesystem->delete($plugin_path, true);
            $action = 'replace';
        }

        if (!$wp_filesystem->move($temp_dir . '/' . $plugin_folder, $plugin_path)) {
            $wp_filesystem->delete($temp_dir, true);
            return new \WP_Error('install_failed', __('Failed to install the plugin.', self::TEXTDOMAIN));
        }

        $wp_filesystem->delete($temp_dir, true);
        
        // Log contents of plugin directory after installation
        rp_dev_log('Plugin folder contents after install: ' . print_r(scandir($plugin_path), true));

        // Find the main plugin file
        $main_file = $this->find_main_plugin_file($plugin_path, $plugin_folder);
        
        if (empty($main_file)) {
            // Log that we couldn't find the main file
            rp_dev_log('Could not find main plugin file in: ' . $plugin_path);
            // Clean up the plugin directory
            $wp_filesystem->delete($plugin_path, true);
            return new \WP_Error('main_file_not_found', __('Unable to determine the main plugin file.', self::TEXTDOMAIN));
        }
        
        // Log the found main file
        rp_dev_log('Main plugin file found: ' . $main_file);
        
        // Verify the main file exists
        if (!file_exists(WP_PLUGIN_DIR . '/' . $main_file)) {
            rp_dev_log('Main plugin file does not exist: ' . WP_PLUGIN_DIR . '/' . $main_file);
            $wp_filesystem->delete($plugin_path, true);
            return new \WP_Error('main_file_missing', __('Main plugin file could not be found after installation.', self::TEXTDOMAIN));
        }

        return array(
            'action' => $action,
            'plugin_folder' => $plugin_folder,
            'main_file' => $main_file
        );
    }
    private function find_main_plugin_file($plugin_path, $plugin_folder) {
        if (!function_exists('get_plugin_data')) {
            require_once ABSPATH . 'wp-admin/includes/plugin.php';
        }
        
        $all_php_files = $this->get_all_php_files($plugin_path);
        
        $root_php_files = array();
        $main_dir_php_files = array();
        $other_php_files = array();
        
        foreach ($all_php_files as $file) {
            $relative_path = substr($file, strlen($plugin_path) + 1);
            $parts = explode('/', $relative_path);
            
            if (count($parts) === 1) {
                $root_php_files[] = $file;
            } else if (count($parts) === 2) {
                $main_dir_php_files[] = $file;
            } else {
                $other_php_files[] = $file;
            }
        }
        
        rp_dev_log('PHP files: Root: ' . count($root_php_files) . ', Main dir: ' . 
                 count($main_dir_php_files) . ', Other: ' . count($other_php_files));
        
        $plugin_name = basename($plugin_path);
        $likely_main_files = array();
        
        foreach ($root_php_files as $file) {
            $basename = basename($file, '.php');
            if (strtolower($basename) === strtolower($plugin_name) || 
                strtolower($basename) === strtolower(str_replace('-', '_', $plugin_name))) {
                $likely_main_files[] = $file;
            }
        }
        
        $sorted_php_files = array_merge($likely_main_files, $root_php_files, $main_dir_php_files, $other_php_files);
        
        $found_main_file = null;
        
        foreach ($sorted_php_files as $file) {
            $file_content = file_get_contents($file);
            
            if (strpos($file_content, 'Plugin Name:') !== false) {
                $plugin_data = get_plugin_data($file, false, false);
                
                if (!empty($plugin_data['Name'])) {
                    // Found a valid plugin main file
                    $relative_path = substr($file, strlen(WP_PLUGIN_DIR) + 1);
                    $found_main_file = $relative_path;
                    rp_dev_log('Found plugin main file: ' . $relative_path . ' with name: ' . $plugin_data['Name']);
                    break;
                }
            }
        }
        
        return $found_main_file;
    }

    /**
     * Get all PHP files in a directory and its subdirectories.
     * 
     * @param string $dir Directory to scan
     * @return array Array of full paths to PHP files
     */
    private function get_all_php_files($dir) {
        $results = array();
        $files = scandir($dir);
        
        foreach ($files as $file) {
            if ($file === '.' || $file === '..') continue;
            
            $path = $dir . '/' . $file;
            
            if (is_dir($path)) {
                $results = array_merge($results, $this->get_all_php_files($path));
            } else if (pathinfo($path, PATHINFO_EXTENSION) === 'php') {
                $results[] = $path;
            }
        }
        
        return $results;
    }

    /**
     * Handle plugin activation or deactivation.
     */
    private function handle_plugin_activation($plugin_file, $status) {
        if (!function_exists('activate_plugin')) {
            require_once ABSPATH . 'wp-admin/includes/plugin.php';
        }

        // Check if the plugin file exists
        if (!file_exists(WP_PLUGIN_DIR . '/' . $plugin_file)) {
            return new \WP_Error('plugin_not_found', __('Plugin file not found.', self::TEXTDOMAIN));
        }

        if ($status === 'deactivate') {
            deactivate_plugins($plugin_file, true); // Force deactivation
            return 'deactivated';
        }

        $is_active = is_plugin_active($plugin_file);

        if ($status === 'activate' && !$is_active) {
            $old_error_reporting = error_reporting(0);
            $result = activate_plugin($plugin_file);
            error_reporting($old_error_reporting);

            if (is_wp_error($result)) {
                return new \WP_Error('activation_failed', $result->get_error_message());
            }
            return 'activated';
        }

        return $is_active ? 'activated' : 'deactivated';
    }

    /**
     * Retrieve plugin data.
     */
    private function get_plugin_data($plugin_file) {
        if (!function_exists('get_plugin_data')) {
            require_once ABSPATH . 'wp-admin/includes/plugin.php';
        }
        return get_plugin_data($plugin_file, false, false);
    }

    /**
     * Generate a response message.
     */
    private function get_response_message($result) {
        $action_message = $result['action'] === 'replace'
            ? __('replaced', self::TEXTDOMAIN)
            : __('uploaded and installed', self::TEXTDOMAIN);

        $message = sprintf(__('Plugin "%s" %s successfully.', self::TEXTDOMAIN), $result['plugin_folder'], $action_message);

        if (isset($result['status_changed'])) {
            $status_message = $result['status_changed'] === 'activated'
                ? __('activated', self::TEXTDOMAIN)
                : __('deactivated', self::TEXTDOMAIN);
            $message .= ' ' . sprintf(__('Plugin has been %s.', self::TEXTDOMAIN), $status_message);
        }

        return $message;
    }

    /**
     * Helper function to recursively remove a directory
     */
    private function recursive_rmdir($dir) {
        if (is_dir($dir)) {
            $objects = scandir($dir);
            foreach ($objects as $object) {
                if ($object != "." && $object != "..") {
                    if (is_dir($dir . DIRECTORY_SEPARATOR . $object)) {
                        $this->recursive_rmdir($dir . DIRECTORY_SEPARATOR . $object);
                    } else {
                        unlink($dir . DIRECTORY_SEPARATOR . $object);
                    }
                }
            }
            rmdir($dir);
        }
    }

}

new Class_Plugins_Manager();
