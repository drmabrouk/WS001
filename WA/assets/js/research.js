jQuery(document).ready(function($) {
    // 1. Dashboard Initial Loaders
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
            data: { action: 'wshc_list_research_admin', nonce: wshc_dashboard_obj.nonce },
            success: function(response) {
                container.css('opacity', '1');
                if (response.success) container.html(response.data.html);
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
            data: { action: 'wshc_list_my_research', nonce: wshc_dashboard_obj.nonce },
            success: function(response) {
                container.css('opacity', '1');
                if (response.success) container.html(response.data.html);
            }
        });
    }

    // 2. Submission Wizard Logic (Stages)
    let currentStage = 1;
    $(document).on('click', '#open-research-submission', function() {
        currentStage = 1;
        updateStageUI();
        $('#research-submission-modal').removeClass('hidden').hide().fadeIn(300);
    });

    $(document).on('click', '#next-stage', function() {
        if (validateStage(currentStage)) {
            currentStage++;
            updateStageUI();
        }
    });

    $(document).on('click', '#prev-stage', function() {
        currentStage--;
        updateStageUI();
    });

    function updateStageUI() {
        $('.submission-stage').addClass('hidden');
        $(`#stage-${currentStage}`).removeClass('hidden');
        $('.dot').removeClass('active');
        $(`.dot[data-step="${currentStage}"]`).addClass('active');

        if (currentStage === 1) $('#prev-stage').addClass('hidden');
        else $('#prev-stage').removeClass('hidden');

        if (currentStage === 4) {
            $('#next-stage').addClass('hidden');
            $('#submit-ms').removeClass('hidden');
        } else {
            $('#next-stage').removeClass('hidden');
            $('#submit-ms').addClass('hidden');
        }
    }

    function validateStage(stage) {
        const inputs = $(`#stage-${stage} [required]`);
        let valid = true;
        inputs.each(function() {
            if (!$(this).val() || ($(this).is(':checkbox') && !$(this).is(':checked'))) {
                valid = false;
                $(this).closest('.floating-input').css('border-color', '#d32f2f');
            } else {
                $(this).closest('.floating-input').css('border-color', '');
            }
        });
        return valid;
    }

    $(document).on('submit', '#wshc-research-submission-form', function(e) {
        e.preventDefault();
        const btn = $('#submit-ms');
        const formData = new FormData(this);
        formData.append('action', 'wshc_submit_research');
        formData.append('nonce', wshc_dashboard_obj.nonce);

        btn.prop('disabled', true).text('PROCESSING...');

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

    // 3. Admin & Author Tab Logic
    $(document).on('click', '.settings-tab', function() {
        const tabId = $(this).data('tab');
        const parent = $(this).closest('.admin-research-workspace, .author-research-workspace');
        if (!parent.length) return;
        parent.find('.settings-tab').removeClass('active');
        $(this).addClass('active');
        parent.find('.settings-pane').addClass('hidden');
        parent.find(`#tab-${tabId}`).removeClass('hidden');
    });

    // 4. Public Repository Search (Academic Matrix)
    $(document).on('click', '#trigger-repo-search', function() {
        const results = $('#repo-results-container');
        results.css('opacity', '0.5');
        $.ajax({
            url: wshc_research_obj.ajaxurl,
            type: 'POST',
            data: {
                action: 'wshc_search_research',
                nonce: wshc_research_obj.nonce,
                keywords: $('#repo-keywords').val(),
                specialization: $('#filter-specialization').val(),
                degree: $('#filter-degree').val(),
                institution: $('#filter-institution').val(),
                date_start: $('#repo-date-start').val(),
                date_end: $('#repo-date-end').val()
            },
            success: function(response) {
                results.css('opacity', '1');
                if (response.success) results.html(response.data.html || '<p class="no-results">No records match the defined academic criteria.</p>');
            }
        });
    });

    // 5. Access Control Layer
    $(document).on('click', '.restricted-access', function() {
        $('#research-access-modal').removeClass('hidden').hide().fadeIn(200);
    });

    $(document).on('click', '.copy-citation-btn', function() {
        const text = $(this).data('citation');
        navigator.clipboard.writeText(text).then(() => alert('Academic citation copied to clipboard.'));
    });

    // 6. Taxonomy Settings
    $(document).on('click', '#open-taxonomy-settings', function() {
        $('#taxonomy-settings-modal').removeClass('hidden').hide().fadeIn(300);
    });

    $(document).on('click', '#save-taxonomy-btn', function() {
        const type = $('#tax-type-select').val();
        const values = $('#tax-values-area').val();
        if (!values) return alert('Please enter dictionary values.');

        $.ajax({
            url: wshc_dashboard_obj.ajaxurl,
            type: 'POST',
            data: {
                action: 'wshc_save_taxonomy_settings',
                nonce: wshc_dashboard_obj.nonce,
                tax_type: type,
                tax_values: values
            },
            success: function(response) {
                if (response.success) {
                    alert(response.data.message);
                    $('#taxonomy-settings-modal').addClass('hidden');
                }
            }
        });
    });
});
