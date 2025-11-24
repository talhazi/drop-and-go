<?php
/**
 * Plugin Name: WP Debug Toolkit Pro
 * Plugin URI: https://wpdebugtoolkit.com
 * Description: Advanced debugging toolkit for WordPress with log viewer and error management.
 * Version: 1.0.0
 * Author: WP Debug Toolkit
 * Author URI: https://wpdebugtoolkit.com
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: wpdebugtoolkit
 */

namespace DebugToolkit;

if (!defined('WPINC')) {
    die;
}

define('DBTK_DEBUG_TOOLKIT_FILE', __FILE__);
define('DBTK_DEBUG_TOOLKIT_PATH', plugin_dir_path(__FILE__));
define('DBTK_DEBUG_TOOLKIT_URL', plugin_dir_url(__FILE__));
define('DBTK_DEBUG_TOOLKIT_BASENAME', plugin_basename(__FILE__));

require_once DBTK_DEBUG_TOOLKIT_PATH . 'includes/class-error-handler.php';

spl_autoload_register(function ($class) {

    if (strpos($class, __NAMESPACE__) !== 0) {
        return;
    }

    $relative_class = substr($class, strlen(__NAMESPACE__) + 1);
    
    if (strpos($relative_class, 'Traits\\') === 0) {
        $path = 'includes/traits/trait-';
        $relative_class = substr($relative_class, strlen('Traits\\'));
    } elseif (strpos($relative_class, 'Services\\') === 0) {
        $path = 'includes/services/class-';
        $relative_class = substr($relative_class, strlen('Services\\'));
    } else {
        $path = 'includes/class-';
    }
    
    $file_name = strtolower(str_replace('_', '-', $relative_class));
    
    $file = DBTK_DEBUG_TOOLKIT_PATH . $path . $file_name . '.php';
    
    if (file_exists($file)) {
        require_once $file;
    }
});

function load_textdomain() {
    load_plugin_textdomain(
        'wpdebugtoolkit',
        false,
        dirname(DBTK_DEBUG_TOOLKIT_BASENAME) . '/languages/'
    );
}

function init() {
    if (defined('REST_REQUEST') && REST_REQUEST) {
        error_reporting(0);
        ini_set('display_errors', 0);
    }

    Core::get_instance();

    if (is_admin()) {
        Admin::get_instance();
    }
}

add_action('init', __NAMESPACE__ . '\\load_textdomain', 0);
add_action('init', __NAMESPACE__ . '\\init', 1);

register_activation_hook(DBTK_DEBUG_TOOLKIT_FILE, function() {
    Activator::activate();
});

register_deactivation_hook(DBTK_DEBUG_TOOLKIT_FILE, function() {
    Deactivator::deactivate();
});

register_uninstall_hook(DBTK_DEBUG_TOOLKIT_FILE, [__NAMESPACE__ . '\\Uninstaller', 'uninstall']); 