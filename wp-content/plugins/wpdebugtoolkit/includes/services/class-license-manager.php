<?php

namespace DebugToolkit\Services;

use DebugToolkit\Error_Handler;
use DebugToolkit\Constants;

require_once __DIR__ . '/../utils.php';

/**
 * Handles license management for EDD Software Licensing
 */
class License_Manager {

    /**
     * Store license key
     *
     * @param string $license_key The license key to store
     * @return bool Success or failure
     */
    public function store_license_key($license_key) {
        if (empty($license_key)) {
            return false;
        }

        return update_option('dbtk_product_activation', $license_key);
    }

    /**
     * Get license key
     *
     * @return string License key or empty string if no key found
     */
    public function get_license_key() {
        return get_option('dbtk_product_activation', '');
    }

    /**
     * Delete stored license key
     * 
     * @return bool Success or failure
     */
    public function delete_license_key() {
        return delete_option('dbtk_product_activation');
    }

    /**
     * Check if license is active
     * 
     * @return bool True if license is active and valid
     */
    public function is_license_active() {
        try {
            $license_key = $this->get_license_key();
            if (empty($license_key)) {
                return false;
            }

            $response = wp_remote_get(
                add_query_arg(
                    [
                        'edd_action' => 'check_license',
                        'license' => $license_key,
                        'item_id' => Constants::DBTK_ITEM_ID,
                        'url' => home_url(),
                        'cache_bust' => time(),
                        'rand' => wp_rand(1000, 9999)
                    ],
                    Constants::DBTK_STORE_URL
                ),
                ['timeout' => 15, 'sslverify' => true]
            );

            if (is_wp_error($response)) {
                Error_Handler::log('License check failed: ' . $response->get_error_message(), 'License');
                return false;
            }

            $license_data = json_decode(wp_remote_retrieve_body($response));
            return isset($license_data->license) && $license_data->license === 'valid';

        } catch (\Exception $e) {
            Error_Handler::log_exception($e, 'License Check');
            return false;
        }
    }

