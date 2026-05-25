<?php
$current_user = wp_get_current_user();
$roles = $current_user->roles;
$role_label = !empty($roles) ? ucwords(str_replace(['_', 'wshc'], [' ', 'WSHC'], $roles[0])) : 'User';
$base_url = home_url('/id');

// Dynamic Branding
$admin_roles = ['administrator', 'wshc_secretary_general', 'wshc_regional_coordinator', 'wshc_programs_manager'];
$system_title = in_array($roles[0], $admin_roles) ? 'Management System' : 'MY ACCOUNT';
?>
<div class="wshc-dashboard-wrapper">
    <!-- Top Navbar -->
    <nav class="wshc-top-nav">
        <div class="nav-left">
            <button id="sidebar-toggle" class="sidebar-btn"><span class="dashicons dashicons-menu"></span></button>
            <span class="system-title"><?php echo esc_html($system_title); ?></span>
        </div>
        <div class="nav-right">
            <div class="user-profile-stack">
                <span class="user-name"><?php echo esc_html($current_user->display_name); ?></span>
                <span class="role-capsule rank-capsule"><?php echo esc_html($role_label); ?></span>
            </div>
            <div class="user-avatar-wrap">
                <?php echo get_avatar($current_user->ID, 40); ?>
            </div>
            <div class="nav-settings-dropdown">
                <button class="settings-trigger-btn circular" title="Account Settings">
                    <span class="dashicons dashicons-admin-generic"></span>
                </button>
                <ul class="dropdown-menu">
                    <li><a href="#" class="edit-my-profile-link"><span class="dashicons dashicons-admin-users"></span> Edit Profile Data</a></li>
                    <li><a href="<?php echo wp_logout_url(home_url('/login')); ?>"><span class="dashicons dashicons-exit"></span> Logout</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="dashboard-body">
        <!-- Sidebar -->
        <aside class="wshc-sidebar" id="wshc-sidebar">
            <ul class="nav-menu">
                <?php if (current_user_can('administrator')) : ?>
                    <li>
                        <a href="<?php echo esc_url(add_query_arg('section', 'dashboard-overview', $base_url)); ?>"
                           class="nav-link <?php echo $current_section === 'dashboard-overview' ? 'active' : ''; ?>">
                            <span class="nav-icon dashicons dashicons-dashboard"></span> Dashboard Overview
                        </a>
                    </li>
                    <li>
                        <a href="<?php echo esc_url(add_query_arg('section', 'membership-hub', $base_url)); ?>"
                           class="nav-link <?php echo $current_section === 'membership-hub' ? 'active' : ''; ?>">
                            <span class="nav-icon dashicons dashicons-businessperson"></span> Memberships & Apps
                        </a>
                    </li>
                    <li>
                        <a href="<?php echo esc_url(add_query_arg('section', 'user-management', $base_url)); ?>"
                           class="nav-link <?php echo $current_section === 'user-management' ? 'active' : ''; ?>">
                            <span class="nav-icon dashicons dashicons-groups"></span> User Management
                        </a>
                    </li>
                    <li>
                        <a href="<?php echo esc_url(add_query_arg('section', 'settings-system', $base_url)); ?>"
                           class="nav-link <?php echo $current_section === 'settings-system' ? 'active' : ''; ?>">
                            <span class="nav-icon dashicons dashicons-admin-settings"></span> Settings
                        </a>
                    </li>
                <?php else : ?>
                    <li>
                        <a href="<?php echo esc_url(add_query_arg('section', 'my-account', $base_url)); ?>"
                           class="nav-link <?php echo $current_section === 'my-account' ? 'active' : ''; ?>">
                            <span class="nav-icon dashicons dashicons-admin-users"></span> My Account
                        </a>
                    </li>
                    <?php if (current_user_can('wshc_visitor') || current_user_can('subscriber')) : ?>
                        <li>
                            <a href="<?php echo esc_url(add_query_arg('section', 'info-apply', $base_url)); ?>"
                               class="nav-link <?php echo $current_section === 'info-apply' ? 'active' : ''; ?>">
                                <span class="nav-icon dashicons dashicons-info"></span> Information
                            </a>
                        </li>
                    <?php endif; ?>
                <?php endif; ?>
            </ul>
        </aside>

        <!-- Main Content -->
        <main class="wshc-content" id="wshc-main-content">
            <?php if (current_user_can('administrator')) : ?>
                <!-- Dashboard Overview Section -->
                <div id="section-dashboard-overview" class="dashboard-section <?php echo $current_section === 'dashboard-overview' ? '' : 'hidden'; ?>">
                    <h1 class="section-title">DASHBOARD OVERVIEW</h1>

                    <div class="stats-grid">
                        <div class="stat-card users">
                            <div class="stat-icon dashicons dashicons-groups"></div>
                            <div class="stat-info">
                                <span class="stat-label">Total Users</span>
                                <span class="stat-value"><?php echo number_format($stats['total_users']); ?></span>
                            </div>
                        </div>
                        <div class="stat-card active">
                            <div class="stat-icon dashicons dashicons-yes-alt"></div>
                            <div class="stat-info">
                                <span class="stat-label">Active Accounts</span>
                                <span class="stat-value"><?php echo number_format($stats['active_users']); ?></span>
                            </div>
                        </div>
                        <div class="stat-card suspended">
                            <div class="stat-icon dashicons dashicons-dismiss"></div>
                            <div class="stat-info">
                                <span class="stat-label">Suspended</span>
                                <span class="stat-value"><?php echo number_format($stats['suspended_users']); ?></span>
                            </div>
                        </div>
                        <div class="stat-card admins">
                            <div class="stat-icon dashicons dashicons-id"></div>
                            <div class="stat-info">
                                <span class="stat-label">Pending Apps</span>
                                <span class="stat-value"><?php echo number_format($stats['pending_apps'] ?? 0); ?></span>
                            </div>
                        </div>
                    </div>

                    <div class="dashboard-secondary-grid">
                        <div class="content-panel">
                            <h2 class="section-title">RECENT SYSTEM ACTIVITIES</h2>
                            <table class="wshc-table compact">
                                <thead>
                                    <tr>
                                        <th>Actor</th>
                                        <th>Event</th>
                                        <th>Information</th>
                                        <th>Date</th>
                                        <th style="text-align: right;">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($stats['recent_logs'] as $log) :
                                        $admin = get_userdata($log->user_id);
                                        $action_label = ucwords(str_replace('_', ' ', $log->action));
                                    ?>
                                        <tr>
                                            <td><strong><?php echo $admin ? esc_html($admin->display_name) : 'System'; ?></strong></td>
                                            <td><span class="action-tag <?php echo esc_attr($log->action); ?>"><?php echo esc_html($action_label); ?></span></td>
                                            <td style="font-size: 12px; color: #666;"><?php echo esc_html($log->details); ?></td>
                                            <td><?php echo date('M d, H:i', strtotime($log->created_at)); ?></td>
                                            <td style="text-align: right;">
                                                <?php if ($log->action !== 'rollback') : ?>
                                                    <button class="revert-btn action-btn" data-id="<?php echo $log->id; ?>" title="Rollback Action">
                                                        <span class="dashicons dashicons-undo"></span>
                                                    </button>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                    <?php if (empty($stats['recent_logs'])) : ?>
                                        <tr>
                                            <td colspan="5" style="text-align: center; padding: 30px; color: #999;">No recent activities found in the last 48 hours.</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- User Management Section -->
                <div id="section-user-management" class="dashboard-section <?php echo $current_section === 'user-management' ? '' : 'hidden'; ?>">
                    <div id="user-management-container">
                        <!-- User list will be loaded here -->
                    </div>
                </div>

                <!-- Unified Settings Section -->
                <div id="section-settings-system" class="dashboard-section <?php echo $current_section === 'settings-system' ? '' : 'hidden'; ?>">
                    <h1 class="section-title">SYSTEM SETTINGS</h1>

                    <div class="settings-tabs">
                        <button class="settings-tab active" data-tab="design-settings">Design Settings</button>
                        <button class="settings-tab" data-tab="auth-config">Auth Configuration</button>
                        <button class="settings-tab" data-tab="system-data">System Data</button>
                    </div>

                    <div class="settings-tab-content">
                        <div id="tab-design-settings" class="settings-pane active">
                            <div class="content-panel">
                                <h3>Design Configuration</h3>
                                <p>Configure the visual appearance and branding of the management system.</p>
                            </div>
                        </div>
                        <div id="tab-auth-config" class="settings-pane hidden">
                            <div class="content-panel">
                                <h3>Authentication Control Panel</h3>
                                <div class="settings-row" style="margin-bottom: 25px;">
                                    <label class="switch-label">Enable Registration System</label>
                                    <input type="checkbox" id="enable-reg" checked>
                                </div>
                                <div class="settings-row" style="margin-bottom: 25px;">
                                    <label class="switch-label">Enable Login System</label>
                                    <input type="checkbox" id="enable-login" checked>
                                </div>
                                <div class="wshc-auth-form-group">
                                    <label>OTP Confirmation Email Message</label>
                                    <textarea id="otp-message" style="height: 120px;" placeholder="Your OTP code is: {otp}"></textarea>
                                </div>
                                <div class="wshc-auth-form-group">
                                    <label>Welcome Email Message</label>
                                    <textarea id="welcome-message" style="height: 120px;" placeholder="Welcome to our system!"></textarea>
                                </div>
                                <button id="save-auth-settings" class="wshc-auth-btn" style="width: auto;">Save Configurations</button>
                            </div>
                        </div>
                        <div id="tab-system-data" class="settings-pane hidden">
                            <div class="content-panel">
                                <h3>System Data Management</h3>
                                <p>Manage and export system-level data, backups, and logs.</p>
                                <div class="settings-actions" style="margin-top: 20px; display: flex; gap: 15px;">
                                    <button id="export-data-btn" class="wshc-auth-btn" style="width: auto;">Export System Data</button>
                                    <button id="import-data-btn" class="wshc-auth-btn" style="width: auto; background: #666;">Import Data Package</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Membership Hub Section (Unified) -->
                <div id="section-membership-hub" class="dashboard-section <?php echo $current_section === 'membership-hub' ? '' : 'hidden'; ?>">
                    <h1 class="section-title">MEMBERSHIPS & APPLICATIONS</h1>

                    <div class="settings-tabs">
                        <button class="settings-tab active" data-tab="hub-directory">Members Directory</button>
                        <button class="settings-tab" data-tab="hub-apps">Pending Applications</button>
                    </div>

                    <div class="settings-tab-content">
                        <div id="tab-hub-directory" class="settings-pane active">
                            <div id="membership-dir-container"></div>
                        </div>
                        <div id="tab-hub-apps" class="settings-pane hidden">
                            <div id="membership-apps-container"></div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Visitor Information & Apply Section -->
            <div id="section-info-apply" class="dashboard-section <?php echo ($current_section === 'info-apply' || (empty($current_section) && (current_user_can('wshc_visitor') || current_user_can('subscriber')))) ? '' : 'hidden'; ?>">
                <h1 class="section-title">MEMBERSHIP APPLICATION WIZARD</h1>

                <?php
                global $wpdb;
                $user_id = get_current_user_id();
                $application = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$wpdb->prefix}wshc_membership_applications WHERE user_id = %d ORDER BY created_at DESC LIMIT 1", $user_id));

                if ($application) :
                    $statuses = ['pending' => 1, 'approved' => 2, 'rejected' => 2];
                    $current_status_step = $statuses[$application->status] ?? 1;
                ?>
                    <div class="content-panel" style="margin-bottom: 30px;">
                        <h3>Application Tracking Timeline</h3>
                        <div class="wizard-progress tracking">
                            <div class="wizard-step completed">1. Submitted</div>
                            <div class="wizard-step <?php echo $application->status === 'pending' ? 'active' : 'completed'; ?>">2. Under Review</div>
                            <div class="wizard-step <?php echo $application->status === 'approved' ? 'active' : ''; ?> <?php echo $application->status === 'rejected' ? 'rejected' : ''; ?>">
                                3. <?php echo $application->status === 'rejected' ? 'Rejected' : 'Approved'; ?>
                            </div>
                        </div>
                        <p style="font-size: 13px; color: #666; margin-top: 15px;">
                            Your application was submitted on <?php echo date('M d, Y', strtotime($application->created_at)); ?>.
                            Status: <strong><?php echo strtoupper($application->status); ?></strong>
                        </p>
                        <?php if ($application->status === 'pending' && !empty($application->admin_note)) : ?>
                            <div class="admin-clarification-box" style="background: #fff8e1; border: 1px solid #ffe082; padding: 20px; border-radius: 8px; margin-top: 20px;">
                                <div style="font-weight: 800; font-size: 11px; color: #f57c00; text-transform: uppercase; margin-bottom: 8px; display: flex; align-items: center; gap: 8px;">
                                    <span class="dashicons dashicons-warning"></span> Administrative Action Required
                                </div>
                                <p style="margin: 0; font-size: 13px; color: #5d4037; line-height: 1.5;"><?php echo nl2br(esc_html($application->admin_note)); ?></p>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>

                <div class="content-panel <?php echo ($application && $application->status === 'pending') ? 'hidden' : ''; ?>">
                    <div class="wizard-progress">
                        <div class="wizard-step active" data-step="1">1. Policy</div>
                        <div class="wizard-step" data-step="2">2. Personal</div>
                        <div class="wizard-step" data-step="3">3. Academic</div>
                        <div class="wizard-step" data-step="4">4. Professional</div>
                        <div class="wizard-step" data-step="5">5. Credentials</div>
                        <div class="wizard-step" data-step="6">6. Research</div>
                    </div>

                    <form id="membership-application-wizard" class="wshc-wizard-form" enctype="multipart/form-data">
                        <!-- Stage 1: Policy & Bylaws -->
                        <div class="wizard-pane active" id="pane-1">
                            <h3>Institutional Regulations & Policies</h3>
                            <div class="policy-content" style="height: 300px; overflow-y: auto; padding: 20px; background: #f9f9f9; border: 1px solid #eee; font-size: 13px; line-height: 1.6; margin-bottom: 20px; border-radius: 8px;">
                                <h4>1. Membership Duration</h4>
                                <p>Membership in the Global Council of Sport Health is valid for a period of exactly one calendar year (365 days) from the date of administrative approval.</p>
                                <h4>2. Institutional Bylaws</h4>
                                <p>All members must adhere to the professional and ethical standards set forth by the Council. Qualifications must be legally accredited and verifiable.</p>
                                <h4>3. Data Privacy</h4>
                                <p>Your data will be used solely for membership evaluation and institutional records.</p>
                                <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.</p>
                            </div>
                            <div class="wshc-auth-form-group">
                                <label style="display: flex; align-items: center; gap: 10px; cursor: pointer;">
                                    <input type="checkbox" id="policy-agree" required style="width: auto;">
                                    <span>I have read and agree to the institutional bylaws and membership policies.</span>
                                </label>
                            </div>
                        </div>

                        <!-- Stage 2: Personal Info -->
                        <div class="wizard-pane hidden" id="pane-2">
                            <h3>Personal Information</h3>
                            <div class="wshc-auth-grid">
                                <div class="wshc-auth-form-group">
                                    <label>Full Legal Name</label>
                                    <input type="text" name="full_name" value="<?php echo esc_attr($current_user->display_name); ?>" required placeholder="Legal full name">
                                </div>
                                <div class="wshc-auth-form-group">
                                    <label>Date of Birth</label>
                                    <input type="date" name="dob" required>
                                </div>
                            </div>
                            <div class="wshc-auth-grid">
                                <div class="wshc-auth-form-group">
                                    <label>Gender</label>
                                    <select name="gender" required>
                                        <option value="Male">Male</option>
                                        <option value="Female">Female</option>
                                        <option value="Other">Other</option>
                                    </select>
                                </div>
                                <div class="wshc-auth-form-group">
                                    <label>Nationality</label>
                                    <input type="text" name="nationality" placeholder="Country of Nationality" required>
                                </div>
                            </div>
                            <div class="wshc-auth-grid">
                                <div class="wshc-auth-form-group">
                                    <label>Country of Residence</label>
                                    <input type="text" name="country_residence" required>
                                </div>
                                <div class="wshc-auth-form-group">
                                    <label>City of Residence</label>
                                    <input type="text" name="city_residence" required>
                                </div>
                            </div>
                            <div class="wshc-auth-grid">
                                <div class="wshc-auth-form-group">
                                    <label>Primary Email Address</label>
                                    <input type="email" name="email" value="<?php echo esc_attr($current_user->user_email); ?>" required>
                                </div>
                                <div class="wshc-auth-form-group">
                                    <label>Primary Phone Number</label>
                                    <input type="tel" name="phone" placeholder="Include country code" required>
                                </div>
                            </div>
                            <div class="wshc-auth-grid">
                                <div class="wshc-auth-form-group">
                                    <label>Secondary Phone Number</label>
                                    <input type="tel" name="phone_secondary" placeholder="Optional alternative">
                                </div>
                                <div class="wshc-auth-form-group"></div>
                            </div>
                        </div>

                        <!-- Stage 3: Academic Background -->
                        <div class="wizard-pane hidden" id="pane-3">
                            <h3>Academic Background</h3>
                            <div style="background: #e3f2fd; padding: 15px; border-radius: 8px; margin-bottom: 20px; font-size: 13px; color: #0d47a1;">
                                <strong>Directive:</strong> Only legally accredited qualifications are recognized. If you hold a Master's or Ph.D., please upload it as your primary qualification.
                            </div>
                            <div class="wshc-auth-grid">
                                <div class="wshc-auth-form-group">
                                    <label>Highest Degree</label>
                                    <select name="degree" required>
                                        <option value="Ph.D.">Ph.D.</option>
                                        <option value="Master's">Master's</option>
                                        <option value="Bachelor's">Bachelor's</option>
                                        <option value="Higher Diploma">Higher Diploma</option>
                                    </select>
                                </div>
                                <div class="wshc-auth-form-group">
                                    <label>Graduation Year</label>
                                    <input type="number" name="grad_year" min="1950" max="2025" required>
                                </div>
                            </div>
                            <div class="wshc-auth-grid">
                                <div class="wshc-auth-form-group">
                                    <label>Major/Field of Study</label>
                                    <input type="text" name="major" placeholder="e.g. Sports Medicine" required>
                                </div>
                                <div class="wshc-auth-form-group">
                                    <label>University/Institution</label>
                                    <input type="text" name="institution" required>
                                </div>
                            </div>
                            <div class="wshc-auth-grid">
                                <div class="wshc-auth-form-group">
                                    <label>Degree Certificate</label>
                                    <input type="file" name="cert_file" accept=".pdf,.jpg,.jpeg,.png">
                                </div>
                                <div class="wshc-auth-form-group">
                                    <label>Verification Document (DataFlow/Quadra Bay)</label>
                                    <input type="file" name="verification_file" accept=".pdf">
                                </div>
                            </div>
                        </div>

                        <!-- Stage 4: Professional Status -->
                        <div class="wizard-pane hidden" id="pane-4">
                            <h3>Professional Status</h3>
                            <div class="wshc-auth-grid">
                                <div class="wshc-auth-form-group">
                                    <label>Current Job Title</label>
                                    <input type="text" name="job_title" placeholder="e.g. Team Physician" required>
                                </div>
                                <div class="wshc-auth-form-group">
                                    <label>Years of Experience</label>
                                    <input type="number" name="experience" min="0" required>
                                </div>
                            </div>
                            <div class="wshc-auth-grid">
                                <div class="wshc-auth-form-group">
                                    <label>Current Country of Work</label>
                                    <input type="text" name="work_country" required>
                                </div>
                                <div class="wshc-auth-form-group">
                                    <label>State/Province/Department</label>
                                    <input type="text" name="work_state" required>
                                </div>
                            </div>
                            <div class="wshc-auth-grid">
                                <div class="wshc-auth-form-group">
                                    <label>Current Employer/Organization</label>
                                    <input type="text" name="employer" required>
                                </div>
                                <div class="wshc-auth-form-group">
                                    <label>CV / Comprehensive Resume</label>
                                    <input type="file" name="cv_file" accept=".pdf,.doc,.docx">
                                </div>
                            </div>
                        </div>

                        <!-- Stage 5: Licensing & Credentials -->
                        <div class="wizard-pane hidden" id="pane-5">
                            <h3 style="font-weight: 900; letter-spacing: 0.5px;">LICENSING & CREDENTIALS</h3>
                            <div class="wshc-auth-grid">
                                <div class="wshc-auth-form-group">
                                    <label style="font-size: 11px; color: #1a1a1a;">PROFESSIONAL LICENSE NUMBER</label>
                                    <input type="text" name="license_number" placeholder="Enter regulatory ministry code" style="border-width: 2px;">
                                </div>
                                <div class="wshc-auth-form-group">
                                    <label style="font-size: 11px; color: #1a1a1a;">SPECIALIZED CERTIFICATIONS</label>
                                    <textarea name="specialized_certs" placeholder="CPR, FIFA certifications, NSCA, etc." style="border-width: 2px; height: 80px;"></textarea>
                                </div>
                            </div>
                            <div class="wshc-auth-grid">
                                <div class="wshc-auth-form-group">
                                    <label style="font-size: 11px; color: #1a1a1a;">OTHER MEMBERSHIPS</label>
                                    <textarea name="other_memberships" placeholder="List active professional associations" style="border-width: 2px; height: 80px;"></textarea>
                                </div>
                                <div class="wshc-auth-form-group"></div>
                            </div>
                        </div>

                        <!-- Stage 6: Research & Membership -->
                        <div class="wizard-pane hidden" id="pane-6">
                            <h3 style="font-weight: 900; letter-spacing: 0.5px;">RESEARCH & FINANCE</h3>
                            <div class="wshc-auth-grid">
                                <div class="wshc-auth-form-group">
                                    <label style="font-size: 11px; color: #1a1a1a;">RESEARCH & PUBLICATIONS</label>
                                    <textarea name="research_publications" placeholder="Links and titles of scientific journal articles" style="border-width: 2px; height: 120px;"></textarea>
                                </div>
                                <div class="wshc-auth-form-group">
                                    <label style="font-size: 11px; color: #1a1a1a;">CORE AREAS OF INTEREST</label>
                                    <select name="interests[]" multiple style="height: 120px; border-width: 2px;">
                                        <option value="Athletic Injuries">Athletic Injuries</option>
                                        <option value="Sports Nutrition">Sports Nutrition</option>
                                        <option value="Exercise Physiology">Exercise Physiology</option>
                                        <option value="Sports Psychology">Sports Psychology</option>
                                        <option value="Biomechanics">Biomechanics</option>
                                        <option value="Kinesiology">Kinesiology</option>
                                    </select>
                                </div>
                            </div>

                            <div class="gratis-box" style="margin-top: 30px; padding: 30px; background: #000; color: #fff; border-radius: 12px; text-align: center;">
                                <div class="dashicons dashicons-awards" style="font-size: 40px; width: 40px; height: 40px; margin-bottom: 15px;"></div>
                                <h4 style="margin: 0 0 10px; font-weight: 800;">100% GRATIS MEMBERSHIP</h4>
                                <p style="margin: 0; font-size: 14px; opacity: 0.8;">Your membership is entirely free for exactly one year starting from the moment of administrative approval.</p>
                            </div>
                        </div>

                        <div class="wizard-actions" style="margin-top: 30px; display: flex; gap: 15px;">
                            <button type="button" id="prev-step" class="wshc-auth-btn hidden" style="background: #666;">Previous</button>
                            <button type="button" id="next-step" class="wshc-auth-btn">Next Step</button>
                            <button type="submit" id="submit-wizard" class="wshc-auth-btn hidden">Complete Application</button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- My Account Section -->
            <div id="section-my-account" class="dashboard-section <?php echo ($current_section === 'my-account' || (empty($current_section) && !current_user_can('administrator') && !current_user_can('wshc_visitor'))) ? '' : 'hidden'; ?>">
                <h1 class="section-title">MY ACCOUNT & PROFILE</h1>
                <div class="content-panel">
                    <div class="user-profile-summary">
                        <h3>Welcome, <?php echo esc_html($current_user->display_name); ?></h3>
                        <div style="margin-bottom: 20px;">
                            <span class="role-capsule rank-capsule"><?php echo esc_html($role_label); ?></span>
                            <button class="wshc-auth-btn edit-my-profile" style="width: auto; padding: 5px 15px; font-size: 10px; margin-left: 10px;">Edit Profile</button>
                        </div>

                        <div class="profile-details-grid" style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-top: 30px;">
                            <div class="detail-item">
                                <label style="display:block; font-size: 10px; font-weight:800; color: #888;">FULL NAME</label>
                                <div style="font-weight: 700;"><?php echo esc_html($current_user->first_name . ' ' . $current_user->last_name); ?></div>
                            </div>
                            <div class="detail-item">
                                <label style="display:block; font-size: 10px; font-weight:800; color: #888;">EMAIL ADDRESS</label>
                                <div style="font-weight: 700;"><?php echo esc_html($current_user->user_email); ?></div>
                            </div>
                        </div>

                        <?php
                        $mid = get_user_meta($current_user->ID, 'wshc_membership_id', true);
                        if ($mid) :
                            $expiry = get_user_meta($current_user->ID, 'wshc_membership_expiry', true);
                            $days_left = ceil((strtotime($expiry) - time()) / 86400);
                        ?>
                            <div class="membership-status-box" style="margin-top: 40px; padding: 25px; border: 1.5px solid #000; border-radius: 12px;">
                                <div style="font-weight: 800; font-size: 11px; text-transform: uppercase; color: #666; margin-bottom: 15px;">Membership Activation Parameters</div>
                                <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px;">
                                    <div>
                                        <div style="font-size: 20px; font-weight: 800;">#<?php echo esc_html($mid); ?></div>
                                        <div style="font-size: 10px; color: #888;">UNIQUE ID</div>
                                    </div>
                                    <div>
                                        <div style="font-size: 20px; font-weight: 800;"><?php echo date('M d, Y', strtotime($expiry)); ?></div>
                                        <div style="font-size: 10px; color: #888;">EXPIRATION</div>
                                    </div>
                                    <div>
                                        <div style="font-size: 20px; font-weight: 800; color: #d32f2f;"><?php echo max(0, $days_left); ?> Days</div>
                                        <div style="font-size: 10px; color: #888;">REMAINING</div>
                                    </div>
                                </div>
                            </div>
                        <?php else : ?>
                            <div class="content-panel" style="margin-top: 40px; background: #f9f9f9; border: none;">
                                <p style="color: #666; font-size: 13px;">No active membership records found. Please complete the application wizard to activate your account profile.</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<!-- User Form Modal -->
