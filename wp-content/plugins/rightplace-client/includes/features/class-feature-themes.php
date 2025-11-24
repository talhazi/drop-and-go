<?php
namespace Rightplace\Features;

/**
 * Theme management functionality for Rightplace
 */
class Themes {
    const TEXTDOMAIN = 'rightplace';

    public function __construct() {
        rp_dev_log('Rightplace Themes class initialized');
        // Register REST API routes
        add_action('rest_api_init', array($this, 'register_routes'));
        
        // Register legacy filters
        add_filter('rightplace_action_filter/getThemes', [$this, 'getThemes']);
        add_filter('rightplace_action_filter/uploadTheme', [$this, 'uploadTheme']);
        add_filter('rightplace_action_filter/activateTheme', [$this, 'activateTheme']);
        add_filter('rightplace_action_filter/deleteTheme', [$this, 'deleteTheme']);
    }

    /** 
     * Register the REST API routes for theme management
     */
    public function register_routes() {
        $namespace = 'rightplace/v1';
        $salt = \Rightplace_Client::get_url_salt();

        rp_dev_log('Registering Rightplace theme REST routes');
        register_rest_route($namespace, '/' . $salt . '/themes/upload-theme', array(
            'methods' => 'POST',
            'callback' => array($this, 'upload_theme_endpoint'),
            'permission_callback' => array($this, 'permissions_check_install'),
            'args' => array(
                'status' => array(
                    'required' => false,
                    'type' => 'string',
                    'enum' => array('activated', 'deactivated'),
                    'default' => 'deactivated',
                    'description' => __('Whether to activate the theme after upload.', self::TEXTDOMAIN),
                ),
                'overwrite' => array(
                    'required' => false,
                    'type' => 'boolean',
                    'default' => false,
                    'description' => __('Whether to overwrite an existing theme.', self::TEXTDOMAIN),
                ),
            ),
        ));
        rp_dev_log('Theme REST routes registered');
    }

    /**
     * Permission check for uploading themes
     */
    public function permissions_check_install($request) {
        rp_dev_log('Theme upload permission check');
        // Skip permission check for development
        rp_dev_log('Permission check returning: true (development mode)');
        return true;
        // For production: Uncomment the line below
        // return current_user_can('switch_themes') && current_user_can('upload_themes');
    }

    /**
     * Handle theme upload via REST API
     * This function serves as the REST API endpoint handler
     */
    public function upload_theme_endpoint($request) {
        rp_dev_log('===== THEME UPLOAD REST ENDPOINT CALLED =====');
        
        // Load REST API if needed
        if (!class_exists('WP_REST_Response')) {
            rp_dev_log('Loading REST API classes');
            require_once(ABSPATH . 'wp-includes/rest-api.php');
        }
        
        try {
            rp_dev_log('Getting file parameters from request');
            $files = $request->get_file_params();
            rp_dev_log('Files received: ' . json_encode($files));
            
            rp_dev_log('Getting status parameter');
            $status = $request->get_param('status') ?: 'deactivated';
            rp_dev_log('Status parameter: ' . $status);
            
            rp_dev_log('Getting overwrite parameter');
            $overwrite = $request->get_param('overwrite');
            rp_dev_log('Raw overwrite param: ' . var_export($overwrite, true));
            $overwrite = filter_var($overwrite, FILTER_VALIDATE_BOOLEAN);
            rp_dev_log('Filtered overwrite value: ' . ($overwrite ? 'true' : 'false'));
            
            rp_dev_log('Raw request data: ' . file_get_contents('php://input'));
            rp_dev_log('REQUEST variables: ' . json_encode($_REQUEST));
            rp_dev_log('POST variables: ' . json_encode($_POST));
            rp_dev_log('FILES variables: ' . json_encode($_FILES));

            // Check if a file was submitted
            if (empty($files['theme'])) {
                rp_dev_log('No theme file found in the request');
                return new \WP_REST_Response(array(
                    'success' => false,
                    'message' => 'No theme file was provided'
                ), 400);
            }
            
            $theme_file = $files['theme'];
            rp_dev_log('Theme file details: ' . json_encode($theme_file));

            // Validate the uploaded file
            rp_dev_log('Validating uploaded file');
            $file_check_result = $this->check_uploaded_file($theme_file);
            if (is_wp_error($file_check_result)) {
                rp_dev_log('File validation failed: ' . $file_check_result->get_error_message());
                return new \WP_REST_Response(array(
                    'success' => false,
                    'message' => $file_check_result->get_error_message()
                ), 400);
            }
            rp_dev_log('File validation passed');

            // Process the upload with the same logic as the filter method
            rp_dev_log('Processing theme upload');
            $result = $this->process_theme_upload($theme_file, $status, $overwrite);
            rp_dev_log('Upload process result: ' . json_encode($result));
            
            if (!$result['success']) {
                rp_dev_log('Theme upload failed: ' . $result['message']);
                return new \WP_REST_Response($result, 400);
            }
            
            rp_dev_log('Theme upload succeeded');
            return new \WP_REST_Response($result, 200);
            
        } catch (\Exception $e) {
            rp_dev_log('Theme upload exception: ' . $e->getMessage());
            rp_dev_log('Exception trace: ' . $e->getTraceAsString());
            return new \WP_REST_Response(array(
                    'success' => false,
                'message' => $e->getMessage()
            ), 500);
        }
    }

