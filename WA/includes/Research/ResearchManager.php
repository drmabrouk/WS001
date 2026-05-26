<?php

namespace WSHC\Research;

/**
 * Handle Scientific Research Publishing Engine.
 */
class ResearchManager {
    /**
     * Initialize hooks.
     */
    public function init() {
        add_shortcode('wshc_scientific_engine', [$this, 'render_public_repository']);

        // Submission Pipeline
        add_action('wp_ajax_wshc_submit_research', [$this, 'handle_submission']);

        // Admin Audit Panel
        add_action('wp_ajax_wshc_list_research_admin', [$this, 'list_research_admin']);
        add_action('wp_ajax_wshc_update_research_status', [$this, 'update_research_status']);

        // Author Dashboard
        add_action('wp_ajax_wshc_list_my_research', [$this, 'list_my_research']);

        // Public Repository Search
        add_action('wp_ajax_wshc_search_research', [$this, 'search_research']);
        add_action('wp_ajax_nopriv_wshc_search_research', [$this, 'search_research']);

        // Tracking
        add_action('wp_ajax_wshc_track_download', [$this, 'track_download']);
        add_action('wp_ajax_nopriv_wshc_track_download', [$this, 'track_download']);
    }

    /**
     * Handle manuscript submission.
     */
    public function handle_submission() {
        check_ajax_referer('wshc_dashboard_nonce', 'nonce');

        if (!current_user_can('wshc_member') && !current_user_can('administrator')) {
            wp_send_json_error(['message' => 'Permission denied.']);
        }

        global $wpdb;
        $table = $wpdb->prefix . 'wshc_research_submissions';

        $data = [
            'user_id'         => get_current_user_id(),
            'title'           => sanitize_text_field($_POST['title']),
            'abstract'        => sanitize_textarea_field($_POST['abstract']),
            'keywords'        => sanitize_text_field($_POST['keywords']),
            'affiliations'    => sanitize_text_field($_POST['affiliations']),
            'doc_type'        => sanitize_text_field($_POST['doc_type']),
            'prior_registry'  => sanitize_text_field($_POST['prior_registry'] ?? ''),
            'policy_agreed'   => 1,
            'status'          => 'pending'
        ];

        // File Uploads
        if (!empty($_FILES['manuscript']['name'])) {
            $data['manuscript_url'] = $this->upload_file('manuscript');
        }
        if (!empty($_FILES['supplementary']['name'])) {
            $data['supplementary_url'] = $this->upload_file('supplementary');
        }

        if (empty($data['manuscript_url'])) {
            wp_send_json_error(['message' => 'High-resolution PDF manuscript is required.']);
        }

        $wpdb->insert($table, $data);

        \WSHC\UserManagement\ActivityLogger::log(get_current_user_id(), 'research_submit', "Submitted manuscript: " . $data['title']);

        wp_send_json_success(['message' => 'Manuscript submitted successfully for peer review.']);
    }

    /**
     * Admin Regulatory Controls.
     */
    public function update_research_status() {
        check_ajax_referer('wshc_dashboard_nonce', 'nonce');

        if (!current_user_can('administrator')) {
            wp_send_json_error(['message' => 'Permission denied.']);
        }

        global $wpdb;
        $id = intval($_POST['submission_id']);
        $action = sanitize_text_field($_POST['admin_action']);
        $notes = sanitize_textarea_field($_POST['admin_notes'] ?? '');
        $table = $wpdb->prefix . 'wshc_research_submissions';

        $status_map = [
            'approve'  => 'published',
            'reject'   => 'rejected',
            'restrict' => 'restricted',
            'revision' => 'needs_revision',
            'assign'   => 'under_peer_review'
        ];

        if (!isset($status_map[$action])) wp_send_json_error();

        $update_data = [
            'status'      => $status_map[$action],
            'admin_notes' => $notes
        ];

        if ($action === 'approve') {
            $update_data['serial_id'] = $this->generate_serial();
            $update_data['published_at'] = current_time('mysql');
        }

        if ($action === 'assign') {
            $update_data['reviewer_id'] = intval($_POST['reviewer_id']);
        }

        $wpdb->update($table, $update_data, ['id' => $id]);

        \WSHC\UserManagement\ActivityLogger::log(get_current_user_id(), 'research_status', "Updated research #$id to $action");

        wp_send_json_success(['message' => 'Status updated successfully.']);
    }

