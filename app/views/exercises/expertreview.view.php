<?php

    $title = "view exercise | Openminds";
    $filename = "exercises/expertreview";

    include_once "../app/views/partials/header.view.php";

    function format_count($n) {
        if ($n >= 1000) {
            return round($n / 1000, 1) . 'k';
        }
        return $n;
    }

?>

<div class="attempt-wrapper">

    <div class="attempt-main-content">
        <div class="review-management-panel">

            <div class="management-card">
                <form action="<?=ROOT?>/exercises/approve" method="POST">
                    <input type="hidden" name="exercise_id" value="<?= htmlspecialchars($data['exercise_details']['id']) ?>">
                    <input type="hidden" name="status" value="approved">
                    <button type="submit" class="btn-approve button btn-primary">Approve</button>
                </form>
                
                <button class="btn-reject button btn-error" id="btn-reject">Reject</button>
            </div>
        </div>
    

        <div class="question-flipper">
            <div class="decorative-card"></div>
            
            <div class="question-card">
                <header class="question-header">
                    <span id="question-number">Question 1 of 3</span>
                </header>
                
                <p class="question-text" id="question-text-p">
                    </p>

                <div class="options-list" id="options-list">
                    </div>

                <footer class="question-navigation">
                    <button class="btn-back" id="btn-back">Back</button>
                    <button class="btn-next" id="btn-next">Next</button>
                </footer>
            </div>
        </div>
    </div>

        <div class="exercise-details-card">
            <h2><?= htmlspecialchars($data['exercise_details']['title']) ?></h2>
            <div class="meta-info">
                by <strong><?= htmlspecialchars($data['exercise_details']['creator']) ?></strong>
                <span class="role"><?= htmlspecialchars($data['exercise_details']['role']) ?></span>
                at <?= htmlspecialchars($data['exercise_details']['created_at']) ?>
            </div>
            <div class="vote-stats">
                <button id="btn-upvote" class="vote-btn upvote">
                    👍 <span id="upvote-count"><?= format_count($data['exercise_details']['upvotes']) ?></span>
                </button>
                <button id="btn-downvote" class="vote-btn downvote">
                    👎 <span id="downvote-count"><?= format_count($data['exercise_details']['downvotes']) ?></span>
                </button>
            </div>
            <div class="tags-list">
                <?php foreach ($data['exercise_details']['tags'] as $tag): ?>
                    <span class="tag-pill"><?= htmlspecialchars($tag) ?></span>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <div id="reject-modal" class="modal">
    <div class="modal-content">
        <span class="close-button" id="close-reject-modal">&times;</span>
        <h2>Reason for Rejection</h2>
        <p>Please provide feedback for the creator before rejecting this exercise.</p>
        
        <form id="reject-form" action="<?=ROOT?>/exercises/reject" method="POST">
            <input type="hidden" name="exercise_id" id="reject-exercise-id" value="<?= htmlspecialchars($data['exercise_details']['id']) ?>">
            <input type="hidden" name="status" value="rejected">
            <input type="hidden" name="reviewer_id" value="expert_user_123"> 

            <div class="input-group">
                <label for="reject-reason">Reason for Rejection</label>
                <textarea id="reject-reason" name="reason" rows="4" placeholder="e.g., Question 2 is ambiguous..." required></textarea>
            </div>

            <button type="submit" class="btn-submit-rejection button btn-primary">Send Rejection</button>
        </form>
    </div>
</div>

    <script>
        // Pass the questions data and view mode flag
        const ALL_QUESTIONS_DATA = <?= json_encode($data['questions']) ?>;
        const VIEW_MODE = '<?= $view_mode ?>';
    </script>

    <script>
        // Pass the questions data to the JavaScript file
        const ALL_QUESTIONS_DATA = <?= json_encode($data['questions']) ?>;
        const isReviewMode = true;
    </script>

<?php
    include_once "../app/views/partials/footer.view.php";
?>