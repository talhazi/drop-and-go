<?php

/**
 * Check for CGI/FastCGI env
 *
 * @return bool
 */
function is_cgi_environment() {
    $sapi = PHP_SAPI;
    
    return (
        $sapi === 'cgi' ||
        $sapi === 'cgi-fcgi' ||
        strpos($sapi, 'fpm') !== false ||
        (isset($_SERVER['GATEWAY_INTERFACE']) && stripos($_SERVER['GATEWAY_INTERFACE'], 'CGI') !== false) ||
        (isset($_SERVER['SERVER_SOFTWARE']) && stripos($_SERVER['SERVER_SOFTWARE'], 'CGI') !== false)
    );
}

/**
 * Clear cache from plugins that cache WordPress options
 * This helps prevent cached license responses and ensures fresh data retrieval
 */
function clear_caching_plugins_cache() {
    // WordPress built-in object cache - clear specific options
    if (function_exists('wp_cache_delete')) {
        wp_cache_delete('dbtk_product_activation', 'options');
        wp_cache_delete('debug_toolkit_license_status', 'options');
        wp_cache_delete('debug_toolkit_license_last_check', 'options');
    }

    // Redis Object Cache - flush options group
    if (function_exists('wp_cache_flush_group')) {
        wp_cache_flush_group('options');
    }

    // Object Cache Pro - flush options group if available
    if (function_exists('wp_cache_flush')) {
        // Only flush if we can target specific groups, otherwise flush all object cache
        wp_cache_flush();
    }

    // W3 Total Cache - clear database cache only (not page cache)
    if (function_exists('w3tc_dbcache_flush')) {
        w3tc_dbcache_flush();
    } elseif (class_exists('W3_Plugin_DbCache') && method_exists('W3_Plugin_DbCache', 'flush')) {
        w3_instance('W3_Plugin_DbCache')->flush();
    }

    // LiteSpeed Cache - clear object cache only
   if (function_exists('litespeed_purge_all')) {
        litespeed_purge_all();
    } 
   
   if (class_exists('LiteSpeed\Purge')) {
        do_action('litespeed_purge_all');
    } 
   
   if (class_exists('LiteSpeed_Cache_API')) {
        \LiteSpeed_Cache_API::purge_all();
    }

    // WP Rocket - clear database cache if available
    if (function_exists('rocket_clean_cache_busting')) {
        // This is more targeted than full cache clear
        rocket_clean_cache_busting();
    }
}