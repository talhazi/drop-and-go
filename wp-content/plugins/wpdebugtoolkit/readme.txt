=== WP Debug Toolkit Pro ===
Contributors: wpdebugtoolkit
Tags: debug, debugging, development, error logging, log viewer, error monitor, debug logs, wp-config, error handling, php errors
Requires at least: 5.6
Tested up to: 6.4
Requires PHP: 7.4
Stable tag: 1.0.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Advanced debugging toolkit for WordPress that enhances the development experience with a beautiful log viewer and powerful error management tools.

== Description ==

**WP Debug Toolkit Pro** is an essential tool for WordPress developers, providing a comprehensive solution for debugging, error logging, and monitoring your WordPress site.

Instead of digging through error logs or adding debug code to track down issues, WP Debug Toolkit gives you a professional, easy-to-use interface that makes debugging a breeze.

[youtube https://www.youtube.com/watch?v=gMUONYtH3wY]

### Key Features

* **Standalone Log Viewer** - A beautiful, modern log viewer that runs outside WordPress for viewing logs even when WordPress crashes
* **Smart Error Detection** - Automatically identifies error types, locations, and provides context for faster debugging
* **One-Click Debug Mode** - Enable/disable WordPress debugging features without editing wp-config.php
* **Secure Architecture** - Built with security in mind, with proper validation and sanitization throughout
* **Plugin & Theme Management** - Quickly disable plugins or themes when troubleshooting conflicts
* **Keyboard Shortcuts** - Power-user features to help you work more efficiently
* **Real-time Log Updates** - Watch logs update in real-time as errors occur
* **Advanced Filtering** - Filter logs by error level, time, or source to find exactly what you need
* **Site Health Integration** - Enhanced site health checks to identify potential issues
* **Modern React UI** - Clean, intuitive interface built with React and Tailwind CSS

### Perfect For

* WordPress developers working on their own or client sites
* Agency owners needing to diagnose site issues quickly
* Freelancers maintaining multiple WordPress installations 
* Site owners who want to understand what's happening behind the scenes



== Installation ==

1. Upload the `wpdebugtoolkit` folder to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Access WP Debug Toolkit from the WordPress admin menu and activate the license key
4. Install the standalone viewer with one click from the plugin's interface
5. Start using the powerful debugging tools!

== Frequently Asked Questions ==

= Is this plugin free? =

There is a free version planned, but at the moment there is only a Premium (paid) licensed version.

= Will this plugin slow down my site? =

No. WP Debug Toolkit is designed to have minimal impact on your site's performance. The debugging features are only activated when you enable them, and the standalone viewer runs independently of WordPress.

= Is it safe to use on a production site? =

Yes! WP Debug Toolkit is built with security as a priority. That said, we recommend only enabling debug mode on production sites temporarily when needed, as debug logging can expose sensitive information. You can also enable password protection on the viewer at any time.

= How does the standalone viewer work? =

The standalone viewer is installed in your site's root directory, separate from WordPress and without any WordPress dependency. This allows it to function even if WordPress crashes or is experiencing errors that prevent admin access.

= Can I use this on a local development environment? =

Absolutely! WP Debug Toolkit is designed to work seamlessly in local development environments like XAMPP, Local by Flywheel, DevKinsta, Docker etc.

= Does this plugin modify my wp-config.php file? =

Yes, when you enable or disable debugging features, WP Debug Toolkit safely modifies your wp-config.php file to add the necessary constants. It creates a backup before making any changes.


== Changelog ==

= 1.0.0 =
* Initial release

== Upgrade Notice ==

= 1.0.0 =
Initial release of WP Debug Toolkit Pro. Enjoy enhanced debugging capabilities for your WordPress site!

== Additional Resources ==

For more information, documentation and support, visit [https://wpdebugtoolkit.com](https://wpdebugtoolkit.com) 