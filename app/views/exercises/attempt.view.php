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

<div class="attempt-wrapper">

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

    <script>
        // Pass the questions data to the JavaScript file
        const ALL_QUESTIONS_DATA = <?= json_encode($data['questions']) ?>;
        const userVoteStatus = <?= json_encode($data['exercise_details']['user_vote_status'])?>;
        const EXERCISE_ID = <?= json_encode($data['exercise_details']['id']) ?>;
    </script>

<?php
    include_once "../app/views/partials/footer.view.php";
?>