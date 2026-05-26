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
        add_filter('get_avatar', [$this, 'use_custom_avatar'], 10, 5);
        add_shortcode('wshc_members_directory', [$this, 'render_public_directory']);

        // Automated Lifecycle Pipeline
        add_action('wshc_check_expirations', [$this, 'automated_expiration_pipeline']);
        if (!wp_next_scheduled('wshc_check_expirations')) {
            wp_schedule_event(time(), 'hourly', 'wshc_check_expirations');
        }

        // AJAX Handlers
        add_action('wp_ajax_wshc_load_more_members', [$this, 'load_more_members']);
        add_action('wp_ajax_nopriv_wshc_load_more_members', [$this, 'load_more_members']);
        add_action('wp_ajax_wshc_submit_membership_app', [$this, 'submit_application']);
        add_action('wp_ajax_wshc_list_applications', [$this, 'list_applications']);
        add_action('wp_ajax_wshc_process_application', [$this, 'process_application']);
        add_action('wp_ajax_wshc_list_memberships', [$this, 'list_memberships']);
        add_action('wp_ajax_wshc_list_expired_memberships', [$this, 'list_expired_memberships']);
        add_action('wp_ajax_wshc_delete_membership', [$this, 'delete_membership']);
        add_action('wp_ajax_wshc_get_application_details', [$this, 'get_application_details']);
        add_action('wp_ajax_wshc_send_clarification', [$this, 'send_clarification']);
        add_action('wp_ajax_wshc_get_membership_data', [$this, 'get_membership_data']);
        add_action('wp_ajax_wshc_save_membership_data', [$this, 'save_membership_data']);
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

        $search = isset($_POST['search']) ? sanitize_text_field($_POST['search']) : '';
        global $wpdb;
        $table = $wpdb->prefix . 'wshc_membership_applications';

        $where = "WHERE status = 'pending'";
        if (!empty($search)) {
            $wildcard = '%' . $wpdb->esc_like($search) . '%';
            $where .= $wpdb->prepare(" AND (full_name LIKE %s OR major LIKE %s OR nationality LIKE %s)", $wildcard, $wildcard, $wildcard);
        }

        $apps = $wpdb->get_results("SELECT * FROM $table $where ORDER BY created_at DESC");

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

        $search = isset($_POST['search']) ? sanitize_text_field($_POST['search']) : '';

        $args = [
            'role__in' => ['wshc_member', 'wshc_research_member', 'wshc_practitioner_member', 'wshc_fellowship_member', 'wshc_scientific_reviewer', 'wshc_programs_manager', 'wshc_regional_coordinator', 'wshc_secretary_general'],
            'search'   => !empty($search) ? '*' . $search . '*' : '',
            'search_columns' => ['display_name', 'user_login', 'user_email'],
        ];

        $args['meta_query'] = [
            'relation' => 'AND',
            [
                'key'     => 'wshc_membership_expiry',
                'value'   => current_time('mysql'),
                'compare' => '>=',
                'type'    => 'DATETIME'
            ]
        ];

        // Advanced meta search for serial ID
        if (!empty($search)) {
            $args['meta_query'][] = [
                'relation' => 'OR',
                [
                    'key'     => 'wshc_membership_id',
                    'value'   => $search,
                    'compare' => 'LIKE'
                ],
                [
                    'key'     => 'wshc_nationality',
                    'value'   => $search,
                    'compare' => 'LIKE'
                ]
            ];
        }

        $users = get_users($args);

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
                            <button class="action-btn view-user" data-id="<?php echo $user->ID; ?>" title="View Account Details" style="background:#444;">
                                <span class="dashicons dashicons-visibility"></span>
                            </button>
                            <button class="action-btn edit-membership-data" data-id="<?php echo $user->ID; ?>" title="Edit Member Data" style="background:#007cba;">
                                <span class="dashicons dashicons-edit"></span>
                            </button>
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
     * Background pipeline to handle member lifecycles.
     */
    public function automated_expiration_pipeline() {
        $users = get_users([
            'role__in' => ['wshc_member', 'wshc_research_member', 'wshc_practitioner_member', 'wshc_fellowship_member', 'wshc_scientific_reviewer', 'wshc_programs_manager', 'wshc_regional_coordinator', 'wshc_secretary_general'],
            'meta_query' => [
                [
                    'key'     => 'wshc_membership_expiry',
                    'value'   => current_time('mysql'),
                    'compare' => '<',
                    'type'    => 'DATETIME'
                ]
            ]
        ]);

        foreach ($users as $user) {
            // Log expiration event
            $mid = get_user_meta($user->ID, 'wshc_membership_id', true);
            \WSHC\UserManagement\ActivityLogger::log(0, 'membership_expired', "Automated revocation for Member ID: $mid (User ID: $user->ID)");

            // Note: We keep the roles but the "list_memberships" filter uses the current date to separate active from expired.
            // Requirement says "revoked live status and seamlessly move into Expired Memberships tab".
            // The tabs are filtered by meta_query in list_memberships/list_expired_memberships.
        }
    }

    /**
     * List expired memberships for Administrator.
     */
    public function list_expired_memberships() {
        check_ajax_referer('wshc_dashboard_nonce', 'nonce');

        if (!current_user_can('administrator')) {
            wp_send_json_error(['message' => 'Permission denied.']);
        }

        $search = isset($_POST['search']) ? sanitize_text_field($_POST['search']) : '';

        $args = [
            'role__in' => ['wshc_member', 'wshc_research_member', 'wshc_practitioner_member', 'wshc_fellowship_member', 'wshc_scientific_reviewer', 'wshc_programs_manager', 'wshc_regional_coordinator', 'wshc_secretary_general'],
            'search'   => !empty($search) ? '*' . $search . '*' : '',
            'meta_query' => [
                'relation' => 'AND',
                [
                    'key'     => 'wshc_membership_expiry',
                    'value'   => current_time('mysql'),
                    'compare' => '<',
                    'type'    => 'DATETIME'
                ]
            ]
        ];

        if (!empty($search)) {
            $args['meta_query'][] = [
                'relation' => 'OR',
                [
                    'key'     => 'wshc_membership_id',
                    'value'   => $search,
                    'compare' => 'LIKE'
                ]
            ];
        }

        $users = get_users($args);

        ob_start();
        ?>
        <table class="wshc-table">
            <thead>
                <tr>
                    <th>Member ID</th>
                    <th>Full Name</th>
                    <th>Expiry Date</th>
                    <th>Status</th>
                    <th style="text-align: right;">Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $user) :
                    $mid = get_user_meta($user->ID, 'wshc_membership_id', true);
                    $expiry = get_user_meta($user->ID, 'wshc_membership_expiry', true);
                ?>
                    <tr style="opacity: 0.7;">
                        <td><strong>#<?php echo esc_html($mid); ?></strong></td>
                        <td><?php echo esc_html($user->display_name); ?></td>
                        <td style="color: #d32f2f; font-weight: 700;"><?php echo date('M d, Y', strtotime($expiry)); ?></td>
                        <td><span class="status-capsule suspended">EXPIRED</span></td>
                        <td style="text-align: right;">
                            <button class="action-btn delete-membership" data-id="<?php echo $user->ID; ?>" title="Archive Record" style="background:#d32f2f;">
                                <span class="dashicons dashicons-archive"></span>
                            </button>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <?php if (empty($users)) : ?>
                    <tr><td colspan="5" style="text-align:center; padding:30px;">No expired memberships found in archive.</td></tr>
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

    public function get_membership_data() {
        check_ajax_referer('wshc_dashboard_nonce', 'nonce');
        if (!current_user_can('administrator')) wp_send_json_error();

        global $wpdb;
        $user_id = intval($_POST['user_id']);
        $table = $wpdb->prefix . 'wshc_membership_applications';
        $data = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table WHERE user_id = %d AND status = 'approved' ORDER BY created_at DESC LIMIT 1", $user_id));

        if (!$data) wp_send_json_error(['message' => 'No approved membership record found.']);
        wp_send_json_success($data);
    }

    public function save_membership_data() {
        check_ajax_referer('wshc_dashboard_nonce', 'nonce');
        if (!current_user_can('administrator')) wp_send_json_error();

        global $wpdb;
        $user_id = intval($_POST['user_id']);
        $table = $wpdb->prefix . 'wshc_membership_applications';

        $data = [
            'full_name'      => sanitize_text_field($_POST['full_name']),
            'nationality'    => sanitize_text_field($_POST['nationality']),
            'degree'         => sanitize_text_field($_POST['degree']),
            'major'          => sanitize_text_field($_POST['major']),
            'institution'    => sanitize_text_field($_POST['institution']),
            'job_title'      => sanitize_text_field($_POST['job_title']),
            'employer'       => sanitize_text_field($_POST['employer']),
            'license_number' => sanitize_text_field($_POST['license_number']),
        ];

        $wpdb->update($table, $data, ['user_id' => $user_id, 'status' => 'approved']);

        \WSHC\UserManagement\ActivityLogger::log(get_current_user_id(), 'membership_update', "Admin updated membership data for user ID: $user_id");

        wp_send_json_success(['message' => 'Membership record updated successfully.']);
    }

    /**
     * Load more members via AJAX.
     */
    public function load_more_members() {
        $offset = isset($_POST['offset']) ? intval($_POST['offset']) : 0;
        $search = isset($_POST['search']) ? sanitize_text_field($_POST['search']) : '';

        global $wpdb;
        $table = $wpdb->prefix . 'wshc_membership_applications';

        $where = "WHERE a.status = 'approved'";
        $params = [];

        if (!empty($search)) {
            $search_wildcard = '%' . $wpdb->esc_like($search) . '%';
            $where .= " AND (a.full_name LIKE %s OR a.major LIKE %s OR m.meta_value LIKE %s)";
            array_push($params, $search_wildcard, $search_wildcard, $search_wildcard);
        }

        $query_str = "
            SELECT a.*, m.meta_value as membership_id
            FROM $table a
            LEFT JOIN $wpdb->usermeta m ON a.user_id = m.user_id AND m.meta_key = 'wshc_membership_id'
            $where
            ORDER BY a.created_at DESC
            LIMIT %d, 10
        ";

        $params[] = $offset;

        $members_raw = $wpdb->get_results($wpdb->prepare($query_str, $params));

        if (empty($members_raw)) {
            wp_send_json_success(['html' => '', 'count' => 0]);
        }

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

        ob_start();
        foreach ($members_raw as $member) :
            // Prepend Dr. logic
            $name = $member->full_name;
            $degree = $member->degree;
            if ($degree && (stripos($degree, 'PhD') !== false || stripos($degree, 'Doctor') !== false || stripos($degree, 'MD') !== false)) {
                if (stripos($name, 'Dr.') === false) {
                    $name = 'Dr. ' . $name;
                }
            }

            $user_data = get_userdata($member->user_id);
            $primary_role = !empty($user_data->roles) ? $user_data->roles[0] : '';
            $category = isset($role_names[$primary_role]) ? $role_names[$primary_role] : 'Council Member';
            $flag_emoji = \WSHC\Utils\CountryPicker::get_flag($member->nationality);
            ?>
            <div class="member-row">
                <div class="col-id-cat">
                    <div class="member-identity-wrap">
                        <?php echo get_avatar($member->user_id, 40); ?>
                        <div class="identity-text">
                            <h2 class="member-name"><?php echo esc_html($name); ?></h2>
                            <span class="member-category"><?php echo esc_html($category); ?></span>
                        </div>
                    </div>
                </div>
                <div class="col-field">
                    <span class="field-text"><?php echo esc_html($member->major); ?></span>
                </div>
                <div class="col-serial">
                    <span class="serial-text">#<?php echo esc_html($member->membership_id); ?></span>
                </div>
                <div class="col-country">
                    <div class="flag-container">
                        <span class="country-flag"><?php echo $flag_emoji; ?></span>
                    </div>
                    <span class="country-name"><?php echo esc_html($member->nationality); ?></span>
                </div>
            </div>
            <?php
        endforeach;
        $html = ob_get_clean();

        wp_send_json_success(['html' => $html, 'count' => count($members_raw)]);
    }

    /**
     * Render the public members directory.
     */
    public function render_public_directory() {
        global $wpdb;
        $table_apps = $wpdb->prefix . 'wshc_membership_applications';

        // Comprehensive Mapping: Retrieve all users with Member-tier roles
        $member_roles = ['wshc_member', 'wshc_research_member', 'wshc_practitioner_member', 'wshc_fellowship_member', 'wshc_scientific_reviewer', 'wshc_programs_manager', 'wshc_regional_coordinator', 'wshc_secretary_general'];

        $users = get_users(['role__in' => $member_roles]);
        $members = [];

        foreach ($users as $user) {
            // Check for suspension or expiration first
            if (get_user_meta($user->ID, 'wshc_suspended', true)) continue;
            $expiry = get_user_meta($user->ID, 'wshc_membership_expiry', true);
            if ($expiry && strtotime($expiry) < time()) continue;

            // Fetch app data if exists, fallback to user profile
            $app = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_apps WHERE user_id = %d AND status = 'approved' ORDER BY created_at DESC LIMIT 1", $user->ID));

            $members[] = (object) [
                'user_id'       => $user->ID,
                'full_name'     => $app ? $app->full_name : $user->display_name,
                'major'         => $app ? $app->major : (get_user_meta($user->ID, 'wshc_specialization', true) ?: 'General Council'),
                'nationality'   => $app ? $app->nationality : (get_user_meta($user->ID, 'wshc_nationality', true) ?: 'Global'),
                'membership_id' => get_user_meta($user->ID, 'wshc_membership_id', true) ?: 'LGC-'.(5000 + $user->ID),
                'degree'        => $app ? $app->degree : ''
            ];
        }

        // Process titles (Automatically prepend "Dr.")
        $members = array_map(function($member) {
            $name = $member->full_name;
            $degree = $member->degree;
            if ($degree && (stripos($degree, 'PhD') !== false || stripos($degree, 'Doctor') !== false || stripos($degree, 'MD') !== false)) {
                if (stripos($name, 'Dr.') === false) {
                    $member->full_name = 'Dr. ' . $name;
                }
            }
            return $member;
        }, $members);

        // Enqueue styles & scripts
        wp_enqueue_style('wshc-directory-style', WSHC_PLUGIN_URL . 'assets/css/directory.css', [], '1.0.0');
        wp_enqueue_script('wshc-directory-js', WSHC_PLUGIN_URL . 'assets/js/directory.js', ['jquery'], '1.0.0', true);
        wp_localize_script('wshc-directory-js', 'wshc_directory_obj', [
            'ajaxurl' => admin_url('admin-ajax.php'),
        ]);

        ob_start();
        $template_path = WSHC_PLUGIN_DIR . 'templates/portal/members-directory.php';
        if (file_exists($template_path)) {
            include $template_path;
        }
        return ob_get_clean();
    }

    /**
     * Filter to use custom uploaded avatar.
     */
    public function use_custom_avatar($avatar, $id_or_email, $size, $default, $alt) {
        $user_id = 0;
        if (is_numeric($id_or_email)) {
            $user_id = (int) $id_or_email;
        } elseif (is_object($id_or_email) && isset($id_or_email->user_id)) {
            $user_id = (int) $id_or_email->user_id;
        } elseif (is_string($id_or_email) && ($user = get_user_by('email', $id_or_email))) {
            $user_id = $user->ID;
        }

        if ($user_id) {
            $avatar_id = get_user_meta($user_id, 'wshc_avatar_id', true);
            if ($avatar_id) {
                $url = wp_get_attachment_image_url($avatar_id, 'thumbnail');
                if ($url) {
                    $avatar = "<img alt='" . esc_attr($alt) . "' src='" . esc_url($url) . "' class='avatar avatar-" . esc_attr($size) . " photo' height='" . esc_attr($size) . "' width='" . esc_attr($size) . "' />";
                }
            }
        }
        return $avatar;
    }

    private function generate_membership_id() {
        $last_id = get_option('wshc_last_membership_id', 1000);
        $new_id = $last_id + 1;
        update_option('wshc_last_membership_id', $new_id);
        return 'GSH-' . $new_id;
    }
}
