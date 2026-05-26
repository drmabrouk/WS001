<div id="research-submission-modal" class="wshc-modal hidden">
    <div class="wshc-modal-content" style="max-width: 700px;">
        <h2>Scientific Research Submission</h2>

        <div id="research-policy-step">
            <h3>Pre-Submission Policy & Open-Access Agreement</h3>
            <div class="policy-box" style="height: 250px; overflow-y: auto; background: #f9f9f9; padding: 20px; border: 1px solid #eee; font-size: 13px; line-height: 1.6; margin-bottom: 20px;">
                <h4>Institutional Publication Bylaws</h4>
                <p>By submitting this research, you declare that you are the primary author or have legal authorization to act on behalf of the authors.</p>
                <h4>Open-Access Licensing</h4>
                <p>I explicitly agree that the submitted asset will be released under a public, open-access framework for general global educational use. This research will be indexed and available for free public download.</p>
                <p>The Global Council of Sport Health (WSHC) assumes no liability for the scientific accuracy of the data but reserves the right to restrict or revoke publication for ethical violations.</p>
            </div>
            <label style="display: flex; align-items: center; gap: 10px; cursor: pointer; margin-bottom: 25px;">
                <input type="checkbox" id="agree-research-policy" style="width: auto;">
                <strong>I accept the publication bylaws and open-access licensing terms.</strong>
            </label>
            <button type="button" class="wshc-auth-btn" id="start-research-upload" disabled>Proceed to Manuscript Upload</button>
        </div>

        <form id="wshc-research-submission-form" class="hidden" enctype="multipart/form-data">
            <div class="wshc-auth-form-group">
                <label>Research Title</label>
                <input type="text" name="title" required placeholder="Full academic title">
            </div>
            <div class="wshc-auth-form-group">
                <label>Abstract</label>
                <textarea name="abstract" required style="height: 120px;" placeholder="Comprehensive summary of research findings"></textarea>
            </div>
            <div class="wshc-auth-grid">
                <div class="wshc-auth-form-group">
                    <label>Keywords</label>
                    <input type="text" name="keywords" required placeholder="Comma-separated keys">
                </div>
                <div class="wshc-auth-form-group">
                    <label>Author(s) Affiliations</label>
                    <input type="text" name="affiliations" required placeholder="Primary institution/agency">
                </div>
            </div>
            <div class="wshc-auth-grid">
                <div class="wshc-auth-form-group">
                    <label>Document Type</label>
                    <select name="doc_type" required>
                        <option value="Original Research">Original Research</option>
                        <option value="Review Article">Review Article</option>
                        <option value="Case Study">Case Study</option>
                        <option value="Technical Report">Technical Report</option>
                    </select>
                </div>
                <div class="wshc-auth-form-group">
                    <label>Prior Publication Registry (Declare If Any)</label>
                    <input type="text" name="prior_registry" placeholder="Journal Name, Vol, Date">
                </div>
            </div>
            <div class="wshc-auth-grid">
                <div class="wshc-auth-form-group">
                    <label>Manuscript (High-Res PDF)</label>
                    <input type="file" name="manuscript" accept=".pdf" required>
                </div>
                <div class="wshc-auth-form-group">
                    <label>Supplementary Data (Optional)</label>
                    <input type="file" name="supplementary" accept=".pdf,.zip,.xlsx">
                </div>
            </div>
            <div class="modal-actions" style="margin-top: 25px;">
                <button type="submit" class="wshc-auth-btn">Finalize Submission</button>
                <button type="button" class="wshc-auth-btn close-modal" style="background: #666;">Cancel</button>
            </div>
        </form>
    </div>
</div>
