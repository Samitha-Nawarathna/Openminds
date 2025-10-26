<?php

    $title = "Create Exercises | Openminds";
    $filename = "exercises/create";

    include_once "../app/views/partials/header.view.php";

?>

<?php
// --- MOCK DATA SETUP ---
// Data initialized for a NEW exercise
$data = [
    'exercise_id' => 'new', // Flag to indicate a new creation
    'exercise_title' => '',
    'subject_name' => '',
    'current_tags' => [],
    'form_action_url' => '/your-backend-controller/create-new-exercise', // New endpoint for creation
    
    // Initial data for questions is empty
    'questions' => []
];
?>


    <div class="editor-wrapper">
        <form action="<?= ROOT?>exercises/create" method="POST" id="exercise-editor-form">
            
            <input type="hidden" name="exercise_id" value="<?= htmlspecialchars($data['exercise_id']) ?>">
            <input type="hidden" name="question_order" id="question-order-field" value="">

            <div class="main-header-card">
                <h1>Create New Exercise</h1>
                <input type="text" id="exercise-title-input" name="exercise_title" value="<?= htmlspecialchars($data['exercise_title']) ?>" placeholder="Enter Main Exercise Title (Required)">
            </div>

            <div class="details-section card">
                <div class="input-group">
                    <label for="exercise-subject-input">Subject Name</label>
                    <input type="text" id="exercise-subject-input" name="subject_name" value="<?= htmlspecialchars($data['subject_name']) ?>" placeholder="e.g., Physics, Maths, Art">
                </div>

                <div class="input-group tag-input">
                    <label for="tags-input">Tags (Type and Enter)</label>
                    <input type="hidden" id="hidden-tags-field" name="tags" value="">
                    
                    <div id="selected-tags-display" class="tags-display-area">
                        </div>
                    
                    <input type="text" id="tags-input" placeholder="e.g., mechanics, wave theory">
                </div>
            </div>
            
            <div class="questions-list-container" id="questions-list">
                </div>

            <div class="editor-controls">
                <button type="button" class="button btn-primary btn-add-" id="btn-add-question">add first question</button>
            </div>
            
            <div class="save-button-container container">
                <button type="submit" class="button btn-primary" id="btn-save-exercise">Save Exercise</button>
            </div>

        </form>
    </div>

    <div id="question-modal" class="modal">
        <div class="modal-content">
            <span class="close-button">&times;</span>
            <h2><span id="modal-title-text">Add New</span> Question</h2>
            <input type="hidden" id="modal-question-id">
            
            <div class="input-group">
                <label for="modal-question-text">Question Text</label>
                <textarea id="modal-question-text" rows="3"></textarea>
            </div>

            <div class="input-group">
                <label for="modal-question-weight">weight</label>
                <textarea id="modal-question-weight" rows="1">1</textarea>
            </div>

            <input type="hidden" id="modal-answer-type" value="multiple_choice" name="answer_type">

            <div class="input-group" id="modal-options-group">
                <label>Options (one per line)</label>
                <textarea id="modal-options-text" rows="4" placeholder="Option A&#10;Option B&#10;Option C"></textarea>
            </div>

            <button type="button" class="btn-modal-save" id="btn-modal-save">Save Question</button>
        </div>
    </div>
    
    <script>
        // Pass empty initial data structure to JavaScript
        const INITIAL_QUESTIONS_DATA = [];
        const INITIAL_TAGS = []; 
    </script>

<?php
    include_once "../app/views/partials/footer.view.php";
?>