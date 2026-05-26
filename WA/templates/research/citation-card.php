<div class="citation-card">
    <div class="citation-header">
        <div class="pub-serial-block">
            <span class="pub-serial"><?php echo esc_html($item->serial_id); ?></span>
            <span class="digital-stamp"><span class="dashicons dashicons-shield"></span> PEER REVIEWED</span>
        </div>
        <span class="pub-date">Published: <?php echo date('M d, Y', strtotime($item->published_at)); ?></span>
    </div>

    <h2 class="pub-title"><?php echo esc_html($item->title); ?></h2>

    <div class="pub-meta">
        <span class="pub-type"><?php echo esc_html($item->doc_type); ?></span>
        <span class="pub-affiliations"><?php echo esc_html($item->affiliations); ?></span>
    </div>

    <p class="pub-abstract"><?php echo wp_trim_words(esc_html($item->abstract), 60); ?></p>

    <?php if ($item->prior_registry) : ?>
        <div class="pub-legacy">
            <strong>Legacy Attribution:</strong> <?php echo esc_html($item->prior_registry); ?>
        </div>
    <?php endif; ?>

    <div class="pub-actions">
        <?php if (current_user_can('wshc_member') || current_user_can('administrator') || current_user_can('wshc_research_member')) : ?>
            <a href="<?php echo esc_url($item->manuscript_url); ?>" class="download-pdf-btn" data-id="<?php echo $item->id; ?>" target="_blank">
                <span class="dashicons dashicons-pdf"></span> Download Full Manuscript
            </a>
        <?php else : ?>
            <button class="download-pdf-btn restricted-access" data-id="<?php echo $item->id; ?>">
                <span class="dashicons dashicons-lock"></span> Restricted: Members Only
            </button>
        <?php endif; ?>

        <button class="copy-citation-btn" data-citation="<?php
            echo esc_attr("WSHC Research Repository. ($item->serial_id). $item->title. " . date('Y', strtotime($item->published_at)) . ".");
        ?>">
            <span class="dashicons dashicons-clipboard"></span> Academic Citation
        </button>
    </div>
</div>
