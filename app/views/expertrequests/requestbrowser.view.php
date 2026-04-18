<?php
    $title = 'My Requests';
    $filename = 'expertrequests/requestbrowser';
    include_once('../app/views/partials/header.view.php');
?>

<div class="profilebrowser-container main-content-container">
    <header class="exercise-browser-header">
        <h3 class="main-title">My Requests</h3>
        <p class="page-description">Manage and track your expert guidance requests.</p>
    </header>
    
    <div class="filter-bar">
        <input type="text" id="exercise-filter-input" placeholder="Search by typing something...">
        <button class="btn-filter-toggle" id="open-filter-btn">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"></polygon></svg>
            Filter
        </button>
        <a href="<?=ROOT?>/expertrequest/create" class="btn-create">+ Create</a>
    </div>

    <!-- Slide-out Side Panel -->
    <div class="side-panel-overlay" id="filter-overlay"></div>
    <div class="side-panel" id="filter-panel">
        <div class="side-panel-header">
            <h4>Advanced Filters</h4>
            <button class="close-panel-btn" id="close-filter-btn">&times;</button>
        </div>
        <div class="side-panel-content">
            <div class="filter-group">
                <label>Sort By</label>
                <select id="sort-column" class="panel-input">
                    <option value="id">Date Created</option>
                    <option value="subject">Subject Name</option>
                </select>
            </div>
            <div class="filter-group">
                <label>Direction</label>
                <select id="sort-order" class="panel-input">
                    <option value="DESC">Newest / Z-A</option>
                    <option value="ASC">Oldest / A-Z</option>
                </select>
            </div>
            <div class="filter-group">
                <label>Results per page</label>
                <select id="filter-limit" class="panel-input">
                    <option value="10">10</option>
                    <option value="25">25</option>
                    <option value="50">50</option>
                </select>
            </div>
            <button class="btn-apply-filters" id="apply-filters-btn">Apply Filters</button>
        </div>
    </div>

    <div class="tab-btns">
        <div class="tab-button btn-primary" data-index="0">pending</div>
        <div class="tab-button" data-index="1">approved</div>   
        <div class="tab-button" data-index="2">rejected</div>      
    </div>

    <div class="content-tabs">
        <div class="content-tab container active" id="list-pending"></div>
        <div class="content-tab container" id="list-approved"></div>
        <div class="content-tab container" id="list-rejected"></div>
    </div>

    <div class="load-more-container">
        <button id="load-more-btn" class="btn-load-more">Load More</button>
    </div>
</div>

<script type="module" src="<?=ROOT?>/assets/js/expertrequests/requestbrowser.view.js"></script>

<?php include_once('../app/views/partials/header.view.php'); ?>