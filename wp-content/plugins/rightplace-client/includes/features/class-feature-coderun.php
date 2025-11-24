<?php

namespace Rightplace\Features;

class CodeRun {
    private $base_directory;
    private $run_code_map = [
        'global_functions' => ['hook' => 'init', 'type' => 'Functions'],
        'header_content' => ['hook' => 'wp_head', 'type' => 'Content'],
        'body_start_content' => ['hook' => 'wp_body_open', 'type' => 'Content'],
        'body_end_content' => ['hook' => 'wp_footer', 'type' => 'Content'],
        'global_styles' => ['hook' => 'wp_enqueue_scripts', 'type' => 'Styles'],
        'global_scripts' => ['hook' => 'wp_enqueue_scripts', 'type' => 'Scripts']
    ];

    public function __construct() {
        $this->base_directory = WP_CONTENT_DIR . '/uploads/rightplace/coderun/';
        if (!file_exists($this->base_directory)) {
            wp_mkdir_p($this->base_directory);
        }

        // Register hooks
        add_filter('rightplace_action_filter/saveCodeRun', [$this, 'save_code_run']);
        add_filter('rightplace_action_filter/deleteCodeRun', [$this, 'delete_code_run']);
        add_filter('rightplace_action_filter/toggleCodeRun', [$this, 'toggle_code_run']);
        add_filter('rightplace_action_filter/getCodeRun', [$this, 'get_code_run']);
        
        // Execute hooks
        add_action('init', [$this, 'execute_functions']);
        add_action('wp_head', [$this, 'execute_header'], 20);
        add_action('wp_body_open', [$this, 'execute_body_start'], 20);
        add_action('wp_footer', [$this, 'execute_footer'], 20);
        add_action('wp_enqueue_scripts', [$this, 'execute_assets']);
    }

    // Core file operations
    private function parseFile($filePath) {
        if (!file_exists($filePath)) return null;
        $content = file_get_contents($filePath);
        if (!$content) return null;

        // Extract doc block - updated pattern to handle all formats
        $extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
        
        // Different patterns for different file types
        if ($extension === 'html') {
            $docPattern = '/<!--\s*(.*?)-->/s';
        } else {
            $docPattern = '/\/\*\*\s*(.*?)\*\//s';
        }

        if (!preg_match($docPattern, $content, $matches)) {
            rp_dev_log("No matching doc block found with pattern. Content start: " . substr($content, 0, 100));
            return null;
        }

        // Parse metadata
        $metadata = [
            'id' => '', 
            'title' => '', 
            'description' => '', 
            'path' => '',
            'runCodeIn' => '', 
            'version' => '', 
            'status' => ''
        ];

        $docBlock = trim($matches[1]);
        foreach (explode("\n", $docBlock) as $line) {
            $line = trim($line, " \t\n\r\0\x0B*");
            if (strpos($line, '@') === 0) {
                list($tag, $value) = array_pad(explode(' ', $line, 2), 2, '');
                $tag = ltrim($tag, '@');
                if (array_key_exists($tag, $metadata)) {
                    $metadata[$tag] = trim($value);
                }
            }
        }

        // Get code after doc block based on file type
        switch ($extension) {
            case 'php':
                $pattern = '/^<\?php\s*\/\*\*.*?\*\/\s*\?>/s';
                $code = preg_replace($pattern, '', $content);
                break;
            case 'html':
                // For HTML, look for content after -->
                $pattern = '/<!--.*?-->\s*/s';
                $code = preg_replace($pattern, '', $content, 1);
                break;
            case 'js':
            case 'css':
                // For JS/CSS, look for content after the last */
                $pattern = '/\/\*.*?\*\/\s*/s';
                $code = preg_replace($pattern, '', $content, 1);
                break;
            default:
                $code = '';
        }

        $metadata['code'] = trim($code);
        return $metadata;
    }

    private function formatContent($docBlock, $code, $extension) {
        switch ($extension) {
            case 'php':
                return "<?php\n/**\n{$docBlock}\n*/\n?>\n{$code}";
            case 'html':
                return "<!--\n{$docBlock}\n-->\n{$code}";
            case 'js':
            case 'css':
                // Don't remove existing comments, just add our docblock at the top
                return "/**\n{$docBlock}\n*/\n\n{$code}";
            default:
                return "/**\n{$docBlock}\n*/\n{$code}";
        }
    }
    