<div id="user-modal" class="wshc-modal hidden">
    <div class="wshc-modal-content">
        <h2 id="modal-title">ADD NEW USER</h2>
        <form id="wshc-user-form">
            <input type="hidden" name="user_id" id="form-user-id">

            <div class="wshc-auth-grid">
                <div class="wshc-auth-form-group">
                    <input type="text" name="first_name" id="form-first-name" placeholder="First Name">
                </div>
                <div class="wshc-auth-form-group">
                    <input type="text" name="last_name" id="form-last-name" placeholder="Last Name">
                </div>
            </div>

            <div class="wshc-auth-grid">
                <div class="wshc-auth-form-group">
                    <input type="text" name="username" id="form-username" placeholder="Username" required minlength="4">
                </div>
                <div class="wshc-auth-form-group">
                    <input type="email" name="email" id="form-email" placeholder="Email Address" required>
                </div>
            </div>

            <div class="wshc-auth-grid">
                <div class="wshc-auth-form-group">
                    <input type="password" name="password" id="form-password" placeholder="Password (8-20 chars)" minlength="8" maxlength="20">
                    <span class="password-toggle dashicons dashicons-visibility"></span>
                </div>
                <div class="wshc-auth-form-group">
                    <select name="role" id="form-role" required>
                        <option value="" disabled selected>Select System Role</option>
                        <option value="subscriber">Subscriber</option>
                        <option value="wshc_member">Member</option>
                        <option value="wshc_research_member">Research Member</option>
                        <option value="wshc_practitioner_member">Practitioner Member</option>
                        <option value="wshc_fellowship_member">Fellowship Member</option>
                        <option value="wshc_scientific_reviewer">Scientific Reviewer</option>
                        <option value="wshc_programs_manager">Programs Manager</option>
                        <option value="wshc_regional_coordinator">Regional Coordinator</option>
                        <option value="wshc_secretary_general">Secretary-General</option>
                        <option value="administrator">Administrator</option>
                    </select>
                </div>
            </div>

            <div class="modal-actions">
                <button type="submit" class="wshc-auth-btn">Save User</button>
                <button type="button" class="wshc-auth-btn close-modal" style="background:#666;">Cancel</button>
            </div>
        </form>
    </div>
