<div class="wshc-public-directory-portal">
    <!-- Institutional Onboarding Guide & Policies -->
    <header class="directory-onboarding-section">
        <div class="guide-header">
            <h1>Institutional Membership Guide</h1>
            <p class="guide-subtitle">Official Directory & Onboarding Framework of the Global Council of Sport Health</p>
        </div>

        <div class="onboarding-grid">
            <div class="guide-card">
                <span class="dashicons dashicons-awards"></span>
                <h3>Core Institutional Values</h3>
                <p>Upholding the highest professional and ethical standards in sport health. Our members represent excellence in scientific review, clinical practice, and regional coordination.</p>
            </div>
            <div class="guide-card">
                <span class="dashicons dashicons-index-card"></span>
                <h3>Application Path</h3>
                <p>Prospective members must complete a 6-stage credentials validation wizard. All qualifications are subject to rigorous verification via DataFlow or Quadra Bay.</p>
            </div>
            <div class="guide-card">
                <span class="dashicons dashicons-shield"></span>
                <h3>Regulatory Compliance</h3>
                <p>Membership is governed by institutional bylaws. Approved members are issued unique serial IDs and are subject to annual review and policy adherence.</p>
            </div>
        </div>

        <div class="policy-footer">
            <p>By using this directory, you acknowledge our <a href="#">Terms of Service</a> and <a href="#">Official Review Policies</a>.</p>
        </div>
    </header>

    <div class="directory-divider">
        <span class="divider-text">Verified Members Registry</span>
    </div>

    <!-- Modern Identity Profiles UI -->
    <div class="members-registry-grid">
        <?php if (!empty($members)) : ?>
            <?php foreach ($members as $member) :
                $role_names = [
                    'wshc_member'               => 'Official Member',
                    'wshc_research_member'      => 'Research Member',
                    'wshc_practitioner_member'  => 'Practitioner Member',
                    'wshc_fellowship_member'    => 'Fellowship Member',
                    'wshc_scientific_reviewer'  => 'Scientific Reviewer',
                    'wshc_programs_manager'     => 'Programs Manager',
                    'wshc_regional_coordinator' => 'Regional Coordinator',
                    'wshc_secretary_general'    => 'Secretary-General',
                ];
                $user_data = get_userdata($member->user_id);
                $primary_role = !empty($user_data->roles) ? $user_data->roles[0] : '';
                $category = isset($role_names[$primary_role]) ? $role_names[$primary_role] : 'Council Member';

                // Get Flag URL
                $flag_url = 'https://flagcdn.com/w40/' . strtolower($member->nationality) . '.png';
            ?>
                <div class="member-profile-card">
                    <div class="card-top">
                        <span class="member-category"><?php echo esc_html($category); ?></span>
                        <div class="member-serial">ID: #<?php echo esc_html($member->membership_id); ?></div>
                    </div>

                    <div class="member-avatar">
                        <?php echo get_avatar($member->user_id, 90); ?>
                    </div>

                    <div class="member-info">
                        <h2 class="member-name"><?php echo esc_html($member->full_name); ?></h2>
                        <p class="member-field"><?php echo esc_html($member->major); ?></p>
                    </div>

                    <div class="member-nationality">
                        <img src="<?php echo esc_url($flag_url); ?>" class="country-flag" alt="<?php echo esc_attr($member->nationality); ?>">
                        <span class="country-name"><?php echo esc_html($member->nationality); ?></span>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else : ?>
            <div class="no-members-notice">
                <span class="dashicons dashicons-groups"></span>
                <p>No verified members found in the current registry state.</p>
            </div>
        <?php endif; ?>
    </div>
</div>
