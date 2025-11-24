<?php

/**
 * @link              https://rightplace.app
 * @since             0.11.1-beta
 * @package           Rightplace_Client
 *
 * @wordpress-plugin
 * Plugin Name:       RightPlace Client
 * Plugin URI:        https://rightplace.app
 * Description:       The official Client plugin to connect the website to your RightPlace application.
 * Version:           0.11.1-beta
 * Requires at least: 5.9
 * Requires PHP:      7.3
 * Author:            Ryan Lee
 * Author URI:        https://rightplace.app/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       rightplace-client
 * Domain Path:       /languages
 * 
 * 
 *  
 */

require_once __DIR__ . '/inc/lib/autoload.php';

// If this file is called directly, abort.
if (!defined('WPINC')) {
	die;
}

/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define('RIGHTPLACE_CLIENT_VERSION', '0.11.1-beta');
define('RIGHTPLACE_ASSET_UUID', 'rightplace_asset_uuid');
define('RIGHTPLACE_TEMPLATE_UUID', 'rp_template_uuid');
define('RIGHTPLACE_CLIENT_FILE', __FILE__);
define('RIGHTPLACE_CLIENT_DEPLOYMENT', 'prod');
define('RIGHTPLACE_SERVER_URL', 'https://api.rightplace.app');
define('RIGHTPLACE_ASSET_URL', 'https://appcdn.rightplace.app');
define('RIGHTPLACE_JWKS_TTL', 60 * 60 * 24 * 7);
define('RIGHTPLACE_BASE_PLUGIN', '0.1');

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-rightplace-client-activator.php
 */
function activate_rightplace_client()
{
	rp_dev_log('activate_rightplace_client ------------------');
	// Set a flag to redirect after activation
	update_option('rightplace_client_do_activation_redirect', true);
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-rightplace-client-deactivator.php
 */
function deactivate_rightplace_client()
{
}

register_activation_hook(__FILE__, 'activate_rightplace_client');
register_deactivation_hook(__FILE__, 'deactivate_rightplace_client');

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path(__FILE__) . 'includes/utilities.php';
require plugin_dir_path(__FILE__) . 'includes/class-rightplace-client.php';


/**
 * Begins execution of the plugin.
 *
 * @since    1.0.0
 */
function run_rightplace_client()
{

	$plugin = new Rightplace_Client();
	$plugin->run();

}
run_rightplace_client();
