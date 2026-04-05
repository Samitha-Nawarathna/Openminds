<?php

$title = 'Analysis: Openminds';
$filename = 'analysis/index';

include_once '../app/views/partials/header.view.php';

?>

<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>


    <main class="main-content">
        
        <h1 class="main-header">Welcome Back!</h1>
            

        <section class="overview-grid" id="overview-panel">
        </section>

        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Weekly Activity Trends (Last 52 Weeks)</h3>
                <div class="transaction-toggles" id="trend-chart-toggles">
                    <button class="toggle-button active" data-series="notes">Notes Created</button>
                    <button class="toggle-button" data-series="questions">Questions Asked</button>
                    <button class="toggle-button" data-series="exercises">Exercises Attempted</button>
                </div>
            </div>
            <div id="weekly-trends-chart-container"></div>
        </div>
        
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">52-Week Activity Matrix (Daily Consistency)</h3>
            </div>
            <div id="activity-matrix-container"></div>
        </div>

        <div class="top-analytics-grid">
            <div class="card" id="top-subjects-card">
                <div class="card-header">
                    <h3 class="card-title">Top 10 Subjects by Avg. Score</h3>
                    <div class="transaction-toggles" id="subject-chart-toggles">
                        <button class="toggle-button active" data-subject-view="all_time">All Time</button>
                        <button class="toggle-button" data-subject-view="last_month">Last Month</button>
                    </div>
                </div>
                <div id="top-subjects-chart-container"></div>
            </div>

            <div class="card" id="top-tags-card">
                <div class="card-header">
                    <h3 class="card-title">Top 10 Tags Activity</h3>
                    <div class="transaction-toggles" id="tag-chart-toggles">
                        <button class="toggle-button active" data-tag-view="all_time">All Time</button>
                        <button class="toggle-button" data-tag-view="last_week">Last Week</button>
                    </div>
                </div>
                <div id="top-tags-chart-container"></div>
            </div>
        </div>

    </main>


<?php
    include_once '../app/views/partials/footer.view.php';
?>