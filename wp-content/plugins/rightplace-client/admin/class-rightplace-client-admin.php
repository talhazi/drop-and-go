<?php

require_once 'partials/vite-for-wp.php';

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://rightplace.app
 * @since      1.0.0
 *
 * @package    Rightplace_Client
 * @subpackage Rightplace_Client/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Rightplace_Client
 * @subpackage Rightplace_Client/admin
 * @author     WiredWP <ryan@wiredwp.com>
 */
class Rightplace_Client_Admin
{

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct($plugin_name, $version)
	{

		$this->plugin_name = $plugin_name;
		$this->version = $version;

		// Add AJAX handlers
		add_action('wp_ajax_rightplace_revoke_device', array($this, 'handle_revoke_device'));

	}

	/**
	 * Add button next to "Deactivate" on Plugins page
	 */
	public function plugin_add_settings_link($links)
	{
		$settings_link = '<a href="' . admin_url('admin.php?page=rightplace-client-settings') . '">' . __('Settings') . '</a>';

		// Add your settings link to the beginning of the links array
		array_unshift($links, $settings_link);

		return $links;
	}


	/**
	 * Add options page
	 */
	public function add_plugin_page()
	{
		add_submenu_page(
			'tools.php',
			'RightPlace',
			'RightPlace',
			'manage_options',
			'rightplace-client-settings',
			array($this, 'create_admin_page'),
			100
		);
	}

