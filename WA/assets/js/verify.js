(function($) {
    'use strict';

    $(document).ready(function() {
        const $searchInput = $('#wshc-serial-search');
        const $resultsContainer = $('#wshc-verification-results');
        let searchTimeout;

        $searchInput.on('input', function() {
            const code = $(this).val().trim();

            clearTimeout(searchTimeout);

            if (code.length < 3) {
                resetResults();
                return;
            }

            // Show loading state
            $resultsContainer.html('<div class="wshc-loader"></div>');

            searchTimeout = setTimeout(function() {
                performVerification(code);
            }, 300); // Debounce
        });

        function performVerification(code) {
            $.ajax({
                url: wshc_verify_obj.ajaxurl,
                type: 'POST',
                data: {
                    action: 'wshc_verify_code',
                    code: code,
                    nonce: wshc_verify_obj.nonce
                },
                success: function(response) {
                    if (response.success) {
                        renderResult(response.data);
                    } else {
                        renderError(response.data.message);
                    }
                },
                error: function() {
                    renderError('An error occurred during verification. Please try again.');
                }
            });
        }

        function renderResult(data) {
            let badgeClass = 'verification-badge';
            let badgeTitle = 'Document Validated';
            let badgeIcon = 'dashicons-yes-alt';

            if (data.status === 'expired') {
                badgeClass += ' warning-alert';
                badgeTitle = 'Document Expired';
                badgeIcon = 'dashicons-warning';
            }

            const html = `
                <div class="verification-sheet">
                    <div class="${badgeClass}">
                        <span class="dashicons ${badgeIcon}"></span>
                        <div class="badge-text">
                            <h3>${badgeTitle}</h3>
                            <p>Official Record Found in Registry</p>
                        </div>
                    </div>

                    <div class="info-grid">
                        <div class="info-item full-width">
                            <label>Holder Full Name</label>
                            <span>${data.holder_name}</span>
                        </div>
                        <div class="info-item">
                            <label>Membership ID</label>
                            <span>#${data.membership_id}</span>
                        </div>
                        <div class="info-item">
                            <label>Document Type</label>
                            <span>${data.doc_type}</span>
                        </div>
                        <div class="info-item">
                            <label>Issue Date</label>
                            <span>${data.issue_date}</span>
                        </div>
                        <div class="info-item">
                            <label>Expiry Status</label>
                            <span style="color: ${data.status === 'expired' ? '#ef6c00' : '#2e7d32'}">
                                ${data.status === 'expired' ? 'Expired' : 'Active'} (${data.expiry_date})
                            </span>
                        </div>
                    </div>
                </div>
            `;
            $resultsContainer.html(html);
        }

        function renderError(message) {
            const html = `
                <div class="verification-sheet">
                    <div class="verification-badge danger-alert">
                        <span class="dashicons dashicons-dismiss"></span>
                        <div class="badge-text">
                            <h3>Invalid Record</h3>
                            <p>${message}</p>
                        </div>
                    </div>
                </div>
            `;
            $resultsContainer.html(html);
        }

        function resetResults() {
            $resultsContainer.html(`
                <div class="initial-placeholder">
                    <span class="dashicons dashicons-shield-search"></span>
                    <p>Awaiting Input...</p>
                </div>
            `);
        }
    });

})(jQuery);
