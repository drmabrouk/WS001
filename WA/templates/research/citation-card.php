<div class="citation-card">
    <div class="citation-header">
        <span class="pub-serial"><?php echo esc_html($item->serial_id); ?></span>
        <span class="pub-date">Published: <?php echo date('M d, Y', strtotime($item->published_at)); ?></span>
    </div>

    <h2 class="pub-title"><?php echo esc_html($item->title); ?></h2>

    <div class="pub-meta">
        <span class="pub-type"><?php echo esc_html($item->doc_type); ?></span>
        <span class="pub-affiliations"><?php echo esc_html($item->affiliations); ?></span>
    </div>

    <p class="pub-abstract"><?php echo wp_trim_words(esc_html($item->abstract), 50); ?></p>

    <?php if ($item->prior_registry) : ?>
        <div class="pub-legacy">
            <strong>Legacy Attribution:</strong> <?php echo esc_html($item->prior_registry); ?>
        </div>
    <?php endif; ?>

    <div class="pub-actions">
        <a href="<?php echo esc_url($item->manuscript_url); ?>" class="download-pdf-btn" data-id="<?php echo $item->id; ?>" target="_blank">
            <span class="dashicons dashicons-pdf"></span> Download PDF
        </a>
        <button class="copy-citation-btn" data-citation="<?php
            echo esc_attr("WSHC Research Repository. ($item->serial_id). $item->title. " . date('Y', strtotime($item->published_at)) . ".");
        ?>">
            <span class="dashicons dashicons-clipboard"></span> Cite
        </button>
    </div>
</div>
