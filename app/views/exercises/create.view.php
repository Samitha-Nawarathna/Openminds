<?php
    // create.view.php
    $title = "Create Exercise | Openminds";
    $filename = "exercises/create";

    // Mock PHP placeholders for root paths (assumes standard framework setup)
    $ROOT = ''; 
    $MOCK_API_SAVE_URL = $ROOT . '/exercises/api/save_draft';
    $MOCK_API_SUBMIT_URL = $ROOT . '/exercises/api/submit';

    include_once "../app/views/partials/header.view.php";
?>

    <style>
        /* Base styles relied upon by the new design system (view.view.css) */
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: var(--font-body);
            font-size: var(--font-size-base);
            color: var(--color-text);
            background-color: var(--color-background);
        }

        /* Re-applying button styles with the correct padding from view.view.css */
        .btn-blue, .btn-red, .btn-none {
            padding: var(--space-sm) var(--space-md); /* Consistent padding */
            border-radius: var(--radius-md);
            font-size: var(--font-size-sm);
            font-weight: 600;
            text-decoration: none;
            cursor: pointer;
            border: none
        }

        .btn-blue {
            background-color: var(--color-primary);
            color: white;
        }

        .btn-none
        {
            border: 1px solid var(--color-placeholder)
        }
        
        /* Modal consistency from view.view.css */
        .modal { 
            position: fixed; z-index: 1000; left: 0; top: 0; width: 100%; height: 100%; overflow: auto; 
            background-color: rgba(0,0,0,0.6); display: flex; justify-content: center; align-items: center; 
        }
        .modal-content { 
            background-color: var(--color-surface); margin: auto; 
            padding: var(--space-lg); /* Larger padding from view.view.css modal */
            border-radius: var(--radius-lg); /* Larger radius from view.view.css modal */
            max-width: 600px; /* Aligned with view.view.css modal max-width */
            box-shadow: var(--shadow-md); 
        }

        /* Form Input Consistency from view.view.css */
        input[type="text"], textarea, input[type="number"] { 
            width: 100%; 
            padding: var(--space-xs); /* Use token for padding */
            margin-top: var(--space-xxs); /* Use token for tight spacing */
            border: 1px solid var(--color-border); 
            border-radius: var(--radius-md); 
            box-sizing: border-box;
            font-size: var(--font-size-base);
            font-family: var(--font-body);
        }
        input[type="text"]:focus, textarea:focus, input[type="number"]:focus { 
            outline: none; 
            border-color: var(--color-primary); 
            box-shadow: 0 0 0 2px rgba(var(--color-primary-rgb), 0.2); 
        }
        .modal-content form label {
            display: block; 
            margin-top: var(--space-sm); 
            font-weight: 600; 
            font-size: var(--font-size-sm); 
            color: var(--color-text-muted);
        }

        /* BUILDER SPECIFIC LAYOUT */
        .builder-layout {
            display: flex;
            max-width: 1200px;
            margin: var(--space-md) auto;
            padding: 0 var(--space-md);
            gap: var(--space-md);
            padding-bottom: 90px; /* Offset for control bar */
        }
        .question-list-panel {
            width: 300px;
            flex-shrink: 0;
            padding: var(--space-md);
        }

        #question-list-container
        {
            margin-top:var(--space-md);
        }
        .editor-panel {
            flex-grow: 1;
        }
        .content-card { 
            background-color: var(--color-bg-card); /* Use token */
            padding: var(--space-md); 
            border: 1px solid var(--color-border); 
            border-radius: var(--radius-md); 
            box-shadow: var(--shadow-sm); 
            margin-bottom: var(--space-sm); 
        }
        .question-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: var(--space-xs);
            margin-bottom: var(--space-xs);
            border: 1px solid var(--color-border);
            border-radius: var(--radius-sm);
            cursor: pointer;
        }
        .question-item.active {
            border-left: 5px solid var(--color-primary);
            background-color: var(--color-blue-50);
        }
        .option-group {
            border: 1px dashed var(--color-border);
            padding: var(--space-sm);
            border-radius: var(--radius-md);
            margin-top: var(--space-sm);
        }
        .control-bar { 
            position: fixed; bottom: 0; left: 0; width: 100%; display: flex; 
            justify-content: space-between; align-items: center; 
            background-color: var(--color-surface); 
            box-shadow: 0 -2px 10px rgba(0, 0, 0, 0.1); 
            padding: var(--space-xs) var(--space-md); 
            box-sizing: border-box; height: 70px; 
        }
        .action-buttons { display: flex; align-items: center; gap: var(--space-xs); }

        .left-controls
        {
            display:flex;
            flex-direction:row-reverse;
            align-items:center;
            gap: var(--space-sm)
        }
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
    <button id="edit-metadata-btn" class="btn-none" style="width: 100%; margin-bottom: var(--space-sm); padding: var(--space-xs) var(--space-md); border-style: solid; font-weight: normal;">
            ✎ Edit Exercise Details
        </button>
        <h3 style="margin-top:0;">Questions <span id="q-count-status">(0)</span></h3>

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
            <textarea id="q-prompt-input" rows="4" required></textarea>

            <label for="q-explanation-input" style="margin-top:var(--space-md);">Explanation (Required for Review)</label>
            <textarea id="q-explanation-input" rows="3" required></textarea>

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
    const MOCK_API_SAVE_URL = '<?= $MOCK_API_SAVE_URL ?>';
    const MOCK_API_SUBMIT_URL = '<?= $MOCK_API_SUBMIT_URL ?>';
    
    // --- GLOBAL STATE ---
    let EXERCISE_METADATA = {};
    let EXERCISE_QUESTIONS = [];
    let currentQIndex = -1; // -1 means no question is selected/being edited.
    let nextQId = 1;

    // --- DOM Elements ---
    const setupModal = document.getElementById('setup-modal');
    const setupForm = document.getElementById('setup-form');
    const mainBuilderContent = document.getElementById('main-builder-content');
    const controlBar = document.getElementById('control-bar');
    const qListContainer = document.getElementById('question-list-container');
    const qEditorForm = document.getElementById('question-editor-form');
    const optionsContainer = document.getElementById('options-container');
    const submitModal = document.getElementById('submit-modal');
    // NEW DOM ELEMENT REFERENCE
    const editMetadataBtn = document.getElementById('edit-metadata-btn');
    
    // --- STEP 1: SETUP MODAL LOGIC ---

    // Function to handle the actual saving of form data and closing the modal
    function updateMetadata() {
        EXERCISE_METADATA = {
            title: document.getElementById('exercise-title-input').value,
            subject: document.getElementById('exercise-subject-input').value,
            description: document.getElementById('exercise-description-input').value,
            tags: document.getElementById('exercise-tags-input').value,
        };
        setupModal.style.display = 'none';
        
        // Ensure the control bar Save Draft button is re-enabled if needed
        document.getElementById('save-draft-btn').disabled = false;
    }
    
    // Function to pre-fill the form with current metadata and update the modal button text
    function prepopulateMetadataForm() {
        document.getElementById('exercise-title-input').value = EXERCISE_METADATA.title || '';
        document.getElementById('exercise-subject-input').value = EXERCISE_METADATA.subject || '';
        document.getElementById('exercise-description-input').value = EXERCISE_METADATA.description || '';
        document.getElementById('exercise-tags-input').value = EXERCISE_METADATA.tags || '';
        
        // Change button text for clarity if exercise already exists
        const submitBtn = setupForm.querySelector('button[type="submit"]');
        submitBtn.textContent = EXERCISE_METADATA.title ? 'Update Details' : 'Start Building';
    }


    setupForm.addEventListener('submit', (e) => {
        e.preventDefault();
        
        const isInitialSetup = Object.keys(EXERCISE_METADATA).length === 0;
        
        updateMetadata(); // Save the data

        if (isInitialSetup) {
            // Initial setup flow: reveal UI and start first question
            mainBuilderContent.style.display = 'flex';
            controlBar.style.display = 'flex';
            addNewQuestion(); 
        }
    });

    // NEW EVENT HANDLER: Re-open modal to edit metadata
    editMetadataBtn.addEventListener('click', () => {
        prepopulateMetadataForm();
        setupModal.style.display = 'flex';
    });


    // --- STEP 2 & 3: BUILDER/EDITOR LOGIC ---

    function generateOptionHtml(index, text = '', isCorrect = false) {
        const type = 'radio'; // Assuming single-choice for simplicity
        return `
            <div style="display:flex; gap:10px; margin-bottom:10px; align-items:center;">
                <input type="${type}" name="correct-option" id="option-correct-${index}" value="${index}" ${isCorrect ? 'checked' : ''} style="width:auto; margin:0;">
                <input type="text" id="option-text-${index}" value="${text}" placeholder="Option ${index + 1} text" required>
                <button type="button" onclick="removeOption(this, ${index})" class="btn-red" style="padding: 8px;">🗑️</button>
            </div>
        `;
    }

    function renderOptions(options = [{text: '', isCorrect: true}, {text: '', isCorrect: false}]) {
        optionsContainer.innerHTML = options.map((opt, index) => generateOptionHtml(index, opt.text, opt.isCorrect)).join('');
    }
    
    window.removeOption = function(el, index) {
        if (optionsContainer.children.length > 2) {
            el.parentElement.remove();
        } else {
            alert('An exercise must have at least two options.');
        }
    }

    document.getElementById('add-option-btn').addEventListener('click', () => {
        const nextIndex = optionsContainer.children.length;
        optionsContainer.insertAdjacentHTML('beforeend', generateOptionHtml(nextIndex));
    });

    function saveCurrentQuestion() {
        const qId = document.getElementById('current-q-id').value;
        const prompt = document.getElementById('q-prompt-input').value.trim();
        const explanation = document.getElementById('q-explanation-input').value.trim();
        const weight = parseInt(document.getElementById('q-weight-input').value);
        
        if (!prompt || !explanation || isNaN(weight)) {
            alert('Please fill out the prompt, explanation, and weight.');
            return false;
        }

        const options = Array.from(optionsContainer.children).map((div, index) => ({
            text: div.querySelector(`#option-text-${index}`).value,
            isCorrect: div.querySelector(`input[name="correct-option"]`).checked, // Assumes radio for simplicity
        }));
        
        if (options.filter(o => o.isCorrect).length === 0) {
            alert('Please select at least one correct answer.');
            return false;
        }

        const newQ = {
            id: qId ? parseInt(qId) : nextQId++,
            prompt,
            explanation,
            weight,
            options,
        };

        if (qId) {
            // Update existing question
            EXERCISE_QUESTIONS[currentQIndex] = newQ;
        } else {
            // Add new question
            EXERCISE_QUESTIONS.push(newQ);
            currentQIndex = EXERCISE_QUESTIONS.length - 1;
        }
        
        renderQuestionList();
        updateBuilderUI(true);
        return true;
    }

    function loadQuestion(index) {
        currentQIndex = index;
        const q = EXERCISE_QUESTIONS[index];
        
        document.getElementById('current-q-id').value = q.id;
        document.getElementById('q-prompt-input').value = q.prompt;
        document.getElementById('q-explanation-input').value = q.explanation;
        document.getElementById('q-weight-input').value = q.weight;
        document.getElementById('current-q-title').textContent = `Question Editor: Q${index + 1}`;
        
        renderOptions(q.options);
        updateBuilderUI();
    }

    function addNewQuestion() {
        if (currentQIndex !== -1 && !saveCurrentQuestion()) {
             return; // Don't proceed if save fails
        }
        
        currentQIndex = -1; // Mark as new question mode
        document.getElementById('current-q-id').value = '';
        document.getElementById('current-q-title').textContent = `Question Editor: New`;
        qEditorForm.reset();
        renderOptions(); // Render two blank options
        updateBuilderUI();
    }

    function renderQuestionList() {
        qListContainer.innerHTML = EXERCISE_QUESTIONS.map((q, index) => `
            <div class="question-item ${index === currentQIndex ? 'active' : ''}" onclick="loadQuestion(${index})">
                <span>Q${index + 1}: ${q.prompt.substring(0, 30)}...</span>
                <button type="button" class="btn-none" onclick="event.stopPropagation(); deleteQuestion(${index})">🗑️</button>
            </div>
        `).join('');
        document.getElementById('q-count-status').textContent = `(${EXERCISE_QUESTIONS.length})`;
    }
    
    function updateBuilderUI(isSaved = false) {
        // Control Bar Logic
        document.getElementById('prev-q-btn').disabled = currentQIndex <= 0;
        document.getElementById('save-next-btn').textContent = currentQIndex === EXERCISE_QUESTIONS.length - 1 ? 'Save & Add New →' : 'Save & Next Question →';
        
        // Draft Status
        if (isSaved) {
            document.getElementById('draft-status').textContent = 'Saved Locally';
            document.getElementById('draft-status').style.color = 'var(--color-success)';
        }
    }
    
    // --- EVENT HANDLERS ---
    
    // NOTE: document.getElementById('add-new-q-btn').addEventListener('click', addNewQuestion) is still in the code 
    // but the button's style is set to display:none, effectively disabling it via CSS/HTML attribute.
    
    document.getElementById('save-next-btn').addEventListener('click', () => {
        if (saveCurrentQuestion()) {
            if (currentQIndex < EXERCISE_QUESTIONS.length - 1) {
                loadQuestion(currentQIndex + 1);
            } else {
                addNewQuestion();
            }
        }
    });

    document.getElementById('prev-q-btn').addEventListener('click', () => {
        // Ensure the current question is saved when navigating backward
        if (saveCurrentQuestion() && currentQIndex > 0) {
             loadQuestion(currentQIndex - 1);
        }
    });

    document.getElementById('save-draft-btn').addEventListener('click', () => {
        if (saveCurrentQuestion()) {
            // Mock API call
            window.showPopupError('save draft not implemented!');
            document.getElementById('draft-status').textContent = 'Draft Saved to Server!';
            document.getElementById('draft-status').style.color = 'var(--color-primary)';
        }
    });
    
    window.deleteQuestion = function(index) {
        if (confirm(`Are you sure you want to delete Question ${index + 1}?`)) {
            EXERCISE_QUESTIONS.splice(index, 1);
            
            let nextIndexToLoad = EXERCISE_QUESTIONS.length > 0 ? Math.min(index, EXERCISE_QUESTIONS.length - 1) : -1;
            
            if (index === currentQIndex || EXERCISE_QUESTIONS.length === 0) {
                 if (nextIndexToLoad > -1) {
                    loadQuestion(nextIndexToLoad);
                 } else {
                     addNewQuestion();
                 }
            }
            
            renderQuestionList();
            updateBuilderUI();
        }
    }


    // --- STEP 4: FINAL SUBMISSION LOGIC ---
    document.getElementById('finalize-btn').addEventListener('click', () => {
        if (!saveCurrentQuestion()) return;

        const count = EXERCISE_QUESTIONS.length;
        document.getElementById('submission-q-count').textContent = count;
        
        if (count < 3) {
            document.getElementById('submission-warning').textContent = 'Warning: We recommend at least 3 questions.';
            document.getElementById('confirm-submit-btn').disabled = false; // Still allow submission
        } else {
            document.getElementById('submission-warning').textContent = '';
            document.getElementById('confirm-submit-btn').disabled = false;
        }

        submitModal.style.display = 'flex';
    });
    
    document.getElementById('cancel-submit-btn').addEventListener('click', () => {
        submitModal.style.display = 'none';
    });

    document.getElementById('confirm-submit-btn').addEventListener('click', () => {
        console.log('API: Submitting FINAL exercise for review...', {metadata: EXERCISE_METADATA, questions: EXERCISE_QUESTIONS});
        alert(`SUCCESS! Exercise "${EXERCISE_METADATA.title}" submitted with ${EXERCISE_QUESTIONS.length} questions. Redirecting...`);
        // In a real app: Redirect to the dashboard or a success page.
        // window.location.href = '<?= $ROOT ?>/dashboard';
    });


    // Initial setup: Render options on load
    renderOptions();

</script>

<?php
    include_once "../app/views/partials/footer.view.php";
?>