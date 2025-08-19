<?php

$title = 'Analysis: Openminds';
$filename = 'analysis';

include_once '../app/views/partials/header.view.php';

?>

<div class="analysis-wrapper">
    <div class="note-wrapper module-wrapper">
        <div class="title">
            Note Analysis
        </div>
        <div class="section section1">
            <div class="title">
                overall analysis
            </div>

            <div class="analysis-content">
                <div class="frequent-topic-graph graph-container container">

                </div>
                <div class="overview-analysis">
                    <div class="stat-cards">
                        <div class="max-note-container container">
                            <div class="title">Max note topic</div>
                            <div class="count">27</div>
                            <div class="tile topic-name">Psychology</div>
                        </div>
                        <div class="min-note-container container">
                            <div class="title">Min note topic</div>
                            <div class="count">1</div>
                            <div class="tile topic-name">History</div>
                        
                        </div>
                    </div>
                    <div class="search-container container">

                    </div>
                </div>
            </div>
        </div>

        <div class="section section2">
            <div class="title">
                previous week analysis
            </div>
            <div class="analysis-content">
                <div class="growth-graph graph-container container">

                </div>

                <div class="overview-analysis">
                    <div class="stat-cards">
                        <div class="create-count-container  container">
                            <div class="title">Create count</div>
                            <div class="count">5</div>
                        </div>
                        <div class="edit-count-container container">
                            <div class="title">Edit count</div>
                            <div class="count">4</div>
                        </div>
                        <div class="delete-count-container container">
                            <div class="title">Delete count</div>
                            <div class="count">1</div>
                        </div>
                        <div class="share-count-container container">
                            <div class="title">Share count</div>
                            <div class="count">10</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="section section3">
        <div class="title">
                top tags analysis
            </div>
            <div class="analysis-content">            
                <div class="top-tag-graph graph-container container">

                </div>
                <div class="search-container container">
                        
                </div>
            </div>
        </div>
    </div>
    <div class="question-wrapper module-wrapper">
        <div class="title">Question Analysis</div>
        <div class="section section1">
            <div class="title">Vote analysis</div>
                <div class="analysis-content">
                    <div class="graph-container container vote-distribution">

                    </div>
                    <div class="overview-analysis">
                        <div class="stat-cards">
                            <div class="question-vote-container container">
                                <div class="title">votes for questions</div>
                                <div class="count">15</div>
                            </div>
                            <div class="question-vote-container container">
                                <div class="title">votes for answers</div>
                                <div class="count">12</div>
                            </div>                          
                        </div>
                        <div class="search-container container">
                        
                        </div>              
                    </div>
                </div>
            </div>
        </div>
        <div class="section section2">
            <div class="title">Question analysis</div>
        
            <div class="analysis-content">
                <div class="graph-container container question-tag-distribution">

                </div>
                <div class="search-container container">
                        
                </div>
            </div>
        </div>

        <div class="section section3">
            <div class="title">Answer analysis</div>
            <div class="analysis-content">
                <div class="graph-container container answer-tag-distribution">

                </div>       
                <div class="search-container container">
                        
                </div>
            </div>
        </div>        
    <div class="exercises-wrapper module-wrapper">
        <div class="title">Exercise Analysis</div>
        <div class="section section1">
            <div class="title">Mark analysis</div>
            <div class="analysis-content">
                <div class="graph-container container top-marks-subjects">

                </div>
                <div class="graph-container container worst-marks-subjects">

                </div>    
            </div>
        </div>
        <div class="section section2">
            <div class="analysis-content">
                <div class="search-container container">

                </div>
            </div>
        </div>     
    </div>
    <div class="mentor-exercises-wrapper module-wrapper">
        <div class="title">Created Exercises Analysis</div>
        <div class="section section1">
            <div class="title">Top subjects</div>
            <div class="analysis-content">
                <div class="graph-container container top-subject-graph">

                </div>
                <div class="overview-analysis">
                    <div class="search-container container">
                    
                    </div>
                </div>
            </div>
        </div>
        <div class="section section2">
            <div class="title">Student Mark analysis</div>
            <div class="analysis-content">
                <div class="graph-container container top-marks-exercises">

                </div>
                <div class="graph-container container top-marks-questions">

                </div>    
            </div>
        </div>
        <div class="section section3">
            <div class="analysis-content">
                <div class="search-container container">

                </div>
            </div>
        </div>
        <div class="section section4">
            <div class="title">
                exercise vote analysis
            </div>
            <div class="analysis-content">
                <div class="exercise-vote-graph graph-container container">

                </div>

                <div class="overview-analysis">
                <div class="stat-cards">
                    <div class="create-count-container  container">
                        <div class="title">Vote count</div>
                        <div class="count">5</div>
                    </div>                    
                </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<?php

include_once '../app/views/partials/footer.view.php';
?>