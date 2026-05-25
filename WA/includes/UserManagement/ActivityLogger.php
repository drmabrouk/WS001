<?php

namespace WSHC\UserManagement;

/**
 * Log user activities.
 */
class ActivityLogger {
    /**
     * Log an action.
     *
     * @param int    $user_id
     * @param string $action
     * @param string|array $details
     */
    public static function log($user_id, $action, $details = '') {
        global $wpdb;
        $table = $wpdb->prefix . 'wshc_activity_logs';

        if (is_array($details)) {
            $details = json_encode($details);
        }

        $wpdb->insert($table, [
            'user_id'    => $user_id,
            'action'     => $action,
            'details'    => $details,
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? '',
        ]);
    }

    /**
     * Get logs for a user or system within the last 48 hours.
     */
    public static function get_logs($user_id = null, $limit = 100) {
        global $wpdb;
        $table = $wpdb->prefix . 'wshc_activity_logs';
        
        $time_threshold = date('Y-m-d H:i:s', strtotime('-48 hours'));

        $query = "SELECT * FROM $table WHERE created_at >= %s";
        $params = [$time_threshold];

        if ($user_id) {
            $query .= " AND user_id = %d";
            $params[] = $user_id;
        }

        $query .= " ORDER BY created_at DESC LIMIT %d";
        $params[] = $limit;

        return $wpdb->get_results($wpdb->prepare($query, $params));
    }

    /**
     * Revert or roll back a specific action.
     */
    public static function revert_action($log_id) {
        global $wpdb;
        $table = $wpdb->prefix . 'wshc_activity_logs';
        $log = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table WHERE id = %d", $log_id));

        if (!$log) return false;

        // Implementation would vary by action type.
        // For now, we record a rollback event.
        self::log(get_current_user_id(), 'rollback', "Rolled back log ID: $log_id (Action: $log->action)");

        return true;
    }
}
