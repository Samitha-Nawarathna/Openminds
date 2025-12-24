<?php
    $title = 'All requests';
    $filename = 'expertrequestsadmin/browser';
    include_once('../app/views/partials/header.view.php');
?>
<div class="requestbrowser-container main-content-container">
    <header class="exercise-browser-header">
        <h3 class="main-title">All Requests</h3>
    </header>
    
    <!-- Top Action Bar -->
    <div class="filter-action-row">
        <div class="search-wrapper">
            <input type="text" id="exercise-filter-input" placeholder="Search by description or user...">
        </div>

        <button id="filter-toggle-btn" class="icon-btn-filter" title="Advanced Filters">
            <strong>advanced filters</strong>
        </button>
    </div>

    <!-- Collapsable Advanced Filter Modal -->
    <div id="advanced-filter-modal" class="advanced-filter-panel hidden">
        <div class="filter-grid">
            <div class="filter-field">
                <label>Subject Topic</label>
                <div class="input-group">
                <input type="text" name="subject-filter" id="subject-filter" placeholder="type a subject name">

                </div>
                            </div>
            <div class="filter-field">
                <label>Sort By</label>
                <div class="sort-flex">
                    <select id="sort-by">
                        <option value="request_id">Date Created</option>
                        <option value="user_name">User Name</option>
                        <option value="subject">Subject</option>
                    </select>
                    <select id="sort-dir">
                        <option value="DESC">Newest/Z-A</option>
                        <option value="ASC">Oldest/A-Z</option>
                    </select>
                </div>
            </div>
            <div class="filter-footer">
                <button id="apply-advanced-filters" class="btn-apply">Apply</button>
            </div>
        </div>
    </div>

    <!-- Status Tabs -->
    <div class="tab-btns">
        <div class="tab-button active" data-status="pending">Pending</div>
        <div class="tab-button" data-status="approved">Approved</div>
        <div class="tab-button" data-status="rejected">Rejected</div>        
    </div>

    <div class="content-tabs">
        <div class="content-tab container active" id="results-target">
            <!-- Results will be injected here via AJAX -->
        </div>
        
        <div class="load-more-container" style="display: none;">
            <button id="load-more-btn" class="btn-load-more">Load More Requests</button>
        </div>
    </div>
</div>

<?php 
    include_once('../app/views/partials/footer.view.php');
?>