<div class="wshc-auth-container" id="wshc-auth-container">
    <!-- Login Form -->
    <div id="wshc-login-form-wrapper">
        <h2>Login</h2>
        <div class="wshc-message hidden"></div>
        <form id="wshc-login-form">
            <div class="wshc-auth-form-group">
                <input type="text" name="username" placeholder="Username or Email" required minlength="4">
            </div>
            <div class="wshc-auth-form-group">
                <input type="password" name="password" placeholder="Password" required minlength="8" maxlength="20">
                <span class="password-toggle dashicons dashicons-visibility"></span>
            </div>
            <button type="submit" class="wshc-auth-btn">Sign In</button>
            <?php wp_nonce_field('wshc_auth_nonce', 'nonce'); ?>
        </form>
        <div class="wshc-auth-links">
            <a href="#" class="switch-form" data-target="registration">Create Account</a> | 
            <a href="#" class="switch-form" data-target="forgot-password">Forgot Password?</a>
        </div>
    </div>

    <!-- Registration Form -->
    <div id="wshc-registration-form-wrapper" class="hidden">
        <h2>Register</h2>
        <div class="wshc-message hidden"></div>
        <form id="wshc-registration-form">
            <div class="wshc-auth-grid">
                <div class="wshc-auth-form-group">
                    <input type="text" name="first_name" placeholder="First Name" required>
                </div>
                <div class="wshc-auth-form-group">
                    <input type="text" name="last_name" placeholder="Last Name" required>
                </div>
                <div class="wshc-auth-form-group">
                    <input type="text" name="username" placeholder="Username" required minlength="4">
                </div>
                <div class="wshc-auth-form-group">
                    <input type="email" name="email" placeholder="Email Address" required>
                </div>
                <div class="wshc-auth-form-group">
                    <input type="password" name="password" placeholder="Password" required minlength="8" maxlength="20">
                    <span class="password-toggle dashicons dashicons-visibility"></span>
                </div>
                <div class="wshc-auth-form-group">
                    <input type="password" name="confirm_password" placeholder="Confirm Password" required minlength="8" maxlength="20">
                    <span class="password-toggle dashicons dashicons-visibility"></span>
                </div>
            </div>
            <button type="submit" class="wshc-auth-btn">Register</button>
            <?php wp_nonce_field('wshc_auth_nonce', 'nonce'); ?>
        </form>
        <div class="wshc-auth-links">
            <a href="#" class="switch-form" data-target="login">Already have an account? Login</a>
        </div>
    </div>

    <!-- Forgot Password Form -->
    <div id="wshc-forgot-password-form-wrapper" class="hidden">
        <h2>Forgot Password</h2>
        <div class="wshc-message hidden"></div>
        <form id="wshc-forgot-password-form">
            <div class="wshc-auth-form-group">
                <input type="text" name="user_login" placeholder="Username or Email" required>
            </div>
            <button type="submit" class="wshc-auth-btn">Request OTP</button>
            <?php wp_nonce_field('wshc_auth_nonce', 'nonce'); ?>
        </form>
        <div class="wshc-auth-links">
            <a href="#" class="switch-form" data-target="login">Back to Login</a>
        </div>
    </div>

    <!-- Reset Password Form -->
    <div id="wshc-reset-password-form-wrapper" class="hidden">
        <h2>Reset Password</h2>
        <div class="wshc-message hidden"></div>
        <form id="wshc-reset-password-form">
            <input type="hidden" name="user_id" id="reset-user-id">
            <div class="wshc-auth-form-group">
                <input type="text" name="otp" placeholder="Enter OTP" required>
            </div>
            <div class="wshc-auth-form-group">
                <input type="password" name="new_password" placeholder="New Password" required minlength="8" maxlength="20">
                <span class="password-toggle dashicons dashicons-visibility"></span>
            </div>
            <button type="submit" class="wshc-auth-btn">Reset Password</button>
            <?php wp_nonce_field('wshc_auth_nonce', 'nonce'); ?>
        </form>
        <div class="wshc-auth-links">
            <a href="#" class="switch-form" data-target="login">Back to Login</a>
        </div>
    </div>
</div>
