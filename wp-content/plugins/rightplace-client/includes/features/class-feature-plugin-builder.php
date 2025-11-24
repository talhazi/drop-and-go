<?php
namespace Rightplace\PluginBuilder;

class PluginBuilder {
    public function __construct() {
        add_filter('rightplace_action_filter/plugin-builder/createPlugin', [$this, 'createPlugin']);
        add_filter('rightplace_action_filter/plugin-builder/addSnippet', [$this, 'addSnippet']);
        add_filter('rightplace_action_filter/plugin-builder/editSnippet', [$this, 'editSnippet']);
        add_filter('rightplace_action_filter/plugin-builder/deleteSnippet', [$this, 'deleteSnippet']);
        add_filter('rightplace_action_filter/plugin-builder/getSnippet', [$this, 'getSnippet']);
        add_filter('rightplace_action_filter/plugin-builder/editPluginDetails', [$this, 'editPluginDetails']);
        add_filter('rightplace_action_filter/plugin-builder/fixConfigFile', [$this, 'fixConfigFile']);
    }

    public function createPlugin($params) {
        if (empty($params['boilerplate'])) {
            return [
                'success' => false,
                'message' => 'Boilerplate data is required'
            ];
        }

        try {
            $boilerplate = $params['boilerplate'];
            $created_files = [];
            $failed_files = [];

            // Iterate through each file in the boilerplate
            foreach ($boilerplate as $file_type => $file_data) {
                if (!isset($file_data['path'])) {
                    $failed_files[] = "Invalid data for file type: {$file_type} - missing path";
                    continue;
                }

                $file_path = ABSPATH . ltrim($file_data['path'], '/');
                
                // Check if this is an empty directory (path ends with / and content is empty)
                if (substr($file_data['path'], -1) === '/' && (!isset($file_data['content']) || $file_data['content'] === '')) {
                    // This is just a directory to create
                    $dir_path = rtrim($file_path, '/');
                    if (!file_exists($dir_path)) {
                        if (mkdir($dir_path, 0755, true)) {
                            $created_files[] = $file_data['path'];
                            error_log("PluginBuilder: Successfully created directory: {$file_data['path']}");
                        } else {
                            $failed_files[] = "Failed to create directory: {$file_data['path']}";
                            error_log("PluginBuilder: Failed to create directory: {$file_data['path']}");
                        }
                    } else {
                        $created_files[] = $file_data['path'];
                        error_log("PluginBuilder: Directory already exists: {$file_data['path']}");
                    }
                    continue;
                }
                
                if (!isset($file_data['content'])) {
                    $failed_files[] = "Invalid data for file type: {$file_type} - missing content";
                    continue;
                }

                $file_content = $file_data['content'];

                // Create directory if it doesn't exist
                $dir = dirname($file_path);
                if (!file_exists($dir)) {
                    if (!mkdir($dir, 0755, true)) {
                        $failed_files[] = "Failed to create directory: {$dir}";
                        continue;
                    }
                }

                // Write the file content
                if (file_put_contents($file_path, $file_content) !== false) {
                    $created_files[] = $file_data['path'];
                    error_log("PluginBuilder: Successfully created file: {$file_data['path']}");
                } else {
                    $failed_files[] = "Failed to create file: {$file_data['path']}";
                    error_log("PluginBuilder: Failed to create file: {$file_data['path']}");
                }
            }

            // Determine success based on results
            $total_files = count($boilerplate);
            $successful_files = count($created_files);
            $is_success = $successful_files > 0 && empty($failed_files);

            $message = $is_success 
                ? "Plugin created successfully. {$successful_files}/{$total_files} files created."
                : "Plugin creation completed with errors. {$successful_files}/{$total_files} files created.";

            if (!empty($failed_files)) {
                $message .= " Errors: " . implode(', ', $failed_files);
            }

            return [
                'success' => $is_success,
                'message' => $message,
                'data' => [
                    'created_files' => $created_files,
                    'failed_files' => $failed_files,
                    'total_files' => $total_files,
                    'successful_files' => $successful_files
                ]
            ];

        } catch (\Exception $e) {
            error_log("PluginBuilder createPlugin error: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Error creating plugin: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Add a new snippet to the config file
     * 
     * @param array $params Parameters containing path, snippetId and newData
     * @return array Response with success status and message
     */
    public function addSnippet($params) {
        $path = ABSPATH . ltrim($params['path'], '/'); // absolute path to config.json
        $newData = $params['newData'];
        $snippetFolder = isset($params['snippetFolder']) ? $params['snippetFolder'] : '';
        
        // Validate required parameters
        if (!$path) {
            return ['success' => false, 'message' => 'Config file path is required'];
        }

        if (!$newData) {
            return ['success' => false, 'message' => 'New snippet data is required'];
        }

        $fileName = isset($newData['fullFileName']) ? $newData['fullFileName'] : $newData['fileName'];
        if (!$fileName) {
            return ['success' => false, 'message' => 'fileName is required in newData'];
        }

        if (!file_exists($path)) {
            return ['success' => false, 'message' => 'Config file not found'];
        }

        $config = json_decode(file_get_contents($path), true);
        if (!is_array($config)) return ['success' => false, 'message' => 'Invalid config format'];

        // Ensure files is an array
        if (!isset($config['files']) || !is_array($config['files'])) {
            $config['files'] = [];
        }

        // If files is an object, convert it to an array
        if (is_object($config['files'])) {
            $config['files'] = array_values((array)$config['files']);
        }

        // Generate unique snippet ID
        $snippetId = wp_generate_uuid4();

        // Check if snippet already exists
        foreach ($config['files'] as $file) {
            if ($file['snippetId'] === $snippetId) {
                return ['success' => false, 'message' => 'Snippet already exists'];
            }
        }

        // Create new snippet with required fields (matching other implementations)
        $newSnippet = $newData;
        $newSnippet['fullFileName'] = $fileName;
        $newSnippet['snippetId'] = $snippetId;
        $newSnippet['priority'] = isset($newData['priority']) ? $newData['priority'] : 10;
        $newSnippet['version'] = isset($newData['version']) ? $newData['version'] : 1;

        // Add snippet to config
        $config['files'][] = $newSnippet;
        file_put_contents($path, json_encode($config, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

        // Construct the full file path using snippetFolder and fileName
        $fullFilePath = $snippetFolder ? ($snippetFolder . '/' . $fileName) : $fileName;
        $file_path = ABSPATH . ltrim($fullFilePath, '/');
        
        // Create directory structure if needed
        $dir = dirname($file_path);
        if (!file_exists($dir)) {
            mkdir($dir, 0755, true);
        }
        
        // Create the actual file if it doesn't exist
        if (!file_exists($file_path)) {
            $ext = pathinfo($file_path, PATHINFO_EXTENSION);
            $template = '';
            
            // Check if custom post type content was passed from the app
            if (isset($params['customPostTypeContent']) && $ext === 'php') {
                // Use the content generated by the app
                $template = $params['customPostTypeContent'];
            } else {
                switch ($ext) {
                    case 'php':
                        $template = "<?php\n// New PHP snippet\n";
                        break;
                    case 'js':
                        $template = "// New JS snippet\n";
                        break;
                    case 'css':
                        $template = "/* New CSS snippet */\n";
                        break;
                    case 'html':
                        $template = "<!-- New HTML snippet -->\n";
                        break;
                    default:
                        $template = "";
                }
            }
            file_put_contents($file_path, $template);
        }

        return ['success' => true, 'message' => 'Snippet added', 'snippetId' => $snippetId];
    }

    /**
     * Edit an existing snippet in the config file
     * 
     * @param array $params Parameters containing path, snippetId and newData
     * @return array Response with success status and message
     */
    public function editSnippet($params) {
        $path = ABSPATH . ltrim($params['path'], '/');
        $snippetId = $params['snippetId'];
        $newData = $params['newData'];

        if (!file_exists($path)) {
            return ['success' => false, 'message' => 'Config file not found'];
        }

        $config = json_decode(file_get_contents($path), true);
        if (!is_array($config)) return ['success' => false, 'message' => 'Invalid config format'];

        // Ensure files is an array
        if (!isset($config['files']) || !is_array($config['files'])) {
            $config['files'] = [];
        }

        // If files is an object, convert it to an array
        if (is_object($config['files'])) {
            $config['files'] = array_values((array)$config['files']);
        }

        $found = false;
        foreach ($config['files'] as &$file) {
            if ($file['snippetId'] === $snippetId) {
                $file = array_merge($file, $newData);
                $found = true;
                break;
            }
        }

        if (!$found) {
            return ['success' => false, 'message' => 'Snippet not found'];
        }

        // PREVENTIVE FIX: Ensure files remains an array after any modifications
        $config['files'] = array_values($config['files']);

        file_put_contents($path, json_encode($config, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

        return ['success' => true, 'message' => 'Snippet updated'];
    }

    /**
     * Delete a snippet from the config file
     * 
     * @param array $params Parameters containing path and snippetId
     * @return array Response with success status and message
     */
    public function deleteSnippet($params) {
        $path = ABSPATH . ltrim($params['path'], '/');
        $snippetId = $params['snippetId'];
        $deletePath = $params['deletePath'];
        
        // DEBUG: Log the parameters to understand what's happening
        error_log("🐛 PHP deleteSnippet - Config path: " . $path);
        error_log("🐛 PHP deleteSnippet - SnippetId: " . $snippetId);
        error_log("🐛 PHP deleteSnippet - DeletePath: " . $deletePath);

        if (!file_exists($path)) {
            return ['success' => false, 'message' => 'Config file not found'];
        }

        $config = json_decode(file_get_contents($path), true);
        if (!is_array($config)) return ['success' => false, 'message' => 'Invalid config format'];

        // Ensure files is an array
        if (!isset($config['files']) || !is_array($config['files'])) {
            $config['files'] = [];
        }

        // If files is an object, convert it to an array
        if (is_object($config['files'])) {
            $config['files'] = array_values((array)$config['files']);
        }

        $initialCount = count($config['files']);
        $config['files'] = array_filter($config['files'], fn($f) => $f['snippetId'] !== $snippetId);

        if (count($config['files']) === $initialCount) {
            return ['success' => false, 'message' => 'Snippet not found'];
        }
        
        // CRITICAL FIX: Re-index the array to prevent object conversion in JSON
        $config['files'] = array_values($config['files']);

        // Preserve the entire config structure including version, frameworkVersion, settings, etc.
        $result = file_put_contents($path, json_encode($config, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
        
        if ($result === false) {
            return ['success' => false, 'message' => 'Failed to update config file'];
        }

        // Delete the actual file
        if (!empty($deletePath)) {
            $file_path = ABSPATH . ltrim($deletePath, '/');
            
            // SAFETY CHECK: Never delete the config file itself!
            if ($file_path === $path) {
                error_log("🚨 CRITICAL: Prevented deletion of config file itself! DeletePath: " . $deletePath);
                return [
                    'success' => false, 
                    'message' => 'Critical error: Cannot delete config file'
                ];
            }
            
            error_log("🐛 PHP deleteSnippet - About to delete file: " . $file_path);
            
            if (file_exists($file_path)) {
                if (!unlink($file_path)) {
                    return [
                        'success' => false, 
                        'message' => 'Snippet removed from config but failed to delete the file'
                    ];
                }
                error_log("🐛 PHP deleteSnippet - Successfully deleted file: " . $file_path);
            } else {
                error_log("🐛 PHP deleteSnippet - File does not exist: " . $file_path);
            }
        } else {
            error_log("🐛 PHP deleteSnippet - No deletePath provided, skipping file deletion");
        }

        return ['success' => true, 'message' => 'Snippet and file deleted'];
    }

    /**
     * Fix corrupted config file by converting files object back to array
     * 
     * @param array $params Parameters containing path to config file
     * @return array Response with success status and message
     */
    public function fixConfigFile($params) {
        $path = ABSPATH . ltrim($params['path'], '/');
        
        if (!file_exists($path)) {
            return ['success' => false, 'message' => 'Config file not found'];
        }

        $config = json_decode(file_get_contents($path), true);
        if (!is_array($config)) {
            return ['success' => false, 'message' => 'Invalid config format'];
        }

        // Check if files is an object and convert to array
        if (isset($config['files']) && is_array($config['files'])) {
            // If it's an associative array (object-like), convert to indexed array
            if (array_keys($config['files']) !== range(0, count($config['files']) - 1)) {
                error_log("🔧 Fixing corrupted config file - converting files object to array");
                $config['files'] = array_values($config['files']);
                
                // Save the fixed config
                $result = file_put_contents($path, json_encode($config, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
                
                if ($result === false) {
                    return ['success' => false, 'message' => 'Failed to save fixed config file'];
                }
                
                return ['success' => true, 'message' => 'Config file fixed successfully'];
            } else {
                return ['success' => true, 'message' => 'Config file is already in correct format'];
            }
        }

        return ['success' => false, 'message' => 'No files array found in config'];
    }

    /**
     * Get a snippet from the config file by its snippetId
     * 
     * @param array $params Parameters containing path and snippetId
     * @return array Response with success status, message and snippet data if found
     */
    public function getSnippet($params) {
        $path = ABSPATH . ltrim($params['path'], '/');
        $snippetId = $params['snippetId'];

        if (!file_exists($path)) {
            return [
                'success' => false, 
                'message' => 'Config file not found'
            ];
        }

        $config = json_decode(file_get_contents($path), true);
        if (!is_array($config)) {
            return [
                'success' => false, 
                'message' => 'Invalid config format'
            ];
        }

        foreach ($config['files'] as $file) {
            $file_snippet_id = $file['snippetId'];
            // if $file_snippet_id starts with /, remove it
            // for legacy code from beta
            if (strpos($file_snippet_id, '/') === 0) {
                $file_snippet_id = substr($file_snippet_id, 1);
            }
            if ($file_snippet_id === $snippetId) {
                return [
                    'success' => true,
                    'message' => 'Snippet found',
                    'data' => $file
                ];
            }
        }

        return [
            'success' => false,
            'message' => 'Snippet not found',
        ];
    }

    /**
     * Edit plugin details and metadata while preserving existing data
     * 
     * @param array $params Parameters containing plugin path and new metadata
     * @return array Response with success status and message
     */
    public function editPluginDetails($params) {
        if (empty($params['pluginPath'])) {
            return [
                'success' => false,
                'message' => 'Plugin path is required'
            ];
        }

        $plugin_path = WP_PLUGIN_DIR . '/' . $params['pluginPath'];
        $main_plugin_file = $plugin_path . '/' . $params['pluginPath'] . '.php';

        if (!file_exists($main_plugin_file)) {
            return [
                'success' => false,
                'message' => 'Plugin file not found'
            ];
        }

        try {
            // Log the plugin path for debugging
            error_log("EditPluginDetails: Processing plugin at path: {$params['pluginPath']}");
            error_log("EditPluginDetails: Looking for file: {$main_plugin_file}");
            
            // Read the current plugin file
            $plugin_content = file_get_contents($main_plugin_file);
            if ($plugin_content === false) {
                throw new \Exception('Failed to read plugin file');
            }

            // Extract the current plugin header
            preg_match('/\/\*\*\s*\n.*?\*\//s', $plugin_content, $matches);
            if (empty($matches[0])) {
                throw new \Exception('Invalid plugin header format');
            }

            $current_header = $matches[0];
            
            // Extract all existing metadata to preserve it
            $existing_meta = [
                'pluginName' => $this->extractPluginMeta($current_header, 'Plugin Name'),
                'pluginURI' => $this->extractPluginMeta($current_header, 'Plugin URI'),
                'description' => $this->extractPluginMeta($current_header, 'Description'),
                'version' => $this->extractPluginMeta($current_header, 'Version'),
                'requiresAtLeast' => $this->extractPluginMeta($current_header, 'Requires at least'),
                'requiresPHP' => $this->extractPluginMeta($current_header, 'Requires PHP'),
                'author' => $this->extractPluginMeta($current_header, 'Author'),
                'authorURI' => $this->extractPluginMeta($current_header, 'Author URI'),
                'license' => $this->extractPluginMeta($current_header, 'License'),
                'licenseURI' => $this->extractPluginMeta($current_header, 'License URI'),
                'textDomain' => $this->extractPluginMeta($current_header, 'Text Domain'),
                'domainPath' => $this->extractPluginMeta($current_header, 'Domain Path'),
                'developedBy' => $this->extractPluginMeta($current_header, 'Developed by'),
                'pluginType' => $this->extractPluginMeta($current_header, 'Plugin Type') ?? 'rightplace',
                'network' => $this->extractPluginMeta($current_header, 'Network'),
                'updateURI' => $this->extractPluginMeta($current_header, 'Update URI'),
                'requiresPlugins' => $this->extractPluginMeta($current_header, 'Requires Plugins'),
            ];

            // Filter out empty values from params to avoid overwriting with empty strings
            $filtered_params = array_filter($params, function($value, $key) {
                return $value !== '' && $value !== null && $key !== 'pluginPath';
            }, ARRAY_FILTER_USE_BOTH);
            
            // Merge existing metadata with new data, preserving existing values
            $updated_meta = array_merge($existing_meta, $filtered_params);
            
            // Generate new header with all metadata preserved
            $new_header = $this->generatePluginHeader($updated_meta);

            // Replace the old header with the new one
            $new_content = str_replace($current_header, $new_header, $plugin_content);

            // Write the updated content back to the file
            if (file_put_contents($main_plugin_file, $new_content) === false) {
                throw new \Exception('Failed to write to plugin file');
            }

            // Clear WordPress plugin cache to ensure changes are recognized
            if (function_exists('wp_clean_plugins_cache')) {
                wp_clean_plugins_cache();
            }
            
            // Clear any plugin data cache
            if (function_exists('delete_site_transient')) {
                delete_site_transient('update_plugins');
            }

            // Log success for debugging
            error_log("EditPluginDetails: Successfully updated plugin metadata");
            error_log("EditPluginDetails: Updated fields: " . implode(', ', array_keys($filtered_params)));

            return [
                'success' => true,
                'message' => 'Plugin metadata updated successfully',
                'data' => [
                    'pluginPath' => $params['pluginPath'],
                    'pluginName' => $updated_meta['pluginName'],
                    'pluginType' => $updated_meta['pluginType'],
                    'updatedFields' => array_keys($filtered_params),
                    'originalBaseName' => $params['pluginPath'] . '/' . $params['pluginPath'] . '.php'
                ]
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Error updating plugin metadata: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Extract plugin meta value from header
     * 
     * @param string $header The plugin header content
     * @param string $key The meta key to extract
     * @return string The extracted value or empty string
     */
    private function extractPluginMeta($header, $key) {
        preg_match('/\*\s*' . preg_quote($key, '/') . ':\s*(.*?)\n/', $header, $matches);
        return isset($matches[1]) ? trim($matches[1]) : '';
    }

    /**
     * Generate plugin header from parameters, preserving all metadata
     * 
     * @param array $params Plugin parameters
     * @return string Generated plugin header
     */
    private function generatePluginHeader($params) {
        $header = "/**\n";
        
        // Required field
        if (!empty($params['pluginName'])) {
            $header .= " * Plugin Name: {$params['pluginName']}\n";
        }
        
        // Optional fields - only add if they have values
        $optional_fields = [
            'pluginURI' => 'Plugin URI',
            'description' => 'Description',
            'version' => 'Version',
            'requiresAtLeast' => 'Requires at least',
            'requiresPHP' => 'Requires PHP',
            'network' => 'Network',
            'requiresPlugins' => 'Requires Plugins',
            'author' => 'Author',
            'authorURI' => 'Author URI',
            'textDomain' => 'Text Domain',
            'domainPath' => 'Domain Path',
            'license' => 'License',
            'licenseURI' => 'License URI',
            'updateURI' => 'Update URI',
            'developedBy' => 'Developed by',
            'pluginType' => 'Plugin Type'
        ];
        
        foreach ($optional_fields as $param_key => $header_key) {
            if (!empty($params[$param_key])) {
                $header .= " * {$header_key}: {$params[$param_key]}\n";
            }
        }
        
        $header .= " */\n";
        
        return $header;
    }

}

new PluginBuilder();
