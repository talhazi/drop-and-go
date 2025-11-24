<?php

namespace DebugToolkit;

use DebugToolkit\Traits\Path_Utils;

/**
 * File system utilities
 */
class Filesystem_Utils {
    use Path_Utils;

    /**
     * Check if a file exists and is writable
     *
     * @param string 
     * @param bool 
     * @return array|bool 
     * @throws \Exception 
     */
    public static function ensure_file_exists_and_writable($path, $throw = true) {
        return Error_Handler::execute_with_error_handling(
            function() use ($path, $throw) {
                $exists = file_exists($path);
                $writable = $exists && is_writable($path);
                
                if ($throw && !$exists) {
                    throw new \Exception("File not found: $path");
                }
                
                if ($throw && !$writable) {
                    throw new \Exception("File not writable: $path");
                }
                
                if ($throw) {
                    return true;
                }
                
                return [
                    'exists' => $exists,
                    'writable' => $writable,
                    'message' => !$exists ? "File does not exist" : 
                        (!$writable ? "File exists but is not writable" : "File exists and is writable")
                ];
            },
            'File Existence Check',
            null
        );
    }
    
    /**
     * Ensure a directory exists and is writable
     *
     * @param string 
     * @param int 
     * @param bool 
     * @return bool 
     * @throws \Exception 
     */
    public static function ensure_directory($directory_path, $permissions = null, $recursive = true) {
        if ($permissions === null) {
            $permissions = Constants::DBTK_FILE_PERMISSIONS['DIRECTORY'];
        }
        
        if (file_exists($directory_path)) {
            if (!is_dir($directory_path)) {
                throw new \Exception("Path exists but is not a directory: $directory_path");
            }
            
            if (!is_writable($directory_path)) {
                throw new \Exception("Directory exists but is not writable: $directory_path");
            }
            
            return true;
        }
        
        return Error_Handler::fs_operation(
            function() use ($directory_path, $permissions, $recursive) {
                return mkdir($directory_path, $permissions, $recursive);
            },
            "Failed to create directory: $directory_path"
        );
    }
    
    /**
     * Verify path
     *
     * @param string 
     * @param array 
     * @return bool 
     * @throws \Exception 
     */
    public static function verify_path_safety($path, $allowed_roots = []) {
        return Error_Handler::execute_with_error_handling(
            function() use ($path, $allowed_roots) {
                if (empty($path)) {
                    throw new \Exception('Empty path provided');
                }
                
                $real_path = realpath($path);
                
                if (!$real_path) {
                    $parent = dirname($path);
                    $real_parent = realpath($parent);
                    
                    if (!$real_parent) {
                        throw new \Exception("Cannot determine real path for: $path");
                    }
                    
                    $real_path = $real_parent . '/' . basename($path);
                }
                
                if (empty($allowed_roots)) {
                    $allowed_roots = [realpath(ABSPATH)];
                }
                
                foreach ($allowed_roots as $root) {
                    $real_root = realpath($root);
                    if ($real_root && strpos($real_path, $real_root) === 0) {
                        return true;
                    }
                }
                
                throw new \Exception("Path is outside allowed boundaries: $path");
            },
            'Path Safety Verification',
            null
        );
    }
    
    /**
     * Check if a path is a critical WP directory
     *
     * @param string 
     * @return bool 
     */
    public static function is_critical_wp_directory($path) {
        $real_path = realpath($path);
        
        if (!$real_path) {
            return false;
        }
        
        $critical_dirs = [
            realpath(ABSPATH),
            realpath(ABSPATH . 'wp-admin'),
            realpath(ABSPATH . 'wp-includes'),
            realpath(WP_CONTENT_DIR),
            realpath(WP_PLUGIN_DIR),
        ];
        
        return in_array($real_path, $critical_dirs);
    }
    
    /**
     * Get the path to the debug log file
     *
     * @return string 
     */
    public static function get_debug_log_path() {
        $log_path = WP_CONTENT_DIR . '/debug.log'; 
        
        if (defined('WP_DEBUG_LOG')) {
            if (is_string(WP_DEBUG_LOG)) {
                $log_path = WP_DEBUG_LOG;
            } elseif (WP_DEBUG_LOG === true) {
                $log_path = WP_CONTENT_DIR . '/debug.log';
            }
        }
        
        $log_path = self::normalize_path($log_path);
        
        if (!file_exists($log_path) && file_exists(WP_CONTENT_DIR . '/debug.log')) {
            $log_path = self::normalize_path(WP_CONTENT_DIR . '/debug.log');
        }
        
        return $log_path;
    }
    