    /**
     * Process theme upload - shared logic used by both REST API and filter
     */
    private function process_theme_upload($theme_file, $status = 'deactivated', $overwrite = false) {
        rp_dev_log('Starting theme upload process');
        rp_dev_log('Theme file: ' . json_encode($theme_file));
        rp_dev_log('Status: ' . $status);
        rp_dev_log('Overwrite: ' . ($overwrite ? 'true' : 'false'));

            // Upload the theme
        rp_dev_log('Uploading theme file');
            $upload_result = $this->handleThemeUpload($theme_file);
            if (is_wp_error($upload_result)) {
            rp_dev_log('Theme upload error: ' . $upload_result->get_error_message());
                return [
                    'success' => false,
                    'message' => $upload_result->get_error_message()
                ];
            }
        rp_dev_log('Theme uploaded to: ' . $upload_result);

            // Install the theme
        rp_dev_log('Installing theme from ZIP');
        $install_result = $this->installThemeFromZip($upload_result, $overwrite);
            if (is_wp_error($install_result)) {
            rp_dev_log('Theme installation error: ' . $install_result->get_error_message());
                return [
                    'success' => false,
                    'message' => $install_result->get_error_message()
                ];
            }
        rp_dev_log('Theme installed: ' . json_encode($install_result));

            // Activate if requested
        $was_activated = false;
        if ($status === 'activated') {
            rp_dev_log('Activating theme: ' . $install_result['theme_directory']);
                switch_theme($install_result['theme_directory']);
            $was_activated = true;
            rp_dev_log('Theme activated');
            }

        rp_dev_log('Theme upload process complete');
            return [
                'success' => true,
                'message' => 'Theme was successfully uploaded' . 
                ($was_activated ? ' and activated' : ''),
                'theme' => $install_result
            ];
    }

    /**
     * Check uploaded file validity
     */
    private function check_uploaded_file($file) {
        rp_dev_log('Checking file validity: ' . json_encode($file));
        
        if ($file['error'] !== UPLOAD_ERR_OK) {
            rp_dev_log('File upload error code: ' . $file['error']);
            $error_messages = [
                UPLOAD_ERR_INI_SIZE => 'The uploaded file exceeds the upload_max_filesize directive in php.ini',
                UPLOAD_ERR_FORM_SIZE => 'The uploaded file exceeds the MAX_FILE_SIZE directive in the HTML form',
                UPLOAD_ERR_PARTIAL => 'The uploaded file was only partially uploaded',
                UPLOAD_ERR_NO_FILE => 'No file was uploaded',
                UPLOAD_ERR_NO_TMP_DIR => 'Missing a temporary folder',
                UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk',
                UPLOAD_ERR_EXTENSION => 'A PHP extension stopped the file upload'
            ];
            
            $error_message = isset($error_messages[$file['error']]) 
                ? $error_messages[$file['error']] 
                : 'Unknown upload error';
                
            return new \WP_Error('upload_error', $error_message);
        }

        rp_dev_log('Checking file size');
        $max_upload_size = wp_max_upload_size();
        rp_dev_log('Max upload size: ' . $max_upload_size . ' bytes');
        rp_dev_log('File size: ' . $file['size'] . ' bytes');
        
        if ($file['size'] > $max_upload_size) {
            rp_dev_log('File too large');
            return new \WP_Error('file_too_large', sprintf(__('File size exceeds the maximum upload size of %s.', self::TEXTDOMAIN), size_format($max_upload_size)));
        }

        rp_dev_log('Checking file type');
        $wp_filetype = wp_check_filetype_and_ext($file['tmp_name'], $file['name']);
        rp_dev_log('File type check result: ' . json_encode($wp_filetype));
        
        if ($wp_filetype['ext'] !== 'zip' || $wp_filetype['type'] !== 'application/zip') {
            rp_dev_log('Invalid file type');
            return new \WP_Error('invalid_file_type', __('Invalid file type. Please upload a ZIP file.', self::TEXTDOMAIN));
        }

        rp_dev_log('File validation successful');
        return true;
    }