    public function execute_functions() { $this->execute_code_by_type('global_functions'); }
    public function execute_header() { $this->execute_code_by_type('header_content'); }
    public function execute_body_start() { $this->execute_code_by_type('body_start_content'); }
    public function execute_footer() { $this->execute_code_by_type('body_end_content'); }
    public function execute_assets() {
        $this->execute_code_by_type('global_styles');
        $this->execute_code_by_type('global_scripts');
    }

    /**
     * Execute code by type
     */
    private function execute_code_by_type($run_code_in) {
        rp_dev_log("=== START execute_code_by_type ===");
        rp_dev_log("Executing code type: " . $run_code_in);

        // Get all files in the coderun directory
        $files = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($this->base_directory, \RecursiveDirectoryIterator::SKIP_DOTS)
        );

        foreach ($files as $file) {
            if (!$file->isFile()) continue;

            $file_path = $file->getPathname();
            $metadata = $this->parseFile($file_path);

            // Skip if no metadata or if not the right type or if not active
            if (!$metadata || 
                $metadata['runCodeIn'] !== $run_code_in || 
                $metadata['status'] !== 'on') {
                continue;
            }

            rp_dev_log("Processing active file: " . $file_path);
            // rp_dev_log("Metadata: " . print_r($metadata, true));

            switch ($this->run_code_map[$run_code_in]['type']) {
                case 'Functions':
                    if (file_exists($file_path)) {
                        // Save previous error reporting level and turn on all errors
                        $errorLevel = error_reporting(E_ALL);
                        
                        // Start output buffering to capture any error messages
                        ob_start();
                        
                        // Try to include the file with error suppression
                        $includeResult = @include_once $file_path;
                        
                        // Get any error that occurred
                        $error = error_get_last();
                        
                        // Discard output buffer
                        ob_end_clean();
                        
                        // Restore previous error reporting level
                        error_reporting($errorLevel);
                        
                        // Log error if one occurred
                        if ($error !== null) {
                            rp_dev_log("Error including file {$file_path}: " . $error['message']);
                            rp_dev_log("Error type: " . $error['type'] . " in " . $error['file'] . " on line " . $error['line']);
                        }
                    }
                    break;

                case 'Content':
                    rp_dev_log("Outputting Content: " . $metadata['title']);
                    echo PHP_EOL . '<!-- Start: ' . esc_html($metadata['title']) . ' -->' . PHP_EOL;
                    $content = $this->extractUserCode($metadata['code'], pathinfo($file_path, PATHINFO_EXTENSION));
                    echo $content;
                    echo PHP_EOL . '<!-- End: ' . esc_html($metadata['title']) . ' -->' . PHP_EOL;
                    break;

                case 'Styles':
                    wp_enqueue_style(
                        'rightplace-custom-' . $metadata['id'],
                        content_url(ltrim($metadata['path'], '/wp-content')),
                        [],
                        $metadata['version']
                    );
                    break;

                case 'Scripts':
                    wp_enqueue_script(
                        'rightplace-custom-' . $metadata['id'],
                        content_url(ltrim($metadata['path'], '/wp-content')),
                        [],
                        $metadata['version'],
                        true
                    );
                    break;
            }
        }
        rp_dev_log("=== END execute_code_by_type ===");
    }
    private function extractUserCode($content, $extension = 'php') {
        if ($extension === 'php') {
            // Remove the doc block and its PHP tags
            return preg_replace('/^<\?php\s*\/\*\*.*?\*\/\s*\?>(\s*)/s', '', $content);
        }

        // For other file types
        $patterns = [
            'html' => '/<!--[\s\S]*?-->\s*\n*(.*?)$/s',
            'css' => '/\/\*[\s\S]*?\*\/\s*\n*(.*?)$/s',
            'js' => '/\/\*[\s\S]*?\*\/\s*\n*(.*?)$/s'
        ];
        
        $pattern = $patterns[$extension] ?? $patterns['html'];
        if (preg_match($pattern, $content, $matches)) {
            return trim($matches[1]);
        }
        return '';
    }

    private function combineMetadataAndCode($metadata, $code, $extension = 'php') {
        $docBlock = "/**\n";
        $docBlock .= "*  Please don't remove this comment; otherwise the file won't be detected.\n";
        $docBlock .= "*  @id {$metadata['id']}\n";
        $docBlock .= "*  @title {$metadata['title']}\n";
        $docBlock .= "*  @description {$metadata['description']}\n";
        $docBlock .= "*  @path {$metadata['path']}\n";
        $docBlock .= "*  @runCodeIn {$metadata['runCodeIn']}\n";
        $docBlock .= "*  @version {$metadata['version']}\n";
        $docBlock .= "*  @status {$metadata['status']}\n";
        $docBlock .= "*/";

        if ($extension === 'php') {
            // Remove any existing doc block with PHP tags
            $code = preg_replace('/^<\?php\s*\/\*\*.*?\*\/\s*\?>(\s*)/s', '', $code);
            
            // Add new doc block at the start
            return "<?php\n" . $docBlock . "\n?>\n" . trim($code);
        }

        switch ($extension) {
            case 'html':
                return "<!--\n" . $docBlock . "\n-->\n\n" . $code;
            case 'css':
            case 'js':
                return "/*\n" . $docBlock . "\n*/\n\n" . $code;
            default:
                return $docBlock . "\n\n" . $code;
        }
    }

    private function createDocBlock($metadata) {
        // Don't add /** at the start - just the content
        $docBlock = "*  Please don't remove this comment; otherwise the file won't be detected.\n";
        $metadataFields = ['id', 'title', 'description', 'path', 'runCodeIn', 'version', 'status'];
        foreach ($metadataFields as $field) {
            $docBlock .= "*  @{$field} {$metadata[$field]}\n";
        }
        return $docBlock;
    }

    private function replaceDocBlock($originalContents, $newDocBlock, $extension) {
        switch ($extension) {
            case 'php':
                return preg_replace('/\<\?php\s*\/\*\*.*?\*\/\s*\?>/', "<?php\n{$newDocBlock}\n?>", $originalContents, 1);
            case 'html':
                return preg_replace('/<!--[\s\S]*?-->/', "<!--\n{$newDocBlock}\n-->", $originalContents, 1);
            case 'js':
            case 'css':
                return preg_replace('/\/\*[\s\S]*?\*\//', "/*\n{$newDocBlock}", $originalContents, 1);
            default:
                return preg_replace('/\/\*[\s\S]*?\*\//', "/*\n{$newDocBlock}\n*/", $originalContents, 1);
        }
    }
    private function getFileContent($filePath) {
        if (!file_exists($filePath)) return null;
        return file_get_contents($filePath);
    }

    //**  -------------------------------- CORE functions --------------------------- */
    public function save_code_run($params) {
        try {
            rp_dev_log("=== START SAVE_CODE_RUN ===");
            rp_dev_log("Params: " . print_r($params, true));

            // Validate required parameters
            $required_params = ['id', 'title', 'description', 'path', 'runCodeIn', 'status', 'code'];
            foreach ($required_params as $param) {
                if (!isset($params[$param])) {
                    throw new \Exception("Missing required parameter: {$param}");
                }
            }

            $new_path = ABSPATH . ltrim($params['path'], '/');
            
            // Get current version from existing file and increment it
            $existing_file = $this->findFileById($params['id']);
            if ($existing_file) {
                $existing_metadata = $this->parseFile($existing_file);
                if ($existing_metadata) {
                    $current_version = (int)$existing_metadata['version'];
                    $params['version'] = $current_version + 1;
                    rp_dev_log("Incrementing version from {$current_version} to: {$params['version']}");
                } else {
                    $params['version'] = 1;
                    rp_dev_log("No existing metadata found, starting with version 1");
                }
            } else {
                $params['version'] = 1;
                rp_dev_log("New file, starting with version 1");
            }

            // Rest of the existing save_code_run logic...
            $docBlock = "*  Please don't remove this comment; otherwise the file won't be detected.\n";
            $docBlock .= "*  @id {$params['id']}\n";
            $docBlock .= "*  @title {$params['title']}\n";
            $docBlock .= "*  @description {$params['description']}\n";
            $docBlock .= "*  @path {$params['path']}\n";
            $docBlock .= "*  @runCodeIn {$params['runCodeIn']}\n";
            $docBlock .= "*  @version {$params['version']}\n";
            $docBlock .= "*  @status {$params['status']}";

            // Format and save content
            $extension = pathinfo($new_path, PATHINFO_EXTENSION);
            rp_dev_log("File extension: " . $extension);
            
            $final_content = $this->formatContent($docBlock, $params['code'], $extension);

            // Delete existing file if it's different from new path
            if ($existing_file && $existing_file !== $new_path) {
                rp_dev_log("Deleting existing file: " . $existing_file);
                if (!unlink($existing_file)) {
                    rp_dev_log("Warning: Failed to delete existing file");
                }
            }

            // Create directory if it doesn't exist
            $dir = dirname($new_path);
            if (!file_exists($dir)) {
                rp_dev_log("Creating directory: " . $dir);
                if (!wp_mkdir_p($dir)) {
                    throw new \Exception("Failed to create directory: " . $dir);
                }
            }

            // Save the file
            rp_dev_log("Saving file to: " . $new_path);
            if (file_put_contents($new_path, $final_content) === false) {
                throw new \Exception("Failed to write file: " . $new_path);
            }

            rp_dev_log("=== END SAVE_CODE_RUN ===");
            return ['success' => true, 'fullPath' => $new_path];

        } catch (\Exception $e) {
            rp_dev_log("Error in save_code_run: " . $e->getMessage());
            rp_dev_log("Stack trace: " . $e->getTraceAsString());
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    public function toggle_code_run($params) {
        if (ob_get_level()) ob_clean();
        
        try {
            rp_dev_log("=== START TOGGLE_CODE_RUN ===");
            rp_dev_log("Params: " . print_r($params, true));

            if (!isset($params['id']) || !isset($params['isActive'])) {
                throw new \Exception('Missing required parameters');
            }

            // Find the file with matching ID
            $files = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($this->base_directory, \RecursiveDirectoryIterator::SKIP_DOTS)
            );

            $file_to_update = null;
            $current_metadata = null;
            foreach ($files as $file) {
                if (!$file->isFile()) continue;

                $file_path = $file->getPathname();
                $metadata = $this->parseFile($file_path);

                if ($metadata && $metadata['id'] === $params['id']) {
                    $file_to_update = $file_path;
                    $current_metadata = $metadata;
                    break;
                }
            }

            if (!$file_to_update || !$current_metadata) {
                throw new \Exception('File not found for ID: ' . $params['id']);
            }

            // Update the status in metadata
            $current_metadata['status'] = $params['isActive'] ? 'on' : 'off';
            
            // Get file extension
            $extension = strtolower(pathinfo($file_to_update, PATHINFO_EXTENSION));

            // Create new doc block
            $docBlock = $this->createDocBlock($current_metadata);
            
            // Get the original content
            $originalContents = file_get_contents($file_to_update);
            
            // Format the final content
            $finalContents = $this->formatContent($docBlock, $current_metadata['code'], $extension);

            // Write the updated content back to the file
            if (file_put_contents($file_to_update, $finalContents) === false) {
                throw new \Exception('Failed to update file');
            }

            rp_dev_log("File updated successfully: " . $file_to_update);
            return wp_send_json([
                'success' => true,
                'message' => 'Code run status updated successfully'
            ]);

        } catch (\Exception $e) {
            rp_dev_log("Error in toggle_code_run: " . $e->getMessage());
            return wp_send_json([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }

    public function get_code_run($params) {
        @ob_clean();
        header('Content-Type: application/json');
        header('X-Content-Type-Options: nosniff');
        
        try {
            rp_dev_log("=== START GET_CODE_RUN ===");
            rp_dev_log("Base directory: " . $this->base_directory);
            rp_dev_log("Directory exists: " . (file_exists($this->base_directory) ? 'yes' : 'no'));
            rp_dev_log("Directory is readable: " . (is_readable($this->base_directory) ? 'yes' : 'no'));

            // Use full namespace for RecursiveIteratorIterator and RecursiveDirectoryIterator
            $files = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($this->base_directory, \RecursiveDirectoryIterator::SKIP_DOTS)
            );

            $code_runs = [];
            
            // Iterate through all files in the directory
            foreach ($files as $file) {
                if (!$file->isFile()) {
                    continue;
                }

                $file_path = $file->getPathname();
                $file_extension = strtolower(pathinfo($file_path, PATHINFO_EXTENSION));
                
                rp_dev_log("Processing file: " . $file_path);
                rp_dev_log("File extension: " . $file_extension);

                $metadata = $this->parseFile($file_path);
                
                if ($metadata) {
                    rp_dev_log("Metadata found for file: " . $file_path);
                    rp_dev_log("Metadata: " . print_r($metadata, true));
                    
                    $code_run = [
                        'id' => $metadata['id'],
                        'title' => $metadata['title'],
                        'description' => $metadata['description'],
                        'full_file_path' => $metadata['path'],
                        'code' => $metadata['code'],
                        'run_code_in' => $metadata['runCodeIn'],
                        'type' => $this->run_code_map[$metadata['runCodeIn']]['type'] ?? '',
                        'version' => (int)$metadata['version'],
                        'is_active' => $metadata['status'] === 'on',
                        'created_at' => filemtime($file_path),
                        'updated_at' => filemtime($file_path)
                    ];
                    
                    rp_dev_log("Formatted code run: " . print_r($code_run, true));
                    $code_runs[] = $code_run;
                } else {
                    rp_dev_log("No metadata found for file: " . $file_path);
                }
            }

            rp_dev_log("Total code runs found: " . count($code_runs));

            rp_dev_log("=== END GET_CODE_RUN ===");

            return wp_send_json([
                'success' => true,
                'data' => $code_runs
            ]);

        } catch (\Exception $e) {
            rp_dev_log("Error in get_code_run: " . $e->getMessage());
            rp_dev_log("Stack trace: " . $e->getTraceAsString());
            return wp_send_json([
                'success' => false,
                'message' => $e->getMessage(),
                'data' => []
            ]);
        }
    }
    public function delete_code_run($params) {
        if (ob_get_level()) ob_clean();
        
        try {
            rp_dev_log("=== START DELETE_CODE_RUN ===");
            rp_dev_log("Params: " . print_r($params, true));

            if (!isset($params['id'])) {
                throw new \Exception('No ID provided');
            }

            // Find the file with matching ID in the coderun directory
            $files = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($this->base_directory, \RecursiveDirectoryIterator::SKIP_DOTS)
            );

            $file_to_delete = null;
            foreach ($files as $file) {
                if (!$file->isFile()) continue;

                $file_path = $file->getPathname();
                $metadata = $this->parseFile($file_path);

                if ($metadata && $metadata['id'] === $params['id']) {
                    $file_to_delete = $file_path;
                    break;
                }
            }

            if (!$file_to_delete) {
                throw new \Exception('File not found for ID: ' . $params['id']);
            }

            // Delete the file
            if (unlink($file_to_delete)) {
                rp_dev_log("File deleted successfully: " . $file_to_delete);
        return wp_send_json([
            'success' => true,
            'message' => 'Code run deleted successfully'
        ]);
            } else {
                throw new \Exception('Failed to delete file');
            }

        } catch (\Exception $e) {
            rp_dev_log("Error in delete_code_run: " . $e->getMessage());
            return wp_send_json([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }

    private function findFileById($id) {
        try {
            $files = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($this->base_directory, \RecursiveDirectoryIterator::SKIP_DOTS)
            );

            foreach ($files as $file) {
                if (!$file->isFile()) continue;

                $metadata = $this->parseFile($file->getPathname());
                if ($metadata && $metadata['id'] === $id) {
                    return $file->getPathname();
                }
            }

            return null;
        } catch (\Exception $e) {
            rp_dev_log("Error in findFileById: " . $e->getMessage());
            return null;
        }
    }
}

new CodeRun();
