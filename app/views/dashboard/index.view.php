<?php

    $title = "Dashboard | Openminds";
    $filename = "dashboard/index";

    // $no_nav;

    include_once "../app/views/partials/header.view.php";

?>

<style>
    .lucide-icon {
        width: 18px;
        height: 18px;
        vertical-align: middle;
        margin-right: 8px;
        stroke: currentColor;
        stroke-width: 2;
        stroke-linecap: round;
        stroke-linejoin: round;
        fill: none;
    }
    .logo .lucide-icon {
        width: 24px;
        height: 24px;
        margin-right: 5px;
        color: var(--color-primary-accent); /* Assuming you want the logo color */
    }
    .search-bar .lucide-icon {
        width: 18px;
        height: 18px;
        margin: 0;
        margin-right: 5px;
    }
    .nav-link, .qa-large-card, .qa-button, .tab {
        display: flex;
        align-items: center;
    }
    .qa-large-icon .lucide-icon {
        width: 36px;
        height: 36px;
        margin: 0;
    }
</style>


    <div class="page-wrapper">
<!-- 
    <header>
        <div class="logo">
             Openminds
        </div>

        <div class="search-bar">
            <span class="search-icon">
                <svg xmlns="http://www.w3.org/2000/svg" class="lucide-icon" viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><line x1="21" x2="16.65" y1="21" y2="16.65"/></svg>
            </span>
            <input type="text" placeholder="Search notes, questions, or exercises...">
        </div>

        <div class="header-actions">
            <a href="#" class="btn-create-header" id="btn-start-work" style="display: none;">
                <svg xmlns="http://www.w3.org/2000/svg" class="lucide-icon" viewBox="0 0 24 24" style="width: 16px; height: 16px; margin-right: 5px;"><circle cx="12" cy="12" r="10"/><line x1="12" x2="12" y1="8" y2="16"/><line x1="8" x2="16" y1="12" y2="12"/></svg>
                <span>Start Work</span>
            </a>
            
            <div class="user-avatar" id="user-avatar" title="Go to Profile Settings"></div>
        </div>
    </header>
     -->

