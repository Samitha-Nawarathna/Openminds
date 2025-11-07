<?php

    $title = "Creator Review | Openminds";
    $filename = "exercises/view"; // New file name

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
// Assuming $data contains exercise_id passed from the Controller (e.g., from the pending review list).
$exercise_id = $data['exercise_id'] ?? 105; // Default to mock ID
// Use the same API as loading the attempt exercise content, but it will be read-only here.
$review_api_url = ROOT . '/exercises/api/load_attempt_data/' . $exercise_id; 

$edit_url = ROOT . '/exercises/edit?id=' . $exercise_id;
$approve_url = ROOT . '/exercises/approve';
$reject_url = ROOT . '/exercises/reject';
?>

<style>
    .action-grid {
        display: grid;
        gap: var(--space-xs);
        margin-top: var(--space-md);
        grid-template-columns: repeat(2, 1fr);
        grid-template-rows: auto auto;
        width: 100%;
    }
    .action-grid .btn-full-row {
        grid-column: 1 / -1; /* Spans both columns */
    }

    .btn-full-width
    {
        width: 100%;
    }
</style>


<div id="action-modal" class="modal">
    <div class="modal-content">

    <span class="close-btn" onclick="window.closeModal('action-modal')">&times;</span>


    <div id="modal-details-meta" class="meta-data" style="padding-top:var(--space-xs);">
            <p>Exercise ID: <strong><?= $exercise_id ?></strong></p>
            <p>Created By: <span id="modal-creator">N/A</span></p>
        </div>

        <h2 id="modal-title">Review Actions</h2>
        <p>This exercise is pending review. Select an action below.</p>
        

        <div class="action-grid">
            <a id="edit-modal-btn" href="<?= $edit_url ?>" class="btn-blue btn primary btn-full-row btn-full-width">Edit Exercise Details</a>
            
            <button id="approve-modal-btn" class="btn secondary btn-full-width" data-action="approve">Hide</button>
            <button id="reject-modal-btn" class="btn-red btn-full-width" data-action="reject">Delete</button>
        </div>
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

        <a href="#" id="details-link" class="details-link">Details</a>

    </div>
    
    <div id="review-action-buttons" class="action-buttons" style="gap: var(--space-xs);">
        <a id="edit-btn" href="<?= $edit_url ?>" class="btn-none btn primary">Edit</a>
        <button id="approve-btn" class="btn-blue btn secondary" data-action="approve">Hide</button>
        <button id="reject-btn" class="btn-red" data-action="reject">Delete</button>
    </div>

    <div id="navigation-buttons" class="action-buttons">
        <button id="prev-btn" class="btn-none btn secondary" disabled>Previous</button>
        <button id="next-btn" class="btn-blue btn primary">Next</button>
    </div>
</div>

<script>
    // Constants for JS
    const REVIEW_API_URL = '<?= $review_api_url ?>';
    const APPROVE_URL = '<?= $approve_url ?>';
    const REJECT_URL = '<?= $reject_url ?>';
    const EXERCISE_ID = '<?= $exercise_id ?>';
    
    // Placeholder function for modal confirmation
    window.handleReviewAction = function(action) {
        if (confirm(`Are you sure you want to ${action} this exercise (ID: ${EXERCISE_ID})?`)) {
            // In a real application, you'd send an AJAX POST request here.
            alert(`ACTION: Submitting POST to ${action === 'approve' ? APPROVE_URL : REJECT_URL} with ID: ${EXERCISE_ID}`);
            window.location.href = '<?= ROOT ?>/exercises/expertreview'; // Redirect to review list
        }
    };
</script>

<?php include_once "../app/views/partials/footer.view.php"; ?>