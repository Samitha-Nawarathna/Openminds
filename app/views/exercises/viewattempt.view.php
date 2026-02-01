<?php

    $title = "Results | Openminds";
    $filename = "exercises/viewattempt";

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
// Assuming $data contains attempt_id passed from the Controller.
$attempt_id = $data['attempt_id'] ?? 2001; // Default to mock ID
// Use the actual attempt ID from the controller to avoid 404s on the results fetch
$attempt_details_api_url = ROOT . '/exercises/api/history/' . $attempt_id;
$exercise_id = $data['exercise_id'] ?? 101; // Needed for vote buttons
$vote_status_api_url = ROOT . '/exercises/api/vote/' . $exercise_id;
$vote_submit_api_url = ROOT . '/exercises/api/vote/' . $exercise_id;
?>

<!-- NEW: Loading Overlay for Initial Data Fetch -->
<div id="loading-overlay" class="loading-overlay">
    <div class="loading-spinner" role="status" aria-label="Loading results"></div>
    <p>Loading results...</p>
</div>

<!-- Summary Modal with Vote UI -->
<div id="summary-modal" class="modal" role="dialog" aria-labelledby="modal-title" aria-modal="true">
    <div class="modal-content">
        <h2 id="modal-title">Quiz Complete!</h2>
        <p style="color: var(--color-text-muted); margin-bottom: 20px;">
            You scored <strong id="score-text">-- / --</strong>
        </p>
        
        <div style="text-align:center; margin:20px 0; display:flex; justify-content:center; align-items:center; flex-direction:column; width:100%">
            <p id="final-score" class="score-badge" style="font-size: 2.5rem; font-weight:700; width:fit-content; margin: 0;">-- / --</p>
        </div>

        <!-- Vote UI (Only in Modal) -->
        <div class="vote-area" style="justify-content:center;" role="group" aria-label="Vote for this exercise">
            <button id="upvote-btn" class="vote-btn btn-none" aria-label="Upvote this exercise">▲</button>
            <span class="vote-count-display" id="vote-count-display" aria-live="polite">0</span>
            <button id="downvote-btn" class="vote-btn btn-none" aria-label="Downvote this exercise">▼</button>
        </div>
        <p id="vote-message" style="text-align:center; color:var(--color-text-muted); font-size:var(--font-size-sm); margin-top:var(--space-xs);" aria-live="polite"></p>

        <div class="modal-actions" style="display: flex; gap: 10px; margin-top: 20px;">
            <button id="try-again-btn" class="btn secondary" style="flex: 1;" aria-label="Try this exercise again">
                Try Again
            </button>
            <button id="finished-btn" class="btn primary" style="flex: 1;" aria-label="Return to exercise list">
                Finished
            </button>
        </div>
        
        <button id="start-review-btn" class="btn secondary modal-start-btn" style="margin-top: 10px;" aria-label="Start reviewing answers">
            Review Answers
        </button>
    </div>
</div>

<div id="main-exercise-content" class="container hidden">
    <header class="exercise-header" style="display:none;">
        <h1 id="exercise-title">Loading...</h1>
        <p><span id="exercise-subject"></span></p>
    </header>

    <div class="question-shell">
        <div class="title-stack">
            <h1 id="exercise-title-hero" class="page-title">Loading...</h1>
            <p id="exercise-subject-hero" class="page-subtitle"></p>
            <div class="progress-track" aria-hidden="true">
                <div id="progress-fill" class="progress-fill"></div>
            </div>
        </div>

        <div class="main-content">
            <div id="question-container" class="question-card" role="main" aria-live="polite">
                <!-- Questions will be dynamically rendered here -->
            </div>
            <p id="question-subtext" class="question-subtext">Review your answers</p>
        </div>

        <div id="explanation-box" class="explanation-box" role="region" aria-label="Answer explanation">
            <!-- Explanation will be dynamically rendered here -->
        </div>
    </div>
</div>

<div id="control-bar" class="control-bar">
    <div class="left-controls">
        <div id="progress-area" class="progress-area" role="status" aria-live="polite">
            <span class="sr-only">Current progress: </span>
            Question <span id="current-q-index">0</span> of <span id="total-q-count">0</span>
        </div>
        
        <div id="score-feedback-area" class="feedback-area" role="status" aria-live="assertive">
            Score: <span id="current-q-score">-- / --</span>
        </div>
    </div>
    
    <div id="action-buttons" class="action-buttons">
        <button id="prev-btn" class="btn secondary" disabled aria-label="Go to previous question">
            ← Previous
        </button>
        <button id="next-btn" class="btn primary" aria-label="Go to next question">
            Next Question →
        </button>
    </div>
</div>

<script>
    // Constants for JS
    window.ATTEMPT_DETAILS_URL = '<?= $attempt_details_api_url ?>';
    window.VOTE_STATUS_URL = '<?= $vote_status_api_url ?>';
    window.VOTE_SUBMIT_URL = '<?= $vote_submit_api_url ?>';
</script>

<?php include_once "../app/views/partials/footer.view.php"; ?>