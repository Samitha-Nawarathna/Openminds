<?php

$title = 'Analysis: Openminds';
$filename = 'analysis/systemview';

include_once '../app/views/partials/header.view.php';

?>

<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>


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