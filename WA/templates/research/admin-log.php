<div class="admin-research-workspace">
    <div class="settings-tabs">
        <button class="settings-tab active" data-tab="res-inbox">📥 Inbox (Awaiting Action)</button>
        <button class="settings-tab" data-tab="res-reviews">🔍 Peer Review Assignments</button>
        <button class="settings-tab" data-tab="res-master">🗄️ Master Research Index</button>
    </div>

    <div class="settings-tab-content">
        <!-- Inbox: Pending & Revision -->
        <div id="tab-res-inbox" class="settings-pane active">
            <?php render_admin_research_table($results, ['pending', 'needs_revision']); ?>
        </div>

        <!-- Peer Review: Under Peer Review -->
        <div id="tab-res-reviews" class="settings-pane hidden">
            <?php render_admin_research_table($results, ['under_peer_review']); ?>
        </div>

        <!-- Master Index: Published, Restricted, Rejected -->
        <div id="tab-res-master" class="settings-pane hidden">
            <?php render_admin_research_table($results, ['published', 'restricted', 'rejected']); ?>
        </div>
    </div>
</div>

<?php
function render_admin_research_table($results, $allowed_statuses) {
    $filtered = array_filter($results, function($item) use ($allowed_statuses) {
        return in_array($item->status, $allowed_statuses);
    });
    ?>
    <table class="wshc-table">
        <thead>
            <tr>
                <th>Author / Date</th>
                <th>Research Title</th>
                <th>Status</th>
                <th style="text-align: right;">Regulatory Controls</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($filtered as $item) : ?>
                <tr>
                    <td>
                        <div style="font-size: 12px; font-weight: 700;"><?php echo esc_html($item->display_name); ?></div>
                        <div style="font-size: 10px; color: #999;"><?php echo date('M d, Y', strtotime($item->created_at)); ?></div>
                    </td>
                    <td>
                        <div style="font-weight: 700; font-size: 13px;"><?php echo esc_html($item->title); ?></div>
                        <div style="font-size: 10px; color: #666;"><?php echo esc_html($item->affiliations); ?></div>
                    </td>
                    <td><span class="status-capsule <?php echo $item->status; ?>"><?php echo strtoupper(str_replace('_', ' ', $item->status)); ?></span></td>
                    <td style="text-align: right; white-space: nowrap;">
                        <a href="<?php echo esc_url($item->manuscript_url); ?>" class="action-btn" target="_blank" title="Verify Payload" style="background:#444;"><span class="dashicons dashicons-media-document"></span></a>

                        <?php if ($item->status !== 'published') : ?>
                            <button class="action-btn admin-research-action" data-id="<?php echo $item->id; ?>" data-action="approve" title="Approve & Publish" style="background:#2e7d32;"><span class="dashicons dashicons-yes"></span></button>
                        <?php else : ?>
                            <button class="action-btn admin-research-action" data-id="<?php echo $item->id; ?>" data-action="restrict" title="Restrict/Hide Document" style="background:#f57c00;"><span class="dashicons dashicons-visibility"></span></button>
                        <?php endif; ?>

                        <?php if ($item->status === 'pending') : ?>
                            <button class="action-btn admin-research-action" data-id="<?php echo $item->id; ?>" data-action="assign" title="Delegate Reviewer" style="background:#007cba;"><span class="dashicons dashicons-businessperson"></span></button>
                        <?php endif; ?>

                        <button class="action-btn admin-research-action" data-id="<?php echo $item->id; ?>" data-action="revision" title="Return for Revision" style="background:#444;"><span class="dashicons dashicons-undo"></span></button>
                        <button class="action-btn admin-research-action" data-id="<?php echo $item->id; ?>" data-action="reject" title="Reject Submission" style="background:#d32f2f;"><span class="dashicons dashicons-no"></span></button>
                    </td>
                </tr>
            <?php endforeach; ?>
            <?php if (empty($filtered)) : ?>
                <tr><td colspan="4" style="text-align: center; padding: 30px; color: #999;">No records found in this segment.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
<?php } ?>

<!-- Delegate Reviewer Modal -->
<div id="assign-reviewer-modal" class="wshc-modal hidden">
    <div class="wshc-modal-content" style="max-width: 400px;">
        <h3>Delegate Reviewer</h3>
        <p style="font-size: 12px; color: #666; margin-bottom: 20px;">Assign this manuscript to an internal scientific reviewer for evaluation.</p>
        <select id="reviewer-pool-select" class="wshc-auth-form-group" style="width: 100%; margin-bottom: 20px; padding: 10px;">
            <option value="0">Select internal reviewer...</option>
            <?php
            $reviewers = get_users(['role' => 'wshc_scientific_reviewer']);
            foreach ($reviewers as $rev) : ?>
                <option value="<?php echo $rev->ID; ?>"><?php echo esc_html($rev->display_name); ?></option>
            <?php endforeach; ?>
        </select>
        <div class="modal-actions" style="display: flex; gap: 10px;">
            <button id="confirm-assign-btn" class="wshc-auth-btn" style="flex: 1;">Confirm Delegation</button>
            <button class="wshc-auth-btn close-modal" style="background:#666; flex: 1;">Cancel</button>
        </div>
    </div>
</div>
