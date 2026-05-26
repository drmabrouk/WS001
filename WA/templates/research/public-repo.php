<div class="wshc-research-repo-portal">
    <div class="repo-header">
        <h1>Global Scientific Research Repository</h1>
        <p>Official Academic Index of the Global Council of Sport Health</p>
    </div>

    <div class="repo-main-layout">
        <!-- Side-Docked Filter Facets -->
        <aside class="repo-filters-sidebar">
            <div class="filter-group">
                <h3>Field of Specialization</h3>
                <select id="filter-specialization">
                    <option value="">All Specializations</option>
                    <?php
                    $specs = get_option('wshc_dict_specializations', ['Sports Medicine', 'Kinesiology', 'Exercise Physiology', 'Sports Nutrition']);
                    foreach ($specs as $spec) echo "<option value='$spec'>$spec</option>";
                    ?>
                </select>
            </div>

            <div class="filter-group">
                <h3>Academic Degree</h3>
                <select id="filter-degree">
                    <option value="">Any Degree</option>
                    <option value="Ph.D.">Ph.D. / Doctorate</option>
                    <option value="Master's">Master's Degree</option>
                    <option value="Bachelor's">Bachelor's Degree</option>
                </select>
            </div>

            <div class="filter-group">
                <h3>Institution / University</h3>
                <input type="text" id="filter-institution" placeholder="Filter by university...">
            </div>

            <div class="filter-group">
                <h3>Publication Date Range</h3>
                <div class="range-inputs">
                    <input type="date" id="repo-date-start" title="Start Date">
                    <input type="date" id="repo-date-end" title="End Date">
                </div>
            </div>

            <button id="trigger-repo-search" class="wshc-auth-btn">Apply Academic Filters</button>
        </aside>

        <!-- Main Search Results -->
        <div class="repo-results-area">
            <div class="unified-query-bar">
                <span class="dashicons dashicons-search"></span>
                <input type="text" id="repo-keywords" placeholder="Semantic match by Title, Abstract, or Keywords...">
            </div>

            <div id="repo-results-container" class="repo-grid">
                <div class="repo-placeholder">
                    <span class="dashicons dashicons-database"></span>
                    <p>Utilize the academic filters to explore the global scientific repository.</p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Access Denied Modal -->
<div id="research-access-modal" class="wshc-modal hidden">
    <div class="wshc-modal-content" style="max-width: 450px; text-align: center;">
        <span class="dashicons dashicons-lock" style="font-size: 60px; width: 60px; height: 60px; color: #d32f2f; margin-bottom: 20px;"></span>
        <h2>Membership Required</h2>
        <p style="color: #666; margin-bottom: 30px;">Access to full scientific manuscripts and PDF downloads is strictly restricted to verified Council Members.</p>
        <div class="modal-actions" style="flex-direction: column; gap: 10px;">
            <a href="<?php echo home_url('/id?section=info-apply'); ?>" class="wshc-auth-btn">Complete Membership Application</a>
            <button class="wshc-auth-btn close-modal" style="background: none; color: #666; border: none; box-shadow: none;">Continue Browsing Metadata</button>
        </div>
    </div>
</div>
