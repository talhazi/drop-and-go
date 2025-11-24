<?php

namespace DebugToolkit\Services;

use DebugToolkit\Constants;
use DebugToolkit\Error_Handler;
use DebugToolkit\Filesystem_Utils;
use DebugToolkit\Traits\Path_Utils;

/**
 * Class Viewer_Manager
 * 
 * Handles all viewer-related functionality
 */
class Viewer_Manager {
    use Path_Utils;

    /**
     * Check if viewer is installed
     *
     * @return bool
     */
    public function is_installed() {
        return file_exists(ABSPATH . Constants::DBTK_VIEWER_DIR . '/index.php');
    }

    /**
     * Setup the viewer
     *
     * @return bool
     * @throws \Exception
     */
    public function setup() {
        return Error_Handler::execute_with_error_handling(
            function() {
                $viewer_directory = ABSPATH . Constants::DBTK_VIEWER_DIR;
                
                $viewer_directory = Filesystem_Utils::validate_path($viewer_directory);
                
                if ($this->is_installed()) {
                    return true;
                }

                if (!file_exists($viewer_directory)) {
                    Filesystem_Utils::ensure_directory($viewer_directory);
                }

                return Error_Handler::execute_with_error_handling(
                    function() use ($viewer_directory) {
                        $source_directory = Constants::DBTK_PATH . 'viewer/dist/assets';
                        
                        $source_directory = Filesystem_Utils::validate_path($source_directory, [
                            'must_exist' => true,
                            'must_be_dir' => true,
                            'allowed_roots' => [realpath(ABSPATH), realpath(Constants::DBTK_PATH)]
                        ]);

                        Filesystem_Utils::copy_directory(
                            $source_directory,
                            $viewer_directory . '/assets'
                        );

                        $this->create_viewer_files($viewer_directory);

                        update_option('debug_toolkit_viewer_installed', true);

                        return true;
                    },
                    'Viewer Installation',
                    'Failed to complete viewer installation',
                    function() use ($viewer_directory) {
                        if (file_exists($viewer_directory)) {
                            Filesystem_Utils::recursively_delete_directory($viewer_directory, false);
                        }
                    }
                );
            },
            'Viewer Setup'
        );
    }

    /**
     * Remove the viewer
     *
     * @return bool
     * @throws \Exception
     */
    public function remove() {
        return Error_Handler::execute_with_error_handling(
            function() {
                $viewer_directory = ABSPATH . Constants::DBTK_VIEWER_DIR;
                
                $viewer_directory = Filesystem_Utils::validate_path($viewer_directory);
                
                if (!$this->is_installed()) {
                    update_option('debug_toolkit_viewer_installed', false);
                    return true;
                }

                if (file_exists($viewer_directory)) {
                    Filesystem_Utils::recursively_delete_directory($viewer_directory, false);
                }

                update_option('debug_toolkit_viewer_installed', false);

                return true;
            },
            'Viewer Removal'
        );
    }

    /**
     * Create necessary viewer files
     *
     * @param string $directory_path Directory path
     * @throws \Exception
     */
    private function create_viewer_files($directory_path) {

        $directory_path = Filesystem_Utils::validate_path($directory_path, [
            'must_be_dir' => true,
            'must_be_writable' => true
        ]);
        
        if (!file_exists($directory_path)) {
            Filesystem_Utils::ensure_directory($directory_path);
        }
        
        Error_Handler::fs_operation(
            function() use ($directory_path) {
                $template_content = $this->load_template('config.php');
                $log_path = Filesystem_Utils::get_debug_log_path();
                $template_content = str_replace('{{LOG_PATH}}', $log_path, $template_content);
                
                return file_put_contents($directory_path . '/config.php', $template_content);
            },
            'Failed to create config file'
        );
        
        Error_Handler::fs_operation(
            function() use ($directory_path) {
                $template_content = $this->load_template('index.php');
                return file_put_contents($directory_path . '/index.php', $template_content);
            },
            'Failed to create index file'
        );
        
        Error_Handler::fs_operation(
            function() use ($directory_path) {
                $template_content = $this->load_template('api.php');
                return file_put_contents($directory_path . '/api.php', $template_content);
            },
            'Failed to create API file'
        );
        
        // Create auth file
        $this->create_auth_file($directory_path);
    }

    /**
     * Load template file
     *
     * @param string $template_name 
     * @return string 
     * @throws \Exception 
     */
    private function load_template($template_name) {
        $template_path = Constants::DBTK_TEMPLATES_DIR . 'viewer/' . $template_name;
        
        if (!file_exists($template_path)) {
            throw new \Exception('Template file not found: ' . $template_path);
        }
        
        $content = file_get_contents($template_path);
        if ($content === false) {
            throw new \Exception('Failed to read template file: ' . $template_path);
        }
        
        return $content;
    }

    /**
     * Create auth file
     *
     * @param string $directory_path 
     * @throws \Exception
     */
    private function create_auth_file($directory_path) {
        Error_Handler::fs_operation(
            function() use ($directory_path) {
                $password_hash = get_option('debug_toolkit_viewer_password_hash', '');
                $is_password_protection_enabled = get_option('debug_toolkit_viewer_password_protection_enabled', false);
                
                $auth_data = [
                    'password_protection_enabled' => (bool)$is_password_protection_enabled,
                    'password_hash' => $password_hash,
                    'created_at' => gmdate('Y-m-d\TH:i:s\Z'),
                    'updated_at' => gmdate('Y-m-d\TH:i:s\Z')
                ];
                
                $php_content = "<?php\n";
                $php_content .= "if (!defined('DBTK_VIEWER_CONTEXT') || DBTK_VIEWER_CONTEXT !== 'api') {\n";
                $php_content .= "    header('HTTP/1.1 403 Forbidden');\n";
                $php_content .= "    exit('Access denied');\n";
                $php_content .= "}\n\n";
                $php_content .= "return " . var_export($auth_data, true) . ";\n";
                
                $auth_file_path = $directory_path . '/auth.php';
                
                $result = @file_put_contents($auth_file_path, $php_content);
                
                // we may be in CGI env, try again
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
                    Error_Handler::log('Failed to set auth file permissions', 'Viewer Manager');
                }
                
                return true;
            },
            'Failed to create auth file'
        );
    }
} 