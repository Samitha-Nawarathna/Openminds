<?php

    $title = "Expert Review | Openminds";
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

    /* Style for the Feedback Textarea */
    .feedback-textarea {
        width: 100%;
        min-height: 100px;
        padding: var(--space-sm);
        border: 1px solid var(--color-border);
        border-radius: var(--radius-sm);
        margin-top: var(--space-md);
        resize: vertical;
        font-family: inherit; /* Inherit global font */
    }

    /* Ensure buttons in confirmation modal are full-width */
    #confirmation-modal .action-grid {
        grid-template-columns: repeat(2, 1fr);
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
            
            <button id="approve-modal-btn" class="btn secondary btn-full-width" data-action="approve">Approve</button>
            <button id="reject-modal-btn" class="btn-red btn-full-width" data-action="reject">Reject</button>
        </div>
    </div>
</div>

<div id="confirmation-modal" class="modal hidden">
    <div class="modal-content">
        <h2 id="confirm-modal-title">Confirm Action</h2>
        <p id="confirm-modal-text">Please confirm your action.</p>
        
        <div id="feedback-wrapper">
            <textarea id="feedback-text" class="feedback-textarea" placeholder="Enter your review feedback here..."></textarea>
        </div>

        <div class="action-grid" style="margin-top: 20px;">
            <button id="confirm-submit-btn" class="btn-blue btn-full-width" data-action="">Confirm & Submit</button>
            <button id="confirm-cancel-btn" class="btn-none btn-full-width">Cancel</button>
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
        <button id="approve-btn" class="btn-blue btn secondary" data-action="approve">Approve</button>
        <button id="reject-btn" class="btn-red" data-action="reject">Reject</button>
    </div>

    <div id="navigation-buttons" class="action-buttons">
        <button id="prev-btn" class="btn-none btn secondary" disabled>Previous</button>
        <button id="next-btn" class="btn-blue btn primary">Next</button>
    </div>
</div>

<script>
    // Constants for JS
    const ROOT = '<?= ROOT ?>';
    const REVIEW_API_URL = '<?= $review_api_url ?>';
    const APPROVE_URL = '<?= $approve_url ?>';
    const REJECT_URL = '<?= $reject_url ?>';
    const EXERCISE_ID = '<?= $exercise_id ?>';
    
    let currentAction = ''; // Store the pending action (approve/reject)

    /**
     * Shows the confirmation modal and sets up the final submission.
     * @param {string} action - 'approve' or 'reject'
     */
    window.handleReviewAction = function(action) {
        currentAction = action; // Store the action globally
        window.closeModal('action-modal'); // Close the initial modal if it's open
        
        const confirmModal = document.getElementById('confirmation-modal');
        const confirmTitle = document.getElementById('confirm-modal-title');
        const confirmText = document.getElementById('confirm-modal-text');
        const feedbackTextarea = document.getElementById('feedback-text');
        const feedbackWrapper = document.getElementById('feedback-wrapper'); // New element
        const confirmSubmitBtn = document.getElementById('confirm-submit-btn');

        // Reset and setup UI based on action
        feedbackTextarea.value = '';
        confirmSubmitBtn.classList.remove('btn-red', 'btn-blue');
        
        if (action === 'reject') {
            confirmTitle.textContent = 'Confirm Rejection';
            confirmText.textContent = 'Please provide detailed feedback on why this exercise is being rejected.';
            feedbackTextarea.placeholder = 'Enter detailed reasons for rejection... (Required)';
            confirmSubmitBtn.textContent = 'Reject & Submit';
            confirmSubmitBtn.classList.add('btn-red');
            feedbackWrapper.style.display = 'block'; // SHOW feedback field
        } else { // approve
            confirmTitle.textContent = 'Confirm Approval';
            confirmText.textContent = 'Are you sure you want to approve this exercise?';
            confirmSubmitBtn.textContent = 'Approve & Submit';
            confirmSubmitBtn.classList.add('btn-blue');
            feedbackWrapper.style.display = 'none'; // HIDE feedback field
        }
        
        confirmModal.classList.remove('hidden');
    };
    
    // Final handler for the confirmation modal's submit button
    document.getElementById('confirm-submit-btn').addEventListener('click', function() {
        const feedbackTextarea = document.getElementById('feedback-text');
        const action = currentAction; // Retrieve stored action

        // Only retrieve and validate feedback if the action is 'reject'
        let feedback = '';
        if (action === 'reject') {
            feedback = feedbackTextarea.value.trim();
            // Validation for rejection
            if (feedback.length < 5) {
                alert('Rejection requires a minimum of 5 characters of feedback.');
                feedbackTextarea.focus();
                return;
            }
        }
        // For 'approve', 'feedback' remains an empty string.

        // Determine target URL and POST data
        const targetUrl = action === 'approve' ? APPROVE_URL : REJECT_URL;
        
        // Use FormData to prepare data for submission
        const formData = new FormData();
        formData.append('exercise_id', EXERCISE_ID);
        // Submit the feedback text (will be empty for approve, required string for reject)
        formData.append('feedback', feedback);

        // Submit data using Fetch API
        fetch(targetUrl, {
            method: 'POST',
            body: formData
        })
        .then(response => {
            // Check if response is JSON or successful redirect/status
            if (response.ok) {
                alert(`Successfully submitted ${action} for Exercise ID ${EXERCISE_ID}.`);
                window.location.href = `${ROOT}/exercises/expertreview`; // Redirect to review list
            } else {
                response.json().then(data => {
                    alert(`Failed to complete action: ${data.message || 'An unknown server error occurred.'}`);
                    window.closeModal('confirmation-modal');
                }).catch(() => {
                    alert(`Failed to complete action (HTTP Status ${response.status}).`);
                    window.closeModal('confirmation-modal');
                });
            }
        })
        .catch(error => {
            console.error('Submission Error:', error);
            alert('A network error occurred during submission.');
        });
    });

    // Handle cancel button on the confirmation modal
    document.getElementById('confirm-cancel-btn').addEventListener('click', () => {
        window.closeModal('confirmation-modal');
    });

    // Global function to close any modal
    window.closeModal = function(id) {
        document.getElementById(id).classList.add('hidden');
    };
</script>

<?php include_once "../app/views/partials/footer.view.php"; ?>