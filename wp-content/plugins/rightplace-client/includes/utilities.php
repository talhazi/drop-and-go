<?php

if (!function_exists('rightplace_is_called_by_electron')) {
    function rightplace_is_called_by_electron() {
        $user_agent = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '';
        if (strpos($user_agent, 'Electron') !== false) {
            return true;
        }
        return false;
    }

    function rightplace_is_called_by_electron_webview() {
        $user_agent = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '';
        if (strpos($user_agent, 'ElectronWebView') !== false) {
            return true;
        }
        return false;
    }
}

if (!function_exists('rightplace_is_dev')) {
    function rightplace_is_dev() {
        if (defined('RIGHTPLACE_CLIENT_DEPLOYMENT') && RIGHTPLACE_CLIENT_DEPLOYMENT === 'dev') {
            return true;
        }
        return false;
    }
}

if (!function_exists('rp_dev_log')) {
    function rp_dev_log($message) {
        if (rightplace_is_dev()) {
            error_log($message);
        }
    }
}

