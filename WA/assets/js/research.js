jQuery(document).ready(function($) {
    // 1. Initial Load for Dashboard Sections
    const activeSection = $('.dashboard-section:not(.hidden)');
    const sectionId = activeSection.attr('id');

    if (sectionId === 'section-research-submissions') {
        loadAdminResearchLog();
    } else if (sectionId === 'section-my-published-works') {
        loadAuthorResearchLedger();
    }

    function loadAdminResearchLog() {
        const container = $('#admin-research-log-container');
        if (!container.length) return;
        container.css('opacity', '0.5');
        $.ajax({
            url: wshc_dashboard_obj.ajaxurl,
            type: 'POST',
            data: {
                action: 'wshc_list_research_admin',
                nonce: wshc_dashboard_obj.nonce
            },
            success: function(response) {
                container.css('opacity', '1');
                if (response.success) {
                    container.html(response.data.html);
                }
            }
        });
    }

    function loadAuthorResearchLedger() {
        const container = $('#author-research-ledger-container');
        if (!container.length) return;
        container.css('opacity', '0.5');
        $.ajax({
            url: wshc_dashboard_obj.ajaxurl,
            type: 'POST',
            data: {
                action: 'wshc_list_my_research',
                nonce: wshc_dashboard_obj.nonce
            },
            success: function(response) {
                container.css('opacity', '1');
                if (response.success) {
                    container.html(response.data.html);
                }
            }
        });
    }

    // 2. Submission Pipeline
    $(document).on('click', '#open-research-submission', function() {
        $('#research-submission-modal').removeClass('hidden').hide().fadeIn(300);
    });

    $(document).on('change', '#agree-research-policy', function() {
        $('#start-research-upload').prop('disabled', !$(this).is(':checked'));
    });

    $(document).on('click', '#start-research-upload', function() {
        $('#research-policy-step').addClass('hidden');
        $('#wshc-research-submission-form').removeClass('hidden');
    });

    $(document).on('submit', '#wshc-research-submission-form', function(e) {
        e.preventDefault();
        const btn = $(this).find('button[type="submit"]');
        const formData = new FormData(this);
        formData.append('action', 'wshc_submit_research');
        formData.append('nonce', wshc_dashboard_obj.nonce);

        btn.prop('disabled', true).text('PROCESSING MANUSCRIPT...');

        $.ajax({
            url: wshc_dashboard_obj.ajaxurl,
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                btn.prop('disabled', false).text('Finalize Submission');
                if (response.success) {
                    $('#research-submission-modal').addClass('hidden');
                    loadAuthorResearchLedger();
                    alert(response.data.message);
                } else {
                    alert(response.data.message);
                }
            }
        });
    });

    // 3. Admin Controls
    let pendingAssignId = null;
    $(document).on('click', '.admin-research-action', function() {
        const id = $(this).data('id');
        const action = $(this).data('action');

        if (action === 'assign') {
            pendingAssignId = id;
            $('#assign-reviewer-modal').removeClass('hidden').hide().fadeIn(300);
            return;
        }

        if (action === 'reject' && !confirm('Are you sure you want to PERMANENTLY REJECT this manuscript?')) return;

        updateResearchStatus(id, action);
    });

    $(document).on('click', '#confirm-assign-btn', function() {
        const revId = $('#reviewer-pool-select').val();
        if (revId == 0) return alert('Please select a reviewer.');

        updateResearchStatus(pendingAssignId, 'assign', { reviewer_id: revId });
        $('#assign-reviewer-modal').addClass('hidden');
    });

    function updateResearchStatus(id, action, extra = {}) {
        $.ajax({
            url: wshc_dashboard_obj.ajaxurl,
            type: 'POST',
            data: $.extend({
                action: 'wshc_update_research_status',
                nonce: wshc_dashboard_obj.nonce,
                submission_id: id,
                admin_action: action
            }, extra),
            success: function(response) {
                if (response.success) {
                    loadAdminResearchLog();
                } else {
                    alert(response.data.message);
                }
            }
        });
    }

    // 4. Public Repository Search
    $(document).on('click', '#trigger-repo-search', function() {
        const btn = $(this);
        const results = $('#repo-results-container');

        btn.prop('disabled', true).text('Searching...');
        results.css('opacity', '0.5');

        $.ajax({
            url: wshc_research_obj.ajaxurl,
            type: 'POST',
            data: {
                action: 'wshc_search_research',
                nonce: wshc_research_obj.nonce,
                keywords: $('#repo-keywords').val(),
                author: $('#repo-author').val(),
                date_start: $('#repo-date-start').val(),
                date_end: $('#repo-date-end').val()
            },
            success: function(response) {
                btn.prop('disabled', false).text('Search Records');
                results.css('opacity', '1');
                if (response.success) {
                    results.html(response.data.html || '<p style="text-align:center; padding:40px;">No records match your criteria.</p>');
                }
            }
        });
    });

    $(document).on('click', '.download-pdf-btn', function() {
        const id = $(this).data('id');
        $.ajax({
            url: wshc_research_obj.ajaxurl,
            type: 'POST',
            data: { action: 'wshc_track_download', id: id }
        });
    });

    $(document).on('click', '.copy-citation-btn', function() {
        const text = $(this).data('citation');
        navigator.clipboard.writeText(text).then(() => {
            alert('Citation copied to clipboard.');
        });
    });

    // 5. Shared Tab Logic for Research Module
    $(document).on('click', '.admin-research-workspace .settings-tab, .author-research-workspace .settings-tab', function() {
        const parent = $(this).closest('.admin-research-workspace, .author-research-workspace');
        const tabId = $(this).data('tab');

        parent.find('.settings-tab').removeClass('active');
        $(this).addClass('active');

        parent.find('.settings-pane').addClass('hidden');
        parent.find(`#tab-${tabId}`).removeClass('hidden');
    });
});
