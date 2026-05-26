<div class="admin-research-workspace">
    <div class="settings-tabs minimalist-tabs">
        <button class="settings-tab active" data-tab="res-inbox">Inbox (Awaiting Action)</button>
        <button class="settings-tab" data-tab="res-reviews">Peer Review Assignments</button>
        <button class="settings-tab" data-tab="res-master">Master Research Index</button>
    </div>

    <div class="settings-tab-content">
        <div id="tab-res-inbox" class="settings-pane active">
            <?php render_admin_research_table($results, ['pending', 'needs_revision']); ?>
        </div>
        <div id="tab-res-reviews" class="settings-pane hidden">
            <?php render_admin_research_table($results, ['under_peer_review']); ?>
        </div>
        <div id="tab-res-master" class="settings-pane hidden">
            <?php render_admin_research_table($results, ['published', 'restricted', 'rejected']); ?>
        </div>
    </div>
</div>

<?php
if (!function_exists('render_admin_research_table')) {
function render_admin_research_table($results, $allowed_statuses) {
    $filtered = array_filter($results, function($item) use ($allowed_statuses) {
        return in_array($item->status, $allowed_statuses);
    });
    ?>
    <table class="wshc-table compact-academic">
        <thead>
            <tr>
                <th>Author / Timestamp</th>
                <th>Research Title</th>
                <th>Review Status</th>
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
                        <div style="font-weight: 800;"><?php echo esc_html($item->title); ?></div>
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
<?php }
}
?>