    /**
     * Check debug log file status
     *
     * @param bool 
     * @return array 
     * @throws \Exception 
     */
    public static function check_debug_log_file($throw = false) {
        $log_path = self::get_debug_log_path();
        return self::ensure_file_exists_and_writable($log_path, $throw);
    }
    
    /**
     * Copy a file with appropriate permissions
     *
     * @param string 
     * @param string 
     * @return bool 
     * @throws \Exception 
     */
    public static function copy_file($source_file, $destination_file) {
        return Error_Handler::fs_operation(
            function() use ($source_file, $destination_file) {
                if (copy($source_file, $destination_file)) {
                    chmod($destination_file, Constants::DBTK_FILE_PERMISSIONS['FILE']);
                    return true;
                }
                return false;
            },
            "Failed to copy file: $source_file to $destination_file"
        );
    }
    
    /**
     * Remove a file
     *
     * @param string 
     * @param bool 
     * @return bool 
     * @throws \Exception 
     */
    public static function remove_file($file_path) {
        return Error_Handler::fs_operation(
            function() use ($file_path) {
                return unlink($file_path);
            },
            "Failed to remove file: $file_path",
            true 
        );
    }
    
    /**
     * Remove a directory
     *
     * @param string $directory_path Directory path to remove
     * @param bool $silent Whether to silently log errors instead of throwing exceptions
     * @return bool True on success
     * @throws \Exception If remove fails and not in silent mode
     */
    public static function remove_directory($directory_path, $silent = false) {
        if (!is_dir($directory_path)) {
            throw new \Exception("Path is not a directory: $directory_path");
        }
        
        return Error_Handler::fs_operation(
            function() use ($directory_path) {
                return rmdir($directory_path);
            },
            "Failed to remove directory: $directory_path",
            $silent
        );
    }
    
    /**
     * Recursively delete a directory and all its contents
     *
     * @param string $directory_path Directory path to delete
     * @param bool $silent Whether to silently log errors instead of throwing exceptions
     * @return bool True on success
     * @throws \Exception If deletion fails and not in silent mode
     */
    public static function recursively_delete_directory($directory_path, $silent = false) {
        return Error_Handler::execute_with_error_handling(
            function() use ($directory_path, $silent) {
                $directory_path = self::validate_path($directory_path, [
                    'must_be_dir' => true,
                    'check_critical' => true,
                ]);

                if (!file_exists($directory_path)) {
                    return true; // Nothing to delete :)
                }
                
                return Error_Handler::fs_operation(
                    function() use ($directory_path) {
                        $items_to_delete = [];
                        
                        foreach (
                            $iterator = new \RecursiveIteratorIterator(
                                new \RecursiveDirectoryIterator($directory_path, \RecursiveDirectoryIterator::SKIP_DOTS),
                                \RecursiveIteratorIterator::CHILD_FIRST
                            ) as $item
                        ) {
                            $real_path = $item->getRealPath();
                            
                            $subpath = $iterator->getSubPathname();
                            if (strpos($subpath, '..') !== false) {
                                Error_Handler::log('Skipping suspicious path: ' . $subpath, 'Directory Deletion');
                                continue;
                            }
                            
                            $full_path = $directory_path . DIRECTORY_SEPARATOR . $subpath;
                            
                            try {
                                $validated_path = self::validate_path($full_path, [
                                    'must_exist' => true,
                                    'check_critical' => true,
                                    'allowed_roots' => [realpath(dirname($directory_path))]
                                ]);
                                
                                $items_to_delete[] = [
                                    'path' => $real_path,
                                    'is_dir' => $item->isDir()
                                ];
                            } catch (\Exception $e) {
                                Error_Handler::log_exception($e, 'Path Validation');

                                continue;
                            }
                        }
                        
                        foreach ($items_to_delete as $item) {
                            if ($item['is_dir']) {
                                if (!rmdir($item['path'])) {
                                    Error_Handler::log('Failed to remove directory: ' . $item['path'], 'Directory Deletion');
                                }
                            } else {
                                if (!unlink($item['path'])) {
                                    Error_Handler::log('Failed to remove file: ' . $item['path'], 'Directory Deletion');
                                }
                            }
                        }
                        
                        return rmdir($directory_path);
                    },
                    "Failed to recursively delete directory: $directory_path",
                    $silent
                );
            },
            'Directory Deletion',
            "Failed to recursively delete directory: $directory_path",
            null
        );
    }
    
