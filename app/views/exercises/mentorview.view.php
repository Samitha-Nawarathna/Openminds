<?php

    $title = "Exercise Overview | Openminds";
    $filename = "exercises/mentorview";
    $add_back = true;

    include_once "../app/views/partials/header.view.php";

    $exercise = $data['exercise'] ?? [];
    $questions = $data['questions'] ?? [];
    $stats = $data['stats'] ?? [];
    $edit_url = $data['edit_url'] ?? (ROOT . '/exercises/edit?id=' . ($exercise['id'] ?? 0));
    $hide_url = $data['hide_url'] ?? (ROOT . '/exercises/hide?id=' . ($exercise['id'] ?? 0));
    $toggle_url = $data['toggle_url'] ?? (ROOT . '/exercises/toggleVisibility');
    $status = strtolower(trim((string)($exercise['status'] ?? 'draft')));
    $status_label = $status === 'approved' ? 'Published' : 'Draft';
    $exercise_id = (int)($exercise['id'] ?? 0);
    $visibility = ($data['visibility'] ?? 'visible') === 'hidden' ? 'hidden' : 'visible';
    $toggle_label = $visibility === 'hidden' ? 'Show to Users' : 'Hide from Users';
?>

<div class="mentor-shell" data-exercise-id="<?= $exercise_id ?>" data-visibility="<?= htmlspecialchars($visibility) ?>" data-edit-url="<?= htmlspecialchars($edit_url) ?>" data-hide-url="<?= htmlspecialchars($hide_url) ?>" data-toggle-url="<?= htmlspecialchars($toggle_url) ?>">
    <header class="mentor-topbar">
        <div class="mentor-topbar__left">
            <p class="eyebrow"><?= htmlspecialchars($exercise['subject_name'] ?? 'Subject') ?></p>
            <h1><?= htmlspecialchars($exercise['title'] ?? 'Exercise Overview') ?></h1>
            <p class="mentor-description"><?= htmlspecialchars($exercise['description'] ?? 'No description provided.') ?></p>
        </div>
        <div class="mentor-topbar__right">
            <span class="status-badge status-<?= htmlspecialchars($status) ?>"><?= htmlspecialchars($status_label) ?></span>
            <div class="mentor-actions">
                <button id="hide-btn" class="btn-none" data-visibility="<?= htmlspecialchars($visibility) ?>"><?= htmlspecialchars($toggle_label) ?></button>
                <button id="edit-btn" class="btn-blue" style="display:none">Edit Exercise</button>
            </div>
        </div>
    </header>

    <section class="mentor-stats">
        <article class="stat-card">
            <span class="stat-label">Total Attempts</span>
            <strong><?= (int)($stats['attempt_count'] ?? 0) ?></strong>
        </article>
        <article class="stat-card">
            <span class="stat-label">Average Score</span>
            <strong><?= number_format((float)($stats['average_score'] ?? 0), 1) ?>%</strong>
        </article>
        <article class="stat-card">
            <span class="stat-label">Questions</span>
            <strong><?= (int)($stats['question_count'] ?? count($questions)) ?></strong>
        </article>
    </section>

    <section class="questions-section">
        <div class="section-header">
            <h2>Review Questions</h2>
            <span><?= count($questions) ?> items total</span>
        </div>

        <?php if (empty($questions)): ?>
            <div class="empty-state">
                <div class="empty-state-icon">📘</div>
                <p class="empty-state-message">No questions found in this exercise.</p>
                <a class="empty-state-link" href="<?= htmlspecialchars($edit_url) ?>">Create your first exercise</a>
            </div>
        <?php else: ?>
            <div class="question-list">
                <?php foreach ($questions as $index => $question): ?>
                    <?php
                        $options = $question['options'] ?? [];
                        $correct_count = 0;
                        foreach ($options as $option) {
                            if (!empty($option['is_correct'])) {
                                $correct_count++;
                            }
                        }
                    ?>
                    <article class="question-card">
                        <div class="question-card__head">
                            <span class="question-number"><?= (int)$index + 1 ?></span>
                            <div>
                                <h3><?= htmlspecialchars($question['prompt'] ?? '') ?></h3>
                                <p class="difficulty-tag">Difficulty: <?= htmlspecialchars((string)($question['weight'] ?? 1)) ?></p>
                            </div>
                        </div>

                        <div class="option-grid">
                            <?php foreach ($options as $option): ?>
                                <div class="option-card <?= !empty($option['is_correct']) ? 'is-correct' : '' ?>">
                                    <span class="option-text"><?= htmlspecialchars($option['text'] ?? '') ?></span>
                                    <?php if (!empty($option['is_correct'])): ?>
                                        <span class="correct-badge">CORRECT</span>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        </div>

                        <?php if (!empty($question['explanation'])): ?>
                            <div class="question-explanation">
                                <strong>Explanation</strong>
                                <p><?= htmlspecialchars($question['explanation']) ?></p>
                            </div>
                        <?php endif; ?>
                    </article>
                <?php endforeach; ?>
            </div>
            
        <?php endif; ?>
    </section>
</div>

<script>
    window.EXERCISE_MENTOR_EDIT_URL = <?= json_encode($edit_url, JSON_UNESCAPED_SLASHES) ?>;
    window.EXERCISE_MENTOR_HIDE_URL = <?= json_encode($hide_url, JSON_UNESCAPED_SLASHES) ?>;
    window.EXERCISE_MENTOR_TOGGLE_URL = <?= json_encode($toggle_url, JSON_UNESCAPED_SLASHES) ?>;
    window.EXERCISE_MENTOR_VISIBILITY = <?= json_encode($visibility, JSON_UNESCAPED_SLASHES) ?>;
</script>

<?php include_once "../app/views/partials/footer.view.php"; ?>