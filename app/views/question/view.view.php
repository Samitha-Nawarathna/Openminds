<?php

$title = 'Question creator';
$filename = 'question/view';

include_once '../app/views/partials/header.view.php';

?>

<?php
// Inject initial data into a global JS variable
$initialDataJson = json_encode($data ?? []);
?>

<style>
    /* Styling for Read-Only Quill Editors to look like Viewers */
    quill-editor[readonly] .editor-container {
        border: 1px solid #e0e0e0 !important;
        border-radius: 8px;
        background-color: #f9f9f9;
        padding: 10px;
    }
    
    /* Ensure content inside doesn't overflow */
    quill-editor[readonly] .ql-editor {
        min-height: auto;
        overflow-y: visible; 
    }
</style>

<div id="question-page-container">
    <div id="question-panel">
        <div id="question-content-container">
            </div>

        <div id="question-footer" class="question-footer">
            <button id="answer-cta-btn" class="btn-blue" style="width: auto;" onclick="openModal('create-answer-modal')">Post Your Answer</button>
            <div id="question-action-controls" class="question-action-controls">
                </div>
        </div>
        <div id="split-trigger-point"></div>
    </div>
    
    <div id="void-filler"></div> 

    <div id="answers-panel">
        <div id="answers-panel-inner-content">
            <div id="answer-count-header" class="answer-count-header">
                </div>
            <div id="answers-list">
                </div>
            <button id="load-more-btn" class="btn-none" onclick="loadMoreAnswers()" style="width: 100%; display: none;">Load More Answers (0/0)</button>
        </div>
    </div>
</div>

<div id="create-answer-modal" class="modal">
    <div class="modal-content">
        <span class="close-btn" onclick="closeModal('create-answer-modal')">&times;</span>
        <h2>Post Your Answer</h2>
        <form id="create-answer-form" onsubmit="handleCreateAnswer(event)">
            <label for="new-answer-content">Answer Content:</label>
            <!-- <textarea id="new-answer-content" rows="10" required></textarea> -->
            <quill-editor 
                    id="new-answer-content"
                    name="content"
                    placeholder="Enter form content..."
                    storage-key="demo-editor-2"
                    height="250px">
                </quill-editor>
            <button type="submit" class="btn-blue">Submit Answer</button>
        </form>
    </div>
</div>

<div id="edit-question-modal" class="modal">
    <div class="modal-content">
        <span class="close-btn" onclick="closeModal('edit-question-modal')">&times;</span>
        <h2>Edit Question</h2>
        <form id="edit-question-form" onsubmit="handleEditQuestion(event)">
            <label for="edit-question-title">Title:</label>
            <input type="text" id="edit-question-title" required>
            <label for="edit-question-description">Description:</label>
            <quill-editor 
                id="edit-question-description"
                name="content"
                placeholder="Enter form content..."
                storage-key="demo-editor-2"
                height="250px">
            </quill-editor>            
            <!-- <textarea id="edit-question-description" rows="12" required></textarea> -->
            <label for="edit-question-tags">Tags (comma separated):</label>
            <input type="text" id="edit-question-tags">
            <button type="submit" class="btn-blue">Save Changes</button>
        </form>
    </div>
</div>

<div id="edit-answer-modal" class="modal">
    <div class="modal-content">
        <span class="close-btn" onclick="closeModal('edit-answer-modal')">&times;</span>
        <h2>Edit Answer</h2>
        <form id="edit-answer-form" onsubmit="handleEditAnswer(event)">
            <input type="hidden" id="edit-answer-id">
            <label for="edit-answer-content">Answer Content:</label>
            <quill-editor 
                id="edit-answer-content"
                name="content"
                placeholder="Enter form content..."
                storage-key="demo-editor-2"
                height="250px">
            </quill-editor>               
            <!-- <textarea id="edit-answer-content" rows="10" required></textarea> -->
            <button type="submit" class="btn-blue">Save Changes</button>
        </form>
    </div>
</div>

<div id="error-popup"></div>

<script>
    // Inject initial data into a global JS variable
    const INITIAL_DATA = <?php echo $initialDataJson; ?>;
    const CURRENT_USER_ID = <?php echo $data['current_user_id']; ?>;
</script>


<?php

include_once '../app/views/partials/footer.view.php';

?>
