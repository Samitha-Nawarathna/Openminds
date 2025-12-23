<?php
    // create.view.php
    $title = "Create Exercise | Openminds";
    $filename = "exercises/create";
    $add_back = true;

    // Mock PHP placeholders for root paths (assumes standard framework setup)
    $ROOT = ''; 
    $MOCK_API_SAVE_URL = $ROOT . '/exercises/api/save_draft';
    $MOCK_API_SUBMIT_URL = $ROOT . '/api/exercises/create';

    include_once "../app/views/partials/header.view.php";
?>

    <style>
    </style>
</head>
<body>

<div id="setup-modal" class="modal">
    <div class="modal-content">
        <h2>Exercise Details</h2>
        <form id="setup-form">
            <label for="exercise-title-input">Exercise Title (e.g., Intro to Big O Notation)</label>
            <input type="text" id="exercise-title-input" placeholder="Required" required>

            <label for="exercise-subject-input">Subject / Topic</label>
            <input type="text" id="exercise-subject-input" placeholder="e.g., Data Structures, PHP" required>

            <label for="exercise-description-input">Description (Optional Overview)</label>
            <textarea id="exercise-description-input" rows="3" placeholder="Learning objectives..."></textarea>
            
            <label for="exercise-tags-input">Tags (Comma separated)</label>
            <input type="text" id="exercise-tags-input" placeholder="e.g., beginner, sorting, trees">

            <button type="submit" class="btn-blue" style="width: 100%; margin-top: var(--space-md);">Start Building</button>
        </form>
    </div>
</div>

<div id="main-builder-content" class="builder-layout" style="display: none;">
    
    <div class="question-list-panel content-card">
        <h3 style="margin-top:0;">Questions <span id="q-count-status">(0)</span></h3>
        <button id="edit-metadata-btn" class="btn-none" style="width: 100%; margin-bottom: var(--space-sm); padding: var(--space-xs) var(--space-md); border-style: solid; font-weight: normal;">
            ✎ Edit Exercise Details
        </button>
        <div id="question-list-container">
            <p style="color:var(--color-text-muted); font-size:var(--font-size-sm);">No questions added yet.</p>
        </div>
        <button id="add-new-q-btn" class="btn-none" style="display:none; width: 100%; margin-top: var(--space-md); border-style: dashed;">+ Add New Question</button>
    </div>

    <div class="editor-panel content-card">
        <h2 id="current-q-title" style="margin-top:0; border-bottom: 1px solid var(--color-border); padding-bottom: var(--space-xs);">Question Editor: New</h2>
        <form id="question-editor-form">
            <input type="hidden" id="current-q-id">

            <label for="q-prompt-input">Question Prompt (The full text of the question)</label>
            <quill-editor 
                id="q-prompt-input"
                name="content"
                placeholder="Enter the question"
                storage-key="demo-editor-2"
                height="250px">
            </quill-editor>

            <label for="q-explanation-input" style="margin-top:var(--space-md);">Explanation (Required for Review)</label>
            <quill-editor 
                id="q-explanation-input"
                name="content"
                placeholder="Enter the question"
                storage-key="demo-editor-2"
                height="250px">
            </quill-editor>            

            <label for="q-weight-input" style="margin-top:var(--space-md);">Question Weight/Points</label>
            <input type="number" id="q-weight-input" min="1" value="1" required style="width: 100px;">

            <h3 style="margin-top:var(--space-md); border-bottom: 1px solid var(--color-border);">Answer Options</h3>
            <div id="options-container" class="option-group">
                </div>
            <button type="button" id="add-option-btn" class="btn-none" style="margin-top: var(--space-xs); padding: var(--space-xs) var(--space-md); border-style: dashed;">+ Add Option</button>
        </form>
    </div>
</div>

<div id="control-bar" class="control-bar" style="display: none;">
    <div class="left-controls">
        <div id="progress-area" style="font-weight: 600;">
            Status: <span id="draft-status" style="color:var(--color-text-muted);">Unsaved Draft</span>
        </div>
        <button id="save-draft-btn" class="btn-none">Save Draft</button>
    </div>
    
    <div class="action-buttons">
        <button id="prev-q-btn" class="btn-none" disabled>← Previous Question</button>
        <button id="save-next-btn" class="btn-blue">Next Question →</button>
        <button id="finalize-btn" class="btn-blue" >Submit</button>
    </div>
</div>


<div id="submit-modal" class="modal" style="display: none;">
    <div class="modal-content">
        <h2>Submit Exercise for Review</h2>
        <p>You have <strong id="submission-q-count">0</strong> questions ready.</p>
        <p id="submission-warning" style="color:var(--color-error);"></p>

        <button id="confirm-submit-btn" class="btn-blue" style="width: 100%; margin-top: var(--space-md);">Submit for Review</button>
        <button id="cancel-submit-btn" class="btn-none" style="width: 100%; margin-top: var(--space-xs);">Go Back to Editing</button>
    </div>
</div>


<script>
</script>

<?php
    include_once "../app/views/partials/footer.view.php";
?>