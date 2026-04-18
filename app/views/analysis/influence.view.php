<?php

$title = 'Analysis: Openminds';
$filename = 'analysis/influence';

include_once '../app/views/partials/header.view.php';

?>

<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>




    <main class="main-content">
        
        <h1 class="main-header">Community Influence Analytics</h1>

        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Q&A Contribution Summary</h3>
            </div>
            <div id="qa-contribution-chart-container"></div>
        </div>

        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Vote Given Trend (From Last 52 Weeks)</h3>
            </div>
            <div id="weekly-votes-chart-container"></div>
        </div>
        
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Top 5 Community Answers</h3>
            </div>
            <div class="table-container">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th style="width: 70%;">Question</th>
                            <th style="width: 15%;">Vote Count</th>
                            <th style="width: 15%;">Accepted Status</th>
                        </tr>
                    </thead>
                    <tbody id="top-answers-table-body">
                        </tbody>
                </table>
            </div>
        </div>

    </main>



<?php
    include_once '../app/views/partials/footer.view.php';
?>