    /**
     * Process legacy upload theme request via filter
     */
    public function uploadTheme($params) {
        rp_dev_log('===== LEGACY THEME UPLOAD FILTER CALLED =====');
        rp_dev_log('Upload theme parameters: ' . json_encode($params));
        rp_dev_log('FILES array: ' . json_encode($_FILES));
        
        try {
            // Check if theme exists in $_FILES
            if (!isset($_FILES['theme']) || empty($_FILES['theme'])) {
                rp_dev_log('No theme file found in $_FILES');
                return [
                    'success' => false,
                    'message' => 'No theme file was provided'
                ];
            }
            
            $theme_file = $_FILES['theme'];
            rp_dev_log('Theme file details: ' . json_encode($theme_file));
            
            $status = isset($params['status']) ? $params['status'] : 'deactivated';
            rp_dev_log('Status parameter: ' . $status);
            
            $overwrite = isset($params['overwrite']) ? filter_var($params['overwrite'], FILTER_VALIDATE_BOOLEAN) : false;
            rp_dev_log('Overwrite parameter: ' . ($overwrite ? 'true' : 'false'));

            // Use the shared process method
            rp_dev_log('Calling shared process_theme_upload method');
            $result = $this->process_theme_upload($theme_file, $status, $overwrite);
            rp_dev_log('Upload process result: ' . json_encode($result));
            
            return $result;
            
        } catch (\Exception $e) {
            rp_dev_log('Theme upload exception: ' . $e->getMessage());
            rp_dev_log('Exception trace: ' . $e->getTraceAsString());
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Calculate the total size of a theme directory
     * @param string $theme_directory The theme directory name
     * @return int|null Size in bytes or null if calculation fails
     */
    private function calculateThemeDirectorySize($theme_directory) {
        rp_dev_log('Calculating size for theme directory: ' . $theme_directory);
        
        $theme_path = get_theme_root() . '/' . $theme_directory;
        if (!is_dir($theme_path)) {
            rp_dev_log('Theme directory not found: ' . $theme_path);
            return null;
        }
        
        try {
            $total_size = 0;
            $iterator = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($theme_path, \RecursiveDirectoryIterator::SKIP_DOTS),
                \RecursiveIteratorIterator::LEAVES_ONLY
            );
            
            foreach ($iterator as $file) {
                if ($file->isFile()) {
                    $total_size += $file->getSize();
                }
            }
            
            rp_dev_log('Theme directory size calculated: ' . $total_size . ' bytes');
            return $total_size;
        } catch (\Exception $e) {
            rp_dev_log('Error calculating theme directory size: ' . $e->getMessage());
            return null;
        }
    }
    
    public function getThemes($params) {
        rp_dev_log('===== GET THEMES CALLED =====');
        rp_dev_log('Get themes parameters: ' . json_encode($params));
        
        try {
            // Get all themes
            rp_dev_log('Getting all themes');
            $all_themes = wp_get_themes();
            rp_dev_log('Found ' . count($all_themes) . ' themes');
            
            $current_theme = wp_get_theme();
            rp_dev_log('Current theme: ' . $current_theme->get('Name'));
            
            $themes = [];

            foreach ($all_themes as $theme_directory => $theme) {
                $is_active = ($current_theme->get_stylesheet() === $theme->get_stylesheet());
                rp_dev_log('Processing theme: ' . $theme->get('Name') . ' (' . ($is_active ? 'active' : 'inactive') . ')');

                // Calculate unzipped file size
                $unzipped_size = $this->calculateThemeDirectorySize($theme_directory);
                
                $themes[] = [
                    'directory' => $theme_directory,
                    'name' => $theme->get('Name'),
                    'version' => $theme->get('Version'),
                    'status' => $is_active ? 'active' : 'inactive',
                    'author' => $theme->get('Author'),
                    'author_uri' => $theme->get('AuthorURI'),
                    'description' => $theme->get('Description'),
                    'theme_uri' => $theme->get('ThemeURI'),
                    'screenshot' => $theme->get_screenshot(),
                    'stylesheet' => $theme->get_stylesheet(),
                    'template' => $theme->get_template(),
                    'parent' => $theme->parent() ? $theme->parent()->get('Name') : null,
                    'is_child_theme' => is_child_theme(),
                    'tags' => $theme->get('Tags'),
                    'requires_wp' => $theme->get('RequiresWP'),
                    'requires_php' => $theme->get('RequiresPHP'),
                    'textdomain' => $theme->get('TextDomain'),
                    'update_available' => $this->hasThemeUpdate($theme_directory),
                    'unzippedSize' => [
                        'bytes' => $unzipped_size,
                        'formatted' => $unzipped_size !== null ? size_format($unzipped_size) : 'Unknown',
                    ],
                    'zippedSize' => [
                        'bytes' => null,
                        'formatted' => 'Unknown',
                    ],
                ];
            }

            rp_dev_log('Get themes completed successfully');
            return [
                'success' => true,
                'themes' => $themes,
                'active_theme' => $current_theme->get_stylesheet()
            ];
        } catch (\Exception $e) {
            rp_dev_log('Get themes exception: ' . $e->getMessage());
            rp_dev_log('Exception trace: ' . $e->getTraceAsString());
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    public function activateTheme($params) {
        rp_dev_log('===== ACTIVATE THEME CALLED =====');
        rp_dev_log('Activate theme parameters: ' . json_encode($params));
        
        try {
            if (!isset($params['stylesheet']) || empty($params['stylesheet'])) {
                rp_dev_log('No stylesheet parameter provided');
                return [
                    'success' => false,
                    'message' => 'Theme stylesheet parameter is required'
                ];
            }

            $stylesheet = $params['stylesheet'];
            rp_dev_log('Attempting to activate theme with stylesheet: ' . $stylesheet);
            
            $themes = wp_get_themes();
            rp_dev_log('Found ' . count($themes) . ' themes');

            if (!isset($themes[$stylesheet])) {
                rp_dev_log('Theme not found: ' . $stylesheet);
                return [
                    'success' => false,
                    'message' => 'Theme not found'
                ];
            }

            rp_dev_log('Theme found, activating: ' . $themes[$stylesheet]->get('Name'));
            switch_theme($stylesheet);
            rp_dev_log('Theme activated successfully');

            return [
                'success' => true,
                'message' => 'Theme activated successfully',
                'theme' => $stylesheet
            ];
        } catch (\Exception $e) {
            rp_dev_log('Activate theme exception: ' . $e->getMessage());
            rp_dev_log('Exception trace: ' . $e->getTraceAsString());
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    private function handleThemeUpload($file) {
        rp_dev_log('===== HANDLE THEME UPLOAD =====');
        rp_dev_log('File details: ' . json_encode($file));
        
        require_once(ABSPATH . 'wp-admin/includes/file.php');
        require_once(ABSPATH . 'wp-admin/includes/misc.php');
        require_once(ABSPATH . 'wp-admin/includes/class-wp-upgrader.php');
        
        if (!isset($file['tmp_name']) || empty($file['tmp_name'])) {
            rp_dev_log('Invalid file data - tmp_name is empty or not set');
            return new \WP_Error('upload_error', 'Invalid file data received');
        }
        
        if (!file_exists($file['tmp_name'])) {
            rp_dev_log('Temporary file not found: ' . $file['tmp_name']);
            return new \WP_Error('upload_error', 'Temporary file not found');
        }
        
        $upload_dir = wp_upload_dir();
        rp_dev_log('Upload directory: ' . json_encode($upload_dir));
        
        $upload_path = $upload_dir['path'] . '/' . basename($file['name']);
        rp_dev_log('Target upload path: ' . $upload_path);
        
        // Move the temporary file to the uploads directory
        rp_dev_log('Moving uploaded file from ' . $file['tmp_name'] . ' to ' . $upload_path);
        if (move_uploaded_file($file['tmp_name'], $upload_path)) {
            rp_dev_log('File moved successfully');
            return $upload_path;
        } else {
                $error = error_get_last();
            rp_dev_log('Failed to move uploaded file. Error: ' . ($error ? json_encode($error) : 'Unknown error'));
            rp_dev_log('PHP file permissions: tmp_name readable: ' . (is_readable($file['tmp_name']) ? 'yes' : 'no'));
            rp_dev_log('Upload dir writable: ' . (is_writable($upload_dir['path']) ? 'yes' : 'no'));
                return new \WP_Error('upload_error', 'Failed to upload theme file: ' . ($error ? $error['message'] : 'Unknown error'));
        }
    }

    private function installThemeFromZip($zip_file, $overwrite = false) {
        rp_dev_log('===== INSTALL THEME FROM ZIP =====');
        rp_dev_log('ZIP file: ' . $zip_file);
        rp_dev_log('Overwrite: ' . ($overwrite ? 'true' : 'false'));
        
        require_once(ABSPATH . 'wp-admin/includes/file.php');
        require_once(ABSPATH . 'wp-admin/includes/class-wp-upgrader.php');
        require_once(ABSPATH . 'wp-admin/includes/theme.php');
        
        // Create upgrader instance with custom options
        rp_dev_log('Creating Theme_Upgrader instance');
        $upgrader = new \Theme_Upgrader(new \WP_Ajax_Upgrader_Skin());
        
        // Add hook to handle overwrite option if needed
        if ($overwrite) {
            rp_dev_log('Setting up overwrite filter');
            add_filter('upgrader_package_options', function($options) {
                rp_dev_log('Modifying upgrader options to enable clear_destination');
                $options['clear_destination'] = true;
                return $options;
            });
        }
        
        rp_dev_log('Installing theme from ' . $zip_file);
        $result = $upgrader->install($zip_file);
        rp_dev_log('Install result: ' . var_export($result, true));
        
        // Clean up the zip file
        rp_dev_log('Cleaning up ZIP file');
        if (file_exists($zip_file)) {
        unlink($zip_file);
            rp_dev_log('ZIP file deleted');
        } else {
            rp_dev_log('ZIP file not found for cleanup');
        }
        
        if (is_wp_error($result)) {
            rp_dev_log('Installation error: ' . $result->get_error_message());
            return $result;
        }
        
        if (!$result) {
            rp_dev_log('Theme installation failed without specific error');
            return new \WP_Error('installation_failed', 'Theme installation failed');
        }
        
        // Get the installed theme info
        $theme_directory = $upgrader->result['destination_name'];
        rp_dev_log('Theme installed to directory: ' . $theme_directory);
        
        $theme = wp_get_theme($theme_directory);
        rp_dev_log('Installed theme details: ' . json_encode([
            'name' => $theme->get('Name'),
            'version' => $theme->get('Version')
        ]));
        
        return [
            'theme_directory' => $theme_directory,
            'name' => $theme->get('Name'),
            'version' => $theme->get('Version'),
            'author' => $theme->get('Author'),
            'description' => $theme->get('Description')
        ];
    }

    private function hasThemeUpdate($theme_slug) {
        $update_themes = get_site_transient('update_themes');
        $has_update = isset($update_themes->response[$theme_slug]);
        rp_dev_log('Theme update check for ' . $theme_slug . ': ' . ($has_update ? 'update available' : 'no update'));
        return $has_update;
    }

    public function deleteTheme($params) {
        rp_dev_log('===== DELETE THEME CALLED =====');
        rp_dev_log('Delete theme parameters: ' . json_encode($params));
        
        try {
            if (!isset($params['stylesheet']) || empty($params['stylesheet'])) {
                rp_dev_log('No stylesheet parameter provided');
                return [
                    'success' => false,
                    'message' => 'Theme stylesheet parameter is required'
                ];
            }

            $stylesheet = $params['stylesheet'];
            rp_dev_log('Attempting to delete theme with stylesheet: ' . $stylesheet);
            
            $themes = wp_get_themes();
            rp_dev_log('Found ' . count($themes) . ' themes');

            if (!isset($themes[$stylesheet])) {
                rp_dev_log('Theme not found: ' . $stylesheet);
                return [
                    'success' => false,
                    'message' => 'Theme not found'
                ];
            }

            // Check if it's the active theme
            $current_theme = wp_get_theme();
            if ($current_theme->get_stylesheet() === $stylesheet) {
                rp_dev_log('Cannot delete the active theme: ' . $stylesheet);
                return [
                    'success' => false,
                    'message' => 'Cannot delete the active theme'
                ];
            }
            
            require_once(ABSPATH . 'wp-admin/includes/file.php');
            require_once(ABSPATH . 'wp-admin/includes/theme.php');
            
            rp_dev_log('Theme found, deleting: ' . $themes[$stylesheet]->get('Name'));
            $result = delete_theme($stylesheet);
            
            if (is_wp_error($result)) {
                rp_dev_log('Delete theme error: ' . $result->get_error_message());
                return [
                    'success' => false,
                    'message' => $result->get_error_message()
                ];
            }
            
            rp_dev_log('Theme deleted successfully');

            return [
                'success' => true,
                'message' => 'Theme deleted successfully',
                'theme' => $stylesheet
            ];
        } catch (\Exception $e) {
            rp_dev_log('Delete theme exception: ' . $e->getMessage());
            rp_dev_log('Exception trace: ' . $e->getTraceAsString());
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }
}

new Themes();
