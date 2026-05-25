jQuery(document).ready(function($) {
    let offset = 10;
    let searchTimer;
    const loadMoreBtn = $('#wshc-load-more');
    const registryList = $('#wshc-member-registry');
    const searchInput = $('#wshc-directory-search');

    // Asynchronous Search Logic
    if (searchInput.length) {
        searchInput.on('input', function() {
            clearTimeout(searchTimer);
            const query = $(this).val();

            searchTimer = setTimeout(function() {
                offset = 0; // Reset offset for new search
                loadBatch(true, query);
            }, 500); // 500ms debounce
        });
    }

    // Load More Logic
    if (loadMoreBtn.length) {
        loadMoreBtn.on('click', function(e) {
            e.preventDefault();
            loadBatch(false, searchInput.val());
        });
    }

    /**
     * Fetch a batch of members.
     * @param {boolean} replace - Whether to replace or append results.
     * @param {string} search - The search query.
     */
    function loadBatch(replace = false, search = '') {
        const btn = $('#wshc-load-more');
        const originalBtnText = btn.html();

        if (replace) {
            registryList.css('opacity', '0.5');
        } else {
            btn.prop('disabled', true).html('<span class="dashicons dashicons-update spin"></span> Loading batches...');
        }

        $.ajax({
            url: wshc_directory_obj.ajaxurl,
            type: 'POST',
            data: {
                action: 'wshc_load_more_members',
                offset: offset,
                search: search
            },
            success: function(response) {
                registryList.css('opacity', '1');
                btn.prop('disabled', false).html(originalBtnText);

                if (response.success) {
                    const html = response.data.html;
                    const count = response.data.count;

                    if (replace) {
                        registryList.html(html || '<div class="no-members-notice"><span class="dashicons dashicons-search"></span><p>No results match your criteria.</p></div>');
                        offset = count;
                    } else {
                        const newRows = $(html).hide();
                        registryList.append(newRows);
                        newRows.fadeIn(400);
                        offset += count;
                    }

                    // Handle button visibility
                    if (count < 10) {
                        btn.hide();
                    } else {
                        btn.show();
                    }
                }
            },
            error: function() {
                registryList.css('opacity', '1');
                btn.prop('disabled', false).html(originalBtnText);
                alert('Connection error. Please refresh and try again.');
            }
        });
    }
});
