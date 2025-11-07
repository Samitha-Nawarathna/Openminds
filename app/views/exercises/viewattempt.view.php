<?php

    $title = "Results | Openminds";
    $filename = "exercises/viewattempt";

    include_once "../app/views/partials/header.view.php";

?>

<?php 
// Helper function to format vote counts (from previous files)
function format_count($n) {
    if ($n >= 1000) {
        return round($n / 1000, 1) . 'k';
    }
    return $n;
}
?>

<?php 
// Assuming $data contains attempt_id passed from the Controller.
$attempt_id = $data['attempt_id'] ?? 2001; // Default to mock ID
$attempt_details_api_url = ROOT . '/exercises/api/history/' . $attempt_id;
$exercise_id = $data['exercise_id'] ?? 101; // Needed for vote buttons
$vote_status_api_url = ROOT . '/exercises/api/vote/' . $exercise_id;
$vote_submit_api_url = ROOT . '/exercises/api/vote/' . $exercise_id;
?>

<div id="summary-modal" class="modal">
    <div class="modal-content">

    <div id="modal-details-meta" class="meta-data">
            <p>Attempt ID: <strong><?= $attempt_id ?></strong></p>
            <p>Attempted On: <span id="modal-date">N/A</span></p>
        </div>

        <h2 id="modal-title"></h2>
        <p>You have reviewed the following exercise:</p>
        
        <div style="text-align:center; margin:20px 0; display:flex; justify-content:center; align-items:center; flex-direction:column; width:100%">
            <p class="caption" style="color:var(--color-placeholder); padding:var(--space-xs)">Your Score:</p> 
            <p id="final-score" class="score-badge" style="font-size: var(--font-size-xl); font-weight:700; width:fit-content">-- / --</p>
        </div>
        <div class="vote-area" style="justify-content:center;">
                <button id="upvote-btn" class="vote-btn btn-none">▲</button>
                <span id="" class="vote-count-display">25</span>
                <button id="downvote-btn" class="vote-btn btn-none">▼</button>
                <span id="vote-message"></span>
            </div>
    

        <button id="start-review-btn" class="btn primary modal-start-btn">Start Review</button>
    </div>
</div>

<div id="main-exercise-content" class="container hidden">
    
    <div class="exercise-header" style="display:none;">
        <h1 id="exercise-title">Loading...</h1>
        <p><span id="exercise-subject"></span></p>
    </div>

    <div class="question-block">
        <p id="question-prompt" class="question-prompt">...</p>
        
        <div id="answer-options" class="answer-options-list">
            </div>

        <div id="explanation-box" class="explanation-box">
            <h3 class="explain-icon">📖</h3>
            <p id="explanation-text">Explanation content will appear here.</p>
        </div>
    </div>
</div>

<div class="control-bar">
    <div class="left-controls">
        <div id="progress-area" class="progress-area">
            Question <span id="current-q-index">0</span> of <span id="total-q-count">0</span>
        </div>
        
        <div id="score-feedback-area" class="feedback-area">
            Score: <span id="current-q-score">-- / --</span>
        </div>
    </div>
    
    <div id="action-buttons" class="action-buttons">

    <div class="vote-area">
                <button id="upvote-btn" class="vote-btn btn-none">▲</button>
                <span id="" class="vote-count-display">25</span>
                <button id="downvote-btn" class="vote-btn btn-none">▼</button>
                <span id="vote-message"></span>
            </div>

        <button id="prev-btn" class="btn-none btn secondary" disabled>Previous</button>
        <button id="next-btn" class="btn-blue btn primary">Next Question</button>
    </div>
</div>

<script>
    // Constants for JS
    const ATTEMPT_DETAILS_URL = '<?= $attempt_details_api_url ?>';
    const VOTE_STATUS_URL = '<?= $vote_status_api_url ?>';
    const VOTE_SUBMIT_URL = '<?= $vote_submit_api_url ?>';
</script>

<?php include_once "../app/views/partials/footer.view.php"; ?>