    /**
     * List submissions for admin.
     */
    public function list_research_admin() {
        check_ajax_referer('wshc_dashboard_nonce', 'nonce');
        if (!current_user_can('administrator')) wp_send_json_error();

        global $wpdb;
        $table = $wpdb->prefix . 'wshc_research_submissions';
        $results = $wpdb->get_results("SELECT r.*, u.display_name FROM $table r LEFT JOIN $wpdb->users u ON r.user_id = u.ID ORDER BY r.created_at DESC");

        ob_start();
        include WSHC_PLUGIN_DIR . 'templates/research/admin-log.php';
        $html = ob_get_clean();

        wp_send_json_success(['html' => $html]);
    }

    /**
     * Author dashboard ledger.
     */
    public function list_my_research() {
        check_ajax_referer('wshc_dashboard_nonce', 'nonce');

        global $wpdb;
        $uid = get_current_user_id();
        $table = $wpdb->prefix . 'wshc_research_submissions';
        $results = $wpdb->get_results($wpdb->prepare("SELECT * FROM $table WHERE user_id = %d ORDER BY created_at DESC", $uid));

        ob_start();
        include WSHC_PLUGIN_DIR . 'templates/research/author-ledger.php';
        $html = ob_get_clean();

        wp_send_json_success(['html' => $html]);
    }

    /**
     * Public Repository Engine.
     */
    public function search_research() {
        $keywords = sanitize_text_field($_POST['keywords'] ?? '');
        $author = sanitize_text_field($_POST['author'] ?? '');
        $date_start = sanitize_text_field($_POST['date_start'] ?? '');
        $date_end = sanitize_text_field($_POST['date_end'] ?? '');

        global $wpdb;
        $table = $wpdb->prefix . 'wshc_research_submissions';

        $where = "WHERE status = 'published'";
        if ($keywords) {
            $where .= $wpdb->prepare(" AND (title LIKE %s OR abstract LIKE %s OR keywords LIKE %s OR serial_id LIKE %s)", '%'.$keywords.'%', '%'.$keywords.'%', '%'.$keywords.'%', '%'.$keywords.'%');
        }
        if ($author) {
            $where .= $wpdb->prepare(" AND affiliations LIKE %s", '%'.$author.'%');
        }
        if ($date_start && $date_end) {
            $where .= $wpdb->prepare(" AND published_at BETWEEN %s AND %s", $date_start, $date_end);
        }

        $results = $wpdb->get_results("SELECT * FROM $table $where ORDER BY published_at DESC");

        ob_start();
        foreach ($results as $item) {
            include WSHC_PLUGIN_DIR . 'templates/research/citation-card.php';
        }
        $html = ob_get_clean();

        wp_send_json_success(['html' => $html]);
    }

    public function track_download() {
        global $wpdb;
        $id = intval($_POST['id']);
        $table = $wpdb->prefix . 'wshc_research_submissions';
        $wpdb->query($wpdb->prepare("UPDATE $table SET download_count = download_count + 1 WHERE id = %d", $id));
        wp_send_json_success();
    }

    public function render_public_repository() {
        wp_enqueue_style('dashicons');
        wp_enqueue_style('wshc-research-style', WSHC_PLUGIN_URL . 'assets/css/research.css', [], '1.1.0');
        wp_enqueue_script('wshc-research-js', WSHC_PLUGIN_URL . 'assets/js/research.js', ['jquery'], '1.1.0', true);
        wp_localize_script('wshc-research-js', 'wshc_research_obj', [
            'ajaxurl' => admin_url('admin-ajax.php'),
            'nonce'   => wp_create_nonce('wshc_dashboard_nonce')
        ]);

        ob_start();
        include WSHC_PLUGIN_DIR . 'templates/research/public-repo.php';
        return ob_get_clean();
    }

    private function upload_file($key) {
        if (!function_exists('wp_handle_upload')) require_once(ABSPATH . 'wp-admin/includes/file.php');
        $file = $_FILES[$key];
        $movefile = wp_handle_upload($file, ['test_form' => false]);
        return ($movefile && !isset($movefile['error'])) ? $movefile['url'] : '';
    }

    private function generate_serial() {
        $year = date('Y');
        $count = get_option('wshc_research_count', 1000);
        $new_count = $count + 1;
        update_option('wshc_research_count', $new_count);
        return "WSHC-RES-$year-" . str_pad($new_count, 4, '0', STR_PAD_LEFT);
    }
}
