<?php
    $title = 'All Profiles';
    $filename = 'profilebrowser';
    $profile_picture_url = ROOT."/uploads/0/profile.avif";
    include_once('../app/views/partials/header.view.php');
?>

<div class="profilebrowser-layout">
    <div class="profilebrowser-main">
        <!-- Sticky Top Section -->
        <div class="sticky-header">
            <div class="main-content-container">
                <header class="exercise-browser-header">
                    <h3 class="main-title">Profiles</h3>
                </header>

                <div class="filter-bar">
                    <input type="text" id="exercise-filter-input" placeholder="Search name or username...">
                    <button class="btn-filter" id="filter-toggle-btn">
                        <i class="fa fa-sliders"></i> Filters
                    </button>
                </div>

                <div class="tab-btns">
                    <div class="tab-button btn-primary" data-index="0">active</div>
                    <div class="tab-button btn-none" data-index="1">banned</div>        
                </div>
            </div>
        </div>

        <!-- Scrollable Content -->
        <div class="main-content-container scrollable-content">
            <div class="content-tabs">
                <div class="content-tab container active" id="tab-0"></div>
                <div class="content-tab container" id="tab-1"></div>
            </div>

            <div class="load-more-container">
                <button class="btn-load-more" id="load-more-btn">Load More</button>
            </div>
        </div>
    </div>

    <!-- Right-side Filter Sidebar -->
    <aside class="filter-sidebar" id="filter-drawer">
        <div class="sidebar-header">
            <h3>Filters</h3>
            <button class="btn-close-sidebar" id="close-sidebar">&times;</button>
        </div>
        
        <div class="filter-scroll-area">
            <div class="filter-group">
                <h4>User Roles</h4>
                <div class="checkbox-group">
                    <label><input type="checkbox" class="role-filter" value="admin"> Admin</label>
                    <label><input type="checkbox" class="role-filter" value="expert"> Expert</label>
                    <label><input type="checkbox" class="role-filter" value="mentor"> Mentor</label>
                    <label><input type="checkbox" class="role-filter" value="student"> Student</label>
                </div>
            </div>

            <div class="filter-group">
                <h4>Sort By</h4>
                <select id="sort-by" class="filter-select">
                    <option value="created_at-DESC">Newest First</option>
                    <option value="created_at-ASC">Oldest First</option>
                    <option value="display_name-ASC">Name (A-Z)</option>
                    <option value="subject_name-ASC">Subject</option>
                </select>
            </div>

            <div class="filter-group">
                <h4>Joined Date</h4>
                <div class="date-inputs">
                    <label>From</label>
                    <input type="date" id="date-start" class="filter-date">
                    <label>To</label>
                    <input type="date" id="date-end" class="filter-date">
                </div>
            </div>
        </div>

        <div class="filter-actions">
            <button class="btn-clear" id="clear-filters">Reset All</button>
        </div>
    </aside>
</div>

<?php include_once('../app/views/partials/footer.view.php'); ?>