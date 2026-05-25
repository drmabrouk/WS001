<?php

namespace WSHC\UserManagement;

/**
 * Handle User CRUD operations.
 */
class UserRegistry {
    /**
     * Initialize hooks.
     */
    public function init() {
        add_action('wp_ajax_wshc_list_users', [$this, 'list_users']);
        add_action('wp_ajax_wshc_save_user', [$this, 'save_user']);
        add_action('wp_ajax_wshc_delete_user', [$this, 'delete_user']);
        add_action('wp_ajax_wshc_toggle_user_status', [$this, 'toggle_user_status']);
        add_action('wp_ajax_wshc_get_user_details', [$this, 'get_user_details']);
        add_action('wp_ajax_wshc_request_deletion', [$this, 'request_self_deletion']);
    }

    /**
     * Handle user self-deletion request (48-hour delay).
     */
    public function request_self_deletion() {
        check_ajax_referer('wshc_dashboard_nonce', 'nonce');

        $user_id = get_current_user_id();
        if (!$user_id) wp_send_json_error();

        $deletion_time = time() + (48 * HOUR_IN_SECONDS);
        update_user_meta($user_id, 'wshc_pending_deletion', $deletion_time);

        ActivityLogger::log($user_id, 'deletion_request', 'Requested account self-deletion (Scheduled for 48 hours)');

        wp_send_json_success([
            'message' => 'Your account has been scheduled for deletion. It will be permanently removed in 48 hours. Logging in again will cancel this request.'
        ]);
    }

    /**
     * Get single user details.
     */
    public function get_user_details() {
        check_ajax_referer('wshc_dashboard_nonce', 'nonce');

        if (!current_user_can('manage_wshc_users')) {
            wp_send_json_error(['message' => 'Permission denied.']);
        }

        $user_id = intval($_POST['user_id']);
        $user = get_userdata($user_id);

        if (!$user) {
            wp_send_json_error(['message' => 'User not found.']);
        }

        wp_send_json_success([
            'ID'         => $user->ID,
            'user_login' => $user->user_login,
            'user_email' => $user->user_email,
            'first_name' => $user->first_name,
            'last_name'  => $user->last_name,
        ]);
    }

    /**
     * List users with search, filter, and pagination.
     */
    public function list_users() {
        check_ajax_referer('wshc_dashboard_nonce', 'nonce');

        if (!current_user_can('manage_wshc_users') && !current_user_can('manage_options')) {
            wp_send_json_error(['message' => 'Permission denied.']);
        }

        $paged = isset($_POST['paged']) ? max(1, intval($_POST['paged'])) : 1;
        $search = isset($_POST['search']) ? sanitize_text_field($_POST['search']) : '';
        $role = isset($_POST['role']) ? sanitize_text_field($_POST['role']) : '';
        $status = isset($_POST['status']) ? sanitize_text_field($_POST['status']) : '';

        $args = [
            'number' => 10,
            'offset' => ($paged - 1) * 10,
            'search' => !empty($search) ? '*' . $search . '*' : '',
            'role'   => $role,
        ];

        if ($status === 'suspended') {
            $args['meta_query'] = [
                [
                    'key'     => 'wshc_suspended',
                    'value'   => '1',
                    'compare' => '='
                ]
            ];
        } elseif ($status === 'active') {
            $args['meta_query'] = [
                [
                    'key'     => 'wshc_suspended',
                    'compare' => 'NOT EXISTS'
                ]
            ];
        }

        $user_query = new \WP_User_Query($args);
        $users = $user_query->get_results();
        $total_users = $user_query->get_total();

        ob_start();
        include WSHC_PLUGIN_DIR . 'templates/dashboard/users-list-table.php';
        $html = ob_get_clean();

        wp_send_json_success([
            'html' => $html,
            'total' => $total_users,
            'pages' => ceil($total_users / 10)
        ]);
    }

    /**
     * Create or update a user.
     */
    public function save_user() {
        check_ajax_referer('wshc_dashboard_nonce', 'nonce');

        if (!current_user_can('manage_wshc_users')) {
            wp_send_json_error(['message' => 'Permission denied.']);
        }

        $user_id = isset($_POST['user_id']) ? intval($_POST['user_id']) : 0;
        $username = sanitize_user($_POST['username']);
        $email = sanitize_email($_POST['email']);
        $password = $_POST['password'];
        $role = sanitize_text_field($_POST['role']);
        $first_name = sanitize_text_field($_POST['first_name'] ?? '');
        $last_name = sanitize_text_field($_POST['last_name'] ?? '');

        $user_data = [
            'user_login' => $username,
            'user_email' => $email,
            'first_name' => $first_name,
            'last_name'  => $last_name,
            'role'       => $role,
        ];

        if ($user_id) {
            $user_data['ID'] = $user_id;
            if (!empty($password)) {
                $user_data['user_pass'] = $password;
            }
            $result = wp_update_user($user_data);
        } else {
            $user_data['user_pass'] = $password;
            $result = wp_insert_user($user_data);
        }

        if (is_wp_error($result)) {
            wp_send_json_error(['message' => $result->get_error_message()]);
        }

        ActivityLogger::log(get_current_user_id(), $user_id ? 'user_update' : 'user_create', "Affected user ID: $result");

        wp_send_json_success(['message' => 'User saved successfully.']);
    }

    /**
     * Delete a user.
     */
    public function delete_user() {
        check_ajax_referer('wshc_dashboard_nonce', 'nonce');

        if (!current_user_can('manage_wshc_users')) {
            wp_send_json_error(['message' => 'Permission denied.']);
        }

        $user_id = intval($_POST['user_id']);
        if (get_current_user_id() === $user_id) {
            wp_send_json_error(['message' => 'You cannot delete yourself.']);
        }

        if (wp_delete_user($user_id)) {
            ActivityLogger::log(get_current_user_id(), 'user_delete', "Deleted user ID: $user_id");
            wp_send_json_success(['message' => 'User deleted successfully.']);
        } else {
            wp_send_json_error(['message' => 'Failed to delete user.']);
        }
    }

    /**
     * Toggle user suspension (using user meta).
     */
    public function toggle_user_status() {
        check_ajax_referer('wshc_dashboard_nonce', 'nonce');

        if (!current_user_can('manage_wshc_users') && !current_user_can('manage_options')) {
            wp_send_json_error(['message' => 'Permission denied.']);
        }

        $user_id = intval($_POST['user_id']);
        $reason = sanitize_text_field($_POST['reason'] ?? '');
        $duration = intval($_POST['duration'] ?? 0);
        $status = get_user_meta($user_id, 'wshc_suspended', true);
        
        if ($status) {
            delete_user_meta($user_id, 'wshc_suspended');
            delete_user_meta($user_id, 'wshc_suspension_reason');
            delete_user_meta($user_id, 'wshc_suspension_duration');
            $message = 'User reactivated.';
            ActivityLogger::log(get_current_user_id(), 'user_reactivate', "Reactivated user ID: $user_id");
        } else {
            update_user_meta($user_id, 'wshc_suspended', 1);
            if ($reason) update_user_meta($user_id, 'wshc_suspension_reason', $reason);
            if ($duration) update_user_meta($user_id, 'wshc_suspension_duration', $duration);

            $log_details = "Suspended user ID: $user_id. Reason: $reason. Duration: $duration days.";
            $message = 'User suspended.';
            ActivityLogger::log(get_current_user_id(), 'user_suspend', $log_details);
        }

        wp_send_json_success(['message' => $message]);
    }
}
