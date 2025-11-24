<?php
namespace Rightplace\Features;
class Plugins
{
    const TEXTDOMAIN = 'rightplace-client';


    public function __construct() {
        // Include WordPress plugin functions
        require_once ABSPATH . 'wp-admin/includes/plugin.php';
        add_filter('rightplace_action_filter/isRpClientActive', array($this, 'is_rp_client_active'));
        add_filter('rightplace_action_filter/getPluginsQuery', array('Rightplace\Features\Plugins', 'get_plugins_query'));
        
        // Add new filters for plugin status change and deletion
        add_filter('rightplace_action_filter/changePluginStatus', array($this, 'change_plugin_status'));
        add_filter('rightplace_action_filter/deletePlugin', array($this, 'delete_plugin'));

        add_filter('rightplace_action_filter/updatePlugins', array($this, 'update_plugins'));
        add_filter('rightplace_action_filter/getPluginsForLlm', array($this, 'get_plugins_for_llm'));
        add_filter('rightplace_action_filter/downloadPlugin', array($this, 'download_plugin'));
    }
    
    public function download_plugin($params) {
        $basename = $params['params']['basename'] ?? $params['basename'] ?? '';
    
        if (empty($basename)) {
            return [
                'success' => false,
                'message' => 'Missing required parameter: basename'
            ];
        }
    
        $plugin_path = WP_PLUGIN_DIR . '/' . $basename;
    
        if (!file_exists($plugin_path)) {
            return [
                'success' => false,
                'message' => 'Plugin not found'
            ];
        }
    
        $plugin_dir = dirname($plugin_path);
        $plugin_name = basename($plugin_dir);
        $zip_temp_file = tempnam(sys_get_temp_dir(), 'plugin_') . '.zip';
    
        if (!class_exists('ZipArchive')) {
            return [
                'success' => false,
                'message' => 'ZipArchive is not available'
            ];
        }
    
        try {
            $zip = new \ZipArchive();
            if ($zip->open($zip_temp_file, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) !== true) {
                throw new \Exception('Cannot open ZIP file.');
            }
    
            // Wrap files in the parent folder inside the ZIP
            $rootPath = dirname($plugin_path);
            $files = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($rootPath, \RecursiveDirectoryIterator::SKIP_DOTS),
                \RecursiveIteratorIterator::LEAVES_ONLY
            );
    
            foreach ($files as $file) {
                if (!$file->isDir()) {
                    $file_path = $file->getRealPath();
                    // Add parent folder name to relative path
                    $relative_path = $plugin_name . '/' . substr($file_path, strlen($rootPath) + 1);
                    $zip->addFile($file_path, $relative_path);
                }
            }
    
            $zip->close();
    
            $zip_content = file_get_contents($zip_temp_file);
            $base64_content = base64_encode($zip_content);
            unlink($zip_temp_file);
    
            return [
                'success' => true,
                'data' => [
                    'buffer' => $base64_content,
                    'name' => $plugin_name . '.zip',
                    'size' => strlen($zip_content)
                ]
            ];
        } catch (\Exception $e) {
            if (file_exists($zip_temp_file)) {
                unlink($zip_temp_file);
            }
    
            return [
                'success' => false,
                'message' => 'Error creating ZIP: ' . $e->getMessage()
            ];
        }
    }
    
    
    
    /**
     * Helper function to recursively add files to a ZIP archive
     * 
     * @param ZipArchive $zip The ZIP archive object
     * @param string $folder The folder to add
     * @param string $base_folder The base folder path to remove from the ZIP entry names
     * @return int Number of files added to the ZIP
     */
    private function add_files_to_zip($zip, $folder, $base_folder) {
        $folder = trailingslashit($folder);
        $base_folder = trailingslashit($base_folder);
        
        rp_dev_log('Adding files from folder: ' . $folder);
        rp_dev_log('Base folder for ZIP entries: ' . $base_folder);
        
        // Use the global namespace for PHP built-in classes
        $files = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($folder, \RecursiveDirectoryIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::LEAVES_ONLY
        );
        
        $file_count = 0;
        
        foreach ($files as $file) {
            // Get real and relative path for current file
            $file_path = $file->getRealPath();
            
            // Get the relative path by removing the base folder path
            $relative_path = str_replace($base_folder, '', $file_path);
            
            if (is_dir($file_path)) {
                // Add empty directory
                $result = $zip->addEmptyDir($relative_path);
                if ($result === false) {
                    rp_dev_log('Failed to add directory to ZIP: ' . $relative_path);
                } else {
                    rp_dev_log('Added directory to ZIP: ' . $relative_path);
                    $file_count++;
                }
            } else {
                // Add file
                $result = $zip->addFile($file_path, $relative_path);
                if ($result === false) {
                    rp_dev_log('Failed to add file to ZIP: ' . $relative_path . ' (from ' . $file_path . ')');
                } else {
                    // Only log every 100 files to avoid excessive logging
                    if ($file_count % 100 === 0) {
                        rp_dev_log('Added file to ZIP (' . $file_count . '): ' . $relative_path);
                    }
                    $file_count++;
                }
            }
        }
        
        rp_dev_log('Total files added to ZIP: ' . $file_count);
        return $file_count;
    }

    /**
     * Get the total size of a directory
     */
    private function get_directory_size($dir) {
        $size = 0;
        
        // Use the global namespace for PHP built-in classes
        foreach (new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($dir, \RecursiveDirectoryIterator::SKIP_DOTS)
        ) as $file) {
            if ($file->isFile()) {
                $size += $file->getSize();
            }
        }
        return $size;
    }

    public function update_plugins($params): array
{
    $basename = $params['basename'];
    $newAction = $params['newAction'];

    // Check if the plugin exists
    if (!file_exists(WP_PLUGIN_DIR . '/' . $basename)) {
        return [
            'success' => false,
            'message' => "Plugin '{$basename}' does not exist.",
            'params' => $params,
            'code' => 'plugin_not_found'
        ];
    }

    // Load necessary WordPress files
    require_once ABSPATH . 'wp-admin/includes/plugin.php';
    require_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';
    require_once ABSPATH . 'wp-includes/option.php';
    require_once ABSPATH . 'wp-admin/includes/file.php';

    $auto_updates = (array) get_site_option('auto_update_plugins', []);

    try {
        switch ($newAction) {
            case 'update':
                try {
                    // Make sure the current user can update plugins
                    if (!current_user_can('update_plugins')) {
                        return [
                            'success' => false,
                            'message' => 'You do not have permission to update plugins.',
                            'params'  => $params,
                            'code'    => 'insufficient_permissions'
                        ];
                    }
                } catch (\Exception $e) {
                    return [
                        'success' => false,
                        'message' => 'Error checking permissions: ' . $e->getMessage(),
                        'params'  => $params,
                        'code'    => 'permission_check_failed'
                    ];
                }

                try {
                    // Clear the plugin update transient and re-check updates
                    delete_site_transient('update_plugins');
                    wp_update_plugins();
                } catch (\Exception $e) {
                    return [
                        'success' => false,
                        'message' => 'Error updating plugin transients: ' . $e->getMessage(),
                        'params'  => $params,
                        'code'    => 'transient_update_failed'
                    ];
                }

                try {
                    // Retrieve updated plugin data
                    $update_plugins = get_site_transient('update_plugins');

                    // Check if the plugin has an available update
                    if (!isset($update_plugins->response[$basename])) {
                        return [
                            'success' => false,
                            'message' => "No update available for plugin '{$basename}'.",
                            'params'  => $params,
                            'code'    => 'no_update_available'
                        ];
                    }
                } catch (\Exception $e) {
                    return [
                        'success' => false,
                        'message' => 'Error retrieving plugin update data: ' . $e->getMessage(),
                        'params'  => $params,
                        'code'    => 'update_data_retrieval_failed'
                    ];
                }

                try {
                    // Store the plugin's activation status before update
                    $was_active = is_plugin_active($basename);

                    // We have an update, so set up the upgrader
                    require_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';

                    // Using a WP_Ajax_Upgrader_Skin (or another skin) lets us capture messages silently
                    $skin = new \WP_Ajax_Upgrader_Skin();
                    $upgrader = new \Plugin_Upgrader($skin);

                    // Attempt the update
                    $plugin_data = $update_plugins->response[$basename];
                    $result = $upgrader->upgrade($basename);

                    // Check result
                    if (is_wp_error($result)) {
                        // Log error details for debugging
                        rp_dev_log("Plugin update failed for '{$basename}': " . $result->get_error_message());

                        return [
                            'success' => false,
                            'message' => $result->get_error_message(),
                            'params'  => $params,
                            'code'    => 'update_failed',
                            'status'  => 'Error',
                            // Provide verbose debug info if in WP_DEBUG mode
                            'debug'   => WP_DEBUG ? [
                                'upgrade_messages' => $skin->get_upgrade_messages(),
                                'trace'           => $result->get_error_data(), // if any
                            ] : null
                        ];
                    }

                    // Restore plugin activation status if it was active before
                    if ($was_active) {
                        activate_plugin($basename);
                        rp_dev_log("Restored activation status for plugin '{$basename}'");
                    }

                    // If successful, log a success message
                    rp_dev_log("Plugin '{$basename}' updated to version {$plugin_data->new_version} successfully.");

                    return [
                        'success' => true,
                        'message' => "Plugin '{$basename}' updated successfully to version {$plugin_data->new_version}.",
                        'data'    => $plugin_data,
                        'params'  => $params,
                        'status'  => 'Success',
                        // Optional debug details
                        'debug'   => WP_DEBUG ? [
                            'upgrade_messages' => $skin->get_upgrade_messages(),
                        ] : null
                    ];
                } catch (\Exception $e) {
                    return [
                        'success' => false,
                        'message' => 'Error during plugin upgrade: ' . $e->getMessage(),
                        'params'  => $params,
                        'code'    => 'upgrade_process_failed'
                    ];
                }
            case 'enable-auto-update':
                try {
                    if (in_array($basename, $auto_updates)) {
                        return [
                            'success' => false,
                            'message' => "Auto-updates are already enabled for plugin '{$basename}'.",
                            'params' => $params,
                            'code' => 'auto_update_already_enabled'
                        ];
                    }

                    $auto_updates[] = $basename;
                    update_site_option('auto_update_plugins', $auto_updates);

                    return [
                        'success' => true,
                        'message' => "Auto-updates enabled for plugin '{$basename}'.",
                        'params' => $params,
                        'status' => 'Success'
                    ];
                } catch (\Exception $e) {
                    return [
                        'success' => false,
                        'message' => 'Error enabling auto-update: ' . $e->getMessage(),
                        'params' => $params,
                        'code' => 'auto_update_enable_failed'
                    ];
                }

            case 'disable-auto-update':
                if (!in_array($basename, $auto_updates)) {
                    return [
                        'success' => false,
                        'message' => "Auto-updates are already disabled for plugin '{$basename}'.",
                        'params' => $params,
                        'code' => 'auto_update_already_disabled'
                    ];
                }

                $auto_updates = array_diff($auto_updates, [$basename]);
                update_site_option('auto_update_plugins', $auto_updates);

                return [
                    'success' => true,
                    'message' => "Auto-updates disabled for plugin '{$basename}'.",
                    'params' => $params,
                    'status' => 'Success'
                ];

            default:
                return [
                    'success' => false,
                    'message' => 'Invalid action specified. Use "update", "enable-auto-update", or "disable-auto-update".',
                    'params' => $params,
                    'code' => 'invalid_action',
                    'status' => 'Warn'
                ];
        }
    } catch (\Exception $e) {
        return [
            'success' => false,
            'message' => 'An error occurred: ' . $e->getMessage(),
            'params' => $params,
            'code' => 'operation_failed',
            'status' => 'Error',
            'debug' => WP_DEBUG ? [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ] : null
        ];
    }
}

    public static function is_rp_client_active($params)
    {
        return array(
            'success' => true,
            'is_installed' => true,
            'status' => 'active',
            'plugin_path' => 'rightplace-client/rightplace-client.php'
        );
    }
     
    //  TODO: to find another way of getting assets.Maybe to do parsing the wordpress.org site for plugins.
     public static function get_plugins_query($params) {
        if (!function_exists('get_plugins')) {
            require_once ABSPATH . 'wp-admin/includes/plugin.php';
        }
    
        $plugin_status = $params['plugin_status'] ?? 'all';
        $basename = $params['basename'] ?? null;
    
        $all_plugins = get_plugins();
        $active_plugins = get_option('active_plugins');
        $recently_activated = get_option('recently_activated', array());
        $auto_updates = (array) get_site_option('auto_update_plugins', array());
        $result_plugins = [];

        // Get network-active plugins for multisite
        $network_active = is_multisite() ? get_site_option('active_sitewide_plugins', array()) : array();
        
        // Get must-use and drop-in plugins
        $mu_plugins = get_mu_plugins();
        $dropins = get_dropins();

        // Helper function to determine if a plugin should be included based on status
        $should_include_plugin = function($plugin_path, $is_active) use (
            $plugin_status, 
            $recently_activated, 
            $auto_updates, 
            $network_active,
            $mu_plugins,
            $dropins
        ) {
            switch ($plugin_status) {
                case 'active':
                    return $is_active;
                case 'inactive':
                    return !$is_active;
                case 'recently_activated':
                    return isset($recently_activated[$plugin_path]);
                case 'upgrade':
                    $update_plugins = get_site_transient('update_plugins');
                    return isset($update_plugins->response[$plugin_path]);
                case 'active-network':
                    return isset($network_active[$plugin_path]);
                case 'mustuse':
                    return isset($mu_plugins[$plugin_path]);
                case 'dropins':
                    return isset($dropins[$plugin_path]);
                case 'auto_update_enabled':
                    return in_array($plugin_path, $auto_updates);
                case 'auto_update_disabled':
                    return !in_array($plugin_path, $auto_updates);
                case 'all':
                    return true;
                default:
                    return true;
            }
        };

        // Force refresh of update information
        delete_site_transient('update_plugins');
        wp_update_plugins();

        $update_plugins = get_site_transient('update_plugins');

        // Helper function to check plugin dependencies and requirements
        $check_plugin_requirements = function($plugin_data, $plugin_path) use ($active_plugins) {
            $requirements = [];
            $can_activate = true;
            $missing_plugins = [];
            
            // Try to include the WP_Plugin_Dependencies class if it exists
            $dependencies_file = ABSPATH . 'wp-includes/class-wp-plugin-dependencies.php';
            if (file_exists($dependencies_file)) {
                require_once $dependencies_file;
            }
            
            // Use WordPress's built-in Plugin Dependencies class if available (WP 6.5+)
            if (class_exists('\\WP_Plugin_Dependencies')) {
                // Initialize the dependencies system
                \WP_Plugin_Dependencies::initialize();
                
                // Check if the plugin has dependencies
                if (\WP_Plugin_Dependencies::has_dependencies($plugin_path)) {
                    // Check if it has unmet dependencies
                    if (\WP_Plugin_Dependencies::has_unmet_dependencies($plugin_path)) {
                        $can_activate = false;
                        
                        // Get the dependency names
                        $dependency_names = \WP_Plugin_Dependencies::get_dependency_names($plugin_path);
                        
                        foreach ($dependency_names as $slug => $name) {
                            $dependency_filepath = \WP_Plugin_Dependencies::get_dependency_filepath($slug);
                            
                            // Check if dependency is missing or inactive
                            if (false === $dependency_filepath || is_plugin_inactive($dependency_filepath)) {
                                $missing_plugins[] = $name;
                            }
                        }
                    }
                    
                    // Check for circular dependencies
                    if (\WP_Plugin_Dependencies::has_circular_dependency($plugin_path)) {
                        $can_activate = false;
                        $requirements['has_circular_dependency'] = true;
                        $requirements['circular_message'] = 'This plugin cannot be activated because it has a circular dependency.';
                    }
                }
            } else {
                // Fallback for WordPress versions before 6.5
                // Check for "Requires Plugins" header in plugin data
                if (!empty($plugin_data['RequiresPlugins'])) {
                    $required_plugins = array_map('trim', explode(',', $plugin_data['RequiresPlugins']));
                    
                    foreach ($required_plugins as $required_plugin) {
                        // Check if the required plugin is active
                        $found = false;
                        $is_active = false;
                        
                        foreach (get_plugins() as $path => $data) {
                            $plugin_slug = dirname($path);
                            if (empty($plugin_slug)) {
                                $plugin_slug = basename($path, '.php');
                            }
                            
                            if ($plugin_slug === $required_plugin) {
                                $found = true;
                                $is_active = in_array($path, $active_plugins);
                                
                                if (!$is_active) {
                                    $missing_plugins[] = $data['Name'];
                                }
                                break;
                            }
                        }
                        
                        if (!$found || !$is_active) {
                            $can_activate = false;
                        }
                    }
                }
            }
            
            // Prepare the final requirements message - use the exact format from WP core
            if (!empty($missing_plugins)) {
                // Match the exact format used in WP_Plugins_List_Table
                $requirements['message'] = 'This plugin cannot be activated because it requires the following plugin' . 
                                          (count($missing_plugins) > 1 ? 's' : '') . 
                                          ': ' . implode(', ', $missing_plugins) . '.';
                $requirements['can_activate'] = false;
                $requirements['missing_plugins'] = $missing_plugins;
                $requirements['missing_count'] = count($missing_plugins);
            } else {
                $requirements['can_activate'] = $can_activate;
            }
            
            // Add WordPress version requirement check
            if (!empty($plugin_data['RequiresWP']) && version_compare(get_bloginfo('version'), $plugin_data['RequiresWP'], '<')) {
                $requirements['wp_version_required'] = $plugin_data['RequiresWP'];
                $requirements['current_wp_version'] = get_bloginfo('version');
                $requirements['wp_version_message'] = 'This plugin requires WordPress version ' . $plugin_data['RequiresWP'] . ' or higher. You are running ' . get_bloginfo('version') . '.';
                $requirements['can_activate'] = false;
            }
            
            // Add PHP version requirement check
            if (!empty($plugin_data['RequiresPHP']) && version_compare(PHP_VERSION, $plugin_data['RequiresPHP'], '<')) {
                $requirements['php_version_required'] = $plugin_data['RequiresPHP'];
                $requirements['current_php_version'] = PHP_VERSION;
                $requirements['php_version_message'] = 'This plugin requires PHP version ' . $plugin_data['RequiresPHP'] . ' or higher. You are running ' . PHP_VERSION . '.';
                $requirements['can_activate'] = false;
            }
            
            return $requirements;
        };

        // Helper function to check if auto-updates can be managed for a plugin
        $check_auto_update_capability = function($plugin_path) {
            // Check if auto-updates are enabled for this specific plugin
            $auto_updates = (array) get_site_option('auto_update_plugins', array());
            $is_auto_update_enabled = in_array($plugin_path, $auto_updates);
            
            // Get update information
            $update_plugins = get_site_transient('update_plugins');
            
            // Check if the plugin has an update available
            $has_update_available = isset($update_plugins->response[$plugin_path]);
            
            // Check if the plugin is from WordPress.org repository
            $is_wp_org_plugin = false;
            if ($has_update_available) {
                // WordPress.org plugins have a package URL that contains wordpress.org
                $is_wp_org_plugin = isset($update_plugins->response[$plugin_path]->package) && 
                                   strpos($update_plugins->response[$plugin_path]->package, 'wordpress.org') !== false;
            } else if (isset($update_plugins->no_update[$plugin_path])) {
                // Even if no update is available, check if it's from WordPress.org
                $is_wp_org_plugin = isset($update_plugins->no_update[$plugin_path]->package) && 
                                   strpos($update_plugins->no_update[$plugin_path]->package, 'wordpress.org') !== false;
            }
            
            // Check for scheduled updates
            $scheduled_update_info = null;
            if (function_exists('wp_get_scheduled_event')) {
                $next_update_event = wp_get_scheduled_event('wp_update_plugins');
                if ($next_update_event && $is_auto_update_enabled && $has_update_available) {
                    $time_to_update = $next_update_event->timestamp - time();
                    $hours = floor($time_to_update / 3600);
                    $minutes = floor(($time_to_update % 3600) / 60);
                    
                    if ($hours > 0) {
                        $scheduled_update_info = "Automatic update scheduled in {$hours} hour" . ($hours > 1 ? 's' : '');
                        if ($minutes > 0) {
                            $scheduled_update_info .= " and {$minutes} minute" . ($minutes > 1 ? 's' : '');
                        }
                        $scheduled_update_info .= ".";
                    } elseif ($minutes > 0) {
                        $scheduled_update_info = "Automatic update scheduled in {$minutes} minute" . ($minutes > 1 ? 's' : '') . ".";
                    } else {
                        $scheduled_update_info = "Automatic update scheduled soon.";
                    }
                }
            }
            
            // Check if plugin has its own update mechanism
            $has_custom_updater = false;
            
            // Check for common custom updater files
            $plugin_dir = WP_PLUGIN_DIR . '/' . dirname($plugin_path);
            $common_updater_files = [
                '/updater.php',
                '/includes/updater.php',
                '/inc/updater.php',
                '/admin/updater.php',
                '/includes/class-updater.php',
                '/includes/updates.php',
                '/includes/update-checker.php',
                '/plugin-update-checker.php'
            ];
            
            foreach ($common_updater_files as $updater_file) {
                if (file_exists($plugin_dir . $updater_file)) {
                    $has_custom_updater = true;
                    break;
                }
            }
            
            // Check if plugin has update URI header
            $plugin_data = get_plugin_data(WP_PLUGIN_DIR . '/' . $plugin_path);
            $has_update_uri = !empty($plugin_data['UpdateURI']);
            
            // Check if plugin has any update hooks
            $plugin_file_content = @file_get_contents(WP_PLUGIN_DIR . '/' . $plugin_path);
            $has_update_hooks = false;
            
            if ($plugin_file_content) {
                // Check for common update hooks and functions
                $update_patterns = [
                    'pre_set_site_transient_update_plugins',
                    'site_transient_update_plugins',
                    'plugins_api',
                    'plugin_row_meta',
                    'update_plugins',
                    'upgrader_process_complete',
                    'http_request_args',
                    'check_for_updates',
                    'update_check',
                    'license_key'
                ];
                
                foreach ($update_patterns as $pattern) {
                    if (strpos($plugin_file_content, $pattern) !== false) {
                        $has_update_hooks = true;
                        break;
                    }
                }
            }
            
            // Use WordPress's built-in function to check if auto-updates are enabled for plugins globally
            $wp_auto_update_enabled = function_exists('\\wp_is_auto_update_enabled_for_type') ? 
                \wp_is_auto_update_enabled_for_type('plugin') : false;
            
            // Determine if this plugin can actually be auto-updated
            // A plugin can be auto-updated if:
            // 1. It's from WordPress.org repository and doesn't have a custom updater, OR
            // 2. It has update hooks or an update URI header
            $can_auto_update = ($is_wp_org_plugin && !$has_custom_updater) || $has_update_hooks || $has_update_uri;
            
            // Determine the reason why auto-updates might not be available
            $reason = null;
            if (!$can_auto_update) {
                if (!$is_wp_org_plugin && !$has_update_hooks && !$has_update_uri) {
                    $reason = 'Plugin does not support automatic updates';
                } elseif ($has_custom_updater) {
                    $reason = 'Plugin has custom update system';
                }
            }
            
            return [
                'can_auto_update' => $can_auto_update,
                'auto_update_enabled' => $is_auto_update_enabled,
                'wp_auto_update_enabled' => $wp_auto_update_enabled,
                'is_wp_org_plugin' => $is_wp_org_plugin,
                'has_custom_updater' => $has_custom_updater,
                'has_update_hooks' => $has_update_hooks,
                'has_update_uri' => $has_update_uri,
                'scheduled_update' => $scheduled_update_info,
                'reason' => $reason
            ];
        };

        // Helper function to get plugin meta data including 'Developed by'
        $get_plugin_meta = function($plugin_path) {
            $plugin_file = WP_PLUGIN_DIR . '/' . $plugin_path;
            if (!file_exists($plugin_file)) {
                return null;
            }

            $plugin_data = get_plugin_data($plugin_file);
            $plugin_content = file_get_contents($plugin_file);
            
            // Extract meta information from plugin header
            $developed_by = '';
            $plugin_type = 'standard';
            
            if (preg_match('/\* Developed by: (.*)/', $plugin_content, $matches)) {
                $developed_by = trim($matches[1]);
            }
            
            if (preg_match('/\* Plugin Type: (.*)/', $plugin_content, $matches)) {
                $plugin_type = trim($matches[1]);
            }

            return [
                'developed_by' => $developed_by,
                'is_rightplace_plugin' => strtolower($developed_by) === 'rightplace app',
                'plugin_type' => $plugin_type
            ];
        };

        // Process single plugin if basename is provided
        if ($basename && isset($all_plugins[$basename])) {
            $plugin_path = $basename;
            $plugin_data = $all_plugins[$basename];
            $is_active = in_array($plugin_path, $active_plugins);
            
            // Get plugin meta including 'Developed by'
            $plugin_meta = $get_plugin_meta($plugin_path);
            
            // Check if the plugin matches the status filter
            if (
                $plugin_status === 'all' ||
                ($plugin_status === 'active' && $is_active) ||
                ($plugin_status === 'inactive' && !$is_active)
            ) {
                // Add the single plugin to results
                $plugin_slug = dirname($plugin_path);
                if (empty($plugin_slug)) {
                    $plugin_slug = basename($plugin_path, '.php');
                }
                
                $update_plugins = get_site_transient('update_plugins');
                $paused_plugins = get_site_option('paused_plugins', array());

                $plugin_file_path = WP_PLUGIN_DIR . '/' . $plugin_path;
                $last_modified = file_exists($plugin_file_path) ? filemtime($plugin_file_path) : null;

                $has_update = isset($update_plugins->response[$plugin_path]);
                $new_version = $has_update ? $update_plugins->response[$plugin_path]->new_version : null;
                
                // Check plugin requirements
                $requirements = $check_plugin_requirements($plugin_data, $plugin_path);
                
                // Check auto-update capability
                $auto_update_capability = $check_auto_update_capability($plugin_path);

                $result_plugins[] = [
                    'plugin' => $plugin_path,
                    'status' => $is_active ? 'active' : 'inactive',
                    'name' => $plugin_data['Name'],
                    'plugin_uri' => $plugin_data['PluginURI'] ?? '',
                    'author' => $plugin_data['Author'],
                    'author_uri' => $plugin_data['AuthorURI'] ?? '',
                    'description' => [
                        'raw' => $plugin_data['Description'],
                        'rendered' => wpautop($plugin_data['Description'])
                    ],
                    'version' => $plugin_data['Version'],
                    'requires_wp' => $plugin_data['RequiresWP'] ?? '',
                    'requires_php' => $plugin_data['RequiresPHP'] ?? '',
                    'license' => $plugin_data['License'] ?? '',
                    'licenseURI' => $plugin_data['LicenseURI'] ?? '',
                    'textDomain' => $plugin_data['TextDomain'] ?? '',
                    'domainPath' => $plugin_data['DomainPath'] ?? '',
                    'auto_update' => in_array($plugin_path, $auto_updates) ? 'enabled' : 'disabled',
                    'last_modified' => $last_modified ? array(
                        'timestamp' => $last_modified,
                        'formatted' => date('Y-m-d H:i:s', $last_modified)
                    ) : null,
                    'developed_by' => $plugin_meta['developed_by'] ?? '',
                    'is_rightplace_plugin' => $plugin_meta['is_rightplace_plugin'] ?? false,
                    'plugin_type' => $plugin_meta['plugin_type'] ?? 'standard',
                    'statuses' => [
                        'is_active' => in_array($plugin_path, $active_plugins),
                        'is_inactive' => !in_array($plugin_path, $active_plugins),
                        'is_recently_activated' => isset($recently_activated[$plugin_path]),
                        'has_update' => $has_update,
                        'is_network_active' => isset($network_active[$plugin_path]),
                        'is_must_use' => isset($mu_plugins[$plugin_path]),
                        'is_dropin' => isset($dropins[$plugin_path]),
                        'is_paused' => isset($paused_plugins[$plugin_path]),
                        'auto_update_enabled' => in_array($plugin_path, $auto_updates),
                        'auto_update_disabled' => !in_array($plugin_path, $auto_updates)
                    ],
                    'has_update' => $has_update,
                    'new_version' => $new_version,
                    'update_message' => $has_update ? "There is a new version available. Update to version $new_version now." : null,
                    'requirements' => $requirements,
                    'auto_update_capability' => $auto_update_capability
                ];
            }
        } else {
            // Original loop for all plugins when no basename is provided
            foreach ($all_plugins as $plugin_path => $plugin_data) {
                $is_active = in_array($plugin_path, $active_plugins);
                
                // Get plugin meta including 'Developed by'
                $plugin_meta = $get_plugin_meta($plugin_path);
                
                // Check if plugin should be included based on status filter
                if (!$should_include_plugin($plugin_path, $is_active)) {
                    continue;
                }
    
                $plugin_slug = dirname($plugin_path);
                if (empty($plugin_slug)) {
                    $plugin_slug = basename($plugin_path, '.php');
                }
    
                $update_plugins = get_site_transient('update_plugins');
                $paused_plugins = get_site_option('paused_plugins', array());

                $plugin_file_path = WP_PLUGIN_DIR . '/' . $plugin_path;
                $last_modified = file_exists($plugin_file_path) ? filemtime($plugin_file_path) : null;

                $has_update = isset($update_plugins->response[$plugin_path]);
                $new_version = $has_update ? $update_plugins->response[$plugin_path]->new_version : null;
                
                // Check plugin requirements
                $requirements = $check_plugin_requirements($plugin_data, $plugin_path);
                
                // Check auto-update capability
                $auto_update_capability = $check_auto_update_capability($plugin_path);

                $result_plugins[] = [
                    'plugin' => $plugin_path,
                    'status' => $is_active ? 'active' : 'inactive',
                    'name' => $plugin_data['Name'],
                    'plugin_uri' => $plugin_data['PluginURI'] ?? '',
                    'author' => $plugin_data['Author'],
                    'author_uri' => $plugin_data['AuthorURI'] ?? '',
                    'description' => [
                        'raw' => $plugin_data['Description'],
                        'rendered' => wpautop($plugin_data['Description'])
                    ],
                    'version' => $plugin_data['Version'],
                    'requires_wp' => $plugin_data['RequiresWP'] ?? '',
                    'requires_php' => $plugin_data['RequiresPHP'] ?? '',
                    'license' => $plugin_data['License'] ?? '',
                    'licenseURI' => $plugin_data['LicenseURI'] ?? '',
                    'textDomain' => $plugin_data['TextDomain'] ?? '',
                    'domainPath' => $plugin_data['DomainPath'] ?? '',
                    'auto_update' => in_array($plugin_path, $auto_updates) ? 'enabled' : 'disabled',
                    'last_modified' => $last_modified ? array(
                        'timestamp' => $last_modified,
                        'formatted' => date('Y-m-d H:i:s', $last_modified)
                    ) : null,
                    'developed_by' => $plugin_meta['developed_by'] ?? '',
                    'is_rightplace_plugin' => $plugin_meta['is_rightplace_plugin'] ?? false,
                    'plugin_type' => $plugin_meta['plugin_type'] ?? 'standard',
                    'statuses' => [
                        'is_active' => in_array($plugin_path, $active_plugins),
                        'is_inactive' => !in_array($plugin_path, $active_plugins),
                        'is_recently_activated' => isset($recently_activated[$plugin_path]),
                        'has_update' => $has_update,
                        'is_network_active' => isset($network_active[$plugin_path]),
                        'is_must_use' => isset($mu_plugins[$plugin_path]),
                        'is_dropin' => isset($dropins[$plugin_path]),
                        'is_paused' => isset($paused_plugins[$plugin_path]),
                        'auto_update_enabled' => in_array($plugin_path, $auto_updates),
                        'auto_update_disabled' => !in_array($plugin_path, $auto_updates)
                    ],
                    'has_update' => $has_update,
                    'new_version' => $new_version,
                    'update_message' => $has_update ? "There is a new version available. Update to version $new_version now." : null,
                    'requirements' => $requirements,
                    'auto_update_capability' => $auto_update_capability
                ];
            }
        }

        // Add must-use plugins if requested
        if ($plugin_status === 'mustuse' || $plugin_status === 'all') {
            foreach ($mu_plugins as $plugin_path => $plugin_data) {
                // Add must-use plugin to results with appropriate formatting
                $result_plugins[] = [
                    'plugin' => $plugin_path,
                    'status' => 'mustuse',
                    // ... rest of plugin data formatting
                ];
            }
        }

        // Add drop-ins if requested
        if ($plugin_status === 'dropins' || $plugin_status === 'all') {
            foreach ($dropins as $plugin_path => $plugin_data) {
                // Add drop-in plugin to results with appropriate formatting
                $result_plugins[] = [
                    'plugin' => $plugin_path,
                    'status' => 'dropin',
                    // ... rest of plugin data formatting
                ];
            }
        }

        return [
            'success' => true,
            'plugins' => $result_plugins,
            'params' => $params
        ];
    }

    // change plugin status

    /**
     * Change plugin status (activate/deactivate)
     */
    public function change_plugin_status($params) {
        try {
            // Force load required WordPress functions
            require_once ABSPATH . 'wp-admin/includes/plugin.php';
            require_once ABSPATH . 'wp-admin/includes/file.php';
            require_once ABSPATH . 'wp-includes/pluggable.php';

            // Check required parameters
            if (empty($params['basename']) || empty($params['new_plugin_status'])) {
                return array(
                    'success' => false,
                    'message' => 'Missing required parameters',
                    'params' => $params,
                    'code' => 'missing_parameters'
                );
            }

            $basename = wp_normalize_path($params['basename']);
            $new_status = $params['new_plugin_status'];

            // Verify plugin exists
            if (!file_exists(WP_PLUGIN_DIR . '/' . $basename)) {
                return array(
                    'success' => false,
                    'message' => 'Plugin does not exist: ' . $basename,
                    'params' => $params,
                    'code' => 'plugin_not_found'
                );
            }

            // Check permissions
            if (!current_user_can('activate_plugins')) {
                return array(
                    'success' => false,
                    'message' => 'Insufficient permissions to manage plugins',
                    'params' => $params,
                    'code' => 'insufficient_permissions'
                );
            }

            // Clear any existing errors and disable error reporting temporarily
            error_clear_last();
            $original_error_reporting = error_reporting(0);

            try {
                if ($new_status === 'active') {
                    // Force deactivate first
                    if (is_plugin_active($basename)) {
                        deactivate_plugins($basename, true, is_network_admin());
                        wp_cache_flush();
                        sleep(1);
                    }

                    // Clear plugin status cache
                    wp_clean_plugins_cache(false);
                    
                    // Activate the plugin
                    $result = activate_plugin($basename, '', is_network_admin(), true);
                    
                    if (is_wp_error($result)) {
                        throw new Exception($result->get_error_message());
                    }

                    // Verify activation
                    if (!is_plugin_active($basename)) {
                        throw new Exception('Plugin activation verification failed');
                    }

                } elseif ($new_status === 'inactive') {
                    // Force deactivate
                    deactivate_plugins($basename, true, is_network_admin());
                    wp_cache_flush();
                    
                    // Clear plugin status cache
                    wp_clean_plugins_cache(false);

                    // Verify deactivation
                    if (is_plugin_active($basename)) {
                        throw new Exception('Plugin deactivation verification failed');
                    }
                } else {
                    throw new Exception('Invalid plugin status. Must be either "active" or "inactive"');
                }

                // Restore error reporting
                error_reporting($original_error_reporting);

                // Clear opcache if available
                if (function_exists('opcache_reset')) {
                    @opcache_reset();
                }

                return array(
                    'success' => true,
                    'message' => 'Plugin ' . ($new_status === 'active' ? 'activated' : 'deactivated') . ' successfully',
                    'params' => $params
                );

            } catch (Exception $inner_e) {
                // Restore error reporting
                error_reporting($original_error_reporting);
                throw $inner_e;
            }

        } catch (Exception $e) {
            rp_dev_log('Plugin status change error for ' . $basename . ': ' . $e->getMessage());
            rp_dev_log('Stack trace: ' . $e->getTraceAsString());
            
            // Try to get additional error context
            $error = error_get_last();
            $additional_info = $error ? ' Additional info: ' . json_encode($error) : '';
            
            return array(
                'success' => false,
                'message' => $e->getMessage() . $additional_info,
                'params' => $params,
                'code' => 'plugin_operation_failed',
                'debug' => WP_DEBUG ? array(
                    'error' => $e->getMessage(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    'trace' => $e->getTraceAsString(),
                    'last_error' => $error
                ) : null
            );
        }
    }

    /**
     * Delete a plugin from WordPress
     * 
     * @param array $params {
     *      @type string $basename Plugin basename (e.g. 'plugin-slug/plugin-slug.php')
     * }
     * @return array Response with success status and message
     */
    public function delete_plugin($params) {
        try {
            // Include required WordPress functions
            if (!function_exists('delete_plugins')) {
                require_once ABSPATH . 'wp-admin/includes/plugin.php';
            }
            
            // Check if basename parameter exists
            if (empty($params['basename'])) {
                return array(
                    'success' => false,
                    'message' => 'Missing required parameter: basename',
                    'params' => $params,
                    'code' => 'missing_parameter'
                );
            }

            $basename = $params['basename'];
            
            // Verify plugin exists
            if (!file_exists(WP_PLUGIN_DIR . '/' . $basename)) {
                return array(
                    'success' => false,
                    'message' => 'Plugin does not exist: ' . $basename,
                    'params' => $params,
                    'code' => 'plugin_not_found'
                );
            }

            // Check if we have permissions to delete plugins
            if (!current_user_can('delete_plugins')) {
                return array(
                    'success' => false,
                    'message' => 'You do not have sufficient permissions to delete plugins',
                    'params' => $params,
                    'code' => 'insufficient_permissions'
                );
            }

            // Check if plugin is active and try to deactivate
            if (is_plugin_active($basename)) {
                if (!current_user_can('activate_plugins')) {
                    return array(
                        'success' => false,
                        'message' => 'You do not have sufficient permissions to deactivate plugins',
                        'params' => $params,
                        'code' => 'insufficient_permissions'
                    );
                }
                
                $deactivate_result = deactivate_plugins($basename, true);
                if (is_wp_error($deactivate_result)) {
                    return array(
                        'success' => false,
                        'message' => 'Failed to deactivate plugin: ' . $deactivate_result->get_error_message(),
                        'params' => $params,
                        'code' => 'deactivation_failed'
                    );
                }
            }

            // Verify filesystem access
            if (!function_exists('WP_Filesystem')) {
                require_once ABSPATH . 'wp-admin/includes/file.php';
            }
            WP_Filesystem();

            // Delete the plugin
            $result = delete_plugins(array($basename));

            if (is_wp_error($result)) {
                return array(
                    'success' => false,
                    'message' => 'Failed to delete plugin: ' . $result->get_error_message(),
                    'params' => $params,
                    'code' => 'deletion_failed',
                    'error' => $result->get_error_message()
                );
            }

            if ($result === false) {
                return array(
                    'success' => false,
                    'message' => 'Failed to delete plugin due to filesystem error',
                    'params' => $params,
                    'code' => 'filesystem_error'
                );
            }

            return array(
                'success' => true,
                'message' => 'Plugin deleted successfully',
                'params' => $params
            );

        } catch (Exception $e) {
            // Log the error for debugging
            rp_dev_log('Plugin deletion error: ' . $e->getMessage());
            rp_dev_log('Stack trace: ' . $e->getTraceAsString());
            
            return array(
                'success' => false,
                'message' => 'Internal server error while deleting plugin: ' . $e->getMessage(),
                'params' => $params,
                'code' => 'internal_error',
                'debug' => WP_DEBUG ? array(
                    'error' => $e->getMessage(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    'trace' => $e->getTraceAsString()
                ) : null
            );
        }
    }

    /**
     * Get a simplified list of plugins for LLM consumption
     * 
     * Retrieves all installed plugins with their basic information including
     * activation status and update availability. This provides a lightweight
     * representation suitable for AI processing.
     * 
     * @param array $params Optional parameters (not currently used)
     * @return array {
     *     @type bool   $success Always true on successful execution
     *     @type array  $plugins List of plugins with their details:
     *         {
     *             @type string $plugin      Plugin basename (e.g. 'plugin-dir/plugin-file.php')
     *             @type string $name        Human-readable plugin name
     *             @type string $status      Either 'active' or 'inactive'
     *             @type string $version     Current installed version
     *             @type bool   $has_update  Whether an update is available
     *             @type string $new_version Version available for update (null if none)
     *         }
     *     @type int    $count   Total number of plugins found
     * }
     */
    public static function get_plugins_for_llm($params = []) {
        if (!function_exists('get_plugins')) {
            require_once ABSPATH . 'wp-admin/includes/plugin.php';
        }
        
        $all_plugins = get_plugins();
        $active_plugins = get_option('active_plugins');
        $result_plugins = [];
        
        // Force refresh of update information
        delete_site_transient('update_plugins');
        wp_update_plugins();
        
        // Get update information
        $update_plugins = get_site_transient('update_plugins');
        
        foreach ($all_plugins as $plugin_path => $plugin_data) {
            $is_active = in_array($plugin_path, $active_plugins);
            $has_update = isset($update_plugins->response[$plugin_path]);
            $new_version = $has_update ? $update_plugins->response[$plugin_path]->new_version : null;
            
            $result_plugins[] = [
                'plugin' => $plugin_path,
                'name' => $plugin_data['Name'],
                'status' => $is_active ? 'active' : 'inactive',
                'version' => $plugin_data['Version'],
                'has_update' => $has_update,
                'new_version' => $new_version
            ];
        }
        
        return [
            'success' => true,
            'plugins' => $result_plugins,
            'count' => count($result_plugins)
        ];
    }

}

new Plugins();
