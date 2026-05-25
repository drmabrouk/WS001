<?php

namespace WSHC\Database;

/**
 * Define and handle database schema.
 */
class Schema {
    /**
     * Create required tables.
     */
    public static function create_tables() {
        global $wpdb;
        $charset_collate = $wpdb->get_charset_collate();

        $table_otp = $wpdb->prefix . 'wshc_otps';
        $sql_otp = "CREATE TABLE $table_otp (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            user_id bigint(20) NOT NULL,
            otp_hash varchar(255) NOT NULL,
            expires_at datetime NOT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
            PRIMARY KEY  (id),
            KEY user_id (user_id)
        ) $charset_collate;";

        $table_logs = $wpdb->prefix . 'wshc_activity_logs';
        $sql_logs = "CREATE TABLE $table_logs (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            user_id bigint(20) NOT NULL,
            action varchar(100) NOT NULL,
            details text,
            ip_address varchar(45),
            created_at datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
            PRIMARY KEY  (id),
            KEY user_id (user_id)
        ) $charset_collate;";

        $table_apps = $wpdb->prefix . 'wshc_membership_applications';
        $sql_apps = "CREATE TABLE $table_apps (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            user_id bigint(20) NOT NULL,
            full_name varchar(255) NOT NULL,
            dob date,
            gender varchar(20),
            nationality varchar(100) NOT NULL,
            email varchar(100) NOT NULL,
            phone varchar(50),
            degree varchar(50),
            major varchar(255),
            institution varchar(255),
            grad_year int(4),
            cert_file_url text,
            verification_file_url text,
            country_residence varchar(100),
            city_residence varchar(100),
            phone_secondary varchar(50),
            work_country varchar(100),
            work_state varchar(100),
            job_title varchar(255),
            employer varchar(255),
            experience int(2),
            cv_file_url text,
            license_number varchar(100),
            specialized_certs text,
            other_memberships text,
            research_publications text,
            interests text,
            payment_status varchar(50) DEFAULT 'pending',
            admin_note text,
            status varchar(20) DEFAULT 'pending' NOT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
            PRIMARY KEY  (id),
            KEY user_id (user_id)
        ) $charset_collate;";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql_otp);
        dbDelta($sql_logs);
        dbDelta($sql_apps);
    }
}
