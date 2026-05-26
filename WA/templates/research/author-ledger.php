<div class="author-research-workspace">
    <div class="settings-tabs">
        <button class="settings-tab active" data-tab="res-tracker">🔄 Under-Review Tracker</button>
        <button class="settings-tab" data-tab="res-ledger">📜 Personal Published Ledger</button>
        <button class="settings-tab" data-tab="res-workroom">📥 Revision Workroom</button>
    </div>

    <div class="settings-tab-content">
        <!-- Tracker: Pending & Under Peer Review -->
        <div id="tab-res-tracker" class="settings-pane active">
            <?php render_author_research_table($results, ['pending', 'under_peer_review']); ?>
        </div>

        <!-- Ledger: Published -->
        <div id="tab-res-ledger" class="settings-pane hidden">
            <?php render_author_research_table($results, ['published']); ?>
        </div>

        <!-- Workroom: Needs Revision -->
        <div id="tab-res-workroom" class="settings-pane hidden">
            <?php render_author_research_table($results, ['needs_revision']); ?>
        </div>
    </div>
</div>

<?php
function render_author_research_table($results, $allowed_statuses) {
    $filtered = array_filter($results, function($item) use ($allowed_statuses) {
        return in_array($item->status, $allowed_statuses);
    });
    ?>
    <table class="wshc-table">
        <thead>
            <tr>
                <th>Serial ID</th>
                <th>Research Title</th>
                <th>Lifecycle Status</th>
                <th style="text-align: right;">Resources</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($filtered as $item) :
                $status_labels = [
                    'pending'           => 'Submitted',
                    'under_peer_review' => 'Under Peer Review',
                    'needs_revision'    => 'Revision Required',
                    'published'         => 'Published',
                    'rejected'          => 'Rejected',
                    'restricted'        => 'Restricted'
                ];
                $label = $status_labels[$item->status] ?? 'Unknown';
            ?>
                <tr>
                    <td><span class="serial-text" style="font-family: monospace; font-weight: 700;"><?php echo $item->serial_id ?: 'TBD'; ?></span></td>
                    <td>
                        <div style="font-weight: 700;"><?php echo esc_html($item->title); ?></div>
                        <div style="font-size: 10px; color: #999;">Type: <?php echo esc_html($item->doc_type); ?></div>
                    </td>
                    <td>
                        <div class="status-timeline">
                            <span class="status-capsule <?php echo $item->status; ?>"><?php echo strtoupper($label); ?></span>
                            <?php if ($item->status === 'under_peer_review') : ?>
                                <div style="font-size: 9px; color: #2e7d32; margin-top: 4px; font-weight: 800;">[ PEER REVIEW IN PROGRESS ]</div>
                            <?php endif; ?>
                        </div>
                    </td>
                    <td style="text-align: right;">
                        <a href="<?php echo esc_url($item->manuscript_url); ?>" class="action-btn" target="_blank" title="View Manuscript" style="background:#000;">
                            <span class="dashicons dashicons-pdf"></span>
                        </a>
                        <?php if ($item->status === 'needs_revision') : ?>
                             <button class="action-btn edit-submission" data-id="<?php echo $item->id; ?>" style="background: #f57c00;" title="Open Revision Workroom">
                                <span class="dashicons dashicons-edit"></span>
                             </button>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            <?php if (empty($filtered)) : ?>
                <tr><td colspan="4" style="text-align: center; padding: 40px; color: #999;">No manuscripts found in this tracking zone.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
<?php } ?>
