<?php

$title = 'Analysis: Openminds';
$filename = 'analysis/systemview';

include_once '../app/views/partials/header.view.php';

?>

<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>


<div class="sidebar">
        <div class="sidebar-menu-title">Main Menu</div>

    <ul class="sidebar-menu">
        <li>
            <a href="<?=ROOT?>/analysis" class="sidebar-link">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path></svg>
                Overview
            </a>
        </li>
        <li>
            <a href="<?=ROOT?>/analysis/reflection" class="sidebar-link">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path></svg>
                Reflection
            </a>
        </li>
        <li>
            <a href="<?=ROOT?>/analysis/influence" class="sidebar-link">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" d="M11 3.055A9.001 9.001 0 1020.945 13H11V3.055z"></path><path stroke-linecap="round" stroke-linejoin="round" d="M20.488 9H15V3.512A9.025 9.025 0 0120.488 9z"></path></svg>
                Influence
            </a>
        </li>
        <li>
            <a href="<?=ROOT?>/analysis/insights" class="sidebar-link">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7h12m0 0l-4-4m4 4l-4 4m-5 3H4m0 0l4 4m-4-4l4-4"></path></svg>
                Insights & Recommendations
            </a>
        </li>
        <li>
            <a href="<?=ROOT?>/analysis/systemview" class="sidebar-link active">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H7a3 3 0 00-3 3v8a3 3 0 003 3z"></path></svg>
                System Overview
            </a>
        </li>
    </ul>
</div>

    <main class="main-content">
        
        <h1 class="main-header">Admin Dashboard</h1>

        <section class="overview-grid">
    <div class="card kpi-card">
        <div class="kpi-title">Pending Expert Requests</div>
        <div id="kpi-expert-requests" class="kpi-value" style="color: var(--color-secondary-accent);">...</div> 
        <a href="#" class="kpi-action-link">
            Review Requests
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"></path></svg>
        </a>
    </div>
    <div class="card kpi-card">
        <div class="kpi-title">Total Active Profiles</div>
        <div id="kpi-active-profiles" class="kpi-value" style="color: var(--color-primary-accent);">...</div> 
        <a href="#" class="kpi-action-link">
            View All Profiles
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"></path></svg>
        </a>
    </div>
    <div class="card kpi-card">
        <div class="kpi-title">System Health Score</div>
        <div id="kpi-system-health" class="kpi-value" style="color: var(--color-tertiary-accent);">...</div> 
        <a href="#" class="kpi-action-link">
            View System Logs
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"></path></svg>
        </a>
    </div>
</section>        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Profile Status Overview</h3>
            </div>
            <div id="profile-status-chart-container"></div>
        </div>

        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Subject Performance and Management</h3>
            </div>
            <div class="table-container">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th style="width: 25%;">Subject</th>
                            <th style="width: 15%;">Growth Rate</th>
                            <th style="width: 20%;">Avg. Exercise Score</th>
                            <th style="width: 20%;">Exercise Count</th>
                            <th style="width: 20%;">Expert Count</th>
                        </tr>
                    </thead>
                    <tbody id="subject-management-table-body">
                        </tbody>
                </table>
            </div>
        </div>

    </main>



<?php
    include_once '../app/views/partials/footer.view.php';
?>