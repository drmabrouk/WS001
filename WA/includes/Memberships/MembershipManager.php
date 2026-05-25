<?php

namespace WSHC\Memberships;

/**
 * Handle membership applications and approved member directory.
 */
class MembershipManager {
    /**
     * Initialize membership hooks.
     */
    public function init() {
        add_action('wp_ajax_wshc_submit_membership_app', [$this, 'submit_application']);
        add_action('wp_ajax_wshc_list_applications', [$this, 'list_applications']);
        add_action('wp_ajax_wshc_process_application', [$this, 'process_application']);
        add_action('wp_ajax_wshc_list_memberships', [$this, 'list_memberships']);
        add_action('wp_ajax_wshc_delete_membership', [$this, 'delete_membership']);
        add_action('wp_ajax_wshc_get_application_details', [$this, 'get_application_details']);
        add_action('wp_ajax_wshc_send_clarification', [$this, 'send_clarification']);
    }

    /**
     * Handle membership application submission by Visitor.
     */
    public function submit_application() {
        check_ajax_referer('wshc_dashboard_nonce', 'nonce');

        if (!is_user_logged_in()) {
            wp_send_json_error(['message' => 'You must be logged in.']);
        }

        global $wpdb;
        $table = $wpdb->prefix . 'wshc_membership_applications';
        $user_id = get_current_user_id();

        // Check if already applied
        $existing = $wpdb->get_var($wpdb->prepare("SELECT id FROM $table WHERE user_id = %d AND status = 'pending'", $user_id));
        if ($existing) {
            wp_send_json_error(['message' => 'You already have a pending application.']);
        }

        $data = [
            'user_id'               => $user_id,
            'full_name'             => sanitize_text_field($_POST['full_name']),
            'dob'                   => sanitize_text_field($_POST['dob']),
            'gender'                => sanitize_text_field($_POST['gender']),
            'nationality'           => sanitize_text_field($_POST['nationality']),
            'country_residence'     => sanitize_text_field($_POST['country_residence']),
            'city_residence'        => sanitize_text_field($_POST['city_residence']),
            'email'                 => sanitize_email($_POST['email']),
            'phone'                 => sanitize_text_field($_POST['phone']),
            'phone_secondary'       => sanitize_text_field($_POST['phone_secondary']),
            'degree'                => sanitize_text_field($_POST['degree']),
            'major'                 => sanitize_text_field($_POST['major']),
            'institution'           => sanitize_text_field($_POST['institution']),
            'grad_year'             => intval($_POST['grad_year']),
            'job_title'             => sanitize_text_field($_POST['job_title']),
            'employer'              => sanitize_text_field($_POST['employer']),
            'experience'            => intval($_POST['experience']),
            'work_country'          => sanitize_text_field($_POST['work_country']),
            'work_state'            => sanitize_text_field($_POST['work_state']),
            'license_number'        => sanitize_text_field($_POST['license_number']),
            'specialized_certs'     => sanitize_textarea_field($_POST['specialized_certs']),
            'other_memberships'     => sanitize_textarea_field($_POST['other_memberships']),
            'research_publications' => sanitize_textarea_field($_POST['research_publications']),
            'interests'             => implode(', ', array_map('sanitize_text_field', (array)$_POST['interests'])),
            'status'                => 'pending'
        ];

        // Handle File Uploads
        if (!empty($_FILES['cert_file']['name'])) {
            $data['cert_file_url'] = $this->handle_file_upload('cert_file');
        }
        if (!empty($_FILES['cv_file']['name'])) {
            $data['cv_file_url'] = $this->handle_file_upload('cv_file');
        }
        if (!empty($_FILES['verification_file']['name'])) {
            $data['verification_file_url'] = $this->handle_file_upload('verification_file');
        }

        $wpdb->insert($table, $data);

        \WSHC\UserManagement\ActivityLogger::log($user_id, 'membership_apply', 'Submitted 5-step membership application');

        wp_send_json_success(['message' => 'Application submitted successfully. We will review it shortly.']);
    }

    private function handle_file_upload($key) {
        if (!function_exists('wp_handle_upload')) {
            require_once(ABSPATH . 'wp-admin/includes/file.php');
        }

        $uploaded_file = $_FILES[$key];
        $upload_overrides = ['test_form' => false];
        $movefile = wp_handle_upload($uploaded_file, $upload_overrides);

        if ($movefile && !isset($movefile['error'])) {
            return $movefile['url'];
        }
        return '';
    }

