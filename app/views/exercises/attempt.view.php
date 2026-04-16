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
$mode = $data['mode'] ?? 'attempt'; // attempt | review

$attempt_api_url = ROOT . '/exercises/api/load_attempt_data/' . $exercise_id;
$review_api_url = ROOT . '/exercises/api/load_review_data/' . $exercise_id;
$vote_status_api_url = ROOT . '/exercises/api/vote/' . $exercise_id;
$vote_submit_api_url = ROOT . '/exercises/api/vote/' . $exercise_id;
$approve_url = ROOT . 'exercises/api/approve_exercise';
$reject_url = ROOT . 'exercises/api/reject_exercise';
?>

<!-- NEW: Loading Overlay for Initial Data Fetch -->
<div id="loading-overlay" class="loading-overlay">
    <div class="loading-spinner" role="status" aria-label="Loading exercise"></div>
    <p>Loading exercise...</p>
</div>

<!-- NEW: Removed Vote UI from Details Modal (will be moved to results page) -->
<div id="details-modal" class="modal <?php echo $mode === 'review' ? 'hidden' : ''; ?>" role="dialog" aria-labelledby="modal-title" aria-modal="true">
    <div class="modal-content">
        
        <div class="meta-data">
            <p><span id="modal-creator-name"></span> (<span id="modal-creator-role"></span>)</p>
            <p><span id="modal-date"></span></p>
        </div>

        <div id="modal-details-container">
            <h2 id="modal-title"></h2>
            <p>Subject: <span id="modal-subject" class="tag-pill yellow"></span></p>
            <p>Tags: <span id="modal-tags"></span></p>
        </div>
        
        <button id="start-btn" class="btn primary modal-start-btn" aria-label="Start exercise assessment">
            Start Assessment
        </button>
    </div>
</div>

<!-- NEW: Confirmation Modal for Final Submission (Attempt Mode Only) -->
<?php if ($mode === 'attempt'): ?>
<div id="confirmation-modal" class="confirmation-modal hidden" role="dialog" aria-labelledby="confirm-title" aria-modal="true">
    <div class="confirmation-content">
        <h3 id="confirm-title">Submit Your Assessment?</h3>
        <p>Once you submit, you cannot change your answers. Make sure you've reviewed all questions.</p>
        <div class="confirmation-actions">
            <button id="confirm-cancel-btn" class="btn secondary" aria-label="Cancel submission">
                Review Again
            </button>
            <button id="confirm-submit-btn" class="btn primary" aria-label="Confirm and submit assessment">
                Yes, Submit
            </button>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- NEW: Review Decision Panel (Expert Review Mode Only) -->
<?php if ($mode === 'review'): ?>
<div id="review-decision-modal" class="confirmation-modal hidden" role="dialog" aria-labelledby="review-decision-title" aria-modal="true">
    <div class="confirmation-content">
        <h3 id="review-decision-title">Finalize Expert Review</h3>
        <p>Please approve or reject this exercise. Feedback is optional for approval and required for rejection.</p>
        <textarea id="review-feedback" class="feedback-textarea" placeholder="Enter review feedback (required for rejection)"></textarea>
        <div class="confirmation-actions">
            <button id="review-cancel-btn" class="btn secondary" aria-label="Return to review">
                Continue Review
            </button>
            <button id="review-approve-btn" class="btn primary" aria-label="Approve exercise">
                Approve
            </button>
            <button id="review-reject-btn" class="btn-red" aria-label="Reject exercise">
                Reject
            </button>
        </div>
    </div>
</div>
<?php endif; ?>

<div class="container hidden" id="main-exercise-content">
    <header class="exercise-header" style="display:none;">
        <h1 id="exercise-title"></h1>
        <p id="exercise-subject"></p>
    </header>

    <div class="question-shell">
        <div class="title-stack">
            <h1 id="exercise-title-hero" class="page-title" aria-live="polite"></h1>
            <p id="exercise-subject-hero" class="page-subtitle"></p>
            <div class="progress-track" aria-hidden="true">
                <div id="progress-fill" class="progress-fill"></div>
            </div>
        </div>

        <div class="main-content">
            <div id="question-container" class="question-card" role="main" aria-live="polite">
                <!-- Question prompt and options are rendered by JS -->
            </div>
            <p id="question-subtext" class="question-subtext">Select the correct answer</p>
        </div>

        <div id="explanation-box" class="explanation-box hidden" role="region" aria-label="Answer explanation">
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
        <a href="#" id="details-link" class="details-link" aria-label="View exercise details">Details</a>
        
        <div id="feedback-area" class="feedback-area" role="status" aria-live="assertive" aria-atomic="true">
            <!-- Feedback appears here -->
        </div>
    </div>
    
    <div id="action-buttons" class="action-buttons">
        <!-- NEW: Previous Button -->
        <button id="prev-btn" class="btn secondary hidden" aria-label="Go to previous question">
            <  Previous
        </button>
        <button id="check-btn" class="btn primary" disabled aria-label="Check your answer">
            Check Answer
        </button>
        <button id="explain-btn" class="btn secondary hidden" aria-label="Show explanation">
            Explain
        </button>
        <button id="next-btn" class="btn primary hidden" aria-label="Go to next question">
            Next Question >
        </button>
        <button id="submit-btn" class="btn primary hidden" aria-label="Submit final assessment">
            Submit Assessment
        </button>
    </div>
</div>

<script>
    window.EXERCISE_MODE = '<?= $mode ?>';
    window.EXERCISE_API_URL = '<?= $mode === "review" ? $review_api_url : $attempt_api_url ?>';
    window.EXERCISE_SUBMIT_URL = '<?= $mode === "review" ? "" : (ROOT . "/exercises/api/attempt/" . $exercise_id) ?>';
    window.VOTE_STATUS_URL = '<?= $vote_status_api_url ?>';
    window.VOTE_SUBMIT_URL = '<?= $vote_submit_api_url ?>';
    window.REVIEW_APPROVE_URL = '<?= $approve_url ?>';
    window.REVIEW_REJECT_URL = '<?= $reject_url ?>';
    window.REVIEW_EXERCISE_ID = '<?= $exercise_id ?>';
</script>

<?php
    include_once "../app/views/partials/footer.view.php";
?>