    /**
     * Super duper mega awesome path validation
     * 
     * @param string 
     * @param array $options {
     *     @type bool        $must_exist      
     *     @type bool        $must_be_dir     
     *     @type bool        $must_be_file    
     *     @type bool        $must_be_writable
     *     @type array       $allowed_roots   
     *     @type bool        $check_critical  
     * }
     * @return string 
     * @throws \Exception 
     */
    public static function validate_path($path, $options = []) {
        return Error_Handler::execute_with_error_handling(
            function() use ($path, $options) {
                $defaults = [
                    'must_exist' => false,
                    'must_be_dir' => false,
                    'must_be_file' => false,
                    'must_be_writable' => false,
                    'allowed_roots' => [],
                    'check_critical' => true,
                ];
                
                $options = array_merge($defaults, $options);
                
                if (empty($path) || !is_string($path)) {
                    throw new \Exception('Invalid path provided');
                }
                
                $path = self::normalize_path($path);
                
                if ($options['must_exist'] && !file_exists($path)) {
                    throw new \Exception("Path does not exist: $path");
                }
                
                if ($options['must_be_dir'] && file_exists($path) && !is_dir($path)) {
                    throw new \Exception("Path is not a directory: $path");
                }
                
                if ($options['must_be_file'] && file_exists($path) && !is_file($path)) {
                    throw new \Exception("Path is not a file: $path");
                }
                
                if ($options['must_be_writable'] && file_exists($path) && !is_writable($path)) {
                    throw new \Exception("Path is not writable: $path");
                }
                
                if ($options['check_critical'] && self::is_critical_wp_directory($path)) {
                    throw new \Exception("Cannot operate on WordPress system directory: $path");
                }
                
                self::verify_path_safety($path, $options['allowed_roots']);
                
                return $path;
            },
            'Path Validation',
            null
        );
    }
    
    /**
     * Copy a directory recursively
     *
     * @param string 
     * @param string 
     * @param array 
     * @return bool 
     * @throws \Exception 
     */
    public static function copy_directory($source_directory, $destination_directory, $options = []) {
        $defaults = [
            'allowed_roots' => [realpath(ABSPATH), realpath(Constants::DBTK_PATH)],
            'skip_invalid_paths' => true
        ];
        
        $options = array_merge($defaults, $options);
        
        $source_directory = self::validate_path($source_directory, [
            'must_exist' => true,
            'must_be_dir' => true,
            'allowed_roots' => $options['allowed_roots']
        ]);
        
        $destination_directory = self::validate_path($destination_directory);
        
        if (!file_exists($destination_directory)) {
            self::ensure_directory($destination_directory);
        }
        
        return Error_Handler::execute_with_error_handling(
            function() use ($source_directory, $destination_directory, $options) {
                foreach (
                    $iterator = new \RecursiveIteratorIterator(
                        new \RecursiveDirectoryIterator($source_directory, \RecursiveDirectoryIterator::SKIP_DOTS),
                        \RecursiveIteratorIterator::SELF_FIRST
                    ) as $item
                ) {
                    $target = $destination_directory . DIRECTORY_SEPARATOR . $iterator->getSubPathname();
                    
                    if (strpos($iterator->getSubPathname(), '..') !== false) {
                        continue;
                    }
                    
                    try {
                        self::validate_path($target);
                        
                        if ($item->isDir()) {
                            if (!file_exists($target)) {
                                self::ensure_directory($target);
                            }
                        } else {
                            self::copy_file($item, $target);
                        }
                    } catch (\Exception $e) {
                        if (!$options['skip_invalid_paths']) {
                            throw $e;
                        }
                        continue;
                    }
                }
                
                return true;
            },
            'Directory Copy',
            'Failed to copy directory: ' . $source_directory . ' to ' . $destination_directory,
            function() use ($destination_directory) {
                if (file_exists($destination_directory)) {
                    self::recursively_delete_directory($destination_directory, true);
                }
            }
        );
    }
} 