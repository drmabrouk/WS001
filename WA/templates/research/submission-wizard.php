<div id="research-submission-modal" class="wshc-modal hidden">
    <div class="wshc-modal-content" style="max-width: 800px;">
        <h2 style="text-align: center; margin-bottom: 30px;">Manuscript Submission Pipeline</h2>

        <div class="submission-progress-dots">
            <div class="dot active" data-step="1">1</div>
            <div class="dot" data-step="2">2</div>
            <div class="dot" data-step="3">3</div>
            <div class="dot" data-step="4">4</div>
        </div>

        <form id="wshc-research-submission-form" enctype="multipart/form-data">
            <!-- STAGE 1: DECLARATION -->
            <div class="submission-stage" id="stage-1">
                <h3>Open-Access Copyright & Licensing Consent</h3>
                <div class="policy-box" style="height: 250px; overflow-y: auto; background: #f9f9f9; padding: 25px; border: 1px solid #eee; font-size: 13px; line-height: 1.6; margin-bottom: 25px; border-radius: 8px;">
                    <h4>Institutional Publication Bylaws</h4>
                    <p>By submitting this research, you declare that you are the primary author or have legal authorization to act on behalf of the authors.</p>
                    <h4>Open-Access Licensing</h4>
                    <p>I explicitly agree that the submitted asset will be released under a public, open-access framework for general global educational use. This research will be indexed and available for free public download.</p>
                </div>
                <div class="floating-group" style="display: flex; align-items: center; gap: 12px;">
                    <input type="checkbox" id="agree-research-policy" name="policy_agreed" required style="width: auto;">
                    <label for="agree-research-policy" style="font-weight: 700;">I accept the open-access terms and institutional bylaws.</label>
                </div>
            </div>

            <!-- STAGE 2: METADATA -->
            <div class="submission-stage hidden" id="stage-2">
                <h3>Core Manuscript Metadata</h3>
                <div class="floating-input">
                    <input type="text" name="title" required placeholder=" ">
                    <label>Research Title</label>
                </div>
                <div class="floating-input" style="margin-top: 20px;">
                    <textarea name="abstract" required placeholder=" " style="height: 150px;"></textarea>
                    <label>Abstract</label>
                </div>
                <div class="floating-input" style="margin-top: 20px;">
                    <input type="text" name="keywords" required placeholder=" ">
                    <label>Keyword Arrays (Comma-separated)</label>
                </div>
            </div>

            <!-- STAGE 3: TAXONOMY -->
            <div class="submission-stage hidden" id="stage-3">
                <h3>Academic Taxonomy & Affiliations</h3>
                <div class="floating-input">
                    <input type="text" name="affiliations" required placeholder=" ">
                    <label>University, College & Departments</label>
                </div>
                <div class="floating-input" style="margin-top: 20px;">
                    <select name="doc_type" required>
                        <option value="" disabled selected></option>
                        <option value="Original Research">Original Research</option>
                        <option value="Review Article">Review Article</option>
                        <option value="Case Study">Case Study</option>
                        <option value="Technical Report">Technical Report</option>
                    </select>
                    <label>Document Type</label>
                </div>
                <div class="floating-input" style="margin-top: 20px;">
                    <select name="author_degree" required>
                        <option value="" disabled selected></option>
                        <option value="Ph.D.">Ph.D. / Doctorate</option>
                        <option value="Master's">Master's Degree</option>
                        <option value="Bachelor's">Bachelor's Degree</option>
                    </select>
                    <label>Academic Degree</label>
                </div>
                <div class="floating-input" style="margin-top: 20px;">
                    <select name="specialization" required>
                        <option value="" disabled selected></option>
                        <?php
                        $specs = get_option('wshc_dict_specializations', ['Sports Medicine', 'Kinesiology', 'Exercise Physiology', 'Sports Nutrition']);
                        foreach ($specs as $spec) echo "<option value='$spec'>$spec</option>";
                        ?>
                    </select>
                    <label>Specialization Tag</label>
                </div>
            </div>

            <!-- STAGE 4: PAYLOAD -->
            <div class="submission-stage hidden" id="stage-4">
                <h3>Payload Upload Matrix</h3>
                <div class="file-upload-zone">
                    <label>High-Resolution Manuscript (PDF)</label>
                    <input type="file" name="manuscript" accept=".pdf" required>
                </div>
                <div class="file-upload-zone" style="margin-top: 20px;">
                    <label>Supplementary Data Tables (Optional)</label>
                    <input type="file" name="supplementary" accept=".pdf,.zip,.xlsx">
                </div>
            </div>

            <div class="modal-actions" style="margin-top: 30px; border-top: 1px solid #eee; padding-top: 20px;">
                <button type="button" class="wshc-auth-btn hidden" id="prev-stage" style="background: #666; width: auto; padding: 10px 30px;">Previous</button>
                <button type="button" class="wshc-auth-btn" id="next-stage" style="width: auto; padding: 10px 30px;">Next Stage</button>
                <button type="submit" class="wshc-auth-btn hidden" id="submit-ms" style="width: auto; padding: 10px 30px;">Finalize Submission</button>
                <button type="button" class="wshc-auth-btn close-modal" style="background: none; color: #999; border: none; box-shadow: none; width: auto;">Cancel</button>
            </div>
        </form>
    </div>
</div>
