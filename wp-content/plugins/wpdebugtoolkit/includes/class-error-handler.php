<?php

namespace DebugToolkit;

require_once __DIR__ . '/utils.php';

/**
 * Error handler
 */
class Error_Handler {
    /**
     * Log an error message
     *
     * @param string $message The error message
     * @param string $context The context in which the error occurred
     * @param string $level The error level (error, warning, info)
     * @return void
     */
    public static function log($message, $context = '', $level = 'error') {
        $plugin_name = 'Debug Toolkit';
        $log_message = sprintf('[%s]%s %s', 
            $plugin_name, 
            !empty($context) ? " [$context]" : '', 
            $message
        );
        
        error_log($log_message);
    }
    
    /**
     * Log an exception
     *
     * @param \Exception $e The exception to log
     * @param string $context The context in which the exception occurred
     * @return void
     */
    public static function log_exception(\Exception $e, $context = '') {
        $message = sprintf(
            'Exception: %s in %s:%d. Trace: %s', 
            $e->getMessage(), 
            $e->getFile(), 
            $e->getLine(),
            $e->getTraceAsString()
        );
        
        self::log($message, $context);
    }
    
    /**
     * Create a WP_Error object with consistent formatting
     *
     * @param string $code Error code
     * @param string $message Error message
     * @param int $status HTTP status code
     * @return \WP_Error
     */
    public static function create_wp_error($code, $message, $status = 400) {
        return new \WP_Error(
            $code,
            $message,
            ['status' => $status]
        );
    }
    
    /**
     * Handle filesystem operation safely
     *
     * @param callable $operation The filesystem operation to perform
     * @param string $error_message Error message if operation fails
     * @param bool $silent 
     * @return mixed 
     * @throws \Exception 
     */
    public static function fs_operation($operation, $error_message, $silent = false) {
        if (is_cgi_environment()) {
            try {

                if (is_callable($operation)) {
                    $result = $operation();
                } else {
                    throw new \Exception('Invalid operation provided');
                }
                
                if ($result === false) {
                    throw new \Exception($error_message);
                }
                
                return $result;
            } catch (\Exception $e) {

                error_log('Debug Toolkit Error: ' . $e->getMessage());
                
                if (!$silent) {
                    throw $e;
                }
                
                return false;
            }
        }
        
        // Non-CGI environments
        try {
            $result = call_user_func($operation);
            
            if ($result === false) {
                throw new \Exception($error_message);
            }
            
            return $result;
        } catch (\Exception $e) {
            self::log_exception($e, 'Filesystem Operation');
            
            if (!$silent) {
                throw $e;
            }
            
            return false;
        }
    }

    /**
     * Execute an operation with error handling
     * 
     * @param callable $operation 
     * @param string $context 
     * @param string $error_message 
     * @param callable $cleanup_callback 
     * @return mixed 
     * @throws \Exception 
     */
    public static function execute_with_error_handling($operation, $context, $error_message = null, $cleanup_callback = null) {
        if (is_cgi_environment()) {
            try {

                if (is_callable($operation)) {
                    return $operation();
                } else {
                    throw new \Exception('Invalid operation provided');
                }
            } catch (\Exception $e) {

                error_log('Debug Toolkit Error [' . $context . ']: ' . $e->getMessage());
                
                if ($cleanup_callback !== null && is_callable($cleanup_callback)) {
                    try {
                        $cleanup_callback();
                    } catch (\Exception $cleanup_e) {
                        error_log('Debug Toolkit Cleanup Error [' . $context . ']: ' . $cleanup_e->getMessage());
                    }
                }
                
                if ($error_message !== null) {
                    throw new \Exception($error_message . ': ' . $e->getMessage(), $e->getCode(), $e);
                }
                
                throw $e;
            }
        }
        
        // Non-CGI envs
        try {
            return call_user_func($operation);
        } catch (\Exception $e) {

            self::log_exception($e, $context);
            
            if ($cleanup_callback !== null) {
                try {
                    call_user_func($cleanup_callback);
                } catch (\Exception $cleanup_e) {
                    self::log_exception($cleanup_e, $context . ' Cleanup');
                }
            }
            
            if ($error_message !== null) {
                throw new \Exception($error_message . ': ' . $e->getMessage(), $e->getCode(), $e);
            }
            
            throw $e;
        }
    }
}

/**
 * Global utility functions
 */

if (!function_exists('dbtk_log_error')) {
    /**
     * Log an error message
     *
     * @param string|\Exception 
     * @param string 
     * @return void
     */
    function dbtk_log_error($message_or_exception, $context = '') {
        if ($message_or_exception instanceof \Exception) {
            Error_Handler::log_exception($message_or_exception, $context);
        } else {
            Error_Handler::log($message_or_exception, $context);
        }
    }
}

if (!function_exists('dbtk_create_error')) {
    /**
     * Create a WP_Error object 
     *
     * @param string $code Error code
     * @param string $message Error message
     * @param int $status HTTP status code
     * @return \WP_Error
     */
    function dbtk_create_error($code, $message, $status = 400) {
        return Error_Handler::create_wp_error($code, $message, $status);
    }
}

if (!function_exists('dbtk_fs_operation')) {
    /**
     * Handle filesystem operation safely
     *
     * @param callable $operation 
     * @param string $error_message 
     * @param bool $silent 
     * @return mixed 
     */
    function dbtk_fs_operation($operation, $error_message, $silent = false) {
        return Error_Handler::fs_operation($operation, $error_message, $silent);
    }
}

if (!function_exists('dbtk_execute_with_error_handling')) {
    /**
     * Execute an operation with standardized error handling
     * 
     * @param callable $operation 
     * @param string $context 
     * @param string $error_message 
     * @param callable $cleanup_callback 
     * @return mixed 
     */
    function dbtk_execute_with_error_handling($operation, $context, $error_message = null, $cleanup_callback = null) {
        return Error_Handler::execute_with_error_handling($operation, $context, $error_message, $cleanup_callback);
    }
} 