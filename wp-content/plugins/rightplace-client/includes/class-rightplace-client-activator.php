<?php

/**
 * Fired during plugin activation
 *
 * @link       https://rightplace.app
 * @since      1.0.0
 *
 * @package    Rightplace_Client
 * @subpackage Rightplace_Client/includes
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    Rightplace_Client
 * @subpackage Rightplace_Client/includes
 * @author     WiredWP <ryan@wiredwp.com>
 */
class Rightplace_Client_Activator
{

	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */
	    public static function activate()
    {
        // The RightPlace Folder taxonomy will be registered automatically when the plugin loads
        // No additional setup required for taxonomy-based folders
    }

}