</div>

<!-- User Details Modal -->
<div id="user-details-modal" class="wshc-modal hidden">
    <div class="wshc-modal-content">
        <h2>USER DETAILS</h2>
        <div id="user-details-content">
            <!-- Details will be loaded here -->
        </div>
        <div class="modal-actions">
            <button type="button" class="wshc-auth-btn close-modal">Close Information</button>
        </div>
    </div>
</div>

<!-- Confirm Delete Modal -->
<div id="delete-user-modal" class="wshc-modal hidden">
    <div class="wshc-modal-content">
        <h2>CONFIRM DELETION</h2>
        <p>Are you sure you want to permanently delete this user? This action cannot be undone.</p>
        <input type="hidden" id="delete-user-id">
        <div class="modal-actions">
            <button type="button" id="confirm-delete-btn" class="wshc-auth-btn" style="background: #d32f2f;">Delete Account</button>
            <button type="button" class="wshc-auth-btn close-modal" style="background: #666;">Cancel</button>
        </div>
    </div>
</div>

<!-- Confirm Status Toggle Modal -->
<div id="status-user-modal" class="wshc-modal hidden">
    <div class="wshc-modal-content">
        <h2 id="status-modal-title">UPDATE ACCOUNT STATUS</h2>
        <p id="status-modal-message"></p>
        <input type="hidden" id="status-user-id">

        <div id="suspension-advanced-fields" class="hidden">
            <div class="wshc-auth-form-group">
                <label>Reason for Suspension</label>
                <select id="suspension-reason">
                    <option value="Policy Violation">Policy Violation</option>
                    <option value="Spamming Activity">Spamming Activity</option>
                    <option value="Suspicious Login">Suspicious Login</option>
                    <option value="Unprofessional Behavior">Unprofessional Behavior</option>
                    <option value="Account Compromised">Account Compromised</option>
                    <option value="Duplicate Account">Duplicate Account</option>
                    <option value="Non-Payment">Non-Payment</option>
                    <option value="Requested by User">Requested by User</option>
                    <option value="Inactivity">Inactivity</option>
                    <option value="Under Investigation">Under Investigation</option>
                </select>
            </div>
            <div class="wshc-auth-form-group">
                <label>Suspension Duration (Days)</label>
                <input type="number" id="suspension-duration" placeholder="e.g. 30" min="1">
            </div>
        </div>

        <div class="modal-actions">
            <button type="button" id="confirm-status-btn" class="wshc-auth-btn">Confirm Change</button>
            <button type="button" class="wshc-auth-btn close-modal" style="background: #666;">Cancel</button>
        </div>
    </div>
