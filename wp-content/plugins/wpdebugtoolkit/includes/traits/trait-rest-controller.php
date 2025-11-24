<?php

namespace DebugToolkit\Traits;

use DebugToolkit\Error_Handler;

trait Rest_Controller {
    /**
     * Check if user has admin permissions
     *
     * @param \WP_REST_Request $request The request object.
     * @return bool|\WP_REST_Response
     */
    protected function check_admin_permissions($request) {
        try {
            if (!is_user_logged_in()) {
                return $this->handle_error(
                    new \Exception(__('You must be logged in to access this endpoint.', 'wpdebugtoolkit')),
                    'Admin Permissions',
                    401
                );
            }

            if (!current_user_can('manage_options')) {
                return $this->handle_error(
                    new \Exception(__('You do not have permission to access this resource.', 'wpdebugtoolkit')),
                    'Admin Permissions',
                    403
                );
            }

            $nonce = $request->get_header('X-WP-Nonce');
            
            if (!$nonce) {
                $params = $request->get_params();
                $nonce = isset($params['_wpnonce']) ? $params['_wpnonce'] : '';
            }

            if (!wp_verify_nonce($nonce, 'wp_rest')) {
                return $this->handle_error(
                    new \Exception(__('Invalid nonce. Please refresh the page and try again.', 'wpdebugtoolkit')),
                    'Admin Permissions',
                    403
                );
            }

            return true;
        } catch (\Exception $e) {
            return $this->handle_error($e, 'Check Admin Permissions', 500);
        }
    }

    /**
     * Handle API response
     *
     * @param mixed $data The response data
     * @param int $status HTTP status code
     * @return \WP_REST_Response
     */
    protected function handle_response($data, $status = 200) {
        $response = [
            'success' => true
        ];

        if (is_array($data)) {
            $response = array_merge($response, $data);
        } else {
            $response['data'] = $data;
        }

        return new \WP_REST_Response($response, $status);
    }

    /**
     * Handle API error 
     *
     * @param \Exception $e The exception
     * @param string $context Error context
     * @param int $status HTTP status code
     * @return \WP_REST_Response
     */
    protected function handle_error(\Exception $e, $context, $status = 500) {

        Error_Handler::log_exception($e, $context);
        
        return new \WP_REST_Response([
            'success' => false,
            'error' => $e->getMessage(),
            'context' => $context
        ], $status);
    }
    
    /**
     * Try-catch wrapper
     *
     * @param callable $callback The callback to execute
     * @param array $args Arguments to pass to the callback
     * @param string $error_context Context for error logging
     * @return mixed|\WP_REST_Response
     */
    protected function try_catch_rest($callback, $args, $error_context) {
        try {
            return call_user_func_array($callback, $args);
        } catch (\Exception $e) {
            return $this->handle_error($e, $error_context);
        }
    }
} 