<div class="wshc-research-repo-portal">
    <div class="repo-header">
        <h1>Global Scientific Research Repository</h1>
        <p>Open-Access Academic Index of the Global Council of Sport Health</p>
    </div>

    <!-- Advanced Search Matrix -->
    <div class="search-matrix-container">
        <div class="search-row">
            <input type="text" id="repo-keywords" placeholder="Keywords, Title, or Serial ID...">
            <input type="text" id="repo-author" placeholder="Author / Affiliation...">
        </div>
        <div class="search-row" style="margin-top: 15px;">
            <label>Publication Date Range:</label>
            <input type="date" id="repo-date-start">
            <span>to</span>
            <input type="date" id="repo-date-end">
            <button id="trigger-repo-search" class="wshc-auth-btn" style="width: auto; padding: 10px 40px;">Search Records</button>
        </div>
    </div>

    <div id="repo-results-container" class="repo-grid">
        <!-- Citation cards loaded here -->
        <div class="repo-placeholder">
            <span class="dashicons dashicons-search"></span>
            <p>Enter search parameters to filter scientific records.</p>
        </div>
    </div>
</div>
