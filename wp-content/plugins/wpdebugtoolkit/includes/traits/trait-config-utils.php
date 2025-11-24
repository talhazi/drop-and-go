<?php
/**
 * Config utilities trait
 *
 * @package DebugToolkit
 */

namespace DebugToolkit\Traits;

/**
 * Config_Utils trait 
 */
trait Config_Utils {
    /**
     * Cleanup incorrect constants
     *
     * @param string $content 
     * @return string 
     */
    public function cleanup_incorrect_constants($content) {

        $incorrect_constants = [
            'WP_DEBUG_ENABLED',
        ];
        
        foreach ($incorrect_constants as $incorrect_constant) {
            // Different patterns to match the constant
            $patterns = [
                // Match active define(...) with optional whitespace and either single or double quotes
                'active' => "/^\s*define\s*\(\s*['\"]" . preg_quote($incorrect_constant, '/') . "['\"][\s,]+([^)]+)\s*\)\s*;/m",
                // Match commented versions too
                'commented_double' => "/^\s*\/\/\s*define\s*\(\s*['\"]" . preg_quote($incorrect_constant, '/') . "['\"][\s,]+([^)]+)\s*\)\s*;/m",
                'commented_hash' => "/^\s*#\s*define\s*\(\s*['\"]" . preg_quote($incorrect_constant, '/') . "['\"][\s,]+([^)]+)\s*\)\s*;/m"
            ];
            
            foreach ($patterns as $pattern) {
                $content = preg_replace($pattern, '', $content);
            }
        }
        
        // Find and remove duplicate WP_DEBUG constants (except for the first one of each)
        $standard_constants = [
            'WP_DEBUG',
            'WP_DEBUG_DISPLAY',
            'WP_DEBUG_LOG'
        ];
        
        foreach ($standard_constants as $constant) {

            $patterns = [
                'active' => "/^\s*define\s*\(\s*['\"]" . preg_quote($constant, '/') . "['\"][\s,]+([^)]+)\s*\)\s*;/m",
            ];
            
            preg_match_all($patterns['active'], $content, $matches);
            
            if (count($matches[0]) > 1) {

                $first_occurrence = $matches[0][0];
                
                $content = preg_replace($patterns['active'], '', $content);
                
                $pos = strpos($content, "require_once");
                if ($pos === false) {
                    $pos = strpos($content, "?>");
                }
                
                if ($pos !== false) {
                    $content = substr_replace($content, $first_occurrence . "\n", $pos, 0);
                } else {
                    // If can't find a good position, append to the end
                    $content .= "\n" . $first_occurrence . "\n";
                }
            }
        }
        
        $content = preg_replace("/(\n\s*){2,}/", "\n\n", $content);
        
        return $content;
    }
} 