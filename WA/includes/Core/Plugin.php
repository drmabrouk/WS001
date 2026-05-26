<?php

namespace WSHC\Core;

/**
 * Main Plugin Class
 */
class Plugin {
    /**
     * @var Plugin
     */
    private static $instance;

    /**
     * Get the singleton instance.
     *
     * @return Plugin
     */
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Private constructor to prevent direct instantiation.
     */
    private function __construct() {
        $this->load_dependencies();
        $this->define_admin_hooks();
        $this->define_public_hooks();
    }

    /**
     * Load required dependencies.
     */
    private function load_dependencies() {
        $this->auth_manager = new \WSHC\Authentication\AuthManager();
        $this->dashboard_manager = new \WSHC\Dashboard\DashboardManager();
        $this->user_registry = new \WSHC\UserManagement\UserRegistry();
        $this->settings_manager = new \WSHC\Settings\SettingsManager();
        $this->access_control = new \WSHC\Security\AccessControl();
        $this->membership_manager = new \WSHC\Memberships\MembershipManager();
        $this->verification_manager = new \WSHC\Memberships\VerificationManager();
        $this->research_manager = new \WSHC\Research\ResearchManager();
    }

    /**
     * Register hooks related to the admin area.
     */
    private function define_admin_hooks() {
        // Admin hooks will be registered here.
    }

    /**
     * Register hooks related to the public-facing side.
     */
    private function define_public_hooks() {
        add_action('init', [$this->auth_manager, 'init']);
        add_action('init', [$this->dashboard_manager, 'init']);
        add_action('init', [$this->user_registry, 'init']);
        add_action('init', [$this->settings_manager, 'init']);
        add_action('init', [$this->access_control, 'init']);
        add_action('init', [$this->membership_manager, 'init']);
        add_action('init', [$this->verification_manager, 'init']);
        add_action('init', [$this->research_manager, 'init']);
    }

    /**
     * Plugin activation handler.
     */
    public static function activate() {
        Activator::activate();
    }

    /**
     * Plugin deactivation handler.
     */
    public static function deactivate() {
        Deactivator::deactivate();
    }
}
