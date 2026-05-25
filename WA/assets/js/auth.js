jQuery(document).ready(function($) {
    const ajaxurl = wshc_auth_obj.ajaxurl;

    function showMessage(wrapper, message, type) {
        const msgDiv = wrapper.find('.wshc-message');
        msgDiv.text(message).removeClass('hidden success error').addClass(type).hide().fadeIn(300);
    }

    function switchForm(target) {
        const container = $('.wshc-auth-container > div');
        const nextForm = $(`#wshc-${target}-form-wrapper`);

        container.fadeOut(200, function() {
            container.addClass('hidden');
            nextForm.removeClass('hidden').hide().fadeIn(300);
            $('.wshc-message').addClass('hidden').text('');

            // Reset buttons
            $('#wshc-login-form .wshc-auth-btn').text('Sign In').prop('disabled', false);
            $('#wshc-registration-form .wshc-auth-btn').text('Register').prop('disabled', false);
            $('#wshc-forgot-password-form .wshc-auth-btn').text('Request OTP').prop('disabled', false);
            $('#wshc-reset-password-form .wshc-auth-btn').text('Reset Password').prop('disabled', false);
        });
    }

    $(document).on('click', '.switch-form', function(e) {
        e.preventDefault();
        switchForm($(this).data('target'));
    });

    // Password Toggle
    $(document).on('click', '.password-toggle', function() {
        const input = $(this).siblings('input');
        if (input.attr('type') === 'password') {
            input.attr('type', 'text');
            $(this).removeClass('dashicons-visibility').addClass('dashicons-hidden');
        } else {
            input.attr('type', 'password');
            $(this).removeClass('dashicons-hidden').addClass('dashicons-visibility');
        }
    });

    // Handle Login
    $(document).on('submit', '#wshc-login-form', function(e) {
        e.preventDefault();
        const wrapper = $(this).closest('div');
        const formData = $(this).serialize();
        
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: formData + '&action=wshc_login',
            beforeSend: function() {
                wrapper.find('.wshc-auth-btn').prop('disabled', true).text('SIGNING IN...');
            },
            success: function(response) {
                if (response.success) {
                    showMessage(wrapper, response.data.message, 'success');
                    setTimeout(() => {
                        window.location.href = response.data.redirect;
                    }, 1500);
                } else {
                    showMessage(wrapper, response.data.message, 'error');
                    wrapper.find('.wshc-auth-btn').prop('disabled', false).text('SIGN IN');
                }
            }
        });
    });

    // Handle Registration
    $(document).on('submit', '#wshc-registration-form', function(e) {
        e.preventDefault();
        const wrapper = $(this).closest('div');
        const formData = $(this).serialize();

        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: formData + '&action=wshc_register',
            beforeSend: function() {
                wrapper.find('.wshc-auth-btn').prop('disabled', true).text('REGISTERING...');
            },
            success: function(response) {
                if (response.success) {
                    showMessage(wrapper, response.data.message, 'success');
                    setTimeout(() => {
                        switchForm('login');
                    }, 2000);
                } else {
                    showMessage(wrapper, response.data.message, 'error');
                    wrapper.find('.wshc-auth-btn').prop('disabled', false).text('REGISTER');
                }
            }
        });
    });

    // Handle Forgot Password
    $(document).on('submit', '#wshc-forgot-password-form', function(e) {
        e.preventDefault();
        const wrapper = $(this).closest('div');
        const formData = $(this).serialize();

        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: formData + '&action=wshc_forgot_password',
            beforeSend: function() {
                wrapper.find('.wshc-auth-btn').prop('disabled', true).text('REQUESTING...');
            },
            success: function(response) {
                if (response.success) {
                    showMessage(wrapper, response.data.message, 'success');
                    $('#reset-user-id').val(response.data.user_id);
                    setTimeout(() => {
                        switchForm('reset-password');
                    }, 2000);
                } else {
                    showMessage(wrapper, response.data.message, 'error');
                    wrapper.find('.wshc-auth-btn').prop('disabled', false).text('REQUEST OTP');
                }
            }
        });
    });

    // Handle Reset Password
    $(document).on('submit', '#wshc-reset-password-form', function(e) {
        e.preventDefault();
        const wrapper = $(this).closest('div');
        const formData = $(this).serialize();

        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: formData + '&action=wshc_reset_password',
            beforeSend: function() {
                wrapper.find('.wshc-auth-btn').prop('disabled', true).text('RESETTING...');
            },
            success: function(response) {
                if (response.success) {
                    showMessage(wrapper, response.data.message, 'success');
                    setTimeout(() => {
                        switchForm('login');
                    }, 2000);
                } else {
                    showMessage(wrapper, response.data.message, 'error');
                    wrapper.find('.wshc-auth-btn').prop('disabled', false).text('RESET PASSWORD');
                }
            }
        });
    });
});