    /**
     * Get license status details
     * 
     * @return array License status details
     */
    public function get_license_status() {
        try {
            $is_active = $this->is_license_active();
            $license_key = $this->get_license_key();
            $status_info = [];
            
            if ($is_active && !empty($license_key)) {
                $api_url = add_query_arg(
                    [
                        'edd_action' => 'check_license',
                        'license' => $license_key,
                        'item_id' => Constants::DBTK_ITEM_ID,
                        'url' => home_url(),
                        'cache_bust' => time(),
                        'rand' => wp_rand(1000, 9999)
                    ],
                    Constants::DBTK_STORE_URL
                );

                $response = wp_remote_get(
                    $api_url,
                    ['timeout' => 15, 'sslverify' => true]
                );

                if (!is_wp_error($response)) {
                    $body = wp_remote_retrieve_body($response);
                    $license_data = json_decode($body);
                    
                    if ($license_data) {
                        $license_limit = $license_data->license_limit ?? 0;
                        $is_unlimited = ($license_limit === 0 || $license_data->activations_left === 'unlimited');
                        
                        $status_info = [
                            'activations_count' => $license_data->site_count ?? 0,
                            'activation_limit' => $is_unlimited ? 'unlimited' : $license_limit,
                            'status' => $license_data->license ?? 'unknown',
                            'is_unlimited' => $is_unlimited,
                            'plan_name' => $this->get_plan_name($license_data->price_id ?? ''),
                            'price_id' => $license_data->price_id ?? ''
                        ];
                    }
                }
            }

            return [
                'success' => true,
                'is_active' => $is_active,
                'status_info' => $status_info
            ];
        } catch (\Exception $e) {
            Error_Handler::log_exception($e, 'Get License Status');
            return [
                'success' => false,
                'is_active' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Get license details from the API
     * 
     * @return array License details array
     */
    public function get_license_details() {
        try {
            $license_key = $this->get_license_key();
            
            if (empty($license_key)) {
                return [
                    'success' => false,
                    'error' => __('No license key found.', 'wpdebugtoolkit')
                ];
            }

            $response = wp_remote_get(
                add_query_arg(
                    [
                        'edd_action' => 'check_license',
                        'license' => $license_key,
                        'item_id' => Constants::DBTK_ITEM_ID,
                        'url' => home_url(),
                        'cache_bust' => time(),
                        'rand' => wp_rand(1000, 9999)
                    ],
                    Constants::DBTK_STORE_URL
                ),
                ['timeout' => 15, 'sslverify' => true]
            );

            if (is_wp_error($response)) {
                return [
                    'success' => false,
                    'error' => $response->get_error_message()
                ];
            }

            $license_data = json_decode(wp_remote_retrieve_body($response));
            
            if (!$license_data) {
                return [
                    'success' => false,
                    'error' => __('Invalid license data received.', 'wpdebugtoolkit')
                ];
            }

            return [
                'success' => true,
                'details' => $license_data
            ];
        } catch (\Exception $e) {
            Error_Handler::log_exception($e, 'Get License Details');
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Activate a license key
     * 
     * @param string $license_key The license key to activate
     * @return array Result of activation attempt
     */
    public function activate_license($license_key) {
        if (empty($license_key)) {
            return ['success' => false, 'error' => __('License key cannot be empty', 'wpdebugtoolkit')];
        }
        
        $api_url = Constants::DBTK_STORE_URL;
        
        $api_args = [
            'edd_action' => 'activate_license',
            'license' => $license_key,
            'item_id' => Constants::DBTK_ITEM_ID,
            'url' => home_url()
        ];
        
        $response = wp_remote_post($api_url, [
            'timeout' => 15,
            'body' => $api_args,
            'sslverify' => true,
            'user-agent' => 'Debug Toolkit/' . Constants::DBTK_VERSION . '; ' . get_bloginfo('url')
        ]);
        
        if (is_wp_error($response)) {
            $error_message = $response->get_error_message();
            return ['success' => false, 'error' => __('License activation failed: ', 'wpdebugtoolkit') . $error_message];
        }
        
        $status_code = wp_remote_retrieve_response_code($response);
        $body = wp_remote_retrieve_body($response);
        
        if ($status_code !== 200) {
            return ['success' => false, 'error' => __('License activation failed with code: ', 'wpdebugtoolkit') . $status_code];
        }
        
        $license_data = json_decode($body);
        
        if (!$license_data || isset($license_data->error) || $license_data->license !== 'valid') {
            $error = isset($license_data->error) ? $license_data->error : __('Unknown error', 'wpdebugtoolkit');
            return ['success' => false, 'error' => __('License activation failed: ', 'wpdebugtoolkit') . $error];
        }
        
        $this->store_license_key($license_key);
        update_option('debug_toolkit_license_status', $license_data->license);
        update_option('debug_toolkit_license_last_check', time());
        
        clear_caching_plugins_cache();
        
        return [
            'success' => true,
            'message' => __('License activated successfully', 'wpdebugtoolkit'),
            'license_data' => $license_data
        ];
    }

    /**
     * Deactivate the current license
     * 
     * @return array Result of deactivation attempt
     */
    public function deactivate_license() {
        try {
            $license_key = $this->get_license_key();
            if (empty($license_key)) {
                return [
                    'success' => false,
                    'error' => __('No active license found.', 'wpdebugtoolkit')
                ];
            }

            $response = wp_remote_post(
                Constants::DBTK_STORE_URL,
                [
                    'timeout' => 15,
                    'sslverify' => true,
                    'body' => [
                        'edd_action' => 'deactivate_license',
                        'license' => $license_key,
                        'item_id' => Constants::DBTK_ITEM_ID,
                        'url' => home_url()
                    ]
                ]
            );

            if (is_wp_error($response)) {
                return [
                    'success' => false,
                    'error' => $response->get_error_message()
                ];
            }

            $license_data = json_decode(wp_remote_retrieve_body($response));
            
            if (isset($license_data->success) && $license_data->success === false) {
                return [
                    'success' => false,
                    'error' => isset($license_data->error) ? $license_data->error : __('License deactivation failed.', 'wpdebugtoolkit')
                ];
            }

            $this->delete_license_key();
            delete_option('debug_toolkit_license_status');
            delete_option('debug_toolkit_license_last_check');

            clear_caching_plugins_cache();

            return [
                'success' => true,
                'message' => __('License deactivated successfully.', 'wpdebugtoolkit')
            ];
        } catch (\Exception $e) {
            Error_Handler::log_exception($e, 'License Deactivation');
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Map price ID to plan name
     *
     * @param string|int $price_id The price ID from EDD
     * @return string The human-readable plan name
     */
    private function get_plan_name($price_id) {
        $plans = [
            '1' => __('Basic', 'wpdebugtoolkit'),
            '2' => __('Pro', 'wpdebugtoolkit'),
            '3' => __('Basic Lifetime', 'wpdebugtoolkit'),
            '4' => __('Pro Lifetime', 'wpdebugtoolkit')
        ];
        
        return isset($plans[$price_id]) ? $plans[$price_id] : __('Pro', 'wpdebugtoolkit');
    }
} 