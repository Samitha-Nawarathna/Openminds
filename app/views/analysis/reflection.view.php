<?php

$title = 'Analysis: Openminds';
$filename = 'analysis/reflection';

include_once '../app/views/partials/header.view.php';

?>

<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>



    <main class="main-content">
        
        <h1 class="main-header">Reflection Analytics</h1>

        <section class="overview-grid" id="reflection-overview-panel">
        </section>

        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Note Activity Trends (Last 52 Weeks)</h3>
            </div>
            <div id="note-activity-chart-container"></div>
        </div>
        
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Subject Proficiency: Weekly Avg. Mark Change</h3>
                <select id="subject-selector" class="subject-selector">
                    </select>
            </div>
            <div id="subject-proficiency-chart-container"></div>
        </div>

        <div class="bar-charts-grid">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Top 10 Popular Tags (Last 4 Weeks)</h3>
                </div>
                <div id="popular-tags-chart-container"></div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Attention Drift by Tag (Last 4 Weeks)</h3>
                </div>
                <div id="attention-drift-container" class="attention-drift-grid">
                    </div>
            </div>
        </div>

    </main>

<?php
    include_once '../app/views/partials/footer.view.php';
?>