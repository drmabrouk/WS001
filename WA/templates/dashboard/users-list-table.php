<div class="user-management-header">
    <h1 class="section-title">SYSTEM USERS MANAGEMENT</h1>
    <button id="add-user-btn" class="wshc-auth-btn"><span class="dashicons dashicons-plus"></span> Add New User</button>
</div>

<div class="user-management-controls">
    <div class="search-filter">
        <input type="text" id="user-search" placeholder="Search by name or email...">
        <select id="role-filter">
            <option value="">All Roles</option>
            <option value="administrator">Administrator</option>
            <option value="wshc_secretary_general">Secretary-General</option>
            <option value="wshc_regional_coordinator">Regional Coordinator</option>
            <option value="wshc_programs_manager">Programs Manager</option>
            <option value="wshc_scientific_reviewer">Scientific Reviewer</option>
            <option value="wshc_fellowship_member">Fellowship Member</option>
            <option value="wshc_practitioner_member">Practitioner Member</option>
            <option value="wshc_research_member">Research Member</option>
            <option value="wshc_member">Member</option>
            <option value="subscriber">Subscriber</option>
        </select>
        <select id="status-filter">
            <option value="">All Status</option>
            <option value="active">Active</option>
            <option value="suspended">Suspended</option>
        </select>
    </div>
</div>

<div class="content-panel no-padding">
<table class="wshc-table">
    <thead>
        <tr>
            <th>User Name</th>
            <th>Email Address</th>
            <th>User Role</th>
            <th>Registration Date</th>
            <th>Account Status</th>
            <th style="text-align: right;">Control Actions</th>
        </tr>
    </thead>
    <tbody id="user-table-body">
        <?php foreach ($users as $user) : 
            $roles = $user->roles;
            $role_label = !empty($roles) ? ucwords(str_replace(['_', 'wshc'], [' ', 'WSHC'], $roles[0])) : 'User';
            $suspended = get_user_meta($user->ID, 'wshc_suspended', true);
            $joined_date = date('M d, Y', strtotime($user->user_registered));
        ?>
            <tr>
                <td><strong><?php echo esc_html($user->user_login); ?></strong></td>
                <td><?php echo esc_html($user->user_email); ?></td>
                <td><span class="role-capsule"><?php echo esc_html($role_label); ?></span></td>
                <td><?php echo esc_html($joined_date); ?></td>
                <td>
                    <?php if ($suspended) : ?>
                        <span class="status-capsule suspended">Suspended</span>
                    <?php else : ?>
                        <span class="status-capsule active">Active</span>
                    <?php endif; ?>
                </td>
                <td class="table-actions" style="text-align: right;">
                    <button class="view-user action-btn" data-id="<?php echo $user->ID; ?>" title="View Details">
                        <span class="dashicons dashicons-visibility"></span>
                    </button>
                    <button class="edit-user action-btn" data-id="<?php echo $user->ID; ?>" title="Edit Account">
                        <span class="dashicons dashicons-edit"></span>
                    </button>
                    <button class="toggle-status action-btn" data-id="<?php echo $user->ID; ?>" title="<?php echo $suspended ? 'Reactivate' : 'Suspend'; ?>">
                        <span class="dashicons <?php echo $suspended ? 'dashicons-yes' : 'dashicons-warning'; ?>"></span>
                    </button>
                    <button class="delete-user action-btn" data-id="<?php echo $user->ID; ?>" title="Delete account">
                        <span class="dashicons dashicons-trash"></span>
                    </button>
                </td>
            </tr>
        <?php endforeach; ?>
        <?php if (empty($users)) : ?>
            <tr>
                <td colspan="6" style="text-align: center; padding: 40px; color: #999;">No users found matching your criteria.</td>
            </tr>
        <?php endif; ?>
    </tbody>
</table>
</div>

<div class="pagination" id="user-pagination">
    <!-- Pagination buttons will be here -->
</div>
