<?php

/**
 * Fired during plugin deactivation
 *
 * @link       https://rightplace.app
 * @since      1.0.0
 *
 * @package    Rightplace_Client
 * @subpackage Rightplace_Client/includes
 */

/**
 * Fired during plugin deactivation.
 *
 * This class defines all code necessary to run during the plugin's deactivation.
 *
 * @since      1.0.0
 * @package    Rightplace_Client
 * @subpackage Rightplace_Client/includes
 * @author     WiredWP <ryan@wiredwp.com>
 */
class Rightplace_Client_Deactivator {

	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */
	public static function deactivate() {
		// Note: We don't drop tables on deactivation, only on uninstall
		// This preserves data if the plugin is temporarily deactivated
	}

}
