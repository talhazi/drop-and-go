<?php

namespace DebugToolkit;

use DebugToolkit\Error_Handler;
use DebugToolkit\Constants;

/**
 * Handles plugin updates using EDD
 */
class Updater {

    /**
     * The plugin current version
     * @var string
     */
    private $current_version;

    /**
     * The plugin remote update path
     * @var string
     */
    private $update_path;

    /**
     * Plugin Slug 
     * @var string
     */
    private $plugin_slug;

    /**
     * Plugin name (plugin_file)
     * @var string
     */
    private $slug;

    /**
     * License key
     * @var string
     */
    private $license;

    /**
     * Item ID
     * @var int
     */
    private $item_id;

    /**
     * Author name
     * @var string
     */
    private $author;

    /**
     * Initialize a new instance of the EDD plugin updater
     *
     * @param string 
     */
    public function __construct($plugin_file = '') {
        try {
            $this->current_version = Constants::DBTK_VERSION;
            $this->update_path = Constants::DBTK_STORE_URL;
            $this->plugin_slug = plugin_basename($plugin_file);
            list($this->slug) = explode('/', $this->plugin_slug);
            $this->license = get_option('dbtk_product_activation', '');
            $this->item_id = Constants::DBTK_ITEM_ID;
            $this->author = 'Debug Toolkit';

            add_filter('pre_set_site_transient_update_plugins', array($this, 'check_update'));
            add_filter('plugins_api', array($this, 'plugins_api_filter'), 10, 3);
            add_action('after_plugin_row_' . $this->plugin_slug, array($this, 'show_update_notification'), 10, 2);
        } catch (\Exception $e) {
            Error_Handler::log_exception($e, 'Updater Initialization');
        }
    }

    /**
     * Check for plugin updates
     *
     * @param object 
     * @return object 
     */
    public function check_update($transient) {
        try {
            if (empty($transient->checked)) {
                return $transient;
            }

            $response = $this->get_version_info();
            if (false !== $response && is_object($response) && isset($response->new_version)) {
                if (version_compare($this->current_version, $response->new_version, '<')) {
                    $response->slug = $this->slug;
                    $response->plugin = $this->plugin_slug;
                    $response->icons = isset($response->icons) ? (array) $response->icons : array();
                    $response->banners = isset($response->banners) ? (array) $response->banners : array();
                    $response->sections = isset($response->sections) ? (array) $response->sections : array();
                    $transient->response[$this->plugin_slug] = $response;
                }
            }

            return $transient;
        } catch (\Exception $e) {
            Error_Handler::log_exception($e, 'Update Check');
            return $transient;
        }
    }

    /**
     * Filter the plugins_api response
     *
     * @param false|object|array 
     * @param string 
     * @param object 
     * @return object 
     */
    public function plugins_api_filter($result, $action, $args) {
        try {
            if ('plugin_information' != $action || !isset($args->slug) || $args->slug != $this->slug) {
                return $result;
            }

            $response = $this->get_version_info();
            if (false !== $response) {
                $response->sections = isset($response->sections) ? (array) $response->sections : array();
                $response->banners = isset($response->banners) ? (array) $response->banners : array();
                $response->icons = isset($response->icons) ? (array) $response->icons : array();
                return $response;
            }

            return $result;
        } catch (\Exception $e) {
            Error_Handler::log_exception($e, 'Plugin API');
            return $result;
        }
    }

    /**
     * Get the version info
     *
     * @return object|false
     */
    private function get_version_info() {
        try {

            $cache_key = 'edd_sl_' . md5(serialize($this->slug));
            $api_response = get_transient($cache_key);

            if (false === $api_response) {

                $this->license = get_option('dbtk_product_activation', '');
                
                $api_params = array(
                    'edd_action' => 'get_version',
                    'license'    => !empty($this->license) ? $this->license : '',
                    'item_id'    => isset($this->item_id) ? $this->item_id : '',
                    'slug'       => $this->slug,
                    'author'     => $this->author,
                    'url'        => home_url(),
                );

                $response = wp_remote_post($this->update_path, array(
                    'timeout'   => 15,
                    'sslverify' => true,
                    'body'      => $api_params
                ));

                if (is_wp_error($response) || 200 !== wp_remote_retrieve_response_code($response)) {
                    Error_Handler::log('Failed to get version info. Response: ' . wp_remote_retrieve_response_code($response), 'Updater');
                    return false;
                }

                $api_response = json_decode(wp_remote_retrieve_body($response));
                set_transient($cache_key, $api_response, 3 * HOUR_IN_SECONDS);
            }

            return $api_response;
        } catch (\Exception $e) {
            Error_Handler::log_exception($e, 'Get Version Info');
            return false;
        }
    }

    /**
     * Show update notification
     *
     * @param string 
     * @param array 
     */
    public function show_update_notification($file, $plugin_data) {
        try {
            if (is_network_admin() || !is_multisite() || !current_user_can('update_plugins')) {
                return;
            }

            if (!is_multisite()) {
                return;
            }

            if (empty($this->license)) {
                $message = sprintf(
                    __('%1$sThere is a new version of %2$s available. %3$sView version %4$s details%5$s or %6$supdate now%7$s.%8$s', 'wpdebugtoolkit'),
                    '<div class="update-message notice inline notice-warning notice-alt">',
                    esc_html($plugin_data['Name']),
                    '<a href="' . esc_url(self_admin_url('plugin-install.php?tab=plugin-information&plugin=' . $this->slug . '&section=changelog&TB_iframe=true&width=772&height=550')) . '" class="thickbox open-plugin-details-modal">',
                    esc_html($plugin_data['Version']),
                    '</a>',
                    '<a href="' . esc_url(wp_nonce_url(self_admin_url('update.php?action=upgrade-plugin&plugin=' . $file), 'upgrade-plugin_' . $file)) . '">',
                    '</a>',
                    '</div>'
                );
                echo '<tr class="plugin-update-tr"><td colspan="3" class="plugin-update colspanchange">' . $message . '</td></tr>';
            }
        } catch (\Exception $e) {
            Error_Handler::log_exception($e, 'Update Notification');
        }
    }
} 