<div class="dashboard-grid">
    
    <div class="col-left"></div>
    
    <div class="impact-row">
        <h3 id="welcome-message">Your Impact Summary</h3>
        <div class="impact-cards">
            <!-- 1. Total Points (New) -->
            <div class="stat-card points-card">
                <div class="stat-icon-box">
                    <img src="<?=ROOT?>/assets/images/points.png" alt="Points">
                </div>
                <div class="stat-content">
                    <div>
                        <div class="kpi-title">Total Points</div>
                        <div class="kpi-value" id="stat-points"><?= isset($data['total_points']) ? $data['total_points'] : '0.00' ?></div>
                    </div>
                    <div class="progress-container">
                        <div class="progress-bar-bg">
                            <div class="progress-bar-fill" id="stat-points-progress" style="width: 0%"></div>
                        </div>
                        <div class="stat-sub" id="stat-points-sub">0 / 50</div>
                    </div>
                </div>
            </div>

            <!-- 2. Consistency (Moved) -->
            <div class="stat-card">
                <div class="stat-icon-box">
                    <img src="<?=ROOT?>/assets/images/streak (2).png" alt="Consistency">
                </div>
                <div class="stat-content">
                    <div class="kpi-title">
                        Consistency (7 Days)
                    </div>
                    <div class="kpi-value" id="stat-consistency">0/7</div>
                    <a href="<?=ROOT?>analysis" class="kpi-action-link">View Analysis <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"></path></svg>
                    </a>
                    <!-- Hidden Heatmap container if needed later: <div class="heatmap" id="heatmap-container"></div> -->
                </div>
            </div>

            <!-- 3. Avg Marks (Existing) -->
            <div class="stat-card">
                <div class="stat-icon-box">
                    <img src="<?=ROOT?>/assets/images/score (2).png" alt="Avg Marks">
                </div>
                <div class="stat-content">
                    <div>
                        <div class="kpi-title">Avg. Exercise Mark</div>
                        <div class="kpi-value" id="stat-marks">0%</div>
                    </div>
                    <div class="stat-sub" id="stat-marks-sub" style="display:none;">...</div>
                </div>
            </div>
        </div>
    </div>

    <main class="col-center">
        <div class="quick-actions-card">
            <h3>Quick Actions</h3>
            <div class="quick-actions-buttons">
                
                <!-- 1. Create a Note (Blue Solid) -->
                <a href="<?=ROOT?>notes/create" class="qa-button btn-solid-blue">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-notebook-pen-icon lucide-notebook-pen"><path d="M13.4 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-7.4"/><path d="M2 6h4"/><path d="M2 10h4"/><path d="M2 14h4"/><path d="M2 18h4"/><path d="M21.378 5.626a1 1 0 1 0-3.004-3.004l-5.01 5.012a2 2 0 0 0-.506.854l-.837 2.87a.5.5 0 0 0 .62.62l2.87-.837a2 2 0 0 0 .854-.506z"/></svg>
                    <span>Create a Note</span>
                </a>

                <!-- 2. Ask a Question (Red Light) -->
                <a href="<?=ROOT?>question/create" class="qa-button btn-light-red">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"/><path d="M12 17h.01"/></svg>
                    <span>Ask a Question</span>
                </a>

                <!-- 3. Create an Exercise (Blue Light) -->
                <a href="<?=ROOT?>exercises/create" class="qa-button btn-light-blue">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-dumbbell-icon lucide-dumbbell"><path d="M17.596 12.768a2 2 0 1 0 2.829-2.829l-1.768-1.767a2 2 0 0 0 2.828-2.829l-2.828-2.828a2 2 0 0 0-2.829 2.828l-1.767-1.768a2 2 0 1 0-2.829 2.829z"/><path d="m2.5 21.5 1.4-1.4"/><path d="m20.1 3.9 1.4-1.4"/><path d="M5.343 21.485a2 2 0 1 0 2.829-2.828l1.767 1.768a2 2 0 1 0 2.829-2.829l-6.364-6.364a2 2 0 1 0-2.829 2.829l1.768 1.767a2 2 0 0 0-2.828 2.829z"/><path d="m9.6 14.4 4.8-4.8"/></svg>
                    <span>Create an Exercise</span>
                </a>

                <!-- 4. Customizations (Generic) -->
                <a href="#" class="qa-button btn-generic">
                    <svg xmlns="http://www.w3.org/2000/svg" class="lucide-icon" viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12.22 2h-.44a2 2 0 0 0-2 2v.18a2 2 0 0 1-1 1.73l-.43.25a2 2 0 0 1-2 0l-.15-.08a2 2 0 0 0-2.73.73l-.78 1.25a2 2 0 0 0 .73 2.73l.15.08a2 2 0 0 1 1 1.74v.44a2 2 0 0 1-1 1.74l-.15.08a2 2 0 0 0-.73 2.73l.78 1.25a2 2 0 0 0 2.73.73l.15-.08a2 2 0 0 1 2 0l.43.25a2 2 0 0 1 1 1.73V20a2 2 0 0 0 2 2h.44a2 2 0 0 0 2-2v-.18a2 2 0 0 1 1-1.73l.43-.25a2 2 0 0 1 2 0l.15.08a2 2 0 0 0 2.73-.73l.78-1.25a2 2 0 0 0-.73-2.73l-.15-.08a2 2 0 0 1-1-1.74v-.44a2 2 0 0 1 1-1.74l.15-.08a2 2 0 0 0 .73-2.73l-.78-1.25a2 2 0 0 0-2.73-.73l-.15.08a2 2 0 0 1-2 0l-.43-.25a2 2 0 0 1-1-1.73V4a2 2 0 0 0-2-2z"/><circle cx="12" cy="12" r="3"/></svg>
                    <span>Customizations</span>
                </a>
                
                <!-- 5. More / Dropdown -->
                <div class="dropdown-container">
                    <a href="#" class="qa-button btn-generic" style="justify-content: space-between; gap: 0.5rem;">
                        <span>More</span>
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" style="color: inherit;"><polyline points="6 9 12 15 18 9"></polyline></svg>
                    </a>
                    <div class="dropdown-content">
                        <a href="<?=ROOT?>expertrequest/create">
                            <svg xmlns="http://www.w3.org/2000/svg" class="lucide-icon" viewBox="0 0 24 24" style="width: 14px; height: 14px;"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                            Become an Expert
                        </a>
                        <a href="<?=ROOT?>topic/create">
                            <svg xmlns="http://www.w3.org/2000/svg" class="lucide-icon" viewBox="0 0 24 24" style="width: 14px; height: 14px;"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2z"/><line x1="7" x2="17" y1="17" y2="17"/><line x1="7" x2="12" y1="13" y2="13"/></svg>
                            Create Topic
                        </a>
                        <a href="<?=ROOT?>analysis">
                            <svg xmlns="http://www.w3.org/2000/svg" class="lucide-icon" viewBox="0 0 24 24" style="width: 14px; height: 14px;"><path d="M22 12h-4l-3 9L9 3l-3 9H2"/></svg>
                            View Full Analysis
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="tabbed-hub">
            <div class="tab-controls">
                <div class="tab active" data-tab="pinned-notes" data-endpoint="/api/content/pinned-notes">
                    <svg xmlns="http://www.w3.org/2000/svg" class="lucide-icon" viewBox="0 0 24 24"><path d="M12 17V3"/><path d="M21 8H3"/><path d="M18 3h-2"/><path d="M8 3H6"/><path d="m14 17 2 2-6 6-2-2 6-6Z"/></svg>
                    Pinned Notes
                </div>
                <div class="tab" data-tab="asked-questions" data-endpoint="/api/content/asked-questions">
                    <svg xmlns="http://www.w3.org/2000/svg" class="lucide-icon" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"/><path d="M12 17h.01"/></svg>
                    Asked Questions
                </div>
                <div class="tab" data-tab="created-exercises" data-endpoint="/api/content/created-exercises">
                    <svg xmlns="http://www.w3.org/2000/svg" class="lucide-icon" viewBox="0 0 24 24"><path d="m14.4 14.4-.2.2c-.38.38-.97.5-1.48.33l-5.87-2.07c-.98-.34-1.45-1.55-.95-2.47l2.84-5.68c.5-.92 1.93-1.12 2.8-.46l1.37 1.03"/><path d="m5.7 15.6-.2-.2c-.38-.38-.5-1.02-.3-1.53l2.07-5.87c.34-.98 1.55-1.45 2.47-.95l5.68 2.84c.92.5 1.12 1.93.46 2.8l-1.03 1.37"/><path d="m19.6 15.6-.2-.2c-.38-.38-.5-1.02-.3-1.53l2.07-5.87c.34-.98 1.55-1.45 2.47-.95l5.68 2.84c.92.5 1.12 1.93.46 2.8l-1.03 1.37"/></svg>
                    Created Exercises
                </div>
                <div class="tab" data-tab="answered-exercises" data-endpoint="/api/content/answered-exercises">
                    <svg xmlns="http://www.w3.org/2000/svg" class="lucide-icon" viewBox="0 0 24 24"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><path d="m9 11 2 2 4-4"/></svg>
                    Answered Exercises
                </div>
                <div class="tab" data-tab="attempt-exercises" data-endpoint="/api/content/attempt-exercises">
                    <svg xmlns="http://www.w3.org/2000/svg" class="lucide-icon" viewBox="0 0 24 24"><path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"/><path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"/></svg>
                    Attempt Exercises
                </div>
                <div class="tab" data-tab="expert-requests" data-endpoint="/api/content/expert-requests" id="tab-expert-requests" style="display: none;">
                    <svg xmlns="http://www.w3.org/2000/svg" class="lucide-icon" viewBox="0 0 24 24"><circle cx="10" cy="8" r="5"/><path d="M2 21a8 8 0 0 1 14 0"/><path d="m18.5 17.5 4.5 4.5"/></svg>
                    Expert Requests
                </div>
                <div class="tab" data-tab="announcements" data-endpoint="/api/content/announcements">
                    <svg xmlns="http://www.w3.org/2000/svg" class="lucide-icon" viewBox="0 0 24 24"><path d="M6 8a6 6 0 0 1 12 0c0 7 3 9 3 9H3s3-2 3-9"/><path d="M10.3 21a1.94 1.94 0 0 0 3.4 0"/></svg>
                    Announcements
                </div>
            </div>

            <div class="tab-content active" id="pinned-notes"><div class="list-container"></div></div>
            <div class="tab-content" id="asked-questions"><div class="list-container"></div></div>
            <div class="tab-content" id="created-exercises"><div class="list-container"></div></div>
            <div class="tab-content" id="answered-exercises"><div class="list-container"></div></div>
            <div class="tab-content" id="attempt-exercises"><div class="list-container"></div></div>
            <div class="tab-content" id="expert-requests"><div class="list-container"></div></div>
            <div class="tab-content" id="announcements"><div class="list-container"></div></div>

        </div>

    </main>


    <aside class="col-right">
        
        <div class="widget-box">
            <div class="widget-title">
                Notifications
                <span style="font-size: 0.8rem; color: var(--color-primary-accent); cursor: pointer; font-weight: 500;">Clear</span>
            </div>
            
            <div id="notifications-list">
                </div>
            <div id="notifications-loader" class="loader">Loading...</div>
        </div>

        <div class="widget-box" id="community-banner">
            <div class="widget-title" style="color: white; margin-bottom: 1rem;" id="banner-title">Community Update</div>
            <p style="font-size: 0.95rem; line-height: 1.6; opacity: 0.95;color: white;" id="banner-message">...</p>
        </div>

    </aside>
</div>
</div>

<?php
    include_once "../app/views/partials/footer.view.php";
?>