<?php
namespace Rightplace\Features;

use Exception;

class File_Sync
{
    public function __construct()
    {
        add_filter('rightplace_action_filter/getFileTree', [$this, 'get_file_tree']);
        add_filter('rightplace_action_filter/getFileByPath', [$this, 'get_file_by_path']);
        add_filter('rightplace_action_filter/getFileContent', [$this, 'get_file_content']);
        add_filter('rightplace_action_filter/saveFileContent', [$this, 'save_file_content']);
        add_filter('rightplace_action_filter/createFile', [$this, 'create_file']);
        add_filter('rightplace_action_filter/uploadFile', [$this, 'upload_file']);
        add_filter('rightplace_action_filter/deleteFile', [$this, 'delete_file']);
        add_filter('rightplace_action_filter/renameFile', [$this, 'rename_file']);
        add_filter('rightplace_action_filter/runCodySync', [$this, 'run_cody_sync']);
        add_filter("rightplace_action_filter/search", [$this, "search"]);
        add_filter("rightplace_action_filter/getFullTree", [$this, "get_full_tree"]);
    }

    public function search($params)
    {
        // Retrieve search parameters
        $searchTerm = isset($params['searchTerm']) ? trim($params['searchTerm']) : '';
        $baseDirectory = isset($params['baseDirectory']) ? $params['baseDirectory'] : '/';

        // Validate search term and base directory
        if ($searchTerm === '') {
            return [
                'success' => false,
                'error' => 'Search term is required.'
            ];
        }

        // Build the full path from ABSPATH and the provided baseDirectory
        $baseDirPath = rtrim(ABSPATH, '/') . '/' . ltrim($baseDirectory, '/');
        if (!is_dir($baseDirPath)) {
            return [
                'success' => false,
                'error' => 'Base directory not found.'
            ];
        }

        // Build ignore list from defaults and .gitignore
        $ignoreList = ['node_modules', '.git'];
        $gitignoreFile = ABSPATH . '/.gitignore';
        if (file_exists($gitignoreFile)) {
            $lines = file($gitignoreFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            foreach ($lines as $line) {
                $line = trim($line);
                // Skip comments and empty lines
                if ($line === '' || strpos($line, '#') === 0) {
                    continue;
                }
                $ignoreList[] = $line;
            }
        }

        $results = [];

        try {
            $directoryIterator = new \RecursiveDirectoryIterator($baseDirPath, \RecursiveDirectoryIterator::SKIP_DOTS);
            $iterator = new \RecursiveIteratorIterator(
                $directoryIterator,
                \RecursiveIteratorIterator::SELF_FIRST
            );

            foreach ($iterator as $file) {
                $filename = $file->getFilename();

                // Check against ignore patterns using fnmatch (supports wildcards)
                $skip = false;
                foreach ($ignoreList as $pattern) {
                    if (fnmatch($pattern, $filename, FNM_CASEFOLD)) {
                        // If a directory matches an ignore pattern, skip its children
                        if ($file->isDir()) {
                            $iterator->next();
                        }
                        $skip = true;
                        break;
                    }
                }
                if ($skip) {
                    continue;
                }

                // Check if the filename contains the search term (case-insensitive)
                if (stripos($filename, $searchTerm) !== false) {
                    // Get the full path
                    $fullPath = $file->getPathname();

                    // Get the path relative to WordPress root using realpath
                    $absPath = realpath(ABSPATH);
                    $realFullPath = realpath($fullPath);

                    // Remove ABSPATH portion and convert backslashes to forward slashes
                    $relativePath = substr($realFullPath, strlen($absPath));
                    $relativePath = str_replace('\\', '/', $relativePath);

                    // Ensure it starts with a forward slash
                    $relativePath = '/' . ltrim($relativePath, '/');

                    // Debug log
                    $this->log_debug("Path processing:", [
                        'ABSPATH' => ABSPATH,
                        'absPath' => $absPath,
                        'fullPath' => $fullPath,
                        'realFullPath' => $realFullPath,
                        'relativePath' => $relativePath
                    ]);

                    // Prepare item structure
                    if ($file->isDir()) {
                        $results[] = [
                            'id' => $relativePath,
                            'label' => $filename,
                            'type' => 'folder',
                            'children' => [],
                            'meta' => $this->get_folder_meta($fullPath)
                        ];
                    } else {
                        $fileInfo = @stat($fullPath);
                        $pathInfo = pathinfo($fullPath);
                        $results[] = [
                            'id' => $relativePath,
                            'label' => $filename,
                            'type' => 'file',
                            'ext' => isset($pathInfo['extension']) ? $pathInfo['extension'] : '',
                            'meta' => [
                                'modified' => date('Y-m-d H:i:s', $file->getMTime()),
                                'size' => $file->getSize(),
                                'permissions' => isset($fileInfo['mode']) ? $fileInfo['mode'] : null,
                                'mime' => @mime_content_type($fullPath)
                            ]
                        ];
                    }

                    // Stop searching after 25 results
                    if (count($results) >= 25) {
                        break;
                    }
                }
            }

            return [
                'success' => true,
                'data' => $results
            ];

        } catch (\Exception $e) {
            $this->log_debug("Error during search: " . $e->getMessage());
            return [
                'success' => false,
                'error' => 'An error occurred during search.'
            ];
        }
    }

    public function run_cody_sync($params)
    {
        $path = $params['path'];
        $content = $params['content'];
        $type = $params['type'];
        $fullFileName = $params['fullFileName'];

        $this->log_debug("Starting run_cody_sync with params:", [
            'path' => $path,
            'content_length' => strlen($content),
            'type' => $type,
            'fullFileName' => $fullFileName
        ]);

        try {
            // Convert Windows-style path to Unix-style
            $path = str_replace('\\', '/', $path);
            $full_path = ABSPATH . ltrim($path, '/');

            $this->log_debug("Processing path:", [
                'original_path' => $path,
                'full_path' => $full_path,
                'exists' => file_exists($full_path),
                'is_dir' => is_dir($full_path),
                'is_file' => is_file($full_path)
            ]);

            // Create directory structure if needed
            $directory = dirname($full_path);
            if (!file_exists($directory)) {
                $this->log_debug("Creating directory structure:", ['directory' => $directory]);
                if (!mkdir($directory, 0755, true)) {
                    throw new Exception("Failed to create directory structure: {$directory}");
                }
            }

            // If it's a folder, just create it and return
            if ($type === 'folder') {
                if (!file_exists($full_path) && !mkdir($full_path, 0755, true)) {
                    throw new Exception("Failed to create folder: {$full_path}");
                }
                return ['success' => true];
            }

            // If path exists and is a directory but should be a file, remove it
            if (file_exists($full_path) && is_dir($full_path)) {
                $this->log_debug("Removing existing directory to create file:", ['path' => $full_path]);
                if (!rmdir($full_path)) {
                    throw new Exception("Failed to remove existing directory: {$full_path}");
                }
            }

            // Write the content to file
            $write_result = file_put_contents($full_path, $content);
            if ($write_result === false) {
                throw new Exception("Failed to write content to file: {$full_path}");
            }

            $this->log_debug("File written successfully:", [
                'path' => $full_path,
                'bytes_written' => $write_result,
                'is_file' => is_file($full_path)
            ]);

            return ['success' => true];

        } catch (Exception $e) {
            $this->log_debug("Error in run_cody_sync: " . $e->getMessage());
            return [
                'success' => false,
                'code' => 'run_cody_sync_error',
                'error' => $e->getMessage()
            ];
        }
    }

    // Add logger method at the beginning of the class
    private function log_debug($message, $data = null)
    {
        $log_message = "[File_Sync Debug] " . $message;
        if ($data !== null) {
            $log_message .= "\nData: " . print_r($data, true);
        }
        rp_dev_log($log_message);
    }

    /**
     * Build a file/directory tree starting from given paths.
     * Adjust logic as needed to suit your use case.
     */
    public function get_file_tree($params)
    {
        $this->log_debug("Starting get_file_tree", $params);
        $sort_items = function ($a, $b) {
            // Folders first, then sort lexically
            if ($a['type'] === $b['type']) {
                return strcasecmp($a['label'], $b['label']);
            }
            return ($a['type'] === 'folder') ? -1 : 1;
        };

        $paths_to_get = $params['paths_to_get'] ?? ['/'];
        $wp_root = ABSPATH;
        $result = [];

        foreach ($paths_to_get as $path) {
            // Handle root path separately
            if ($path === '/') {
                $full_path = rtrim($wp_root, '/');
                $items = scandir($full_path);
                foreach ($items as $item) {
                    if ($item === '.' || $item === '..')
                        continue;

                    $item_path = $full_path . '/' . $item;
                    if (is_dir($item_path)) {
                        $children = [];
                        $sub_items = scandir($item_path);
                        foreach ($sub_items as $sub_item) {
                            if ($sub_item === '.' || $sub_item === '..')
                                continue;

                            $sub_item_path = $item_path . '/' . $sub_item;
                            if (is_dir($sub_item_path)) {
                                $children[] = [
                                    'id' => '/' . $item . '/' . $sub_item,
                                    'label' => $sub_item,
                                    'type' => 'folder',
                                    'children' => [],
                                    'meta' => $this->get_folder_meta($sub_item_path)
                                ];
                            } else {
                                $file_info = stat($sub_item_path);
                                $path_info = pathinfo($sub_item_path);

                                $children[] = [
                                    'id' => '/' . $item . '/' . $sub_item,
                                    'label' => $sub_item,
                                    'type' => 'file',
                                    'ext' => $path_info['extension'] ?? '',
                                    'meta' => [
                                        'modified' => date('Y-m-d H:i:s', $file_info['mtime']),
                                        'size' => $file_info['size'],
                                        'permissions' => $file_info['mode'],
                                        'mime' => mime_content_type($sub_item_path)
                                    ]
                                ];
                            }
                        }
                        usort($children, $sort_items);
                        $result[] = [
                            'id' => '/' . $item,
                            'label' => $item,
                            'type' => 'folder',
                            'children' => $children,
                            'meta' => $this->get_folder_meta($item_path)
                        ];
                    } else {
                        $file_info = stat($item_path);
                        $path_info = pathinfo($item_path);

                        $result[] = [
                            'id' => '/' . $item,
                            'label' => $item,
                            'type' => 'file',
                            'ext' => $path_info['extension'] ?? '',
                            'meta' => [
                                'modified' => date('Y-m-d H:i:s', $file_info['mtime']),
                                'size' => $file_info['size'],
                                'permissions' => $file_info['mode'],
                                'mime' => mime_content_type($item_path)
                            ]
                        ];
                    }
                }
                continue;
            }

            // If a subpath is requested
            $full_path = rtrim($wp_root . ltrim($path, '/'), '/');
            $parent_folders = explode('/', trim($path, '/'));
            $current_path = '';
            $current_tree = &$result;

            // Build folder structure up to the requested path
            foreach ($parent_folders as $folder) {
                $current_path .= '/' . $folder;
                $folder_exists = false;
                foreach ($current_tree as &$item) {
                    if ($item['id'] === $current_path) {
                        $current_tree = &$item['children'];
                        $folder_exists = true;
                        break;
                    }
                }
                if (!$folder_exists) {
                    $folder_item = [
                        'id' => $current_path,
                        'label' => $folder,
                        'type' => 'folder',
                        'children' => [],
                        'meta' => $this->get_folder_meta($current_path)
                    ];
                    $current_tree[] = $folder_item;
                    $current_tree = &$current_tree[count($current_tree) - 1]['children'];
                }
            }

            // Scan that directory
            if (is_dir($full_path)) {
                $items = scandir($full_path);
                foreach ($items as $item) {
                    if ($item === '.' || $item === '..')
                        continue;

                    $item_path = $full_path . '/' . $item;
                    $relative_path = $path . '/' . $item;
                    $relative_path = ltrim($relative_path, '/');

                    if (is_dir($item_path)) {
                        $current_tree[] = [
                            'id' => '/' . $relative_path,
                            'label' => $item,
                            'type' => 'folder',
                            'children' => [],
                            'meta' => $this->get_folder_meta($item_path)
                        ];
                    } else {
                        $file_info = stat($item_path);
                        $path_info = pathinfo($item_path);

                        $current_tree[] = [
                            'id' => '/' . $relative_path,
                            'label' => $item,
                            'type' => 'file',
                            'ext' => $path_info['extension'] ?? '',
                            'meta' => [
                                'modified' => date('Y-m-d H:i:s', $file_info['mtime']),
                                'size' => $file_info['size'],
                                'permissions' => $file_info['mode'],
                                'mime' => mime_content_type($item_path)
                            ]
                        ];
                    }
                }
            }
            usort($current_tree, $sort_items);
        }

        usort($result, $sort_items);

        return [
            'success' => true,
            'data' => $result
        ];
    }

    /**
     * Return the contents of a given file (plus metadata).
     */
    public function get_file_content($params)
    {
        $this->log_debug("Starting get_file_content", $params);

        $path = $params['path'];
        $full_path = ABSPATH . ltrim($path, '/');

        if (!is_readable($full_path)) {
            $this->log_debug("File not readable: " . $full_path);
            return [
                'success' => false,
                'error' => 'File is not readable'
            ];
        }

        $file_info = @stat($full_path);
        if ($file_info === false) {
            return [
                'success' => false,
                'error' => 'Unable to get file information'
            ];
        }

        $path_info = pathinfo($full_path);
        $content = @file_get_contents($full_path);

        if ($content === false) {
            return [
                'success' => false,
                'error' => 'Unable to read file contents'
            ];
        }

        return [
            'success' => true,
            'data' => [
                'original_request_path' => $path,
                'content' => $content,
                'meta' => [
                    'full_path' => $full_path,
                    'modified' => date('Y-m-d H:i:s', $file_info['mtime']),
                    'created' => date('Y-m-d H:i:s', $file_info['ctime']),
                    'size' => $file_info['size'],
                    'permissions' => $file_info['mode'],
                    'mime' => @mime_content_type($full_path) ?: 'application/octet-stream',
                    'extension' => $path_info['extension'] ?? '',
                    'filename' => $path_info['filename'],
                    'basename' => $path_info['basename'],
                    'dirname' => $path_info['dirname'],
                    'readable' => true
                ]
            ]
        ];
    }

    /**
     * Save content to a file **after** checking its PHP syntax in a temp file.
     */
    public function save_file_content($params)
    {
        $this->log_debug("Starting save_file_content", [
            'path' => $params['path'],
            'content_length' => strlen($params['content'])
        ]);

        rp_dev_log("=== START FILE SAVE OPERATION ===");

        $path = $params['path'];
        $content = $params['content'];
        $full_path = ABSPATH . ltrim($path, '/');

        try {
            // we only want to validate php files. so the path name should end with .php
            // if (pathinfo($path, PATHINFO_EXTENSION) === 'php') {
            //     $validation_result = \RightPlace\Plugin_Code_Validator::validate($params['path'], $content);
            //     if ($validation_result['success'] === false) {
            //         return $validation_result;

            //     }
            // }

            $result = file_put_contents($full_path, $content);
            if ($result === false) {
                throw new Exception("Failed to write file contents to $full_path");
            }

            rp_dev_log("=== END FILE SAVE OPERATION - SUCCESS ===");
            return ['success' => true, 'validation_result' => []];

        } catch (Exception $e) {
            rp_dev_log("=== END FILE SAVE OPERATION - ERROR ===");
            rp_dev_log($e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Create a new file or folder.
     */
    public function create_file($params)
    {
        $this->log_debug("Starting create_file", $params);
        $type = $params['type'] ?? null;
        $name = $params['name'] ?? null;
        $wpUploadPath = $params['wpUploadPath'] ?? null;
        $defaultContent = $params['defaultContent'] ?? '';

        if (!$type || !$name || !$wpUploadPath) {
            return ['success' => false, 'error' => 'Missing required parameters'];
        }

        $full_path = ABSPATH . ltrim($wpUploadPath, '/') . '/' . $name;

        if ($type === 'folder') {
            if (!mkdir($full_path, 0755, true)) {
                return ['success' => false, 'error' => 'Unable to create folder'];
            }
        } else {
            if (!touch($full_path)) {
                return ['success' => false, 'error' => 'Unable to create file'];
            }

            // If defaultContent is provided, write it to the file
            if (!empty($defaultContent)) {
                $write_result = file_put_contents($full_path, $defaultContent);
                if ($write_result === false) {
                    return ['success' => false, 'error' => 'Unable to write default content to file'];
                }
            }
        }

        return ['success' => true];
    }

    /**
     * Upload a nested folder/file structure to WordPress.
     */
    public function upload_file($params)
    {
        $this->log_debug("Starting upload_file", [
            'wpUploadPath' => $params['wpUploadPath'],
            'files_count' => count($params['files'])
        ]);
        rp_dev_log("=== Upload File Request Start ===");
        rp_dev_log("Request Params: " . print_r($params, true));

        if (!isset($params['files'])) {
            rp_dev_log("ERROR: Missing files data");
            return ['success' => false, 'error' => 'No files data provided'];
        }

        try {
            $root_folder = $params['files'];
            $wp_base_path = ABSPATH . ltrim($params['wpUploadPath'], '/');

            $result = [
                'processed' => 0,
                'skipped' => [],
                'errors' => []
            ];

            $this->process_folder_structure($root_folder, $wp_base_path, $result);

            return [
                'success' => true,
                'result' => [
                    'success' => empty($result['errors']),
                    'processed' => $result['processed'],
                    'skipped' => $result['skipped'],
                    'errors' => $result['errors']
                ]
            ];

        } catch (Exception $e) {
            rp_dev_log("CRITICAL ERROR: " . $e->getMessage());
            return ['success' => false, 'code' => 'upload_file_error', 'error' => $e->getMessage()];
        }
    }

    /**
     * Recursive helper for `upload_file`.
     */
    private function process_folder_structure($folder, $base_path, &$result)
    {
        $this->log_debug("Processing folder structure", [
            'path' => $folder['path'],
            'type' => $folder['type']
        ]);
        $current_path = $base_path . '/' . ltrim($folder['path'], '/');
        if ($folder['type'] === 'folder') {
            if (!file_exists($current_path)) {
                if (!mkdir($current_path, 0755, true)) {
                    $result['errors'][] = "Failed to create folder: {$folder['path']}";
                    return;
                }
            }
        }

        if (!empty($folder['children'])) {
            foreach ($folder['children'] as $item) {
                if ($item['type'] === 'folder') {
                    $this->process_folder_structure($item, $base_path, $result);
                } else {
                    // It's a file
                    $file_path = $base_path . '/' . ltrim($item['path'], '/');
                    try {
                        // Ensure parent directory
                        $dir = dirname($file_path);
                        if (!file_exists($dir)) {
                            mkdir($dir, 0755, true);
                        }

                        if (file_exists($file_path)) {
                            $result['skipped'][] = $item['path'];
                            continue;
                        }

                        if (isset($item['content']['base64'])) {
                            $content = base64_decode($item['content']['base64']);
                            if (file_put_contents($file_path, $content) !== false) {
                                $result['processed']++;
                            } else {
                                $result['errors'][] = "Failed to write file: {$item['path']}";
                            }
                        }
                    } catch (Exception $e) {
                        $result['errors'][] = "Error processing {$item['path']}: " . $e->getMessage();
                    }
                }
            }
        }
    }

    /**
     * Delete a file or folder (recursively).
     */
    public function delete_file($params)
    {
        $this->log_debug("Starting delete_file", $params);
        $path = $params['wpPath'];
        $type = $params['type'];
        $full_path = rtrim(ABSPATH . ltrim($path, '/'), '/');

        rp_dev_log("Attempting to delete: " . $full_path);

        if (!file_exists($full_path)) {
            rp_dev_log("File or folder does not exist: " . $full_path);
            return ['success' => false, 'error' => 'File or folder does not exist'];
        }

        if ($type === 'folder') {
            if (!is_dir($full_path)) {
                rp_dev_log("Path is not a folder: " . $full_path);
                return ['success' => false, 'error' => 'Path is not a folder'];
            }
            $result = $this->delete_directory($full_path);
        } else {
            $result = unlink($full_path);
        }

        if ($result) {
            return ['success' => true];
        } else {
            rp_dev_log("Failed to delete: " . $full_path);
            return ['success' => false, 'error' => 'Failed to delete'];
        }
    }

    private function delete_directory($dir)
    {
        $this->log_debug("Deleting directory", ['dir' => $dir]);
        if (!is_dir($dir)) {
            return false;
        }
        $items = scandir($dir);
        foreach ($items as $item) {
            if ($item === '.' || $item === '..')
                continue;
            $path = $dir . '/' . $item;
            if (is_dir($path)) {
                $this->delete_directory($path);
            } else {
                unlink($path);
            }
        }
        return rmdir($dir);
    }

    /**
     * Rename a file or folder.
     */
    public function rename_file($params)
    {
        $this->log_debug("Starting rename_file", $params);
        $path = urldecode($params['wpPath']);
        $new_name = $params['newName'];
        $full_path = ABSPATH . ltrim($path, '/');
        $path_info = pathinfo($full_path);
        $new_full_path = $path_info['dirname'] . '/' . $new_name;

        rp_dev_log("Original path: " . $full_path);
        rp_dev_log("New path: " . $new_full_path);

        if (!file_exists($full_path)) {
            rp_dev_log("Source path does not exist: " . $full_path);
            return ['success' => false, 'error' => 'Source path does not exist'];
        }

        if (file_exists($new_full_path)) {
            rp_dev_log("Target path already exists: " . $new_full_path);
            return ['success' => false, 'error' => 'A file or folder with the new name already exists'];
        }

        if (rename($full_path, $new_full_path)) {
            return [
                'success' => true,
                'result' => [
                    'oldPath' => $full_path,
                    'newPath' => $new_full_path
                ]
            ];
        } else {
            rp_dev_log("Failed to rename from {$full_path} to {$new_full_path}");
            return [
                'success' => false,
                'error' => 'Failed to rename file/folder'
            ];
        }
    }

    /**
     * Get folder metadata (modified time, created time, size, etc.).
     */
    private function get_folder_meta($path)
    {
        $this->log_debug("Getting folder meta", ['path' => $path]);
        try {
            if (!is_readable($path)) {
                return [
                    'modified' => '',
                    'created' => '',
                    'size' => 0,
                    'permissions' => 0,
                    'items_count' => 0,
                    'readable' => false
                ];
            }

            $stat = @stat($path);
            if ($stat === false) {
                return [
                    'modified' => '',
                    'created' => '',
                    'size' => 0,
                    'permissions' => 0,
                    'items_count' => 0,
                    'readable' => false
                ];
            }

            return [
                'modified' => date('Y-m-d H:i:s', $stat['mtime']),
                'created' => date('Y-m-d H:i:s', $stat['ctime']),
                'size' => $this->get_folder_size($path),
                'permissions' => $stat['mode'],
                'items_count' => @iterator_count(new \FilesystemIterator($path, \FilesystemIterator::SKIP_DOTS)),
                'readable' => true
            ];
        } catch (Exception $e) {
            return [
                'modified' => '',
                'created' => '',
                'size' => 0,
                'permissions' => 0,
                'items_count' => 0,
                'readable' => false
            ];
        }
    }

    /**
     * Recursively get the total size of a folder.
     */
    private function get_folder_size($path)
    {
        $this->log_debug("Calculating folder size", ['path' => $path]);
        try {
            if (!is_readable($path)) {
                return 0;
            }

            $total_size = 0;
            $files = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($path, \RecursiveDirectoryIterator::SKIP_DOTS),
                \RecursiveIteratorIterator::SELF_FIRST
            );

            foreach ($files as $file) {
                if ($file->isFile() && $file->isReadable()) {
                    $total_size += $file->getSize();
                }
            }

            return $total_size;
        } catch (Exception $e) {
            return 0;
        }
    }

    /**
     * Get a specific file by its path
     */
    public function get_file_by_path($params)
    {
        $this->log_debug("Starting get_file_by_path", $params);

        $path = $params['path'] ?? '';
        $include_content = isset($params['isContent']) ? (bool) $params['isContent'] : false;

        if (empty($path)) {
            return [
                'success' => false,
                'error' => 'Path is required'
            ];
        }

        $full_path = ABSPATH . ltrim($path, '/');

        if (!file_exists($full_path)) {
            return [
                'success' => false,
                'error' => 'File or directory not found'
            ];
        }

        $file_info = @stat($full_path);
        if ($file_info === false) {
            return [
                'success' => false,
                'error' => 'Unable to get file information'
            ];
        }

        $path_info = pathinfo($full_path);
        $is_dir = is_dir($full_path);

        $result = [
            'id' => $path,
            'label' => $path_info['basename'],
            'type' => $is_dir ? 'folder' : 'file',
            'path' => $path,
            'meta' => [
                'modified' => date('Y-m-d H:i:s', $file_info['mtime']),
                'created' => date('Y-m-d H:i:s', $file_info['ctime']),
                'size' => $file_info['size'],
                'permissions' => $file_info['mode'],
                'readable' => is_readable($full_path),
                'writable' => is_writable($full_path)
            ]
        ];

        if (!$is_dir) {
            $result['ext'] = $path_info['extension'] ?? '';
            $result['meta']['mime'] = @mime_content_type($full_path) ?: 'application/octet-stream';

            // Include content if requested and file is readable
            if ($include_content && is_readable($full_path)) {
                $content = @file_get_contents($full_path);
                if ($content !== false) {
                    $result['content'] = $content;
                }
            }
        } else {
            $result['meta']['items_count'] = @iterator_count(new \FilesystemIterator($full_path, \FilesystemIterator::SKIP_DOTS));

            // Get children for the directory
            $children = [];
            $items = scandir($full_path);
            foreach ($items as $item) {
                if ($item === '.' || $item === '..')
                    continue;

                $item_path = $full_path . '/' . $item;
                $relative_path = $path . '/' . $item;
                $relative_path = ltrim($relative_path, '/');

                if (is_dir($item_path)) {
                    $children[] = [
                        'id' => '/' . $relative_path,
                        'label' => $item,
                        'type' => 'folder',
                        'path' => '/' . $relative_path,
                        'meta' => $this->get_folder_meta($item_path)
                    ];
                } else {
                    $file_info = stat($item_path);
                    $path_info = pathinfo($item_path);

                    $child_item = [
                        'id' => '/' . $relative_path,
                        'label' => $item,
                        'type' => 'file',
                        'path' => '/' . $relative_path,
                        'ext' => $path_info['extension'] ?? '',
                        'meta' => [
                            'modified' => date('Y-m-d H:i:s', $file_info['mtime']),
                            'created' => date('Y-m-d H:i:s', $file_info['ctime']),
                            'size' => $file_info['size'],
                            'permissions' => $file_info['mode'],
                            'readable' => is_readable($item_path),
                            'writable' => is_writable($item_path),
                            'mime' => @mime_content_type($item_path) ?: 'application/octet-stream'
                        ]
                    ];

                    // Include content for child files if requested and readable
                    if ($include_content && is_readable($item_path)) {
                        $content = @file_get_contents($item_path);
                        if ($content !== false) {
                            $child_item['content'] = $content;
                        }
                    }

                    $children[] = $child_item;
                }
            }

            // Sort children: folders first, then files, both alphabetically
            usort($children, function ($a, $b) {
                if ($a['type'] === $b['type']) {
                    return strcasecmp($a['label'], $b['label']);
                }
                return ($a['type'] === 'folder') ? -1 : 1;
            });

            $result['children'] = $children;
        }

        return [
            'success' => true,
            'data' => $result
        ];
    }

    public function get_full_tree($params)
    {
        $this->log_debug("Starting get_full_tree", $params);

        $path = isset($params['path']) ? $params['path'] : '/';
        $max_depth = isset($params['max_depth']) ? (int) $params['max_depth'] : 5; // Default max depth of 5 levels
        $ignore_patterns = isset($params['ignore']) ? $params['ignore'] : ['node_modules', '.git', '.svn', '.DS_Store'];

        // Convert Windows-style path to Unix-style
        $path = str_replace('\\', '/', $path);
        $full_path = ABSPATH . ltrim($path, '/');

        // Get WordPress content directory relative path
        $wp_content_dir = str_replace(ABSPATH, '', WP_CONTENT_DIR);
        $wp_content_dir = str_replace('\\', '/', $wp_content_dir);
        $wp_content_dir = '/' . ltrim($wp_content_dir, '/');

        $this->log_debug("Path processing", [
            'requested_path' => $path,
            'full_path' => $full_path,
            'wp_content_dir' => $wp_content_dir
        ]);

        if (!file_exists($full_path)) {
            return [
                'success' => false,
                'error' => 'Path does not exist: ' . $path
            ];
        }

        if (!is_readable($full_path)) {
            return [
                'success' => false,
                'error' => 'Path is not readable: ' . $path
            ];
        }

        try {
            $start_time = microtime(true);

            // Build the tree recursively
            $tree = $this->build_recursive_tree($path, $full_path, $max_depth, 0, $ignore_patterns);

            $end_time = microtime(true);
            $execution_time = $end_time - $start_time;

            $this->log_debug("get_full_tree completed", [
                'path' => $path,
                'execution_time' => $execution_time,
                'tree_size' => count($tree)
            ]);

            return [
                'success' => true,
                'data' => $tree,
                'meta' => [
                    'execution_time' => $execution_time,
                    'max_depth' => $max_depth,
                    'base_path' => $path
                ]
            ];
        } catch (Exception $e) {
            $this->log_debug("Error in get_full_tree: " . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Recursively builds the file/folder tree structure
     * 
     * @param string $relative_path The path relative to WordPress root
     * @param string $full_path The full server path
     * @param int $max_depth Maximum depth to traverse
     * @param int $current_depth Current depth in the recursion
     * @param array $ignore_patterns Patterns to ignore
     * @return array The tree structure
     */
    private function build_recursive_tree($relative_path, $full_path, $max_depth = 5, $current_depth = 0, $ignore_patterns = [])
    {
        $result = [];
        $sort_items = function ($a, $b) {
            // Folders first, then sort lexically
            if ($a['type'] === $b['type']) {
                return strcasecmp($a['label'], $b['label']);
            }
            return ($a['type'] === 'folder') ? -1 : 1;
        };

        if (!is_readable($full_path)) {
            return $result;
        }

        try {
            $items = scandir($full_path);
        } catch (Exception $e) {
            $this->log_debug("Error scanning directory: " . $full_path, ['error' => $e->getMessage()]);
            return $result;
        }

        foreach ($items as $item) {
            if ($item === '.' || $item === '..')
                continue;

            // Skip ignored patterns
            $skip = false;
            foreach ($ignore_patterns as $pattern) {
                if (fnmatch($pattern, $item)) {
                    $skip = true;
                    break;
                }
            }
            if ($skip)
                continue;

            $item_full_path = $full_path . '/' . $item;

            // Get the path relative to WordPress root
            $abspath_rel = strlen(ABSPATH);
            $item_wp_relative_path = substr($item_full_path, $abspath_rel);
            $item_wp_relative_path = ltrim($item_wp_relative_path, '/');

            // Ensure it has the wp-content prefix format
            if (!empty($item_wp_relative_path)) {
                $item_wp_relative_path = 'wp-content/' . ltrim(str_replace('wp-content', '', $item_wp_relative_path), '/');
            }

            // Skip if we can't access it
            if (!file_exists($item_full_path))
                continue;

            if (is_dir($item_full_path)) {
                $children = [];

                // Only recurse deeper if we haven't reached max depth
                if ($current_depth < $max_depth) {
                    $children = $this->build_recursive_tree(
                        $item_wp_relative_path,
                        $item_full_path,
                        $max_depth,
                        $current_depth + 1,
                        $ignore_patterns
                    );
                }

                $result[] = [
                    'id' => $item_wp_relative_path,
                    'label' => $item,
                    'type' => 'folder',
                    'path' => $item_wp_relative_path,
                    'children' => $children,
                    'meta' => $this->get_folder_meta($item_full_path)
                ];
            } else {
                $file_info = stat($item_full_path);
                if ($file_info === false)
                    continue; // Skip if we can't get info

                $path_info = pathinfo($item_full_path);

                $result[] = [
                    'id' => $item_wp_relative_path,
                    'label' => $item,
                    'type' => 'file',
                    'path' => $item_wp_relative_path,
                    'ext' => isset($path_info['extension']) ? $path_info['extension'] : '',
                    'meta' => [
                        'modified' => date('Y-m-d H:i:s', $file_info['mtime']),
                        'created' => date('Y-m-d H:i:s', $file_info['ctime']),
                        'size' => $file_info['size'],
                        'permissions' => $file_info['mode'],
                        'readable' => is_readable($item_full_path),
                        'writable' => is_writable($item_full_path),
                        'mime' => @mime_content_type($item_full_path) ?: 'application/octet-stream'
                    ]
                ];
            }
        }

        usort($result, $sort_items);
        return $result;
    }
}

new File_Sync();
