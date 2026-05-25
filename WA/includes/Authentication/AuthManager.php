<?php

namespace WSHC\Authentication;

/**
 * Handle AJAX-based authentication actions.
 */
class AuthManager {
    /**
     * Initialize the authentication hooks.
     */
    public function init() {
        add_action('wp_ajax_nopriv_wshc_login', [$this, 'handle_login']);
        add_action('wp_ajax_nopriv_wshc_register', [$this, 'handle_registration']);
        add_action('wp_ajax_nopriv_wshc_forgot_password', [$this, 'handle_forgot_password']);
        add_action('wp_ajax_nopriv_wshc_reset_password', [$this, 'handle_reset_password']);
        
        add_action('wp_enqueue_scripts', [$this, 'enqueue_assets']);

        add_shortcode('wshc_login_form', [$this, 'render_auth_container']);
        add_shortcode('wshc_registration_form', [$this, 'render_auth_container']);
        add_shortcode('wshc_forgot_password_form', [$this, 'render_auth_container']);

        add_filter('auth_cookie_expiration', [$this, 'extend_login_session'], 10, 3);
    }

    /**
     * Extend login session to 30 days.
     */
    public function extend_login_session($expiration, $user_id, $remember) {
        return 30 * DAY_IN_SECONDS;
    }

    /**
     * Enqueue authentication assets.
     */
    public function enqueue_assets() {
        if ($this->is_auth_page()) {
            wp_enqueue_style('wshc-style', WSHC_PLUGIN_URL . 'assets/css/style.css', [], '1.0.0');
            wp_enqueue_script('wshc-auth-js', WSHC_PLUGIN_URL . 'assets/js/auth.js', ['jquery'], '1.0.0', true);
            wp_localize_script('wshc-auth-js', 'wshc_auth_obj', [
                'ajaxurl' => admin_url('admin-ajax.php'),
            ]);
        }
    }

    /**
     * Check if current page is an auth page.
     */
    private function is_auth_page() {
        return is_page('login');
    }

    /**
     * Handle user login.
     */
    public function handle_login() {
        check_ajax_referer('wshc_auth_nonce', 'nonce');

        if (\WSHC\Security\SecurityProvider::is_rate_limited('login')) {
            wp_send_json_error(['message' => 'Too many attempts. Please try again later.']);
        }

        $credentials = [
            'user_login'    => sanitize_text_field($_POST['username']),
            'user_password' => $_POST['password'],
            'remember'      => isset($_POST['remember']),
        ];

        $user = wp_signon($credentials, false);

        if (!is_wp_error($user)) {
            if (get_user_meta($user->ID, 'wshc_suspended', true)) {
                wp_logout();
                wp_send_json_error(['message' => 'Your account has been suspended. Please contact support.']);
            }
        }

        if (is_wp_error($user)) {
            wp_send_json_error(['message' => $user->get_error_message()]);
        }

        // Cancel pending deletion if user logs back in
        if (get_user_meta($user->ID, 'wshc_pending_deletion', true)) {
            delete_user_meta($user->ID, 'wshc_pending_deletion');
            \WSHC\UserManagement\ActivityLogger::log($user->ID, 'deletion_cancelled', 'Account deletion request automatically cancelled upon login');
        }

        \WSHC\UserManagement\ActivityLogger::log($user->ID, 'login', 'User logged in');

        wp_send_json_success([
            'message' => 'Login successful. Redirecting...',
            'redirect' => home_url('/id')
        ]);
    }

    /**
     * Handle user registration.
     */
    public function handle_registration() {
        check_ajax_referer('wshc_auth_nonce', 'nonce');

        $first_name = sanitize_text_field($_POST['first_name']);
        $last_name = sanitize_text_field($_POST['last_name']);
        $username = sanitize_user($_POST['username']);
        $email = sanitize_email($_POST['email']);
        $password = $_POST['password'];
        $confirm_password = $_POST['confirm_password'];

        if ($password !== $confirm_password) {
            wp_send_json_error(['message' => 'Passwords do not match.']);
        }

        if (username_exists($username) || email_exists($email)) {
            wp_send_json_error(['message' => 'Username or email already exists.']);
        }

        $user_id = wp_insert_user([
            'user_login' => $username,
            'user_email' => $email,
            'user_pass'  => $password,
            'first_name' => $first_name,
            'last_name'  => $last_name,
            'role'       => 'wshc_visitor'
        ]);

        if (is_wp_error($user_id)) {
            wp_send_json_error(['message' => $user_id->get_error_message()]);
        }

        \WSHC\UserManagement\ActivityLogger::log($user_id, 'registration', 'User registered');

        wp_send_json_success([
            'message' => 'Registration successful. You can now log in.',
            'form' => 'login'
        ]);
    }

    /**
     * Handle forgot password (request OTP).
     */
    public function handle_forgot_password() {
        check_ajax_referer('wshc_auth_nonce', 'nonce');

        $user_login = sanitize_text_field($_POST['user_login']);
        $user = get_user_by('login', $user_login) ?: get_user_by('email', $user_login);

        if (!$user) {
            wp_send_json_error(['message' => 'User not found.']);
        }

        $otp = OTPService::generate_otp($user->ID);
        
        // In a real scenario, send OTP via email. For now, we simulate success.
        // wp_mail($user->user_email, 'Your OTP Code', "Your OTP is: $otp");

        wp_send_json_success([
            'message' => 'OTP sent to your email.',
            'user_id' => $user->ID,
            'form' => 'reset_password'
        ]);
    }

    /**
     * Handle password reset with OTP.
     */
    public function handle_reset_password() {
        check_ajax_referer('wshc_auth_nonce', 'nonce');

        $user_id = intval($_POST['user_id']);
        $otp = sanitize_text_field($_POST['otp']);
        $new_password = $_POST['new_password'];

        if (OTPService::validate_otp($user_id, $otp)) {
            wp_set_password($new_password, $user_id);
            wp_send_json_success([
                'message' => 'Password reset successful. You can now log in.',
                'form' => 'login'
            ]);
        } else {
            wp_send_json_error(['message' => 'Invalid or expired OTP.']);
        }
    }

    /**
     * Render the unified auth container.
     */
    public function render_auth_container() {
        if (is_user_logged_in()) {
            wp_redirect(home_url('/id'));
            exit;
        }
        return $this->load_template('auth/container');
    }

    private function load_template($name) {
        ob_start();
        $path = WSHC_PLUGIN_DIR . 'templates/' . $name . '.php';
        if (file_exists($path)) {
            include $path;
        }
        return ob_get_clean();
    }
}