</div>

<!-- Global Notification Modal -->
<div id="wshc-notification-modal" class="wshc-modal hidden">
    <div class="wshc-modal-content" style="text-align: center; padding: 40px;">
        <div id="notification-icon" class="dashicons" style="font-size: 60px; width: 60px; height: 60px; margin-bottom: 20px;"></div>
        <h2 id="notification-title" style="margin-bottom: 10px;">SUCCESS</h2>
        <p id="notification-message" style="color: #666; margin-bottom: 30px;"></p>
        <button type="button" class="wshc-auth-btn close-modal" style="width: auto; padding: 10px 40px;">CONTINUE</button>
    </div>
</div>

<!-- Application Dossier Modal -->
<div id="app-dossier-modal" class="wshc-modal hidden">
    <div class="wshc-modal-content" style="max-width: 800px; height: 90vh; display: flex; flex-direction: column;">
        <h2 style="border-bottom: 2px solid #eee; padding-bottom: 15px;">MEMBERSHIP DOSSIER</h2>
        <div id="app-dossier-content" style="flex: 1; overflow-y: auto; padding-right: 15px;">
            <!-- Content dynamic -->
        </div>
        <div style="margin-top: 25px; padding-top: 20px; border-top: 2px solid #eee;">
            <div class="wshc-auth-form-group">
                <label>ADMIN CLARIFICATION DISPATCH (Notes for Applicant)</label>
                <textarea id="admin-clarification-note" placeholder="Write missing requirements or instructions for the user..." style="height: 80px;"></textarea>
            </div>
            <div class="modal-actions" style="display: flex; gap: 10px;">
                <button type="button" id="send-clarification-btn" class="wshc-auth-btn" style="background: #444;">Dispatch Clarification</button>
                <button type="button" class="wshc-auth-btn close-modal" style="background: #666; width: auto;">Close Dossier</button>
            </div>
        </div>
    </div>
