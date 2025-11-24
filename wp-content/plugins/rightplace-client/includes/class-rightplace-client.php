<?php
use RightPlace\Vendor\Firebase\JWT\JWT;
use RightPlace\Vendor\Firebase\JWT\Key;

/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       https://rightplace.app
 * @since      1.0.0
 *
 * @package    Rightplace_Client
 * @subpackage Rightplace_Client/includes
 */

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @package    Rightplace_Client
 * @subpackage Rightplace_Client/includes
 * @author     WiredWP <ryan@wiredwp.com>
 */
class Rightplace_Client
{

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      Rightplace_Client_Loader    $loader    Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

	protected $access;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $plugin_name    The string used to uniquely identify this plugin.
	 */
	protected $plugin_name;

	/**
	 * The current version of the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $version    The current version of the plugin.
	 */
	protected $version;

	private $allowed_api_namespaces = array(
		'rightplace/v1',
	);

	private const NONCE_PREFIX = 'rp_nonce_';
	public const META_KEY = 'rightplace_user_secrets';


	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function __construct()
	{
		if (defined('RIGHTPLACE_CLIENT_VERSION')) {
			$this->version = RIGHTPLACE_CLIENT_VERSION;
		} else {
			$this->version = '1.0.0';
		}
		$this->plugin_name = 'rightplace-client';

		$this->load_dependencies();
		$this->set_locale();
		$this->define_admin_hooks();

	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Include the following files that make up the plugin:
	 *
	 * - Rightplace_Client_Loader. Orchestrates the hooks of the plugin.
	 * - Rightplace_Client_i18n. Defines internationalization functionality.
	 * - Rightplace_Client_Admin. Defines all hooks for the admin area.
	 *
	 * Create an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function load_dependencies()
	{

		/**
		 * The class responsible for orchestrating the actions and filters of the
		 * core plugin.
		 */
		require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-rightplace-client-loader.php';

		/**
		 * The class responsible for defining internationalization functionality
		 * of the plugin.
		 */
		require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-rightplace-client-i18n.php';

		/**
		 * The class responsible for defining all actions that occur in the admin area.
		 */
		require_once plugin_dir_path(dirname(__FILE__)) . 'admin/class-rightplace-client-admin.php';

		/**
		 * The class responsible for defining the custom admin bar button.
		 */
		require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-open-in-right-place.php';

		/**
		 * The class responsible for handling plugin uploads.
		 */

		/**
		 * The class responsible for handling plugin uploads.
		 */
		require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-plugins-manager.php';

		/**
		 * The class responsible for handling media uploads.
		 */
		require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-media-manager.php';

		/**
		 * The class responsible for handling token-based authentication.
		 */
		require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-login-token-service.php';
		$token_service = new \RightPlace\LoginTokenService();
		$token_service->init();

		$this->loader = new Rightplace_Client_Loader();

		/**
		 * The class responsible for handling file sync
		 */
		require_once plugin_dir_path(dirname(__FILE__)) . 'includes/features/class-feature-file-sync.php';

		/**
		 * The class responsible for handling posts
		 */
		require_once plugin_dir_path(dirname(__FILE__)) . 'includes/features/class-feature-posts.php';

		/**
		 * The class responsible for handling builder
		 */
		require_once plugin_dir_path(dirname(__FILE__)) . 'includes/features/builder/class-feature-builder.php';

		/**
		 * The class responsible for plugins features
		 */
		require_once plugin_dir_path(dirname(__FILE__)) . 'includes/features/class-feature-plugins.php';

		/**
		 * The class responsible for media sync
		 */

		/**
		 * The class responsible for code run
		 */
		require_once plugin_dir_path(dirname(__FILE__)) . 'includes/features/class-feature-coderun.php';

		/**
		 * The class responsible for plugin builder
		 */
		require_once plugin_dir_path(dirname(__FILE__)) . 'includes/features/class-feature-plugin-builder.php';


		/**
		 * The class responsible for custom table management
		 */
		require_once plugin_dir_path(dirname(__FILE__)) . 'includes/db/class-custom-table-manager.php';

		// Media folders are now handled by WordPress taxonomy (rightplace_folder)
		// No separate table manager needed

		/**
		 * The class responsible for media sync
		 */
		require_once plugin_dir_path(dirname(__FILE__)) . 'includes/features/class-feature-media-sync.php';

		/**
		 * The class responsible for collaboration features
		 */
		require_once plugin_dir_path(dirname(__FILE__)) . 'includes/features/class-feature-collaboration.php';

		/**
		 * The class responsible for logs
		 */
		require_once plugin_dir_path(dirname(__FILE__)) . 'includes/features/class-feature-logs.php';




		require_once plugin_dir_path(dirname(__FILE__)) . 'includes/features/class-feature-themes.php';

		/**
		 * The class responsible for handling users
		 */
		require_once plugin_dir_path(dirname(__FILE__)) . 'includes/features/class-feature-users.php';

		/**
		 * The class responsible for handling data crawler
		 */
		require_once plugin_dir_path(dirname(__FILE__)) . 'includes/features/class-feature-custom-field.php';

		/**
		 * The class responsible for handling plugin code validator
		 */
		require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-plugin-code-validator.php';
	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the Rightplace_Client_i18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function set_locale()
	{

		$plugin_i18n = new Rightplace_Client_i18n();

		$this->loader->add_action('init', $plugin_i18n, 'load_plugin_textdomain');
	}

	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_admin_hooks()
	{
		$plugin_admin = new Rightplace_Client_Admin($this->get_plugin_name(), $this->get_version());
		$this->loader->add_filter('plugin_action_links_rightplace-client/rightplace-client.php', $plugin_admin, 'plugin_add_settings_link', 10, 1);
		$this->loader->add_action('admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts');
		$this->loader->add_action('admin_menu', $plugin_admin, 'add_plugin_page', 10);
		$this->loader->add_filter('rest_authentication_errors', $this, 'conditional_nonce_bypass', 0);
		$this->loader->add_filter('pre_set_site_transient_update_plugins', $this, 'rightplace_client_check_for_updates', 0);
		$this->loader->add_filter('plugins_api', $this, 'rightplace_client_plugin_info', 10, 3);

		// Add REST API registration
		$this->loader->add_action('rest_api_init', $this, 'register_rest_endpoints');

		// Add filter to hide RightPlace endpoints from REST API index
		$this->loader->add_filter('rest_index', $this, 'hide_rightplace_endpoints');

		// Add header using wp_headers filter
		$this->loader->add_action('wp_logout', $this, 'remove_rightplace_keys_on_logout', 10, 1);
		$this->loader->add_action('deactivated_plugin', $this, 'after_deactivate_plugin', 10, 1);
		$this->loader->add_action('plugins_loaded', $this, 'open_in_right_place_init', 10);
		$this->loader->add_action('admin_init', $this, 'redirect_to_settings_after_activation', 10);
	}

	function redirect_to_settings_after_activation()
	{
		rp_dev_log('rightplace_client_do_activation_redirect' . get_option('rightplace_client_do_activation_redirect'));

		if (get_option('rightplace_client_do_activation_redirect', false)) {
			
			delete_option('rightplace_client_do_activation_redirect');
			
			// Only redirect if not doing network activation (multisite)
			if (!isset($_GET['activate-multi'])) {
				wp_safe_redirect(admin_url('tools.php?page=rightplace-client-settings'));
				exit;
			}
		}
	}

	public function open_in_right_place_init()
	{
		// new \RightPlace\Class_Open_In_Right_Place();
	}

	/**
	 * Register REST API endpoints for the plugin
	 *
	 * @since    1.0.0
	 * @access   public
	 */
	public function register_rest_endpoints()
	{
		$url_salt = $this->get_url_salt();

		register_rest_route('rightplace/v1', '/' . $url_salt . '/check-plugin-info', array(
			'methods' => 'POST',
			'callback' => array($this, 'handle_check_plugin_info_callback'),
			'permission_callback' => '__return_true',
		));


		register_rest_route('rightplace/v1', '/' . $url_salt . '/core-api', array(
			'methods' => 'POST',
			'callback' => array($this, 'handle_core_api_callback'),
			'permission_callback' => function ($request) {
				return $this->verify_rightplace_key($request);
			},
		));

		register_rest_route('rightplace/v1', '/' . $url_salt . '/core-api/secure', array(
			'methods' => 'POST',
			'callback' => array($this, 'handle_core_api_callback'),
			'permission_callback' => function ($request) {
				return $this->verify_rightplace_key($request) &&
					is_user_logged_in() &&
					current_user_can('administrator');
			},
		));

		register_rest_route('rightplace/v1', '/' . $url_salt . '/secret', array(
			'methods' => 'POST',
			'callback' => array($this, 'handle_secret_callback'),
			'permission_callback' => function ($request) {
				return is_user_logged_in() &&
					current_user_can('administrator');
			},
		));

		// Fall back from the POST method
		register_rest_route('rightplace/v1', '/' . $url_salt . '/secret', array(
			'methods' => 'PUT',
			'callback' => array($this, 'handle_secret_callback'),
			'permission_callback' => function ($request) {
				return is_user_logged_in() &&
					current_user_can('administrator');
			},
		));

		register_rest_route('rightplace/v1', '/' . $url_salt . '/health', array(
			'methods' => 'POST',
			'callback' => array($this, 'handle_health_check_callback'),
			'permission_callback' => function ($request) {
				return $this->verify_rightplace_key($request);
			},
		));

		register_rest_route('rightplace/v1', '/' . $url_salt . '/disconnect', array(
			'methods' => 'POST',
			'callback' => array($this, 'handle_disconnect_callback'),
			'permission_callback' => function ($request) {
				return $this->verify_rightplace_key($request);
			},
		));

		register_rest_route('rightplace/v1', '/' . $url_salt . '/auto-login', array(
			'methods' => 'POST',
			'callback' => array($this, 'handle_auto_login_callback'),
			'permission_callback' => function ($request) {
				return $this->verify_rightplace_key($request);
			},
		));
	}

	public function handle_auto_login_callback($request)
	{
		$jwt_header = $request->get_header('Authorization');
		$jwt = explode('Bearer ', $jwt_header)[1];

		$jwks = $this->get_jwks();

		$payload = JWT::decode($jwt, new Key($jwks['keys'][0]['x'], 'EdDSA'));

		// check expiration
		if ($payload->exp < time()) {
			return self::handle_rest_response(array(
				'success' => false,
				'message' => 'Token expired',
			));
		}

		$audience = $payload->aud;

		if ($audience !== get_site_url()) {
			return self::handle_rest_response(array(
				'success' => false,
				'message' => 'Invalid audience',
			));
		}
		
		$device_pub_key = $payload->sub;

		$website_secrets = get_option(self::META_KEY, array());

		if (!isset($website_secrets[$device_pub_key])) {
			return self::handle_rest_response(array(
				'success' => false,
				'message' => 'Device public key not found in website secrets',
			));
		}

		$user_id = $website_secrets[$device_pub_key]['user_id'];

		if (!$user_id) {
			return self::handle_rest_response(array(
				'success' => false,
				'message' => 'User ID not found in website secrets',
			));
		}

		// login the user
		$remember_me = true;
		$secure = false;

		wp_set_auth_cookie($user_id, $remember_me, $secure);
		wp_set_current_user($user_id);

		return self::handle_rest_response(array(
			'success' => true,
			'message' => 'Auto login successful',
			'user_id' => $user_id,
		));
	}

	public function handle_check_plugin_info_callback()
	{
		return self::handle_rest_response(array(
			'success' => true,
			'rpPluginVersion' => $this->version,
			'pluginVersion' => $this->version, // @since 0.3.1
		));
	}

	static public function get_url_salt()
	{
		$url_salt = get_option('rightplace_url_salt');
		if (!$url_salt) {
			$url_salt = wp_generate_password(16, false, false);
			update_option('rightplace_url_salt', $url_salt);
		}
		return $url_salt;
	}

	public function handle_disconnect_callback($request)
	{
		$device_pub_key = $request->get_header('x-rp-device-pub-key');

		$website_secrets = get_option(self::META_KEY, array());
		if (empty($website_secrets)) {
			return self::handle_rest_response(array(
				'ok' => false,
				'message' => 'No website secrets found'
			));
		}

		if (!isset($website_secrets[$device_pub_key])) {
			return self::handle_rest_response(array(
				'ok' => false,
				'message' => 'No secret found for device pub key: ' . $device_pub_key
			));
		}

		unset($website_secrets[$device_pub_key]);
		update_option(self::META_KEY, $website_secrets);

		return self::handle_rest_response(array(
			'ok' => true,
			'message' => 'Device disconnected successfully',
		));
	}


	/**
	 * Conditional nonce bypass for RightPlace API
	 * 
	 * This function is used to bypass the nonce check for the RightPlace API.
	 * 
	 * @param array $errors Authentication errors
	 * @return array Modified authentication errors
	 */
	function conditional_nonce_bypass($errors)
	{
		// Check the requested route. For example:
		$request_uri = $_SERVER['REQUEST_URI'];

		foreach ($this->allowed_api_namespaces as $namespace) {
			if (strpos($request_uri, $namespace) !== false) {
				return true;
			}
		}

		return $errors;
	}

	
	private function get_jwks() : array {
		$cached = get_transient( 'rp_jwks_cache' );
		if ( $cached ) {
			return $cached;
		}
	
		$resp = wp_remote_get( RIGHTPLACE_SERVER_URL . '/.well-known/jwks.json', [ 'timeout' => 5 ] );
		if ( is_wp_error( $resp ) || wp_remote_retrieve_response_code( $resp ) !== 200 ) {
			error_log( '[RightPlace] JWKS fetch failed: ' . wp_remote_retrieve_response_message( $resp ) );
			return [];
		}
	
		$body = wp_remote_retrieve_body( $resp );
		$json = json_decode( $body, true );
		if ( ! isset( $json['keys'] ) ) {
			return [];
		}
	
		set_transient( 'rp_jwks_cache', $json, RIGHTPLACE_JWKS_TTL );
		return $json;
	}


	private function verify_rightplace_key($request)
	{
		$start_time = microtime(true);

		$device_pub_key = $request->get_header('x-rp-device-pub-key');

		/* 1. store / update pubkey in user-meta ----------------------------- */
		$website_secrets = get_option(self::META_KEY, array());
		if (empty($website_secrets)) {
			$website_secrets = array();
		}

		if (!isset($website_secrets[$device_pub_key])) {
			rp_dev_log('Verification took ' . round((microtime(true) - $start_time) * 1000, 2) . 'ms - Key not found');
			return new WP_Error(
				'verification_failed',
				'Device public key not found in website secrets',
				array('step' => 'key_lookup', 'status' => 401)
			);
		}

		$token = $website_secrets[$device_pub_key]['pubJwt'];

		rp_dev_log('Token: ' . $token);

		try {
			$jwks = $this->get_jwks();
			$key = new Key($jwks['keys'][0]['x'], 'EdDSA');
			$payload = JWT::decode($token, $key);
		} catch (\Throwable $e) {
			rp_dev_log('Token invalid ------->' . $e->getMessage());
			rp_dev_log('Verification took ' . round((microtime(true) - $start_time) * 1000, 2) . 'ms - Token invalid');
			return new WP_Error(
				'verification_failed',
				'Invalid JWT token: ' . $e->getMessage(),
				array('step' => 'token_validation', 'status' => 401)
			);
		}

		$clientPubB64 = $payload->pub;
		$clientPubRaw = base64_decode($clientPubB64);

		/* 4. replay defence -------------------------------------------------- */
		$ts = $request->get_header('x-rightplace-ts') ?? '';
		$nonce = $request->get_header('x-rightplace-nonce') ?? '';
		if (abs(time() - intval($ts)) > 300) {
			rp_dev_log('Clock skew');
			rp_dev_log('Verification took ' . round((microtime(true) - $start_time) * 1000, 2) . 'ms - Clock skew');
			return new WP_Error(
				'verification_failed',
				'Clock skew detected - timestamp difference too large',
				array('step' => 'timestamp_validation', 'status' => 401)
			);
		}
		if (wp_cache_get($nonce, self::NONCE_PREFIX)) {
			rp_dev_log('Nonce used');
			rp_dev_log('Verification took ' . round((microtime(true) - $start_time) * 1000, 2) . 'ms - Nonce used');
			return new WP_Error(
				'verification_failed',
				'Nonce already used - possible replay attack',
				array('step' => 'nonce_validation', 'status' => 401)
			);
		}
		wp_cache_set($nonce, 1, self::NONCE_PREFIX, 600);

		/* 5. signature check ------------------------------------------------- */
		$method = $_SERVER['REQUEST_METHOD'];
		$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
		$host = $_SERVER['HTTP_HOST'];
		// Get the full URI including query parameters
		$uri = $_SERVER['REQUEST_URI']; // Changed from strtok($_SERVER['REQUEST_URI'], '?')
		$full_uri = $protocol . '://' . $host . $uri;
		$body = file_get_contents('php://input');
		$canon = sprintf(
			"%s\n%s\n%s\n%s\n%s",
			$method,
			$full_uri,
			$ts,
			$nonce,
			hash('sha256', $body)
		);

		$sig = base64_decode($request->get_header('x-rightplace-sig') ?? '', true);

		rp_dev_log('Canon: ' . $canon);
		rp_dev_log('Sig: ' . base64_encode($sig));
		rp_dev_log('Client Pub Raw: ' . base64_encode($clientPubRaw));

		if (!$sig || !sodium_crypto_sign_verify_detached($sig, $canon, $clientPubRaw)) {
			rp_dev_log('Bad signature');
			rp_dev_log('Verification took ' . round((microtime(true) - $start_time) * 1000, 2) . 'ms - Bad signature');
			return new WP_Error(
				'verification_failed',
				'Invalid signature verification',
				array(
					'step' => 'signature_validation',
					'status' => 401
				)
			);
		}

		/* 6. success --------------------------------------------------------- */
		rp_dev_log('Verification took ' . round((microtime(true) - $start_time) * 1000, 2) . 'ms - Success');
		return true;
	}

	public static function handle_rest_response($response)
	{
		return rest_ensure_response($response);
	}


	function handle_secret_callback($request)
	{

		try {
			// Turn off warnings
			// error_reporting(0);

			$request_key = $request->get_header('x-rightplace-request-key');
			$device_signature = $request->get_header('x-device-signature');

			if (!$request_key || !$device_signature) {
				return self::handle_rest_response(array(
					'ok' => false,
					'message' => 'Missing request key or device signature'
				));
			}

			$response = wp_remote_get(RIGHTPLACE_SERVER_URL . '/v1/auth/existing-secret', array(
				'headers' => array(
					'x-device-signature' => $device_signature,
					'x-rightplace-request-key' => $request_key,
				),
			));

			if (is_wp_error($response)) {
				return self::handle_rest_response(array(
					'ok' => false,
					'message' => 'Failed to get secret from server'
				));
			}

			$response_body = json_decode($response['body'], true);

			if (!isset($response_body['pubJwt']) || !isset($response_body['devicePubKey'])) {
				return self::handle_rest_response(array(
					'ok' => false,
					'message' => 'Invalid secret response'
				));
			}

			$pubJwt = $response_body['pubJwt'];
			$device_pub_key = $response_body['devicePubKey'];

			// store the pubJwt in the user meta
			$user_id = get_current_user_id();
			$website_secrets = get_option(self::META_KEY, array());

			$website_secrets[$device_pub_key] = array(
				'pubJwt' => $pubJwt,
				'devicePubKey' => $device_pub_key,
				'user_id' => $user_id,
			);

			update_option(self::META_KEY, $website_secrets);

			return self::handle_rest_response(array(
				'ok' => true,
				'message' => 'Secret stored successfully',
			));
		} catch (\Throwable $e) {
			error_log('Error storing secret', $e->getMessage());
			return self::handle_rest_response(array(
				'ok' => false,
				'message' => 'Error storing secret',
			));
		}
	}


	/**
	 * Remove RightPlace keys when plugin is deactivated
	 *
	 * @param string $plugin The plugin being deactivated
	 */
	public function after_deactivate_plugin($plugin)
	{
		if ($plugin === 'rightplace-client/rightplace-client.php') {
			// NOTE: not tested yet
			// delete_option('rightplace_website_secrets');
		}
	}

	/**
	 * Remove RightPlace keys when user logs out
	 */
	public function remove_rightplace_keys_on_logout($user_id)
	{
		if (!isset($_SERVER['HTTP_X_RIGHTPLACE_APP'])) {
			return;
		}
	}


	public function handle_health_check_callback($request)
	{
		// Turn off warnings
		// error_reporting(0);

		$device_pub_key = $request->get_header('x-rp-device-pub-key');

		if (!$device_pub_key) {
			return self::handle_rest_response(array(
				'ok' => false,
				'message' => 'Illegal request. Missing device id'
			));
		}

		$website_secrets = get_option(self::META_KEY, array());

		if (isset($website_secrets[$device_pub_key])) {
			return self::handle_rest_response(array(
				'ok' => true,
				'version' => $this->version,
			));
		}

		return self::handle_rest_response(array(
			'ok' => false,
			'version' => $this->version,
		));
	}

	/**
	 * Handle the core API endpoint callback
	 *
	 * @since    1.0.0
	 * @access   public
	 * @param    WP_REST_Request $request    The request object
	 * @return   WP_REST_Response
	 */
	public function handle_core_api_callback($request)
	{
		// Turn off warnings
		error_reporting(0);

		// Get the POST data
		$params = $request->get_params();

		if (!isset($params['func'])) {
			return self::handle_rest_response(array(
				'success' => false,
				'message' => 'No func provided'
			));
		}

		$func = $params['func'];

		if (!has_filter('rightplace_action_filter/' . $func)) {
			return self::handle_rest_response(array(
				'success' => false,
				'message' => 'rp-client: No action found for ' . $func,
			));
		}

		$result = apply_filters('rightplace_action_filter/' . $func, $params['params'] ?? array());

		// Add your logic here
		$response = array(
			'success' => $result['success'] ?? $result['ok'] ?? true,
			'result' => $result,
			'params' => $params,
		);

		return self::handle_rest_response($response);
	}

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since    1.0.0
	 */
	public function run()
	{
		$this->loader->run();
	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @since     1.0.0
	 * @return    string    The name of the plugin.
	 */
	public function get_plugin_name()
	{
		return $this->plugin_name;
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @since     1.0.0
	 * @return    Rightplace_Client_Loader    Orchestrates the hooks of the plugin.
	 */
	public function get_loader()
	{
		return $this->loader;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @since     1.0.0
	 * @return    string    The version number of the plugin.
	 */
	public function get_version()
	{
		return $this->version;
	}


	private function decrypt($encrypted_data, $key)
	{
		// Debug key length
		$decoded_key = base64_decode($key);

		// Decode the base64 encrypted data
		$decoded = base64_decode($encrypted_data);

		// Split the parts
		$iv = substr($decoded, 0, 12);
		$auth_tag = substr($decoded, 12, 16);
		$ciphertext = substr($decoded, 28);

		// Try decrypting without re-encoding to base64
		$decrypted = openssl_decrypt(
			$ciphertext,           // Use raw ciphertext instead of base64_encode
			'aes-256-gcm',
			$decoded_key,
			OPENSSL_RAW_DATA,
			$iv,
			$auth_tag
		);

		if ($decrypted === false) {
			rp_dev_log("Decryption failed. OpenSSL error: " . openssl_error_string());
		}

		return $decrypted;
	}

	private function encrypt($data, $key)
	{
		// Decode the base64 key to get raw bytes
		$decoded_key = base64_decode($key);

		// Generate random 12 bytes IV
		$iv = random_bytes(12);

		// Get tag variable ready
		$tag = '';

		// Encrypt
		$ciphertext = openssl_encrypt(
			$data,
			'aes-256-gcm',
			$decoded_key,
			OPENSSL_RAW_DATA,
			$iv,
			$tag     // Will be filled with 16 byte authentication tag
		);

		if ($ciphertext === false) {
			throw new Exception("Encryption failed: " . openssl_error_string());
		}

		// Concatenate IV + Auth Tag + Ciphertext and encode as base64
		return base64_encode($iv . $tag . $ciphertext);
	}

	public function rightplace_client_check_for_updates($transient)
	{
		// Only proceed if we have version data to check against
		if (empty($transient->checked)) {
			return $transient;
		}

		// Define plugin identification info
		$plugin_file = plugin_basename(RIGHTPLACE_CLIENT_FILE);  // e.g. "rightplace-client/rightplace-client.php"
		$plugin_slug = 'rightplace-client';                // your plugin slug (folder name)

		// URL of the JSON update metadata (served by Cloudflare Worker)
		$update_url = RIGHTPLACE_ASSET_URL . '/plugins/' . RIGHTPLACE_BASE_PLUGIN . '/update.json';  // Replace with your actual endpoint

		// Fetch update info from the remote JSON
		$response = wp_remote_get($update_url);
		if (is_wp_error($response) || wp_remote_retrieve_response_code($response) != 200) {
			return $transient; // On error, just return existing transient (no change)
		}
		$update_info = json_decode(wp_remote_retrieve_body($response));
		if (!$update_info) {
			return $transient; // JSON parsing failed or empty
		}

		// Compare remote version with current plugin version
		$current_version = $transient->checked[$plugin_file] ?? null;  // current installed version 
		$remote_version = $update_info->new_version ?? '';
		if (
			$current_version && $remote_version
			&& version_compare($current_version, $remote_version, '<')
		) {

			// Build the object that represents the update
			$plugin_update = array(
				'slug' => $plugin_slug,
				'plugin' => $plugin_file,
				'new_version' => $remote_version,
				'url' => $update_info->url ?? '',       // more info (could be plugin homepage or changelog)
				'package' => $update_info->package ?? ''    // direct download URL of the zip file
			);
			// Optional fields if available in JSON:
			if (!empty($update_info->tested))
				$plugin_update['tested'] = $update_info->tested;
			if (!empty($update_info->requires))
				$plugin_update['requires'] = $update_info->requires;
			if (!empty($update_info->requires_php))
				$plugin_update['requires_php'] = $update_info->requires_php;

			// Attach to transient response - notifies WordPress of the update
			$transient->response[$plugin_file] = (object) $plugin_update;
		}
		return $transient;
	}

	/**
	 * Provide plugin information for the update details modal
	 */
	public function rightplace_client_plugin_info($res, $action, $args)
	{
		if ($action !== 'plugin_information' || empty($args->slug) || $args->slug !== 'rightplace-client') {
			return $res;
		}

		$update_url = RIGHTPLACE_ASSET_URL . '/plugins/' . RIGHTPLACE_BASE_PLUGIN . '/update.json';
		$response = wp_remote_get($update_url);
		if (is_wp_error($response) || wp_remote_retrieve_response_code($response) != 200) {
			return $res;
		}
		$update_info = json_decode(wp_remote_retrieve_body($response));
		if (!$update_info) {
			return $res;
		}

		$plugin_info = new stdClass();
		$plugin_info->name = 'RightPlace Client';
		$plugin_info->slug = 'rightplace-client';
		$plugin_info->version = $update_info->new_version;
		$plugin_info->author = '<a href="https://rightplace.app">RightPlace</a>';
		$plugin_info->homepage = $update_info->url ?? '';
		$plugin_info->requires = $update_info->requires ?? '';
		$plugin_info->tested = $update_info->tested ?? '';
		$plugin_info->requires_php = $update_info->requires_php ?? '';
		$plugin_info->download_link = $update_info->package ?? '';
		$plugin_info->sections = (array) ($update_info->sections ?? []);
		return $plugin_info;
	}

	/**
	 * Hide RightPlace endpoints from the REST API index
	 * 
	 * @param WP_REST_Response $response The REST API index response
	 * @return WP_REST_Response Modified response with RightPlace endpoints removed
	 */
	public function hide_rightplace_endpoints($response)
	{
		if (!is_wp_error($response)) {
			$data = $response->get_data();

			// Remove RightPlace namespaces from the response
			if (isset($data['namespaces'])) {
				$data['namespaces'] = array_filter($data['namespaces'], function ($namespace) {
					return strpos($namespace, 'rightplace/v1') === false;
				});
			}

			// Remove RightPlace routes from the response
			if (isset($data['routes'])) {
				foreach ($data['routes'] as $route => $route_data) {
					if (strpos($route, 'rightplace/v1') !== false) {
						unset($data['routes'][$route]);
					}
				}
			}

			$response->set_data($data);
		}

		return $response;
	}

}

