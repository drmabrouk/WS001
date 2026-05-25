<div class="content-panel">
    <h3>Portfolio: Published Works & Active Submissions</h3>
    <table class="wshc-table">
        <thead>
            <tr>
                <th>Serial ID</th>
                <th>Title</th>
                <th>Status</th>
                <th>Metrics</th>
                <th style="text-align: right;">Resources</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($results as $item) :
                $status_labels = [
                    'pending'           => 'Submitted',
                    'under_peer_review' => 'Under Review',
                    'needs_revision'    => 'Revision Required',
                    'published'         => 'Published',
                    'rejected'          => 'Rejected',
                    'restricted'        => 'Restricted'
                ];
                $label = $status_labels[$item->status] ?? 'Unknown';
            ?>
                <tr>
                    <td><span class="serial-text"><?php echo $item->serial_id ?: 'TBD'; ?></span></td>
                    <td><strong><?php echo esc_html($item->title); ?></strong></td>
                    <td><span class="status-capsule <?php echo $item->status; ?>"><?php echo strtoupper($label); ?></span></td>
                    <td><span class="dashicons dashicons-download"></span> <?php echo intval($item->download_count); ?></td>
                    <td style="text-align: right;">
                        <a href="<?php echo esc_url($item->manuscript_url); ?>" class="action-btn" target="_blank" title="View Manuscript">
                            <span class="dashicons dashicons-pdf"></span>
                        </a>
                        <?php if ($item->status === 'needs_revision') : ?>
                             <button class="action-btn edit-submission" data-id="<?php echo $item->id; ?>" style="background: #f57c00;" title="Update Data">
                                <span class="dashicons dashicons-edit"></span>
                             </button>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            <?php if (empty($results)) : ?>
                <tr><td colspan="5" style="text-align: center; padding: 40px;">No research manuscripts found in your academic ledger.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>
