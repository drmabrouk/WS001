<?php

namespace WSHC\Security;

/**
 * Handle basic rate limiting and security utilities.
 */
class SecurityProvider {
    /**
     * Check if a user/IP is rate limited for an action.
     * 
     * @param string $action
     * @param int    $limit
     * @param int    $window in seconds
     * @return bool True if limited.
     */
    public static function is_rate_limited($action, $limit = 5, $window = 60) {
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        $transient_key = 'wshc_rate_limit_' . md5($action . $ip);
        $attempts = get_transient($transient_key) ?: 0;

        if ($attempts >= $limit) {
            return true;
        }

        set_transient($transient_key, $attempts + 1, $window);
        return false;
    }
}
