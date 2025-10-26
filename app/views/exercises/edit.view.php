<?php

    $title = "Exercises | Openminds";
    $filename = "exercises/edit";

    include_once "../app/views/partials/header.view.php";

    // show($data['questions']);
    // exit;

?>

    <div class="editor-wrapper">
        <form action="<?= ROOT ?>/exercises/edit" method="POST" id="exercise-editor-form">
            
            <input type="hidden" name="exercise_id" value="<?= htmlspecialchars($data['exercise_details']['exercise_id']) ?>">
            <input type="hidden" name="question_order" id="question-order-field" value="">

            <div class="main-header-card">
                <h1>Edit Exercise</h1>
                <input type="text" id="exercise-title-input" name="exercise_title" value="<?= htmlspecialchars($data['exercise_details']['title']) ?>" placeholder="Enter Main Exercise Title">
            </div>

            <div class="details-section card">
                <div class="input-group">
                    <label for="exercise-subject-input">Subject Name</label>
                    <input type="text" id="exercise-subject-input" name="subject_name" value="<?= htmlspecialchars($data['exercise_details']['subject']) ?>" placeholder="e.g., Physics, Maths, Art">
                </div>

                <div class="input-group tag-input">
                    <label for="tags-input">Tags (Type and Enter)</label>
                    <input type="hidden" id="hidden-tags-field" name="tags" value="<?= htmlspecialchars(implode(',', $data['exercise_details']['tags'])) ?>">
                    
                    <div id="selected-tags-display" class="tags-display-area">
                        </div>
                    
                    <input type="text" id="tags-input" placeholder="e.g., mechanics, wave theory">
                </div>
            </div>
            
            <div class="questions-list-container" id="questions-list">
                <?php foreach ($data['questions'] as $index => $q): ?>
                
                <div class="question-panel" id="question-<?= htmlspecialchars($q['id']) ?>" data-question-id="<?= htmlspecialchars($q['id']) ?>" draggable="true">
                    
                    <div class="question-header">
                        <span class="question-number">Question <?= $index + 1 ?></span>
                        <div class="panel-actions">
                            <button type="button" class="btn-edit" data-id="<?= htmlspecialchars($q['id']) ?>">Edit</button>
                            <button type="button" class="btn-delete" data-id="<?= htmlspecialchars($q['id']) ?>">Delete</button>
                        </div>
                    </div>
                    
                    <p class="question-text"><?= htmlspecialchars($q['question_text']) ?></p>
                    
                    <div class="answer-preview">
                        <span class="preview-type">Type: Multiple Choice</span>
                        <ul>
                            <?php foreach ($q['options'] as $option): ?>
                                <li><label><input type="radio" disabled><?= htmlspecialchars($option) ?></label></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </div>

                <?php endforeach; ?>
            </div>

            <div class="editor-controls">
                <button type="button" class=" button btn-primary" id="btn-add-question">add another question</button>
            </div>
            
            <div class="save-button-container container">
                <button type="submit" class="button btn-primary">Save Exercise</button>
            </div>

        </form>
    </div>

    <div id="question-modal" class="modal">
        <div class="modal-content">
            <span class="close-button">&times;</span>
            <h2><span id="modal-title-text">Edit</span> Question</h2>
            <input type="hidden" id="modal-question-id">
            
            <div class="input-group">
                <label for="modal-question-text">Question Text</label>
                <textarea id="modal-question-text" rows="3"></textarea>
            </div>

            <div class="input-group">
                <label for="modal-question-text">weight</label>
                <textarea id="modal-question-text" rows="1">1</textarea>
            </div>

            <input type="hidden" id="modal-answer-type" value="multiple_choice" name="answer_type">

            <div class="input-group" id="modal-options-group">
                <label>Options (one per line)</label>
                <textarea id="modal-options-text" rows="4" placeholder="Option A&#10;Option B&#10;Option C"></textarea>
            </div>

            <button type="button" class="btn-modal-save" id="btn-modal-save">Save Changes</button>
        </div>
    </div>
    
    <script>
        window.INITIAL_QUESTIONS_DATA = <?= json_encode($data['questions'] ?? []) ?>;
        window.INITIAL_TAGS = <?= json_encode($data['current_tags'] ?? [])?>; 
    </script>



<?php
    include_once "../app/views/partials/footer.view.php";
?>