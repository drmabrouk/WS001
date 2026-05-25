jQuery(document).ready(function($) {
    let offset = 10;
    const loadMoreBtn = $('#wshc-load-more');
    const registryContainer = $('#wshc-member-registry');

    if (loadMoreBtn.length) {
        loadMoreBtn.on('click', function(e) {
            e.preventDefault();

            const btn = $(this);
            const originalText = btn.html();

            // Set Loading State
            btn.prop('disabled', true).html('<span class="dashicons dashicons-update spin"></span> Loading batches...');

            $.ajax({
                url: wshc_directory_obj.ajaxurl,
                type: 'POST',
                data: {
                    action: 'wshc_load_more_members',
                    offset: offset
                },
                success: function(response) {
                    if (response.success) {
                        if (response.data.count > 0) {
                            const newRows = $(response.data.html).hide();
                            registryContainer.append(newRows);
                            newRows.fadeIn(500);

                            offset += response.data.count;

                            // If we fetched less than 10, no more members exist
                            if (response.data.count < 10) {
                                btn.fadeOut(300);
                            } else {
                                btn.prop('disabled', false).html(originalText);
                            }
                        } else {
                            btn.fadeOut(300);
                        }
                    } else {
                        btn.prop('disabled', false).html(originalText);
                        console.error('Directory fetch error:', response.data.message);
                    }
                },
                error: function() {
                    btn.prop('disabled', false).html(originalText);
                    alert('Asynchronous fetch failed. Please check your network connection.');
                }
            });
        });
    }

    // Add spin animation via CSS if not present
    $('<style>.spin { animation: spin 1s linear infinite; } @keyframes spin { 100% { transform: rotate(360deg); } }</style>').appendTo('head');
});