</div>

<!-- My Profile Edit Modal -->
<div id="my-profile-modal" class="wshc-modal hidden">
    <div class="wshc-modal-content">
        <h2>EDIT PROFILE DATA</h2>
        <form id="wshc-my-profile-form">
            <div class="wshc-auth-grid">
                <div class="wshc-auth-form-group">
                    <label>Username</label>
                    <input type="text" name="username" id="my-form-username" required minlength="4">
                </div>
                <div class="wshc-auth-form-group">
                    <label>Email Address</label>
                    <input type="email" name="email" id="my-form-email" required>
                </div>
            </div>
            <div class="wshc-auth-form-group">
                <label>New Password (leave blank to keep current)</label>
                <input type="password" name="password" id="my-form-password" minlength="8" maxlength="20">
                <span class="password-toggle dashicons dashicons-visibility"></span>
            </div>

            <div style="margin-top: 30px; border-top: 1px solid #eee; padding-top: 20px;">
                <button type="button" id="request-deletion-btn" class="wshc-auth-btn" style="background: #d32f2f; width: auto; font-size: 11px;">Request Account Deletion</button>
            </div>

            <div class="modal-actions">
                <button type="submit" class="wshc-auth-btn">Update Profile</button>
                <button type="button" class="wshc-auth-btn close-modal" style="background: #666;">Cancel</button>
            </div>
        </form>
    </div>
</div>
