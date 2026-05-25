<?php

namespace WSHC\Security;

/**
 * Handle dashboard and admin bar restrictions.
 */
class AccessControl {
    /**
     * Initialize restriction hooks.
     */
    public function init() {
        add_action('admin_init', [$this, 'restrict_admin_backend']);
        add_action('after_setup_theme', [$this, 'restrict_admin_bar']);
    }

    /**
     * Redirect non-administrators away from the WordPress back-end.
     */
    public function restrict_admin_backend() {
        if (defined('DOING_AJAX') && DOING_AJAX) {
            return;
        }

        if (!current_user_can('administrator')) {
            wp_redirect(home_url('/id'));
            exit;
        }
    }

    /**
     * Check if current user has administrator access to system modules.
     */
    public static function is_admin() {
        return current_user_can('administrator');
    }

    /**
     * Hide the admin bar for all roles except Administrator.
     */
    public function restrict_admin_bar() {
        if (!current_user_can('administrator')) {
            show_admin_bar(false);
        }
    }
}
