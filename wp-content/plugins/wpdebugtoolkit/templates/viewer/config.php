<?php
if (!defined('ABSPATH')) {
    define('ABSPATH', dirname(__FILE__) . '/');
}

if (!defined('WP_CONTENT_DIR')) {
    define('WP_CONTENT_DIR', ABSPATH . 'wp-content');
}

function dbtk_normalize_path($path) {
    $path = str_replace('\\', '/', $path);
    $path = preg_replace('|/+|', '/', $path);
    return $path;
}

$log_file = '{{LOG_PATH}}';

$fallback_paths = [
    dbtk_normalize_path(WP_CONTENT_DIR . '/debug.log'),
    dbtk_normalize_path(ABSPATH . 'wp-content/debug.log'),
    dbtk_normalize_path(ABSPATH . 'debug.log')
];

if (!file_exists($log_file)) {
    foreach ($fallback_paths as $path) {
        if (file_exists($path)) {
            $log_file = $path;
            break;
        }
    }
}

return [
    'log_file' => $log_file,
]; 