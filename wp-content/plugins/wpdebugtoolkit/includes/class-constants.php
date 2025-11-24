<?php

namespace DebugToolkit;

/**
 * Plugin Constants
 */
class Constants {
    /**
     * Plugin version
     */
    const DBTK_VERSION = '1.0.0';

    /**
     * Plugin path
     */
    const DBTK_PATH = DBTK_DEBUG_TOOLKIT_PATH;

    /**
     * Plugin URL
     */
    const DBTK_URL = DBTK_DEBUG_TOOLKIT_URL;

    /**
     * Plugin basename
     */
    const DBTK_BASENAME = DBTK_DEBUG_TOOLKIT_BASENAME;

    /**
     * Plugin file
     */
    const DBTK_FILE = DBTK_DEBUG_TOOLKIT_FILE;

    /**
     * Templates directory
     */
    const DBTK_TEMPLATES_DIR = self::DBTK_PATH . 'templates/';

    /**
     * Debug log default path
     */
    const DBTK_DEFAULT_LOG_PATH = WP_CONTENT_DIR . '/debug.log';

    /**
     * Viewer directory name
     */
    const DBTK_VIEWER_DIR = 'wpdebugtoolkit';

    /**
     * Cache keys
     */
    const DBTK_CACHE_KEYS = [
        'HEALTH_CHECK' => 'debug_toolkit_health_check',
    ];

    /**
     * Cache durations (in seconds)
     */
    const DBTK_CACHE_DURATIONS = [
        'HEALTH_CHECK' => 300, 
    ];

    /**
     * Option names
     */
    const DBTK_OPTIONS = [
        'LICENSE_KEY' => 'debug_toolkit_license_key',
    ];

    /**
     * REST API namespace
     */
    const DBTK_REST_NAMESPACE = 'wpdebugtoolkit/v1';

    /**
     * EDD Item ID for licensing
     */
    const DBTK_ITEM_ID = 378; 

    /**
     * EDD Store URL
     */
    const DBTK_STORE_URL = 'https://wpdebugtoolkit.com';

    /**
     * File permissions
     */
    const DBTK_FILE_PERMISSIONS = [
        'DIRECTORY' => 0755,
        'FILE' => 0644,
    ];
} 