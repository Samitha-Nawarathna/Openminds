<?php

$title = 'Question creator';
$filename = 'question/question_creator';

include_once '../app/views/partials/header.view.php';

?>

<div class="form-wrapper">
        <form action="<?= ROOT?>/question/create" method="POST" id="create-question-form">
            
            <div class="input-group">
                <input type="text" id="title" name="title" placeholder="Title" required>
            </div>

            <div class="input-group">
                <textarea id="content" name="content" placeholder="Content" rows="8" required></textarea>
            </div>

            <div class="input-group">
                <div id="tags-display-container"></div>
                <input type="text" id="tag-input" placeholder="Tags (type and press Enter)">
                <input type="hidden" name="tags" id="hidden-tags-input">
            </div>

            <button type="submit" class="btn-save">Save</button>

        </form>
    </div>

<?php

include_once '../app/views/partials/footer.view.php';

?>