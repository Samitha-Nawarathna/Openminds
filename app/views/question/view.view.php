<?php

$title = 'Question view';
$filename = 'question/view';

include_once '../app/views/partials/header.view.php';

?>

<?php
// Helper function to format large numbers
function format_count($n) {
    if ($n >= 1000) {
        return round($n / 1000, 1) . 'k';
    }
    return $n;
}

// --- NEW: Check if the current user has already answered ---
$currentUserHasAnswered = false;
foreach ($data['answers'] as $answer) {
    if ($answer['creator_id'] === $data['current_user_id']) {
        $currentUserHasAnswered = true;
        break;
    }
}


// --- UPDATED LOGIC ---

// Check 1: Is the current user the one who created the question?
$isQuestionCreator = ($data['question']['creator_id'] === $data['current_user_id']);

// Check 2: Has the current user (if not the creator) already posted an answer?
$currentUserHasAnswered = false;
if (!$isQuestionCreator) { // No need to check if they are the creator
    foreach ($data['answers'] as $answer) {
        if ($answer['creator_id'] === $data['current_user_id']) {
            $currentUserHasAnswered = true;
            break;
        }
    }
}
?>



<div class="container">
  <section class="question-container">
    <p class="question-meta-top">
      by <strong><?= htmlspecialchars($data['question']['creator']) ?></strong>
      at <?= htmlspecialchars($data['question']['created_at']) ?>
    </p>

    <h1><?= htmlspecialchars($data['question']['title']) ?></h1>
    <p class="question-content"><?= htmlspecialchars($data['question']['content']) ?></p>

    <div class="tags">
      <?php foreach ($data['question']['tags'] as $tag): ?>
        <span class="tag-item"><?= htmlspecialchars($tag) ?></span>
      <?php endforeach; ?>
    </div>

    <div class="vote-stats">
      <span class="upvote-count">👍 <?= format_count($data['question']['upvotes']) ?></span>
      <span class="downvote-count">👎 <?= format_count($data['question']['downvotes']) ?></span>
    </div>
  </section>

  <?php foreach ($data['answers'] as $answer): ?>
    <section class="answer-container <?= $answer['is_chosen'] ? 'chosen-answer' : '' ?>">
      <?php //if ($answer['creator_id'] === $data['current_user_id']):
            if (1):
        ?>
        <div class="your-answer-badge">your answer</div>
      <?php endif; ?>

      <?php if ($answer['is_chosen']): ?>
        <div class="chosen-badge">ASKER'S CHOICE</div>
      <?php endif; ?>

      <p class="answer-meta-top">
        by <strong><?= htmlspecialchars($answer['creator']) ?></strong>
        <span class="role"><?= htmlspecialchars($answer['role']) ?></span>
        at <?= htmlspecialchars($answer['created_at']) ?>
      </p>

      <div class="answer-content"><?= $answer['content'] ?></div>

      <?php //if ($answer['creator_id'] === $data['current_user_id']):
            if (1):
        ?>
        <div class="action-buttons">
          <button class="btn-edit" data-type="answer" data-id="<?= $answer['id'] ?>">Edit</button>
          <button class="btn-delete" data-type="answer" data-id="<?= $answer['id'] ?>">Delete</button>
        </div>
      <?php else: ?>
        <div class="vote-stats">
          <span class="upvote-count">👍 <?= format_count($answer['upvotes']) ?></span>
          <span class="downvote-count">👎 <?= format_count($answer['downvotes']) ?></span>
        </div>
      <?php endif; ?>
    </section>
  <?php endforeach; ?>
</div>


<?php //if (!$currentUserHasAnswered): ?>
  <?php if (1): ?>
  <div class="fixed-footer">
  <div class="action-buttons-footer">
    <button id="answer-btn"><a href="<?=ROOT?>/question/answer?=<?=$data['question']['id']?>" class="no-style-link">Answer</a></button>
<?php endif; ?>

<?php //if ($isQuestionCreator): ?>
  <?php if (1): ?>

      <button class="btn-edit" data-type="question" data-id="<?= $data['question']['id'] ?>">Edit</button>
      <button class="btn-delete" data-type="question" data-id="<?= $data['question']['id'] ?>">Delete</button>
    </div>

<?php endif; ?>

<script>

</script>


    <script src="scripts.js"></script>

<?php

include_once '../app/views/partials/footer.view.php';

?>