jQuery(document).ready(function($) {
    // Sidebar Toggle
    $('#sidebar-toggle').on('click', function() {
        const sidebar = $('#wshc-sidebar');
        sidebar.toggleClass('collapsed');
    });

    // Settings Dropdown - Hover Persistence
    let settingsTimeout;
    $('.nav-settings-dropdown').on('mouseenter', function() {
        clearTimeout(settingsTimeout);
        $(this).find('.dropdown-menu').stop(true, true).fadeIn(200);
    }).on('mouseleave', function() {
        const $menu = $(this).find('.dropdown-menu');
        settingsTimeout = setTimeout(function() {
            $menu.stop(true, true).fadeOut(200);
        }, 2000); // 2-second hover persistence
    });

    // Password Toggle (Universal)
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

    // Initial Load
    const activeSection = $('.dashboard-section:not(.hidden)');
    const sectionId = activeSection.attr('id');

    if (sectionId === 'section-user-management') {
        loadUserManagement();
    } else if (sectionId === 'section-membership-hub') {
        loadMembershipHub();
    }

    function loadMembershipHub() {
        const search = $('#membership-hub-search').val();
        loadMembershipDirectory(search);
        loadMembershipApplications(search);
        loadExpiredMemberships(search);
    }

    function loadMembershipApplications(search = '') {
        const container = $('#membership-apps-container');
        container.css('opacity', '0.5');
        $.ajax({
            url: wshc_dashboard_obj.ajaxurl,
            type: 'POST',
            data: {
                action: 'wshc_list_applications',
                nonce: wshc_dashboard_obj.nonce,
                search: search
            },
            success: function(response) {
                container.css('opacity', '1');
                if (response.success) {
                    container.html(response.data.html);
                }
            }
        });
    }

    function loadExpiredMemberships(search = '') {
        const container = $('#membership-expired-container');
        if (!container.length) return;
        container.css('opacity', '0.5');
        $.ajax({
            url: wshc_dashboard_obj.ajaxurl,
            type: 'POST',
            data: {
                action: 'wshc_list_expired_memberships',
                nonce: wshc_dashboard_obj.nonce,
                search: search
            },
            success: function(response) {
                container.css('opacity', '1');
                if (response.success) {
                    container.html(response.data.html);
                }
            }
        });
    }

    function loadMembershipDirectory(search = '') {
        const container = $('#membership-dir-container');
        container.css('opacity', '0.5');
        $.ajax({
            url: wshc_dashboard_obj.ajaxurl,
            type: 'POST',
            data: {
                action: 'wshc_list_memberships',
                nonce: wshc_dashboard_obj.nonce,
                search: search
            },
            success: function(response) {
                container.css('opacity', '1');
                if (response.success) {
                    container.html(response.data.html);
                }
            }
        });
    }

    let membershipSearchTimer;
    $(document).on('input', '#membership-hub-search', function() {
        clearTimeout(membershipSearchTimer);
        const search = $(this).val();
        membershipSearchTimer = setTimeout(() => {
            loadMembershipHub();
        }, 500);
    });

    $(document).on('click', '.process-app', function() {
        const id = $(this).data('id');
        const action = $(this).data('action');
        const btn = $(this);

        if (action === 'reject' && !confirm('Are you sure you want to REJECT this application?')) return;

        btn.prop('disabled', true);

        $.ajax({
            url: wshc_dashboard_obj.ajaxurl,
            type: 'POST',
            data: {
                action: 'wshc_process_application',
                nonce: wshc_dashboard_obj.nonce,
                app_id: id,
                process_action: action
            },
            success: function(response) {
                if (response.success) {
                    $('#app-dossier-modal').addClass('hidden');
                    loadMembershipApplications();
                    showNotification(action.toUpperCase() + 'ED', response.data.message);
                } else {
                    alert(response.data.message);
                    btn.prop('disabled', false);
                }
            }
        });
    });

    $(document).on('click', '#save-design-settings', function() {
        const btn = $(this);
        const data = {
            action: 'wshc_save_design_settings',
            nonce: wshc_dashboard_obj.nonce,
            nav_bg: $('#design-nav-bg').val(),
            sidebar_bg: $('#design-sidebar-bg').val(),
            accent_color: $('#design-accent').val(),
            canvas_bg: $('#design-canvas-bg').val(),
            font_family: $('#design-font').val(),
            base_font_size: $('#design-font-size').val()
        };

        btn.prop('disabled', true).text('APPLYING...');

        $.ajax({
            url: wshc_dashboard_obj.ajaxurl,
            type: 'POST',
            data: data,
            success: function(response) {
                alert(response.data.message);
                if (response.success) window.location.reload();
            }
        });
    });

    let currentAppId = null;
    $(document).on('click', '.view-app', function() {
        const id = $(this).data('id');
        currentAppId = id;

        $.ajax({
            url: wshc_dashboard_obj.ajaxurl,
            type: 'POST',
            data: {
                action: 'wshc_get_application_details',
                nonce: wshc_dashboard_obj.nonce,
                app_id: id
            },
            success: function(response) {
                if (response.success) {
                    const app = response.data;
                    let html = `
                        <div class="dossier-grid" style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                            <div class="dossier-section">
                                <h4 style="border-bottom: 1px solid #000; padding-bottom: 5px;">PERSONAL</h4>
                                <p><strong>Full Name:</strong> ${app.full_name}</p>
                                <p><strong>DOB:</strong> ${app.dob}</p>
                                <p><strong>Gender:</strong> ${app.gender}</p>
                                <p><strong>Nationality:</strong> ${app.nationality}</p>
                                <p><strong>Residence:</strong> ${app.city_residence}, ${app.country_residence}</p>
                                <p><strong>Email:</strong> ${app.email}</p>
                                <p><strong>Phone:</strong> ${app.phone}</p>
                            </div>
                            <div class="dossier-section">
                                <h4 style="border-bottom: 1px solid #000; padding-bottom: 5px;">ACADEMIC</h4>
                                <p><strong>Degree:</strong> ${app.degree}</p>
                                <p><strong>Major:</strong> ${app.major}</p>
                                <p><strong>Institution:</strong> ${app.institution}</p>
                                <p><strong>Year:</strong> ${app.grad_year}</p>
                                <p><strong>Certificate:</strong> ${app.cert_file_url ? `<a href="${app.cert_file_url}" target="_blank">View File</a>` : 'Not Provided'}</p>
                                <p><strong>Verification:</strong> ${app.verification_file_url ? `<a href="${app.verification_file_url}" target="_blank">View DataFlow</a>` : 'Not Provided'}</p>
                            </div>
                            <div class="dossier-section">
                                <h4 style="border-bottom: 1px solid #000; padding-bottom: 5px;">PROFESSIONAL</h4>
                                <p><strong>Job Title:</strong> ${app.job_title}</p>
                                <p><strong>Employer:</strong> ${app.employer}</p>
                                <p><strong>Experience:</strong> ${app.experience} Years</p>
                                <p><strong>Location:</strong> ${app.work_state}, ${app.work_country}</p>
                                <p><strong>License:</strong> ${app.license_number || 'N/A'}</p>
                                <p><strong>CV:</strong> ${app.cv_file_url ? `<a href="${app.cv_file_url}" target="_blank">Download CV</a>` : 'Not Provided'}</p>
                            </div>
                            <div class="dossier-section">
                                <h4 style="border-bottom: 1px solid #000; padding-bottom: 5px;">RESEARCH & MISC</h4>
                                <p><strong>Interests:</strong> ${app.interests}</p>
                                <p><strong>Special Certs:</strong> ${app.specialized_certs || 'None'}</p>
                                <p><strong>Publications:</strong> ${app.research_publications || 'None'}</p>
                            </div>
                        </div>
                    `;
                    $('#app-dossier-content').html(html);
                    $('#admin-clarification-note').val(app.admin_note || '');
                    $('#app-dossier-modal').removeClass('hidden').hide().fadeIn(300);
                }
            }
        });
    });

    $(document).on('click', '#send-clarification-btn', function() {
        const note = $('#admin-clarification-note').val();
        if (!note) return alert('Please enter a note for the applicant.');

        const btn = $(this);
        btn.prop('disabled', true).text('DISPATCHING...');

        $.ajax({
            url: wshc_dashboard_obj.ajaxurl,
            type: 'POST',
            data: {
                action: 'wshc_send_clarification',
                nonce: wshc_dashboard_obj.nonce,
                app_id: currentAppId,
                note: note
            },
            success: function(response) {
                btn.prop('disabled', false).text('Dispatch Clarification');
                if (response.success) {
                    showNotification('DISPATCH SENT', response.data.message);
                }
            }
        });
    });

    $(document).on('click', '.delete-membership', function() {
        if (!confirm('Are you sure you want to PERMANENTLY SUSPEND/DELETE this membership record? The user will be reverted to Visitor status.')) return;
        const id = $(this).data('id');
        $.ajax({
            url: wshc_dashboard_obj.ajaxurl,
            type: 'POST',
            data: {
                action: 'wshc_delete_membership',
                nonce: wshc_dashboard_obj.nonce,
                user_id: id
            },
            success: function(response) {
                if (response.success) {
                    loadMembershipDirectory();
                } else {
                    alert(response.data.message);
                }
            }
        });
    });

    // Wizard Navigation & Draft Saving
    let currentWizardStep = 1;
    const wizardForm = $('#membership-application-wizard');
    const storageKey = `wshc_wizard_draft_${wshc_dashboard_obj.current_user_id}`;

    if (wizardForm.length) {
        // Load Draft
        const savedDraft = localStorage.getItem(storageKey);
        if (savedDraft) {
            const draft = JSON.parse(savedDraft);
            currentWizardStep = draft.step || 1;

            // Populate fields
            Object.keys(draft.data).forEach(key => {
                const el = wizardForm.find(`[name="${key}"]`);
                if (el.length && el.attr('type') !== 'file') {
                    el.val(draft.data[key]);
                }
            });

            $(`.wizard-pane`).addClass('hidden');
            $(`#pane-${currentWizardStep}`).removeClass('hidden');
            updateWizardUI();
        }

        // Save Draft on Change
        wizardForm.on('input change', 'input, select, textarea', function() {
            saveWizardDraft();
        });
    }

    function saveWizardDraft() {
        const formData = {};
        wizardForm.serializeArray().forEach(item => {
            formData[item.name] = item.value;
        });

        localStorage.setItem(storageKey, JSON.stringify({
            step: currentWizardStep,
            data: formData
        }));
    }

    $(document).on('click', '#next-step', function() {
        if (validateStep(currentWizardStep)) {
            $(`#pane-${currentWizardStep}`).addClass('hidden');
            currentWizardStep++;
            $(`#pane-${currentWizardStep}`).removeClass('hidden');
            updateWizardUI();
            saveWizardDraft();
        }
    });

    $(document).on('click', '#prev-step', function() {
        $(`#pane-${currentWizardStep}`).addClass('hidden');
        currentWizardStep--;
        $(`#pane-${currentWizardStep}`).removeClass('hidden');
        updateWizardUI();
        saveWizardDraft();
    });

    function updateWizardUI() {
        $('.wizard-step').removeClass('active completed');
        for (let i = 1; i <= 6; i++) {
            if (i < currentWizardStep) $(`.wizard-step[data-step="${i}"]`).addClass('completed');
            if (i === currentWizardStep) $(`.wizard-step[data-step="${i}"]`).addClass('active');
        }

        if (currentWizardStep === 1) $('#prev-step').addClass('hidden');
        else $('#prev-step').removeClass('hidden');

        if (currentWizardStep === 6) {
            $('#next-step').addClass('hidden');
            $('#submit-wizard').removeClass('hidden');
        } else {
            $('#next-step').removeClass('hidden');
            $('#submit-wizard').addClass('hidden');
        }
    }

    function validateStep(step) {
        const inputs = $(`#pane-${step} [required]`);
        let valid = true;
        inputs.each(function() {
            if (!$(this).val()) {
                valid = false;
                $(this).css('border-color', '#d32f2f');
            } else {
                $(this).css('border-color', '');
            }
        });
        return valid;
    }

    function showNotification(title, message, isSuccess = true) {
        $('#notification-title').text(title);
        $('#notification-message').text(message);
        $('#notification-icon').removeClass('dashicons-yes-alt dashicons-dismiss')
            .addClass(isSuccess ? 'dashicons-yes-alt' : 'dashicons-dismiss')
            .css('color', isSuccess ? '#2e7d32' : '#d32f2f');

        $('#wshc-notification-modal').removeClass('hidden').hide().fadeIn(300);
    }

    $(document).on('submit', '#membership-application-wizard', function(e) {
        e.preventDefault();
        const btn = $('#submit-wizard');
        const formData = new FormData(this);
        formData.append('action', 'wshc_submit_membership_app');
        formData.append('nonce', wshc_dashboard_obj.nonce);

        btn.prop('disabled', true).text('PROCESSING...');

        $.ajax({
            url: wshc_dashboard_obj.ajaxurl,
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                if (response.success) {
                    localStorage.removeItem(storageKey);
                    showNotification('APPLICATION SUBMITTED', 'Your institutional membership request has been dispatched for administrative review.');
                    setTimeout(() => {
                        window.location.reload();
                    }, 3000);
                } else {
                    showNotification('ERROR', response.data.message, false);
                }
                btn.prop('disabled', false).text('COMPLETE APPLICATION');
            }
        });
    });

    function loadUserManagement(paged = 1) {
        const searchInput = $('#user-search');
        const roleFilter = $('#role-filter');
        const statusFilter = $('#status-filter');
        const container = $('#user-management-container');

        if (!container.length) return;

        const search = searchInput.length ? searchInput.val() : '';
        const role = roleFilter.length ? roleFilter.val() : '';
        const status = statusFilter.length ? statusFilter.val() : '';

        container.css('opacity', '0.5');

        $.ajax({
            url: wshc_dashboard_obj.ajaxurl,
            type: 'POST',
            data: {
                action: 'wshc_list_users',
                nonce: wshc_dashboard_obj.nonce,
                paged: paged,
                search: search,
                role: role,
                status: status
            },
            success: function(response) {
                container.css('opacity', '1');
                if (response.success) {
                    container.html(response.data.html);
                    renderPagination(response.data.pages, paged);
                } else {
                    container.html(`<div class="wshc-message error">${response.data.message}</div>`);
                }
            },
            error: function() {
                container.css('opacity', '1').html('<div class="wshc-message error">FAILED TO LOAD USERS. PLEASE TRY AGAIN.</div>');
            }
        });
    }

    function renderPagination(totalPages, current) {
        let html = '';
        if (totalPages <= 1) return $('#user-pagination').html('');

        for (let i = 1; i <= totalPages; i++) {
            html += `<button class="page-btn ${i === current ? 'active' : ''}" data-page="${i}">${i}</button>`;
        }
        $('#user-pagination').html(html);
    }

    $(document).on('click', '.page-btn', function() {
        loadUserManagement($(this).data('page'));
    });

    let searchTimer;
    $(document).on('input', '#user-search', function() {
        clearTimeout(searchTimer);
        searchTimer = setTimeout(() => {
            loadUserManagement(1);
        }, 400);
    });

    $(document).on('change', '#role-filter, #status-filter', function() {
        loadUserManagement(1);
    });

    // Settings Tabs Switching
    $(document).on('click', '.settings-tab', function() {
        const tabId = $(this).data('tab');

        $('.settings-tab').removeClass('active');
        $(this).addClass('active');

        $('.settings-pane').addClass('hidden');
        $(`#tab-${tabId}`).removeClass('hidden');
    });

    $(document).on('click', '#export-data-btn', function() {
        const btn = $(this);
        btn.prop('disabled', true).text('EXPORTING...');

        $.ajax({
            url: wshc_dashboard_obj.ajaxurl,
            type: 'POST',
            data: {
                action: 'wshc_export_data',
                nonce: wshc_dashboard_obj.nonce
            },
            success: function(response) {
                if (response.success) {
                    const blob = new Blob([response.data.data], { type: 'text/plain' });
                    const link = document.createElement('a');
                    link.href = URL.createObjectURL(blob);
                    link.download = response.data.filename;
                    link.click();
                    alert(response.data.message);
                } else {
                    alert(response.data.message);
                }
                btn.prop('disabled', false).text('EXPORT SYSTEM DATA');
            }
        });
    });

    $(document).on('click', '#save-auth-settings', function() {
        const btn = $(this);
        const data = {
            action: 'wshc_save_auth_settings',
            nonce: wshc_dashboard_obj.nonce,
            enable_reg: $('#enable-reg').is(':checked'),
            enable_login: $('#enable-login').is(':checked'),
            otp_message: $('#otp-message').val(),
            welcome_message: $('#welcome-message').val()
        };

        btn.prop('disabled', true).text('SAVING...');

        $.ajax({
            url: wshc_dashboard_obj.ajaxurl,
            type: 'POST',
            data: data,
            success: function(response) {
                alert(response.data.message);
                btn.prop('disabled', false).text('SAVE CONFIGURATIONS');
            }
        });
    });

    $(document).on('click', '#import-data-btn', function() {
        const input = document.createElement('input');
        input.type = 'file';
        input.accept = '.bin';
        input.onchange = e => {
            const file = e.target.files[0];
            const reader = new FileReader();
            reader.readAsText(file);
            reader.onload = readerEvent => {
                const content = readerEvent.target.result;
                if (!confirm('Are you sure you want to restore this data? ALL current records will be overwritten.')) return;

                const btn = $(this);
                btn.prop('disabled', true).text('RESTORING...');

                $.ajax({
                    url: wshc_dashboard_obj.ajaxurl,
                    type: 'POST',
                    data: {
                        action: 'wshc_import_data',
                        nonce: wshc_dashboard_obj.nonce,
                        backup_data: content
                    },
                    success: function(response) {
                        alert(response.data.message);
                        if (response.success) window.location.reload();
                        btn.prop('disabled', false).text('IMPORT DATA PACKAGE');
                    }
                });
            };
        };
        input.click();
    });

    // Toggle Status Modal
    $(document).on('click', '.toggle-status', function() {
        const userId = $(this).data('id');
        const title = $(this).attr('title');
        $('#status-user-id').val(userId);
        $('#status-modal-message').text(`Are you sure you want to ${title.toLowerCase()}?`);

        if (title.toLowerCase().includes('suspend')) {
            $('#suspension-advanced-fields').removeClass('hidden');
        } else {
            $('#suspension-advanced-fields').addClass('hidden');
        }

        $('#status-user-modal').removeClass('hidden').hide().fadeIn(200);
    });

    $('#confirm-status-btn').on('click', function() {
        const userId = $('#status-user-id').val();
        const btn = $(this);
        const reason = $('#suspension-reason').val();
        const duration = $('#suspension-duration').val();

        btn.prop('disabled', true).text('PROCESSING...');

        $.ajax({
            url: wshc_dashboard_obj.ajaxurl,
            type: 'POST',
            data: {
                action: 'wshc_toggle_user_status',
                nonce: wshc_dashboard_obj.nonce,
                user_id: userId,
                reason: reason,
                duration: duration
            },
            success: function(response) {
                btn.prop('disabled', false).text('Confirm Change');
                $('#status-user-modal').addClass('hidden');
                if (response.success) {
                    loadUserManagement();
                } else {
                    alert(response.data.message);
                }
            }
        });
    });

    $(document).on('click', '#add-user-btn', function() {
        $('#modal-title').text('ADD NEW USER');
        $('#wshc-user-form')[0].reset();
        $('#form-user-id').val('');
        $('#user-modal').removeClass('hidden').hide().fadeIn(300);
    });

    $(document).on('click', '.view-user', function(e) {
        e.preventDefault();
        const userId = $(this).data('id');

        $.ajax({
            url: wshc_dashboard_obj.ajaxurl,
            type: 'POST',
            data: {
                action: 'wshc_get_user_details',
                nonce: wshc_dashboard_obj.nonce,
                user_id: userId
            },
            success: function(response) {
                if (response.success) {
                    const u = response.data;
                    let html = `
                        <div id="printable-user-profile">
                            <div class="user-detail-row"><strong>ID</strong> #${u.ID}</div>
                            <div class="user-detail-row"><strong>First Name</strong> ${u.first_name || 'N/A'}</div>
                            <div class="user-detail-row"><strong>Last Name</strong> ${u.last_name || 'N/A'}</div>
                            <div class="user-detail-row"><strong>Username</strong> ${u.user_login}</div>
                            <div class="user-detail-row"><strong>Email</strong> ${u.user_email}</div>
                            <div class="user-detail-row"><strong>Role</strong> ${u.role}</div>
                            <div class="user-detail-row"><strong>Joined Date</strong> ${u.joined}</div>
                            <div class="user-detail-row"><strong>Account Status</strong> ${u.status}</div>
                        </div>
                        <div style="margin-top: 25px; display: flex; gap: 10px;">
                            <button class="wshc-auth-btn edit-trigger" data-id="${u.ID}" style="background: #000; flex: 1;">Edit Account</button>
                            <button class="wshc-auth-btn print-user-btn" style="background: #444; width: auto;"><span class="dashicons dashicons-printer"></span></button>
                        </div>
                    `;
                    $('#user-details-content').html(html);
                    $('#user-details-modal').removeClass('hidden').hide().fadeIn(300);
                }
            }
        });
    });

    $(document).on('click', '.print-user-btn', function() {
        const content = $('#printable-user-profile').html();
        const printWindow = window.open('', '_blank', 'height=600,width=800');
        printWindow.document.write('<html><head><title>User Profile</title>');
        printWindow.document.write('<style>body{font-family:sans-serif;padding:40px;}.user-detail-row{margin-bottom:15px;padding-bottom:5px;border-bottom:1px solid #eee;}strong{display:inline-block;width:150px;text-transform:uppercase;font-size:12px;color:#666;}</style>');
        printWindow.document.write('</head><body>');
        printWindow.document.write('<h1>USER PROFILE</h1>');
        printWindow.document.write(content);
        printWindow.document.write('</body></html>');
        printWindow.document.close();
        printWindow.print();
    });

    $(document).on('click', '#close-details-modal', function() {
        $('#user-details-modal').fadeOut(200, function() {
            $(this).addClass('hidden');
        });
    });

    $(document).on('click', '.edit-trigger', function() {
        $('#user-details-modal').addClass('hidden');
        const userId = $(this).data('id');
        triggerEditUser(userId);
    });

    $(document).on('click', '.edit-user', function(e) {
        e.preventDefault();
        triggerEditUser($(this).data('id'));
    });

    $(document).on('click', '.edit-membership-data', function(e) {
        e.preventDefault();
        const userId = $(this).data('id');

        $.ajax({
            url: wshc_dashboard_obj.ajaxurl,
            type: 'POST',
            data: {
                action: 'wshc_get_membership_data',
                nonce: wshc_dashboard_obj.nonce,
                user_id: userId
            },
            success: function(response) {
                if (response.success) {
                    const data = response.data;
                    $('#membership-form-user-id').val(userId);
                    $('#membership-form-name').val(data.full_name);
                    $('#membership-form-nationality').val(data.nationality);
                    $('#membership-form-degree').val(data.degree);
                    $('#membership-form-major').val(data.major);
                    $('#membership-form-institution').val(data.institution);
                    $('#membership-form-job').val(data.job_title);
                    $('#membership-form-employer').val(data.employer);
                    $('#membership-form-license').val(data.license_number);
                    $('#membership-data-edit-modal').removeClass('hidden').hide().fadeIn(300);
                }
            }
        });
    });

    $(document).on('submit', '#wshc-membership-data-form', function(e) {
        e.preventDefault();
        const btn = $(this).find('button[type="submit"]');
        const formData = $(this).serialize();
        btn.prop('disabled', true).text('SAVING...');

        $.ajax({
            url: wshc_dashboard_obj.ajaxurl,
            type: 'POST',
            data: formData + '&action=wshc_save_membership_data&nonce=' + wshc_dashboard_obj.nonce,
            success: function(response) {
                btn.prop('disabled', false).text('Save Changes');
                if (response.success) {
                    $('#membership-data-edit-modal').addClass('hidden');
                    loadMembershipDirectory();
                    showNotification('UPDATED', 'Membership record has been successfully modified.');
                } else {
                    alert(response.data.message);
                }
            }
        });
    });

    $(document).on('click', '.edit-my-profile, .edit-my-profile-link', function(e) {
        e.preventDefault();
        const userId = wshc_dashboard_obj.current_user_id;

        $.ajax({
            url: wshc_dashboard_obj.ajaxurl,
            type: 'POST',
            data: {
                action: 'wshc_get_user_details',
                nonce: wshc_dashboard_obj.nonce,
                user_id: userId
            },
            success: function(response) {
                if (response.success) {
                    const u = response.data;
                    $('#my-form-username').val(u.user_login);
                    $('#my-form-email').val(u.user_email);
                    $('#my-form-password').val('');
                    $('#my-form-confirm-password').val('');

                    // Cooldown Notice
                    if (u.username_cooldown) {
                        $('#username-cooldown-notice').text(u.username_cooldown).removeClass('hidden');
                        $('#my-form-username').prop('disabled', true);
                    } else {
                        $('#username-cooldown-notice').addClass('hidden');
                        $('#my-form-username').prop('disabled', false);
                    }

                    $('#my-profile-modal').removeClass('hidden').hide().fadeIn(300);
                }
            }
        });
    });

    $('#profile-avatar-trigger').on('click', function() {
        $('#profile-avatar-input').click();
    });

    $('#profile-avatar-input').on('change', function() {
        const file = this.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                $('#profile-avatar-trigger img').attr('src', e.target.result);
            }
            reader.readAsDataURL(file);
        }
    });

    $(document).on('click', '#request-deletion-btn', function() {
        if (!confirm('Are you sure you want to request account deletion? Your account will be removed in 48 hours. Logging back in will cancel this request.')) return;

        const btn = $(this);
        btn.prop('disabled', true).text('REQUESTING...');

        $.ajax({
            url: wshc_dashboard_obj.ajaxurl,
            type: 'POST',
            data: {
                action: 'wshc_request_deletion',
                nonce: wshc_dashboard_obj.nonce
            },
            success: function(response) {
                alert(response.data.message);
                window.location.href = wshc_dashboard_obj.logout_url; // Use localized logout URL
            }
        });
    });

    $(document).on('submit', '#wshc-my-profile-form', function(e) {
        e.preventDefault();

        const password = $('#my-form-password').val();
        const confirmPassword = $('#my-form-confirm-password').val();

        if (password !== confirmPassword) {
            alert('Passwords do not match.');
            return;
        }

        const btn = $(this).find('button[type="submit"]');
        const formData = new FormData(this);
        formData.append('action', 'wshc_save_user');
        formData.append('user_id', wshc_dashboard_obj.current_user_id);
        formData.append('nonce', wshc_dashboard_obj.nonce);

        const avatarFile = $('#profile-avatar-input')[0].files[0];
        if (avatarFile) {
            formData.append('profile_avatar', avatarFile);
        }

        btn.prop('disabled', true).text('UPDATING...');

        $.ajax({
            url: wshc_dashboard_obj.ajaxurl,
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                btn.prop('disabled', false).text('UPDATE PROFILE');
                if (response.success) {
                    alert('Profile updated successfully.');
                    window.location.reload();
                } else {
                    alert(response.data.message);
                }
            }
        });
    });

    function triggerEditUser(userId) {
        $.ajax({
            url: wshc_dashboard_obj.ajaxurl,
            type: 'POST',
            data: {
                action: 'wshc_get_user_details',
                nonce: wshc_dashboard_obj.nonce,
                user_id: userId
            },
            success: function(response) {
                if (response.success) {
                    const u = response.data;
                    $('#modal-title').text('EDIT ACCOUNT');
                    $('#form-user-id').val(userId);
                    $('#form-first-name').val(u.first_name);
                    $('#form-last-name').val(u.last_name);
                    $('#form-username').val(u.user_login);
                    $('#form-email').val(u.user_email);
                    $('#form-password').val('');
                    $('#user-modal').removeClass('hidden').hide().fadeIn(300);
                }
            }
        });
    }

    // Real-time Username Availability Check
    let usernameCheckTimer;
    $(document).on('keyup', '#my-form-username, #form-username', function() {
        const input = $(this);
        const username = input.val();
        const userId = input.attr('id') === 'my-form-username' ? wshc_dashboard_obj.current_user_id : $('#form-user-id').val();

        clearTimeout(usernameCheckTimer);

        if (username.length < 4) {
            input.css('border-color', '#d32f2f');
            return;
        }

        usernameCheckTimer = setTimeout(function() {
            $.ajax({
                url: wshc_dashboard_obj.ajaxurl,
                type: 'POST',
                data: {
                    action: 'wshc_check_username',
                    nonce: wshc_dashboard_obj.nonce,
                    username: username,
                    user_id: userId
                },
                success: function(response) {
                    if (response.success) {
                        input.css('border-color', '#2e7d32');
                    } else {
                        input.css('border-color', '#d32f2f');
                        console.log('Username error:', response.data.message);
                    }
                }
            });
        }, 500);
    });

    $(document).on('click', '.close-modal', function() {
        $(this).closest('.wshc-modal').fadeOut(200, function() {
            $(this).addClass('hidden');
        });
    });

    $('#close-modal').on('click', function() {
        $('#user-modal').fadeOut(200, function() {
            $(this).addClass('hidden');
        });
    });

    $(document).on('submit', '#wshc-user-form', function(e) {
        e.preventDefault();
        const btn = $(this).find('button[type="submit"]');
        const formData = $(this).serialize();
        btn.prop('disabled', true).text('SAVING...');
        $.ajax({
            url: wshc_dashboard_obj.ajaxurl,
            type: 'POST',
            data: formData + '&action=wshc_save_user&nonce=' + wshc_dashboard_obj.nonce,
            success: function(response) {
                btn.prop('disabled', false).text('SAVE USER');
                if (response.success) {
                    $('#user-modal').addClass('hidden');
                    loadUserManagement();
                } else {
                    alert(response.data.message);
                }
            }
        });
    });

    // Delete User Modal
    $(document).on('click', '.delete-user', function() {
        const userId = $(this).data('id');
        $('#delete-user-id').val(userId);
        $('#delete-user-modal').removeClass('hidden').hide().fadeIn(200);
    });

    $('#confirm-delete-btn').on('click', function() {
        const userId = $('#delete-user-id').val();
        const btn = $(this);
        btn.prop('disabled', true).text('DELETING...');
        $.ajax({
            url: wshc_dashboard_obj.ajaxurl,
            type: 'POST',
            data: {
                action: 'wshc_delete_user',
                nonce: wshc_dashboard_obj.nonce,
                user_id: userId
            },
            success: function(response) {
                btn.prop('disabled', false).text('Delete Account');
                $('#delete-user-modal').addClass('hidden');
                if (response.success) {
                    loadUserManagement();
                } else {
                    alert(response.data.message);
                }
            }
        });
    });

    // Revert Activity Log
    $(document).on('click', '.revert-btn', function() {
        const logId = $(this).data('id');
        const btn = $(this);

        if (!confirm('Are you sure you want to rollback this system update/action?')) return;

        btn.prop('disabled', true);

        $.ajax({
            url: wshc_dashboard_obj.ajaxurl,
            type: 'POST',
            data: {
                action: 'wshc_revert_log',
                nonce: wshc_dashboard_obj.nonce,
                log_id: logId
            },
            success: function(response) {
                alert(response.data.message);
                if (response.success) {
                    window.location.reload(); // Reload to refresh activity log and stats
                } else {
                    btn.prop('disabled', false);
                }
            }
        });
    });
});
