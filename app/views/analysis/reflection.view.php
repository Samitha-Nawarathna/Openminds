<?php

$title = 'Analysis: Openminds';
$filename = 'analysis/reflection';

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
            <a href="<?=ROOT?>/analysis/reflection" class="sidebar-link active">
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
            <a href="<?=ROOT?>/analysis/systemview" class="sidebar-link">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H7a3 3 0 00-3 3v8a3 3 0 003 3z"></path></svg>
                System Overview
            </a>
        </li>
    </ul>
</div>

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