    /**
     * List pending applications for Administrator.
     */
    public function list_applications() {
        check_ajax_referer('wshc_dashboard_nonce', 'nonce');

        if (!current_user_can('administrator')) {
            wp_send_json_error(['message' => 'Permission denied.']);
        }

        global $wpdb;
        $table = $wpdb->prefix . 'wshc_membership_applications';
        $apps = $wpdb->get_results("SELECT * FROM $table WHERE status = 'pending' ORDER BY created_at DESC");

        ob_start();
        ?>
        <table class="wshc-table">
            <thead>
                <tr>
                    <th>Full Name</th>
                    <th>Degree/Major</th>
                    <th>Nationality</th>
                    <th>Date</th>
                    <th style="text-align: right;">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($apps as $app) : ?>
                    <tr>
                        <td><strong><?php echo esc_html($app->full_name); ?></strong></td>
                        <td style="font-size: 11px;"><?php echo esc_html($app->degree . ' in ' . $app->major); ?></td>
                        <td><?php echo esc_html($app->nationality); ?></td>
                        <td><?php echo date('M d, Y', strtotime($app->created_at)); ?></td>
                        <td style="text-align: right;">
                            <button class="action-btn view-app" data-id="<?php echo $app->id; ?>" title="View Dossier" style="background:#444;">
                                <span class="dashicons dashicons-id"></span>
                            </button>
                            <button class="action-btn process-app" data-id="<?php echo $app->id; ?>" data-action="approve" title="Approve">
                                <span class="dashicons dashicons-yes"></span>
                            </button>
                            <button class="action-btn process-app" data-id="<?php echo $app->id; ?>" data-action="reject" title="Reject" style="background:#d32f2f;">
                                <span class="dashicons dashicons-no"></span>
                            </button>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <?php if (empty($apps)) : ?>
                    <tr><td colspan="5" style="text-align:center; padding:30px;">No pending applications.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
        <?php
        $html = ob_get_clean();
        wp_send_json_success(['html' => $html]);
    }

    /**
     * Approve or reject application.
     */
    public function process_application() {
        check_ajax_referer('wshc_dashboard_nonce', 'nonce');

        if (!current_user_can('administrator')) {
            wp_send_json_error(['message' => 'Permission denied.']);
        }

        global $wpdb;
        $app_id = intval($_POST['app_id']);
        $action = sanitize_text_field($_POST['process_action']);
        $table = $wpdb->prefix . 'wshc_membership_applications';

        $app = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table WHERE id = %d", $app_id));
        if (!$app) wp_send_json_error(['message' => 'Application not found.']);

        if ($action === 'approve') {
            $wpdb->update($table, ['status' => 'approved'], ['id' => $app_id]);

            // Upgrade user role to Member
            $user = new \WP_User($app->user_id);
            $user->set_role('wshc_member');

            // Set Membership Details
            $membership_id = $this->generate_membership_id();
            update_user_meta($app->user_id, 'wshc_membership_id', $membership_id);
            update_user_meta($app->user_id, 'wshc_nationality', $app->nationality);
            update_user_meta($app->user_id, 'wshc_membership_start', current_time('mysql'));
            update_user_meta($app->user_id, 'wshc_membership_expiry', date('Y-m-d H:i:s', strtotime('+365 days')));

            \WSHC\UserManagement\ActivityLogger::log(get_current_user_id(), 'membership_approve', "Approved membership for user ID: $app->user_id (ID: $membership_id)");
        } else {
            $wpdb->update($table, ['status' => 'rejected'], ['id' => $app_id]);
            \WSHC\UserManagement\ActivityLogger::log(get_current_user_id(), 'membership_reject', "Rejected application for user ID: $app->user_id");
        }

        wp_send_json_success(['message' => 'Application processed successfully.']);
    }

