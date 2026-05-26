<?php

namespace WSHC\Settings;

/**
 * Manage system-wide settings and data operations.
 */
class SettingsManager {
    /**
     * Initialize settings hooks.
     */
    public function init() {
        add_action('wp_ajax_wshc_export_data', [$this, 'export_system_data']);
        add_action('wp_ajax_wshc_import_data', [$this, 'import_system_data']);
        add_action('wp_ajax_wshc_save_auth_settings', [$this, 'save_auth_settings']);
        add_action('wp_ajax_wshc_save_design_settings', [$this, 'save_design_settings']);
    }

    /**
     * Save design customizer settings.
     */
    public function save_design_settings() {
        check_ajax_referer('wshc_dashboard_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => 'Permission denied.']);
        }

        $settings = [
            'nav_bg'        => sanitize_hex_color($_POST['nav_bg']),
            'sidebar_bg'    => sanitize_hex_color($_POST['sidebar_bg']),
            'accent_color'  => sanitize_hex_color($_POST['accent_color']),
            'canvas_bg'     => sanitize_hex_color($_POST['canvas_bg']),
            'font_family'   => sanitize_text_field($_POST['font_family']),
            'base_font_size'=> intval($_POST['base_font_size']),
        ];

        update_option('wshc_design_settings', $settings);

        wp_send_json_success(['message' => 'Design settings applied successfully.']);
    }

    /**
     * Save authentication control panel settings.
     */
    public function save_auth_settings() {
        check_ajax_referer('wshc_dashboard_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => 'Permission denied.']);
        }

        $settings = [
            'enable_registration' => isset($_POST['enable_reg']) && $_POST['enable_reg'] === 'true',
            'enable_login'        => isset($_POST['enable_login']) && $_POST['enable_login'] === 'true',
            'otp_message'         => wp_kses_post($_POST['otp_message']),
            'welcome_message'     => wp_kses_post($_POST['welcome_message']),
        ];

        update_option('wshc_auth_settings', $settings);

        wp_send_json_success(['message' => 'Authentication settings saved successfully.']);
    }

    /**
     * Handle system data export (Professional Backup).
     */
    public function export_system_data() {
        check_ajax_referer('wshc_dashboard_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => 'Permission denied.']);
        }

        global $wpdb;

        // Package Settings
        $settings = [
            'auth'   => get_option('wshc_auth_settings'),
            'design' => get_option('wshc_design_settings'),
        ];

        // Package Tables
        $tables = [
            'activity_logs' => $wpdb->get_results("SELECT * FROM {$wpdb->prefix}wshc_activity_logs"),
            'applications'  => $wpdb->get_results("SELECT * FROM {$wpdb->prefix}wshc_membership_applications"),
            'otps'          => $wpdb->get_results("SELECT * FROM {$wpdb->prefix}wshc_otps"),
            'research_submissions' => $wpdb->get_results("SELECT * FROM {$wpdb->prefix}wshc_research_submissions"),
        ];

        // Package Relevant User Meta
        $user_meta = $wpdb->get_results("SELECT * FROM $wpdb->usermeta WHERE meta_key LIKE 'wshc_%'");

        $backup_data = [
            'version'   => '1.3.0',
            'timestamp' => current_time('mysql'),
            'settings'  => $settings,
            'tables'    => $tables,
            'user_meta' => $user_meta
        ];

        $json_data = json_encode($backup_data);

        // Professional AES-256 Encryption
        $encryption_key = defined('NONCE_SALT') ? NONCE_SALT : 'WSHC-BACKUP-SALT-2026';
        $cipher = "aes-256-cbc";
        $iv_len = openssl_cipher_iv_length($cipher);
        $iv = openssl_random_pseudo_bytes($iv_len);
        $encrypted = openssl_encrypt(gzcompress($json_data), $cipher, $encryption_key, 0, $iv);

        $payload = base64_encode($iv . $encrypted);

        wp_send_json_success([
            'message' => 'System state successfully packaged and encrypted.',
            'data'    => $payload,
            'filename' => 'WSHC_Backup_' . date('Y-m-d_His') . '.bin'
        ]);
    }

    /**
     * Handle system data import (Professional Restore).
     */
    public function import_system_data() {
        check_ajax_referer('wshc_dashboard_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => 'Permission denied.']);
        }

        $raw_payload = $_POST['backup_data'] ?? '';
        if (empty($raw_payload)) {
            wp_send_json_error(['message' => 'No backup package detected.']);
        }

        try {
            $decoded = base64_decode($raw_payload);
            $encryption_key = defined('NONCE_SALT') ? NONCE_SALT : 'WSHC-BACKUP-SALT-2026';
            $cipher = "aes-256-cbc";
            $iv_len = openssl_cipher_iv_length($cipher);

            $iv = substr($decoded, 0, $iv_len);
            $encrypted = substr($decoded, $iv_len);

            $compressed_data = openssl_decrypt($encrypted, $cipher, $encryption_key, 0, $iv);
            if (!$compressed_data) throw new \Exception('Decryption failed. Invalid key or corrupted file.');

            $json_data = gzuncompress($compressed_data);
            $data = json_decode($json_data, true);

            if (!$data || !isset($data['version'])) {
                throw new \Exception('Invalid backup package structure.');
            }

            global $wpdb;

            // Restore Settings
            if (isset($data['settings']['auth'])) update_option('wshc_auth_settings', $data['settings']['auth']);
            if (isset($data['settings']['design'])) update_option('wshc_design_settings', $data['settings']['design']);

            // Restore Tables (Safely mapping/overwriting)
            foreach ($data['tables'] as $table_key => $rows) {
                $table_name = $wpdb->prefix . 'wshc_' . $table_key;
                $wpdb->query("TRUNCATE TABLE $table_name");
                foreach ($rows as $row) {
                    $wpdb->insert($table_name, (array)$row);
                }
            }

            // Restore User Meta
            foreach ($data['user_meta'] as $meta) {
                update_user_meta($meta['user_id'], $meta['meta_key'], $meta['meta_value']);
            }

            wp_send_json_success(['message' => 'System state successfully restored from backup.']);

        } catch (\Exception $e) {
            wp_send_json_error(['message' => 'Restoration Failed: ' . $e->getMessage()]);
        }
    }
}
