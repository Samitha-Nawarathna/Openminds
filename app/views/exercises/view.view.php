<?php

    $title = "Review | Openminds";
    $filename = "exercises/view"; // New file name
    $add_back = true;

    include_once "../app/views/partials/header.view.php";

?>

<?php
    $exercise_details = $data['exercise_details'] ?? [];
    $questions = $data['questions'] ?? [];
    $review_data = $data['review_data'] ?? [];

    $initial_payload = [
        'exercise' => [
            'id' => (int)($exercise_details['id'] ?? 0),
            'title' => (string)($exercise_details['title'] ?? 'Exercise Review'),
            'description' => (string)($exercise_details['role'] ?? ''),
        ],
        'questions' => is_array($questions) ? $questions : [],
        'user_answers' => $review_data['user_answers'] ?? [],
        'correct_answers' => $review_data['correct_answers'] ?? [],
        'average_score' => $review_data['average_score'] ?? 0,
        'total_questions' => $review_data['total_questions'] ?? count($questions),
    ];
?>

<div class="review-shell" id="review-shell">
    <header class="review-topbar">
        <div class="review-topbar__left">
            <p class="eyebrow">Exercise Review</p>
            <h1 id="review-title">Loading exercise...</h1>
            <p id="review-description" class="review-description">Preparing review details.</p>
        </div>
    </header>

    <section class="review-stats">
        <article class="stat-card">
            <span class="stat-label">Average Score</span>
            <strong id="average-score">0%</strong>
        </article>
        <article class="stat-card">
            <span class="stat-label">Questions</span>
            <strong id="total-questions">0</strong>
        </article>
    </section>

    <section class="questions-section">
        <div class="section-header">
            <h2>Review Questions</h2>
            <span id="question-count-label">0 items total</span>
        </div>

        <div id="review-empty" class="empty-state" hidden>
            <p class="empty-state-message">No questions are available for this review.</p>
        </div>

        <div id="review-questions" class="question-list"></div>
    </section>

    <footer class="review-footer">
        <button id="end-review-btn" class="btn-blue" type="button">End Review</button>
    </footer>
</div>

<script>
    window.EXERCISE_REVIEW_INITIAL_DATA = <?= json_encode($initial_payload, JSON_UNESCAPED_SLASHES) ?>;
</script>

<?php include_once "../app/views/partials/footer.view.php"; ?>

