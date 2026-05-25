<?php

namespace WSHC\Memberships;

/**
 * Handle document and membership verification.
 */
class VerificationManager {
    /**
     * Initialize verification hooks.
     */
    public function init() {
        add_shortcode('wshc_verification', [$this, 'render_verification_page']);
        add_action('wp_ajax_wshc_verify_code', [$this, 'handle_verification']);
        add_action('wp_ajax_nopriv_wshc_verify_code', [$this, 'handle_verification']);
    }

    /**
     * Render the verification portal page.
     */
    public function render_verification_page() {
        wp_enqueue_style('dashicons');
        wp_enqueue_style('wshc-verify-style', WSHC_PLUGIN_URL . 'assets/css/verify.css', [], '1.0.0');
        wp_enqueue_script('wshc-verify-js', WSHC_PLUGIN_URL . 'assets/js/verify.js', ['jquery'], '1.0.0', true);
        wp_localize_script('wshc-verify-js', 'wshc_verify_obj', [
            'ajaxurl' => admin_url('admin-ajax.php'),
            'nonce'   => wp_create_nonce('wshc_verify_nonce'),
        ]);

        ob_start();
        $template_path = WSHC_PLUGIN_DIR . 'templates/portal/verification-page.php';
        if (file_exists($template_path)) {
            include $template_path;
        } else {
            echo '<p>Verification template not found.</p>';
        }
        return ob_get_clean();
    }

    /**
     * Handle AJAX verification request.
     */
    public function handle_verification() {
        check_ajax_referer('wshc_verify_nonce', 'nonce');

        $code = strtoupper(sanitize_text_field($_POST['code']));

        if (empty($code)) {
            wp_send_json_error(['message' => 'Please enter a valid code.']);
        }

        global $wpdb;

        // Log the verification attempt
        \WSHC\UserManagement\ActivityLogger::log(0, 'doc_verification', "Public query for code: $code");

        // Search for user by membership ID
        $user_id = $wpdb->get_var($wpdb->prepare(
            "SELECT user_id FROM $wpdb->usermeta WHERE meta_key = 'wshc_membership_id' AND meta_value = %s",
            $code
        ));

        if (!$user_id) {
            wp_send_json_error(['message' => 'No active record found for this code.']);
        }

        $user = get_userdata($user_id);
        if (!$user) {
            wp_send_json_error(['message' => 'User not found.']);
        }

        // Check for suspension
        if (get_user_meta($user_id, 'wshc_suspended', true)) {
            wp_send_json_error(['message' => 'This certificate has been suspended.']);
        }

        // Get Membership Details
        $issue_date = get_user_meta($user_id, 'wshc_membership_start', true);
        $expiry_date = get_user_meta($user_id, 'wshc_membership_expiry', true);
        $membership_id = get_user_meta($user_id, 'wshc_membership_id', true);

        // Check Expiry
        $is_expired = false;
        if ($expiry_date && strtotime($expiry_date) < time()) {
            $is_expired = true;
        }

        // Get Application Data for Full Name and Degree
        $table_apps = $wpdb->prefix . 'wshc_membership_applications';
        $app = $wpdb->get_row($wpdb->prepare(
            "SELECT full_name, degree FROM $table_apps WHERE user_id = %d AND status = 'approved' ORDER BY created_at DESC LIMIT 1",
            $user_id
        ));

        $holder_name = $app ? $app->full_name : $user->display_name;
        $degree = $app ? $app->degree : '';

        // Apply Honorifics
        if ($degree && (stripos($degree, 'PhD') !== false || stripos($degree, 'Doctor') !== false || stripos($degree, 'MD') !== false)) {
            if (stripos($holder_name, 'Dr.') === false) {
                $holder_name = 'Dr. ' . $holder_name;
            }
        }

        // Document Type based on Role
        $role_names = [
            'wshc_member'               => 'Official Member',
            'wshc_research_member'      => 'Research Member',
            'wshc_practitioner_member'  => 'Practitioner Member',
            'wshc_fellowship_member'    => 'Fellowship Member',
            'wshc_scientific_reviewer'  => 'Scientific Reviewer',
            'wshc_programs_manager'     => 'Programs Manager',
            'wshc_regional_coordinator' => 'Regional Coordinator',
            'wshc_secretary_general'    => 'Secretary-General',
        ];

        $primary_role = !empty($user->roles) ? $user->roles[0] : '';
        $doc_type = isset($role_names[$primary_role]) ? $role_names[$primary_role] : 'Official Document';

        wp_send_json_success([
            'status'        => $is_expired ? 'expired' : 'valid',
            'holder_name'   => $holder_name,
            'membership_id' => $membership_id,
            'issue_date'    => $issue_date ? date('M d, Y', strtotime($issue_date)) : 'N/A',
            'expiry_date'   => $expiry_date ? date('M d, Y', strtotime($expiry_date)) : 'N/A',
            'doc_type'      => $doc_type,
        ]);
    }
}
