<?php

/**
 * Fired when the plugin is uninstalled.

 * @link       https://rightplace.app
 * @since      1.0.0
 *
 * @package    Rightplace_Client
 * @subpackage Rightplace_Client/includes
 */

// If uninstall not called from WordPress, then exit.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

// Include the database table managers for cleanup
require_once plugin_dir_path(__FILE__) . 'includes/db/class-custom-table-manager.php';

// Clean up any custom tables if needed
// Note: Taxonomy terms are automatically cleaned up by WordPress when the plugin is uninstalled
