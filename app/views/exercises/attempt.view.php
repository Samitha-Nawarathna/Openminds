<?php

    $title = "attempt | Openminds";
    $filename = "exercises/attempt";

    include_once "../app/views/partials/header.view.php";

?>

<?php

// Helper function to format vote counts
function format_count($n) {
    if ($n >= 1000) {
        return round($n / 1000, 1) . 'k';
    }
    return $n;
}
?>

<?php 
// Assuming $data contains initial metadata (like exercise_id) passed from the Controller.
$exercise_id = $data['exercise_id'] ?? 92; // Default to mock ID
$attempt_api_url = ROOT . '/exercises/api/load_attempt_data/' . $exercise_id;
$vote_status_api_url = ROOT . '/exercises/api/vote/' . $exercise_id;
$vote_submit_api_url = ROOT . '/exercises/api/vote/' . $exercise_id;
?>


<div id="details-modal" class="modal">
    <div class="modal-content">
        <div class="meta-data">
            <p><span id="modal-creator-name"></span> (<span id="modal-creator-role"></span>)</p>
            <p><span id="modal-date"></span></p>
        </div>

        <!-- <h2>Exercise Overview</h2> -->

        <div id="modal-details-container">
            <h2 id="modal-title"></h2>
            <p>Subject: <span id="modal-subject"></span></p>
            <p><span id="modal-tags"></span></p>
            
            <div class="vote-area">
                <button id="upvote-btn" class="vote-btn btn-none">▲</button>
                <span id="" class="vote-count-display">25</span>
                <button id="downvote-btn" class="vote-btn btn-none">▼</button>
                <span id="vote-message"></span>
            </div>
            
        </div>
        
        <button id="start-btn" class="btn primary modal-start-btn">Start Assessment</button>
    </div>
</div>
<div class="container hidden" id="main-exercise-content">
    <header class="exercise-header" style="display:none;">
        <h1 id="exercise-title"></h1>
        <p id="exercise-subject"></p>
    </header>

    <div class="main-content">
        <div id="question-container">
            <div id="question-prompt" class="question-prompt"></div>
            <div id="answer-options" class="answer-options-list"></div>
        </div>
    </div>

    <div id="explanation-box" class="explanation-box hidden">
        <h3 class="explain-icon">📖</h3>
        <p id="explanation-text"></p>
    </div>
</div>

<div class="control-bar">
    <div class="left-controls">
        <div id="progress-area" class="progress-area">
            Question <span id="current-q-index">0</span> of <span id="total-q-count">0</span>
        </div>
        <a href="#" id="details-link" class="details-link">Details</a>
        
        <div id="feedback-area" class="feedback-area">
            </div>
    </div>
    
    <div id="action-buttons" class="action-buttons">
        <button id="check-btn" class="btn primary" disabled>Check Answer</button>
        <button id="explain-btn" class="btn secondary hidden">Explain</button>
        <button id="next-btn" class="btn primary hidden">Next Question</button>
        <button id="submit-btn" class="btn primary hidden">Submit Assessment</button>
    </div>
</div>

<script>
    const API_URL = '<?= $attempt_api_url ?>';
    const SUBMIT_URL = '<?= ROOT . "/exercises/api/attempt/" . $exercise_id ?>';
    const VOTE_STATUS_URL = '<?= $vote_status_api_url ?>';
    const VOTE_SUBMIT_URL = '<?= $vote_submit_api_url ?>';
    
    // Global variable to hold all exercise data and state
    let EXERCISE_DATA = {};
    let currentQIndex = 0;
    let userAnswers = {}; 
    let currentVoteStatus = 'None'; // Store user's current vote
</script>

<?php
    include_once "../app/views/partials/footer.view.php";
?>