	function create_admin_page()
	{
		if (isset($_GET['reset_plugin']) && $_GET['reset_plugin'] === 'true') {
			delete_option('rightplace_url_salt');
			delete_option(Rightplace_Client::META_KEY);
			die;
		}

		$server_url = RIGHTPLACE_SERVER_URL;

		$health_response = wp_remote_get($server_url . '/v1/health');
		$health_data = null;
		if (!is_wp_error($health_response)) {
			$health_data = json_decode($health_response['body'], true);
		}

		// expose the url salt as javascript variable
		$url_salt = Rightplace_Client::get_url_salt();

		$website_secrets = get_option(Rightplace_Client::META_KEY, array());

		$is_connecting = isset($_GET['connect']) && $_GET['connect'] === 'true';

		$nonce = wp_create_nonce('wp_rest');
		?>
		<script>
			var rightplace_url_salt = '<?php echo $url_salt; ?>';
			var rightplace_website_secrets = <?php echo json_encode($website_secrets); ?>;
			var runRightPlaceConnect = function (successCallback, errorCallback) {
				// remove query string 'connect=true' from url but keeping other query strings
				window.history.replaceState({}, '', window.location.pathname + window.location.search.replace('connect=true', ''));

				const wpJsonUrl = '<?php echo Rightplace_Client_Admin::get_reachable_rest_url(); ?>';
				const urlSalt = '<?php echo Rightplace_Client::get_url_salt(); ?>';
				const nonce = '<?php echo $nonce; ?>';

				if (typeof __rightplaceClient === 'undefined') {
					errorCallback('RightPlace client not found');
					return;
				}

				return __rightplaceClient.connect(wpJsonUrl, urlSalt, nonce)
					.then(function (result) {
						// handle success if needed
						if (successCallback) {
							successCallback(result);
						}
					})
					.catch(function (error) {
						// handle error if needed
						if (errorCallback) {
							errorCallback(error);
						}
					});
			}
		</script>
		<?php

		?>
		<div class="rightplace-client-settings">
			<style>
				.rightplace-client-settings {
					max-width: 800px;
					margin: 20px;
					padding: 20px;
					background: #fff;
					border-radius: 8px;
					box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
				}

				.rightplace-client-settings h1 {
					color: #1d2327;
					margin-bottom: 20px;
					padding-bottom: 10px;
					border-bottom: 2px solid #f0f0f1;
				}

				.rightplace-client-settings h2 {
					color: #1d2327;
					margin: 30px 0 15px;
				}

				.rightplace-client-settings ul {
					list-style: none;
					padding: 0;
					margin: 0;
				}

				.rightplace-client-settings li {
					background: #f6f7f7;
					margin-bottom: 10px;
					padding: 15px;
					border-radius: 4px;
					border-left: 4px solid #2271b1;
				}

				.rightplace-client-settings li strong {
					color: #1d2327;
					display: inline-block;
					min-width: 140px;
				}

				.rightplace-client-settings p {
					margin: 10px 0;
					line-height: 1.5;
				}

				.rightplace-client-settings .device-actions {
					margin-top: 10px;
					text-align: right;
				}

				.rightplace-client-settings .revoke-button {
					background: #dc3232;
					color: white;
					border: none;
					padding: 5px 10px;
					border-radius: 3px;
					cursor: pointer;
					font-size: 12px;
				}

				.rightplace-client-settings .revoke-button:hover {
					background: #b32d2e;
				}

				.rightplace-client-settings .revoke-button:disabled {
					background: #ccc;
					cursor: not-allowed;
				}

				.rightplace-error-message {
					display: none;
					background: #f8d7da;
					border-left: 4px solid #dc3545;
					color: #721c24;
					padding: 15px;
					margin: 20px 0;
					border-radius: 4px;
				}

				.rightplace-error-message h3 {
					margin: 0 0 10px 0;
					color: #dc3545;
					font-size: 16px;
				}

				.rightplace-error-message pre {
					background: rgba(0,0,0,0.05);
					padding: 10px;
					border-radius: 3px;
					margin: 10px 0;
					overflow-x: auto;
					font-size: 12px;
				}

				.rightplace-error-message .error-actions {
					margin-top: 15px;
					text-align: right;
				}

				.rightplace-error-message .error-actions button {
					margin-left: 10px;
				}
			</style>

			<h1>RightPlace</h1>
			<p>Version: <?php echo RIGHTPLACE_CLIENT_VERSION; ?></p>

			<div id="rightplace-error-message" class="rightplace-error-message">
				<h3>Connection Error</h3>
				<p class="error-message"></p>
				<pre class="error-details"></pre>
				<div class="error-actions">
					<button class="button" onclick="document.getElementById('rightplace-error-message').style.display='none'">Dismiss</button>
					<button class="button button-primary" onclick="window.location.reload()">Try Again</button>
				</div>
			</div>

			<?php
			// if query param 'connect=true', call the connect endpoint
			if ($is_connecting) {
				?>
				<p style="margin-top: 20px; display: flex; align-items: center; font-weight: bold; color: #2271b1; font-size: 1.2em;">
					<span class="rightplace-connecting-spinner"
						style="display: inline-block; width: 22px; height: 22px; margin-right: 10px; border: 3px solid #e0e0e0; border-top: 3px solid #2271b1; border-radius: 50%; animation: rightplace-spin 1s linear infinite;"></span>
					Connecting to RightPlace...
				</p>
				<style>
					@keyframes rightplace-spin {
						0% {
							transform: rotate(0deg);
						}

						100% {
							transform: rotate(360deg);
						}
					}
				</style>
				<script>
					function showError(message, details) {
						const errorDiv = document.getElementById('rightplace-error-message');
						errorDiv.querySelector('.error-message').textContent = message;
						errorDiv.querySelector('.error-details').textContent = JSON.stringify(details, null, 2);
						errorDiv.style.display = 'block';

						// Hide the spinner if it exists
						const spinner = document.querySelector('.rightplace-connecting-spinner');
						if (spinner) {
							spinner.style.display = 'none';
						}
					}

					runRightPlaceConnect(function (result) {
						if (result.ok) {
							window.location.href = window.location.pathname + window.location.search.replace('connect=true', '') + '&connect-successful-modal=true';
						} else {
							showError('Failed to connect to RightPlace', result);
						}
					}, function (error) {
						showError('Connection error occurred', error);
					});
				</script>
				<?php
			}
			?>

			<?php if (!$is_connecting) { ?>

				<?php if (!isset($health_data['ok'])) { ?>
					<p>Server is not responding. </p>
				<?php } else { ?>
					<p style="color: green;">Server is responding. (Version: <?php echo $health_data['version']; ?>)</p>
				<?php } ?>

				<?php if (Rightplace_Client_Admin::is_rest_api_active()) { ?>
					<p style="color: green;">REST API is active. (URL: <?php echo Rightplace_Client_Admin::get_reachable_rest_url(); ?>)
					</p>
				<?php } else { ?>
					<p style="color: red;">REST API is not active. Please check if the REST API is enabled in the WordPress settings.</p>
				<?php } ?>




				<h2>Registered Devices</h2>
				<?php
				if (empty($website_secrets)) {
					?>
					<p style="color: #666; font-style: italic;">There is no device registered.</p>
					<?php
				} else {
					?>
					<ul>
						<?php
						foreach ($website_secrets as $device_pub_key => $secret_data) {
							$user = get_user_by('id', $secret_data['user_id']);
							if ($user) {
								?>
								<li>
									<strong>Sign-in User:</strong> <?php echo esc_html($user->display_name); ?>
									(<?php echo esc_html($user->user_email); ?>)<br>
									<strong>Device Public Key:</strong> <?php echo esc_html($device_pub_key); ?><br>
									<div class="device-actions">
										<button class="revoke-button" data-device-key="<?php echo esc_attr($device_pub_key); ?>"
											data-nonce="<?php echo wp_create_nonce('revoke_device_' . $device_pub_key); ?>">
											Revoke Device
										</button>
									</div>
								</li>
								<?php
							}
						}
						?>
					</ul>
					<?php
				}
				?>
			</div>

			<script>
				jQuery(document).ready(function ($) {
					$('.revoke-button').on('click', function (e) {
						e.preventDefault();

						const button = $(this);
						const deviceKey = button.data('device-key');
						const nonce = button.data('nonce');

						if (!confirm('Are you sure you want to revoke this device? This action cannot be undone.')) {
							return;
						}

						button.prop('disabled', true);

						$.ajax({
							url: ajaxurl,
							type: 'POST',
							data: {
								action: 'rightplace_revoke_device',
								device_key: deviceKey,
								nonce: nonce
							},
							success: function (response) {
								if (response.success) {
									button.closest('li').fadeOut(300, function () {
										$(this).remove();
									});
								} else {
									alert('Failed to revoke device: ' + (response.data || 'Unknown error'));
									button.prop('disabled', false);
								}
							},
							error: function () {
								alert('Failed to revoke device. Please try again.');
								button.prop('disabled', false);
							}
						});
					});
				});
			</script>
		<?php } // end if !$is_connecting ?>


		<?php
		$url_salt = Rightplace_Client::get_url_salt();
		?>
		<script>
			if (typeof __rightplaceClient !== 'undefined') {
				__rightplaceClient.updateUrlSalt('<?php echo $url_salt; ?>');
			}
		</script>
		<?php



		// Add success modal
		if (isset($_GET['connect-successful-modal']) && $_GET['connect-successful-modal'] == 'true') {
			?>
			<div id="rightplace-success-modal"
				style="display: flex; align-items: center; justify-content: center; position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.5); z-index: 9999;">
				<div style="background: white; padding: 30px; border-radius: 8px; text-align: center; max-width: 400px;">
					<div style="color: #46b450; font-size: 48px; margin-bottom: 10px;">
						<span class="dashicons dashicons-yes" style="font-size: 60px; width: 60px; height: 60px;"></span>
					</div>
					<h2 style="margin: 0 0 15px 0; color: #1d2327;">Connected to RightPlace!</h2>
					<p style="margin: 0 0 20px 0; color: #50575e; font-size: 16px;">Plugin has been connected successfully.</p>
					<button class="button button-primary" id="rightplace-success-modal-continue" style="min-width: 120px;">Continue</button>
				</div>
			</div>
			<script>
				// Remove the query parameter from URL
				window.history.replaceState({}, '', window.location.pathname + window.location.search.replace('connect-successful-modal=true', ''));

				// Handle continue button click
				jQuery(document).ready(function($) {
					$('#rightplace-success-modal-continue').on('click', function() {
						if (typeof __rightplaceClient !== 'undefined') {
							__rightplaceClient.closeConnectionBrowser();
						} else {
							$('#rightplace-success-modal').fadeOut(200, function() {
								$(this).remove();
							});
						}
					});
				});
			</script>
			<?php
		}

	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * https://github.com/kucrut/vite-for-wp
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts()
	{
		// Only load Vite assets if the assets directory exists
		$assets_dir = __DIR__ . '/assets';
		if (is_dir($assets_dir)) {
			\Kucrut\Vite\enqueue_asset(
				$assets_dir,
				'main.js',
				[
					'handle' => $this->plugin_name,
					'dependencies' => ['jquery',], // Optional script dependencies. Defaults to empty array.
					'css-media' => 'all', // Optional.
				]
			);
		}
	}

	static function is_rest_api_active()
	{

		// 1) First, check if REST infrastructure (functions/classes) is loaded
		if (!function_exists('rest_url') || !class_exists('WP_REST_Server')) {
			return false;
		}

		// 2) Respect the core's internal filter value as is
		if (!apply_filters('rest_enabled', true)) {
			return false;          // e.g., when a security plugin disables it
		}

		// 3) Check if the server object is properly created
		$server = rest_get_server();               // WP_REST_Server|null
		if (empty($server)) {
			return false;
		}

		// 4) Check if at least the index route ("/") is registered
		$routes = $server->get_routes();
		return isset($routes['/']);
	}

	/**
	 * Returns a reachable REST API root URL.
	 * - Tries https first → if it fails, tries http
	 * - Returns false if all attempts fail
	 *
	 * @param int|null $blog_id Blog ID for multisite. (Default: current site)
	 * @param int      $timeout Timeout per request (seconds)
	 * @return string|false
	 */
	function get_reachable_rest_url($blog_id = null)
	{
		return get_rest_url($blog_id, '/');
	}


	/**
	 * Handle device revocation via AJAX
	 */
	public function handle_revoke_device()
	{
		// Check nonce
		if (!isset($_POST['nonce']) || !isset($_POST['device_key'])) {
			wp_send_json_error('Invalid request');
			return;
		}

		$device_key = sanitize_text_field($_POST['device_key']);
		if (!wp_verify_nonce($_POST['nonce'], 'revoke_device_' . $device_key)) {
			wp_send_json_error('Invalid nonce');
			return;
		}

		// Check user capabilities
		if (!current_user_can('manage_options')) {
			wp_send_json_error('Insufficient permissions');
			return;
		}

		// Get current secrets
		$website_secrets = get_option(Rightplace_Client::META_KEY, array());

		// Remove the device
		if (isset($website_secrets[$device_key])) {
			unset($website_secrets[$device_key]);
			update_option(Rightplace_Client::META_KEY, $website_secrets);
			wp_send_json_success('Device revoked successfully');
		} else {
			wp_send_json_error('Device not found');
		}
	}
}
