<?php

$title = 'Answer creator';
$filename = 'question/answer_creator';

include_once '../app/views/partials/header.view.php';

?>

<?php
// --- MOCK DATA SETUP ---
// Data required for the view (e.g., the specific question being answered)
$data = [
    'question_id'     => 2,
    'question_title'  => 'What are myelinated axons?',
];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Answer Question: <?= htmlspecialchars($data['question_title']) ?></title>
    <link rel="stylesheet" href="styles_create.css"> 
</head>
<body>

    <div class="form-wrapper">
        <h2 class="form-title">Answering: <?= htmlspecialchars($data['question_title']) ?></h2>
        
        <form action="<?=ROOT?>/question/answer" method="POST" id="answer-form">
            
            <input type="hidden" name="question_id" value="<?= htmlspecialchars($data['question_id']) ?>">
            
            <div class="input-group">
                <label for="answer-content">Your Answer</label>
                <textarea id="answer-content" name="content" placeholder="Write your detailed answer here..." rows="12" required></textarea>
            </div>

            <button type="submit" class="btn-save">Submit Answer</button>

        </form>
    </div>

    <script src="scripts_answer.js"></script>

</body>
</html>

<style>
/* Basic styling override for the title within this specific view */
.form-title {
    font-size: 1.5em;
    font-weight: 600;
    margin-bottom: 20px;
    color: #333;
}

label {
    display: block;
    margin-bottom: 5px;
    font-weight: bold;
    color: #555;
    font-size: 0.9em;
}
</style>

<?php

include_once '../app/views/partials/footer.view.php';

?>