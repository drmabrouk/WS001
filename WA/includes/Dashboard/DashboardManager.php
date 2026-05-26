<?php

namespace WSHC\Dashboard;

/**
 * Manage the system dashboard.
 */
class DashboardManager {
    /**
     * Initialize dashboard hooks.
     */
    public function init() {
        add_shortcode('wshc_dashboard', [$this, 'render_dashboard']);
        add_action('wp_enqueue_scripts', [$this, 'enqueue_dashboard_assets']);
        add_action('wp_ajax_wshc_revert_log', [$this, 'handle_revert_log']);
    }

    /**
     * Handle log reversion AJAX.
     */
    public function handle_revert_log() {
        check_ajax_referer('wshc_dashboard_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => 'Permission denied.']);
        }

        $log_id = intval($_POST['log_id']);
        if (\WSHC\UserManagement\ActivityLogger::revert_action($log_id)) {
            wp_send_json_success(['message' => 'Action successfully rolled back.']);
        } else {
            wp_send_json_error(['message' => 'Failed to revert action.']);
        }
    }

    /**
     * Enqueue dashboard assets.
     */
    public function enqueue_dashboard_assets() {
        if (is_page('id')) {
            wp_enqueue_style('dashicons');
            wp_enqueue_style('wshc-style', WSHC_PLUGIN_URL . 'assets/css/style.css', [], '1.0.0');
            wp_enqueue_style('wshc-dashboard-style', WSHC_PLUGIN_URL . 'assets/css/dashboard.css', [], '1.0.0');

            // Research Assets
            wp_enqueue_style('wshc-research-style', WSHC_PLUGIN_URL . 'assets/css/research.css', [], '1.1.0');
            wp_enqueue_script('wshc-research-js', WSHC_PLUGIN_URL . 'assets/js/research.js', ['jquery'], '1.1.0', true);

            wp_enqueue_script('wshc-dashboard-js', WSHC_PLUGIN_URL . 'assets/js/dashboard.js', ['jquery'], '1.0.0', true);
            wp_localize_script('wshc-dashboard-js', 'wshc_dashboard_obj', [
                'ajaxurl'         => admin_url('admin-ajax.php'),
                'nonce'           => wp_create_nonce('wshc_dashboard_nonce'),
                'current_user_id' => get_current_user_id(),
                'logout_url'      => wp_logout_url(home_url('/login')),
            ]);
        }
    }

    /**
     * Render the dashboard layout.
     */
    public function render_dashboard() {
        if (!is_user_logged_in()) {
            wp_redirect(home_url('/wshc-login'));
            exit;
        }

        if (!current_user_can('read')) {
            wp_die('Access denied.');
        }

        $current_section = isset($_GET['section']) ? sanitize_text_field($_GET['section']) : 'dashboard-overview';
        $stats = $this->get_dashboard_stats();

        ob_start();
        $this->load_template('dashboard/layout', [
            'stats'           => $stats,
            'current_section' => $current_section
        ]);
        return ob_get_clean();
    }

    /**
     * Get statistics for the dashboard.
     */
    private function get_dashboard_stats() {
        $user_count = count_users();

        $suspended_query = new \WP_User_Query([
            'meta_key'   => 'wshc_suspended',
            'meta_value' => '1',
            'count_total' => true
        ]);
        $suspended_count = $suspended_query->get_total();

        global $wpdb;
        $table = $wpdb->prefix . 'wshc_membership_applications';
        $institutional_members = $wpdb->get_var("SELECT COUNT(*) FROM $table WHERE status = 'approved'");

        return [
            'total_users'           => $user_count['total_users'],
            'suspended_users'       => $suspended_count,
            'active_users'          => $user_count['total_users'] - $suspended_count,
            'institutional_members' => $institutional_members,
            'recent_logs'           => \WSHC\UserManagement\ActivityLogger::get_logs(null, 10),
        ];
    }

    /**
     * Load a dashboard template.
     */
    public function load_template($name, $args = []) {
        extract($args);
        $path = WSHC_PLUGIN_DIR . 'templates/' . $name . '.php';
        if (file_exists($path)) {
            include $path;
        }
    }
}
