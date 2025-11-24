<?php
namespace Rightplace\Logs;

class Logs {
    public function __construct() {
        add_filter('rightplace_action_filter/getErrorLogContent', [$this, 'getErrorLogContent']);
    }

public function getErrorLogContent($params) {
    if (empty($params['path'])) {
        return ['error' => 'No log file path provided.'];
    }

    $relativePath = ltrim($params['path'], '/'); // remove leading slash
    $type = isset($params['type']) && strtolower($params['type']) === 'top' ? 'top' : 'bottom';
    $codeOption = isset($params['codeOption']) ? $params['codeOption'] : 'bottom';

    // If codeOption is 'all', ignore lines and return the whole file
    if ($codeOption === 'all') {
        $lines = null;
    } else {
        $lines = isset($params['lines']) ? (int)$params['lines'] : 100;
    }

    $currentDir = rtrim(ABSPATH, '/');
    $maxDepth = 10; // Limit how far up we can go
    $fileFound = false;

    for ($i = 0; $i < $maxDepth; $i++) {
        $tryPath = $currentDir . '/' . $relativePath;

        if (file_exists($tryPath) && is_readable($tryPath)) {
            $fileFound = true;
            break;
        }

        $parent = dirname($currentDir);
        if ($parent === $currentDir) break; // Reached root
        $currentDir = $parent;
    }

    if (!$fileFound) {
        rp_dev_log("Rightplace Logs: Failed to find log. Relative path: '/{$relativePath}'. Final checked path: '{$tryPath}'. WordPress base checked from: '{$currentDir}'.");
        return ['error' => 'Log file not found or not readable.'];
    }

    try {
        $file_content = @file($tryPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        if ($file_content === false) {
            return ['error' => 'Failed to read log file.'];
        }

        if ($codeOption === 'all') {
            $result = $file_content;
        } else {
            $total_lines = count($file_content);
            $lines_to_return = min($lines, $total_lines);
            $result = $type === 'top'
                ? array_slice($file_content, 0, $lines_to_return)
                : array_slice($file_content, $total_lines - $lines_to_return);
        }

        // Join the array of lines into a single string separated by newlines
        return ['result' => implode("\n", $result), 'filePath' => $tryPath];

    } catch (\Throwable $e) {
        rp_dev_log("Rightplace Logs: Exception reading '{$tryPath}': " . $e->getMessage());
            return ['error' => 'Exception occurred while reading the log.'];
        }
    }
}

new Logs();
