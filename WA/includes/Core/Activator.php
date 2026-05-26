<?php

namespace WSHC\Core;

/**
 * Logic to run during plugin activation.
 */
class Activator {
    /**
     * Activate the plugin.
     */
    public static function activate() {
        self::create_tables();
        self::register_roles();
        self::create_pages();
    }

    /**
     * Create custom database tables.
     */
    private static function create_tables() {
        \WSHC\Database\Schema::create_tables();
    }

    /**
     * Register custom user roles and permissions.
     */
    private static function register_roles() {
        // Purge old roles
        remove_role('wshc_administrator');
        remove_role('wshc_staff');

        // Define exact hierarchy
        $roles = [
            'wshc_visitor' => [
                'display_name' => 'Visitor',
                'caps'         => ['read' => true]
            ],
            'wshc_member' => [
                'display_name' => 'Member',
                'caps'         => ['read' => true]
            ],
            'wshc_research_member' => [
                'display_name' => 'Research Member',
                'caps'         => ['read' => true]
            ],
            'wshc_practitioner_member' => [
                'display_name' => 'Practitioner Member',
                'caps'         => ['read' => true]
            ],
            'wshc_fellowship_member' => [
                'display_name' => 'Fellowship Member',
                'caps'         => ['read' => true]
            ],
            'wshc_scientific_reviewer' => [
                'display_name' => 'Scientific Reviewer',
                'caps'         => ['read' => true]
            ],
            'wshc_programs_manager' => [
                'display_name' => 'Programs Manager',
                'caps'         => ['read' => true]
            ],
            'wshc_regional_coordinator' => [
                'display_name' => 'Regional Coordinator',
                'caps'         => ['read' => true]
            ],
            'wshc_secretary_general' => [
                'display_name' => 'Secretary-General',
                'caps'         => ['read' => true]
            ]
        ];

        foreach ($roles as $role_key => $data) {
            add_role($role_key, $data['display_name'], $data['caps']);
        }

        // Ensure Administrator has all plugin capabilities
        $admin = get_role('administrator');
        if ($admin) {
            $admin->add_cap('manage_wshc_system');
            $admin->add_cap('manage_wshc_users');
        }

        // Set default role for new registrations
        update_option('default_role', 'wshc_visitor');
    }

    /**
     * Automatically generate required WordPress pages.
     */
    private static function create_pages() {
        $pages = [
            'login' => [
                'title'   => 'Login',
                'content' => '[wshc_login_form]',
            ],
            'id' => [
                'title'   => 'Dashboard',
                'content' => '[wshc_dashboard]',
            ],
            'verify' => [
                'title'   => 'Verification Portal',
                'content' => '[wshc_verification]',
            ],
            'directory' => [
                'title'   => 'Official Members Directory',
                'content' => '[wshc_members_directory]',
            ],
            'research' => [
                'title'   => 'Scientific Research Engine & Repository',
                'content' => '[wshc_scientific_engine]',
            ],
        ];

        foreach ($pages as $slug => $page_data) {
            if (!get_page_by_path($slug)) {
                wp_insert_post([
                    'post_title'   => $page_data['title'],
                    'post_content' => $page_data['content'],
                    'post_status'  => 'publish',
                    'post_type'    => 'page',
                    'post_name'    => $slug,
                ]);
            }
        }
    }
}
