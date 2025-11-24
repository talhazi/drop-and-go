<?php

namespace DebugToolkit\Traits;

/**
 * Path utility functions trait
 */
trait Path_Utils {
    /**
     * Normalize path
     * 
     * @param string $path 
     * @param bool $remove_trailing_slash 
     * @return string 
     */
    public static function normalize_path($path, $remove_trailing_slash = true) {

        if (function_exists('wp_normalize_path')) {
            $path = wp_normalize_path($path);
        } else {

            $path = str_replace('\\', '/', $path);
            $path = preg_replace('|/+|', '/', $path);
        }
        
        if ($remove_trailing_slash) {
            $path = rtrim($path, '/\\');
        }
        
        return $path;
    }
} 