    /**
     * List members in directory.
     */
    public function list_memberships() {
        check_ajax_referer('wshc_dashboard_nonce', 'nonce');

        if (!current_user_can('administrator')) {
            wp_send_json_error(['message' => 'Permission denied.']);
        }

        $users = get_users(['role__in' => ['wshc_member', 'wshc_research_member', 'wshc_practitioner_member', 'wshc_fellowship_member', 'wshc_scientific_reviewer', 'wshc_programs_manager', 'wshc_regional_coordinator', 'wshc_secretary_general']]);

        ob_start();
        ?>
        <table class="wshc-table">
            <thead>
                <tr>
                    <th>Member ID</th>
                    <th>Full Name</th>
                    <th>Country of Nationality</th>
                    <th>Expiry Date</th>
                    <th style="text-align: right;">Control</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $user) :
                    $mid = get_user_meta($user->ID, 'wshc_membership_id', true);
                    $nationality = get_user_meta($user->ID, 'wshc_nationality', true);
                    $expiry = get_user_meta($user->ID, 'wshc_membership_expiry', true);
                ?>
                    <tr>
                        <td><strong>#<?php echo esc_html($mid); ?></strong></td>
                        <td><?php echo esc_html($user->display_name); ?></td>
                        <td><?php echo esc_html($nationality); ?></td>
                        <td><?php echo date('M d, Y', strtotime($expiry)); ?></td>
                        <td style="text-align: right;">
                            <?php
                            $is_suspended = get_user_meta($user->ID, 'wshc_suspended', true);
                            ?>
                            <button class="action-btn toggle-status" data-id="<?php echo $user->ID; ?>" title="<?php echo $is_suspended ? 'Reactivate' : 'Temporarily Suspend'; ?>" style="background:<?php echo $is_suspended ? '#2e7d32' : '#f57c00'; ?>;">
                                <span class="dashicons <?php echo $is_suspended ? 'dashicons-yes' : 'dashicons-warning'; ?>"></span>
                            </button>
                            <button class="action-btn delete-membership" data-id="<?php echo $user->ID; ?>" title="Delete Membership" style="background:#d32f2f;">
                                <span class="dashicons dashicons-trash"></span>
                            </button>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <?php if (empty($users)) : ?>
                    <tr><td colspan="5" style="text-align:center; padding:30px;">No registered members.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
        <?php
        $html = ob_get_clean();
        wp_send_json_success(['html' => $html]);
    }

    /**
     * Delete membership and revert to Visitor.
     */
    public function delete_membership() {
        check_ajax_referer('wshc_dashboard_nonce', 'nonce');

        if (!current_user_can('administrator')) {
            wp_send_json_error(['message' => 'Permission denied.']);
        }

        $user_id = intval($_POST['user_id']);
        $user = new \WP_User($user_id);
        $user->set_role('wshc_visitor');

        // Clear membership data
        delete_user_meta($user_id, 'wshc_membership_id');
        delete_user_meta($user_id, 'wshc_membership_start');
        delete_user_meta($user_id, 'wshc_membership_expiry');

        \WSHC\UserManagement\ActivityLogger::log(get_current_user_id(), 'membership_delete', "Deleted membership for user ID: $user_id. Reverted to Visitor.");

        wp_send_json_success(['message' => 'Membership deleted and user reverted to Visitor status.']);
    }

    public function get_application_details() {
        check_ajax_referer('wshc_dashboard_nonce', 'nonce');

        if (!current_user_can('administrator')) {
            wp_send_json_error(['message' => 'Permission denied.']);
        }

        global $wpdb;
        $app_id = intval($_POST['app_id']);
        $table = $wpdb->prefix . 'wshc_membership_applications';
        $app = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table WHERE id = %d", $app_id));

        if (!$app) wp_send_json_error(['message' => 'Application not found.']);

        wp_send_json_success($app);
    }

    public function send_clarification() {
        check_ajax_referer('wshc_dashboard_nonce', 'nonce');

        if (!current_user_can('administrator')) {
            wp_send_json_error(['message' => 'Permission denied.']);
        }

        global $wpdb;
        $app_id = intval($_POST['app_id']);
        $note = sanitize_textarea_field($_POST['note']);
        $table = $wpdb->prefix . 'wshc_membership_applications';

        $wpdb->update($table, ['admin_note' => $note], ['id' => $app_id]);

        \WSHC\UserManagement\ActivityLogger::log(get_current_user_id(), 'clarification_sent', "Sent clarification request to applicant ID: $app_id");

        wp_send_json_success(['message' => 'Clarification dispatch sent to applicant dashboard.']);
    }

    private function generate_membership_id() {
        $last_id = get_option('wshc_last_membership_id', 1000);
        $new_id = $last_id + 1;
        update_option('wshc_last_membership_id', $new_id);
        return 'GSH-' . $new_id;
    }
}
