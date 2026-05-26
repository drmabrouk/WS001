<table class="wshc-table">
    <thead>
        <tr>
            <th>Author / Date</th>
            <th>Research Title</th>
            <th>Type</th>
            <th>Review Status</th>
            <th style="text-align: right;">Regulatory Controls</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($results as $item) : ?>
            <tr>
                <td>
                    <div style="font-size: 13px; font-weight: 700;"><?php echo esc_html($item->display_name); ?></div>
                    <div style="font-size: 10px; color: #999;"><?php echo date('M d, Y', strtotime($item->created_at)); ?></div>
                </td>
                <td>
                    <div style="font-weight: 800;"><?php echo esc_html($item->title); ?></div>
                    <div style="font-size: 11px; color: #666;"><?php echo esc_html($item->affiliations); ?></div>
                </td>
                <td><span class="role-capsule"><?php echo esc_html($item->doc_type); ?></span></td>
                <td><span class="status-capsule <?php echo $item->status; ?>"><?php echo strtoupper($item->status); ?></span></td>
                <td style="text-align: right; white-space: nowrap;">
                    <a href="<?php echo esc_url($item->manuscript_url); ?>" class="action-btn" target="_blank" title="Verify PDF" style="background:#444;"><span class="dashicons dashicons-pdf"></span></a>

                    <?php if ($item->status !== 'published') : ?>
                        <button class="action-btn admin-research-action" data-id="<?php echo $item->id; ?>" data-action="approve" title="Approve & Publish" style="background:#2e7d32;"><span class="dashicons dashicons-yes"></span></button>
                    <?php else : ?>
                        <button class="action-btn admin-research-action" data-id="<?php echo $item->id; ?>" data-action="restrict" title="Restrict View" style="background:#f57c00;"><span class="dashicons dashicons-visibility"></span></button>
                    <?php endif; ?>

                    <?php if ($item->status === 'pending') : ?>
                        <button class="action-btn admin-research-action" data-id="<?php echo $item->id; ?>" data-action="assign" title="Assign Reviewer" style="background:#007cba;"><span class="dashicons dashicons-businessperson"></span></button>
                    <?php endif; ?>

                    <button class="action-btn admin-research-action" data-id="<?php echo $item->id; ?>" data-action="revision" title="Return for Revision" style="background:#444;"><span class="dashicons dashicons-undo"></span></button>
                    <button class="action-btn admin-research-action" data-id="<?php echo $item->id; ?>" data-action="reject" title="Reject Permanently" style="background:#d32f2f;"><span class="dashicons dashicons-no"></span></button>
                </td>
            </tr>
        <?php endforeach; ?>
        <?php if (empty($results)) : ?>
            <tr><td colspan="5" style="text-align: center; padding: 40px;">No research submissions found in log.</td></tr>
        <?php endif; ?>
    </tbody>
</table>

<!-- Reviewer Assignment Modal Snippet (Managed by JS) -->
<div id="assign-reviewer-modal" class="wshc-modal hidden">
    <div class="wshc-modal-content" style="max-width: 400px;">
        <h3>Delegate Reviewer</h3>
        <select id="reviewer-pool-select" class="wshc-auth-form-group">
            <option value="0">Select internal reviewer...</option>
            <?php
            $reviewers = get_users(['role' => 'wshc_scientific_reviewer']);
            foreach ($reviewers as $rev) : ?>
                <option value="<?php echo $rev->ID; ?>"><?php echo esc_html($rev->display_name); ?></option>
            <?php endforeach; ?>
        </select>
        <div class="modal-actions">
            <button id="confirm-assign-btn" class="wshc-auth-btn">Confirm Delegation</button>
            <button class="wshc-auth-btn close-modal" style="background:#666;">Cancel</button>
        </div>
    </div>
</div>
