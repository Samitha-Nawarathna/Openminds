<?php
    // edit.view.php
    $title = "Edit Exercise | Openminds";
    $filename = "exercises/edit";

    // Mock PHP placeholders for root paths
    $ROOT = ''; 
    $MOCK_API_SAVE_URL = $ROOT . '/exercises/api/save_draft';
    $MOCK_API_SUBMIT_URL = $ROOT . '/exercises/api/update'; // Note: Changed to update

    // --- MOCK DATA SIMULATION (This represents $data passed from Controller) ---
    // In production, this block is replaced by the actual $data array passed to the view.
    if (!isset($data['initial_data'])) {
        $data['initial_data'] = [
            'metadata' => [
                'id' => 105,
                'title' => 'Newton Laws Challenge',
                'subjectId' => 2,
                'subject' => 'Physics',
                'description' => 'A comprehensive review of Newton\'s three laws of motion. Targeted for high school physics students.',
                'tags' => 'physics, mechanics, newton, forces', // Comma-separated string
            ],
            'questions' => [
                [
                    'id' => 201,
                    'prompt' => 'Which equation represents Newton\'s Second Law of Motion?',
                    'explanation' => 'Newton\'s Second Law states that Force equals mass times acceleration (F=ma).',
                    'weight' => 5,
                    'options' => [
                        ['text' => 'F = m / a', 'isCorrect' => false],
                        ['text' => 'F = ma', 'isCorrect' => true],
                        ['text' => 'F = m + a', 'isCorrect' => false],
                        ['text' => 'E = mc^2', 'isCorrect' => false]
                    ]
                ],
                [
                    'id' => 202,
                    'prompt' => 'What is the "reaction" force to a book resting on a table?',
                    'explanation' => 'According to the 3rd law, if the book pushes down on the table (action), the table pushes up on the book (reaction).',
                    'weight' => 3,
                    'options' => [
                        ['text' => 'Gravity pulling the book down', 'isCorrect' => false],
                        ['text' => 'The table pushing up on the book', 'isCorrect' => true],
                        ['text' => 'Friction holding the book', 'isCorrect' => false]
                    ]
                ]
            ]
        ];
    }
    // --------------------------------------------------------------------------

    include_once "../app/views/partials/header.view.php";
?>

    <style>
        /* --- IDENTICAL CSS TO CREATE VIEW (Ensures visual consistency) --- */
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: var(--font-body); font-size: var(--font-size-base); color: var(--color-text); background-color: var(--color-background); }
        
        /* Buttons */
        .btn-blue, .btn-red, .btn-none { padding: var(--space-sm) var(--space-md); border-radius: var(--radius-md); font-size: var(--font-size-sm); font-weight: 600; text-decoration: none; cursor: pointer; border: none; }
        .btn-blue { background-color: var(--color-primary); color: white; }
        .btn-none { border: 1px solid var(--color-placeholder); background: transparent; }
        .btn-red { background-color: var(--color-red-500); color: white; }

        /* Modal */
        .modal { position: fixed; z-index: 1000; left: 0; top: 0; width: 100%; height: 100%; overflow: auto; background-color: rgba(0,0,0,0.6); display: flex; justify-content: center; align-items: center; }
        .modal-content { background-color: var(--color-surface); margin: auto; padding: var(--space-lg); border-radius: var(--radius-lg); max-width: 600px; box-shadow: var(--shadow-md); }
        .modal-content form label { display: block; margin-top: var(--space-sm); font-weight: 600; font-size: var(--font-size-sm); color: var(--color-text-muted); }

        /* Inputs */
        input[type="text"], textarea, input[type="number"] { width: 100%; padding: var(--space-xs); margin-top: var(--space-xxs); border: 1px solid var(--color-border); border-radius: var(--radius-md); box-sizing: border-box; font-size: var(--font-size-base); font-family: var(--font-body); }
        input[type="text"]:focus, textarea:focus, input[type="number"]:focus { outline: none; border-color: var(--color-primary); box-shadow: 0 0 0 2px rgba(var(--color-primary-rgb), 0.2); }

        /* Layout */
        .builder-layout { display: flex; max-width: 1200px; margin: var(--space-md) auto; padding: 0 var(--space-md); gap: var(--space-md); padding-bottom: 90px; }
        .question-list-panel { width: 300px; flex-shrink: 0; padding: var(--space-md); }
        .editor-panel { flex-grow: 1; }
        .content-card { background-color: var(--color-bg-card); padding: var(--space-md); border: 1px solid var(--color-border); border-radius: var(--radius-md); box-shadow: var(--shadow-sm); margin-bottom: var(--space-sm); }
        
        /* Question List Items */
        .question-item { display: flex; justify-content: space-between; align-items: center; padding: var(--space-xs); margin-bottom: var(--space-xs); border: 1px solid var(--color-border); border-radius: var(--radius-sm); cursor: pointer; }
        .question-item.active { border-left: 5px solid var(--color-primary); background-color: var(--color-blue-50); }
        
        .option-group { border: 1px dashed var(--color-border); padding: var(--space-sm); border-radius: var(--radius-md); margin-top: var(--space-sm); }
        
        /* Control Bar */
        .control-bar { position: fixed; bottom: 0; left: 0; width: 100%; display: flex; justify-content: space-between; align-items: center; background-color: var(--color-surface); box-shadow: 0 -2px 10px rgba(0, 0, 0, 0.1); padding: var(--space-xs) var(--space-md); box-sizing: border-box; height: 70px; }
        .action-buttons { display: flex; align-items: center; gap: var(--space-xs); }
        .left-controls { display:flex; flex-direction:row-reverse; align-items:center; gap: var(--space-sm); }
    </style>
</head>
<body>

<div id="setup-modal" class="modal" style="display: none;">
    <div class="modal-content">
        <h2>Edit Exercise Details</h2>
        <form id="setup-form">
            <label for="exercise-title-input">Exercise Title</label>
            <input type="text" id="exercise-title-input" placeholder="Required" required>

            <label for="exercise-subject-input">Subject / Topic</label>
            <input type="text" id="exercise-subject-input" placeholder="e.g., Data Structures" required>

            <label for="exercise-description-input">Description</label>
            <textarea id="exercise-description-input" rows="3" placeholder="Learning objectives..."></textarea>
            
            <label for="exercise-tags-input">Tags (Comma separated)</label>
            <input type="text" id="exercise-tags-input" placeholder="e.g., beginner, sorting">

            <button type="submit" class="btn-blue" style="width: 100%; margin-top: var(--space-md);">Update Details</button>
        </form>
    </div>
</div>

<div id="main-builder-content" class="builder-layout" style="display: none;"> <div class="question-list-panel content-card">
        <h3 style="margin-top:0;">Questions <span id="q-count-status">(0)</span></h3>
        <button id="edit-metadata-btn" class="btn-none" style="width: 100%; margin-bottom: var(--space-sm); padding: var(--space-xs) var(--space-md); border-style: solid; font-weight: normal;">
            ✎ Edit Exercise Details
        </button>
        
        <div id="question-list-container">
            </div>
    </div>

    <div class="editor-panel content-card">
        <h2 id="current-q-title" style="margin-top:0; border-bottom: 1px solid var(--color-border); padding-bottom: var(--space-xs);">Question Editor</h2>
        <form id="question-editor-form">
            <input type="hidden" id="current-q-id">

            <label for="q-prompt-input">Question Prompt</label>
            <textarea id="q-prompt-input" rows="4" required></textarea>

            <label for="q-explanation-input" style="margin-top:var(--space-md);">Explanation</label>
            <textarea id="q-explanation-input" rows="3" required></textarea>

            <label for="q-weight-input" style="margin-top:var(--space-md);">Weight/Points</label>
            <input type="number" id="q-weight-input" min="1" value="1" required style="width: 100px;">

            <h3 style="margin-top:var(--space-md); border-bottom: 1px solid var(--color-border);">Answer Options</h3>
            <div id="options-container" class="option-group">
                </div>
            <button type="button" id="add-option-btn" class="btn-none" style="margin-top: var(--space-xs); padding: var(--space-xs) var(--space-md); border-style: dashed;">+ Add Option</button>
        </form>
    </div>
</div>

<div id="control-bar" class="control-bar" style="display: none;"> <div class="left-controls">
        <div id="progress-area" style="font-weight: 600;">
            Status: <span id="draft-status" style="color:var(--color-text-muted);">Editing Existing</span>
        </div>
        <button id="save-draft-btn" class="btn-none">💾 Save Progress</button>
    </div>
    
    <div class="action-buttons">
        <button id="prev-q-btn" class="btn-none" disabled>← Previous</button>
        <button id="save-continue-btn" class="btn-blue">Save & Continue →</button>
        <button id="finalize-btn" class="btn-blue" >Update Exercise</button>
    </div>
</div>

<div id="submit-modal" class="modal" style="display: none;">
    <div class="modal-content">
        <h2>Update Exercise?</h2>
        <p>You have <strong id="submission-q-count">0</strong> questions.</p>
        <p id="submission-warning" style="color:var(--color-error);"></p>

        <button id="confirm-submit-btn" class="btn-blue" style="width: 100%; margin-top: var(--space-md);">Confirm Update</button>
        <button id="cancel-submit-btn" class="btn-none" style="width: 100%; margin-top: var(--space-xs);">Return to Editing</button>
    </div>
</div>


<script>
    // Inject the Mock Data from PHP into JS
    const INITIAL_PHP_DATA = <?= json_encode($data['initial_data'] ?? null) ?>;
    const MOCK_API_SUBMIT_URL = '<?= $MOCK_API_SUBMIT_URL ?>';
    
    // --- GLOBAL STATE ---
    let EXERCISE_METADATA = {};
    let EXERCISE_QUESTIONS = [];
    let currentQIndex = -1; 
    let nextQId = 1000; // Start high to avoid conflict with existing IDs if adding new ones locally

    // --- DOM Elements ---
    const setupModal = document.getElementById('setup-modal');
    const setupForm = document.getElementById('setup-form');
    const mainBuilderContent = document.getElementById('main-builder-content');
    const controlBar = document.getElementById('control-bar');
    const qListContainer = document.getElementById('question-list-container');
    const qEditorForm = document.getElementById('question-editor-form');
    const optionsContainer = document.getElementById('options-container');
    const submitModal = document.getElementById('submit-modal');
    const editMetadataBtn = document.getElementById('edit-metadata-btn');
    const saveContinueBtn = document.getElementById('save-continue-btn');

    // --- INITIALIZATION LOGIC (Populating from Mock Data) ---

    function initializeBuilder(data) {
        // 1. Populate Global State
        EXERCISE_METADATA = data.metadata;
        EXERCISE_QUESTIONS = data.questions;

        // 2. Update UI Visibility
        setupModal.style.display = 'none';
        mainBuilderContent.style.display = 'flex';
        controlBar.style.display = 'flex';

        // 3. Render the list
        renderQuestionList();

        // 4. Load the first question if it exists
        if (EXERCISE_QUESTIONS.length > 0) {
            loadQuestion(0);
        } else {
            addNewQuestion(); // Fallback if the imported exercise has no questions
        }
    }

    // --- METADATA MODAL LOGIC ---

    function prepopulateMetadataForm() {
        document.getElementById('exercise-title-input').value = EXERCISE_METADATA.title || '';
        document.getElementById('exercise-subject-input').value = EXERCISE_METADATA.subject || '';
        document.getElementById('exercise-description-input').value = EXERCISE_METADATA.description || '';
        document.getElementById('exercise-tags-input').value = EXERCISE_METADATA.tags || '';
    }

    function updateMetadata() {
        EXERCISE_METADATA.title = document.getElementById('exercise-title-input').value;
        EXERCISE_METADATA.subject = document.getElementById('exercise-subject-input').value;
        EXERCISE_METADATA.description = document.getElementById('exercise-description-input').value;
        EXERCISE_METADATA.tags = document.getElementById('exercise-tags-input').value;
        
        setupModal.style.display = 'none';
        document.getElementById('draft-status').textContent = 'Metadata Updated';
    }

    setupForm.addEventListener('submit', (e) => {
        e.preventDefault();
        updateMetadata();
    });

    editMetadataBtn.addEventListener('click', () => {
        prepopulateMetadataForm();
        setupModal.style.display = 'flex';
    });

    // --- BUILDER LOGIC ---

    function generateOptionHtml(index, text = '', isCorrect = false) {
        const type = 'radio'; 
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
        
        if (!prompt && currentQIndex === -1) return true; // Allow navigation if new blank

        if (!prompt || !explanation || isNaN(weight)) {
            alert('Please fill out prompt, explanation, and weight.');
            return false;
        }

        const options = Array.from(optionsContainer.children).map((div, index) => ({
            text: div.querySelector(`#option-text-${index}`).value,
            isCorrect: div.querySelector(`input[name="correct-option"]`).checked
        }));
        
        if (options.filter(o => o.isCorrect).length === 0) {
            alert('Select at least one correct answer.');
            return false;
        }

        const newQ = {
            id: qId ? parseInt(qId) : nextQId++, // Keep existing ID or mint new one
            prompt,
            explanation,
            weight,
            options,
        };

        if (currentQIndex > -1) {
            EXERCISE_QUESTIONS[currentQIndex] = newQ; // Update existing
        } else {
            EXERCISE_QUESTIONS.push(newQ); // Add new
            currentQIndex = EXERCISE_QUESTIONS.length - 1;
        }
        
        renderQuestionList();
        updateBuilderUI(true);
        return true;
    }

    function loadQuestion(index) {
        // Save current before switching, unless switching to itself or failed validation
        if (currentQIndex !== -1 && currentQIndex !== index) {
             if (!saveCurrentQuestion()) return;
        }

        currentQIndex = index;
        const q = EXERCISE_QUESTIONS[index];
        
        document.getElementById('current-q-id').value = q.id;
        document.getElementById('q-prompt-input').value = q.prompt;
        document.getElementById('q-explanation-input').value = q.explanation;
        document.getElementById('q-weight-input').value = q.weight;
        document.getElementById('current-q-title').textContent = `Editing: Q${index + 1}`;
        
        renderOptions(q.options);
        updateBuilderUI();
    }

    function addNewQuestion() {
        // Save current before creating new
        if (currentQIndex !== -1 && !saveCurrentQuestion()) return;
        
        currentQIndex = -1; 
        document.getElementById('current-q-id').value = '';
        document.getElementById('current-q-title').textContent = `Question Editor: New`;
        qEditorForm.reset();
        renderOptions(); 
        updateBuilderUI();
    }

    function renderQuestionList() {
        if (EXERCISE_QUESTIONS.length === 0) {
             qListContainer.innerHTML = '<p style="color:var(--color-text-muted);">No questions yet.</p>';
        } else {
            qListContainer.innerHTML = EXERCISE_QUESTIONS.map((q, index) => `
                <div class="question-item ${index === currentQIndex ? 'active' : ''}" onclick="loadQuestion(${index})">
                    <span>Q${index + 1}: ${q.prompt.substring(0, 25)}...</span>
                    <button type="button" class="btn-none" onclick="event.stopPropagation(); deleteQuestion(${index})">🗑️</button>
                </div>
            `).join('');
        }
        document.getElementById('q-count-status').textContent = `(${EXERCISE_QUESTIONS.length})`;
    }
    
    function updateBuilderUI(isSaved = false) {
        const qCount = EXERCISE_QUESTIONS.length;
        
        // Prev Button
        document.getElementById('prev-q-btn').disabled = currentQIndex <= 0;

        // Dynamic Save Button Text
        if (currentQIndex === qCount - 1) {
            saveContinueBtn.textContent = 'Save & Add New →';
        } else if (currentQIndex === -1 && qCount > 0) {
            saveContinueBtn.textContent = 'Save & Add New →';
        } else if (currentQIndex < qCount - 1 && currentQIndex !== -1) {
            saveContinueBtn.textContent = 'Save & Load Next →';
        } else {
            saveContinueBtn.textContent = 'Save & Continue →';
        }

        if (isSaved) {
            const status = document.getElementById('draft-status');
            status.textContent = 'Changes Saved';
            status.style.color = 'var(--color-success)';
        }
    }

    // --- HANDLERS ---

    saveContinueBtn.addEventListener('click', () => {
        if (saveCurrentQuestion()) {
            const qCount = EXERCISE_QUESTIONS.length;
            if (currentQIndex < qCount - 1) {
                loadQuestion(currentQIndex + 1);
            } else {
                addNewQuestion();
            }
        }
    });

    document.getElementById('prev-q-btn').addEventListener('click', () => {
        if (saveCurrentQuestion() && currentQIndex > 0) {
             loadQuestion(currentQIndex - 1);
        }
    });

    window.deleteQuestion = function(index) {
        if (confirm(`Delete Question ${index + 1}?`)) {
            EXERCISE_QUESTIONS.splice(index, 1);
            // Logic to handle active selection after deletion
            if (index === currentQIndex) {
                 currentQIndex = -1; 
                 if(EXERCISE_QUESTIONS.length > 0) loadQuestion(Math.max(0, index - 1));
                 else addNewQuestion();
            } else if (index < currentQIndex) {
                currentQIndex--; // Adjust index if a preceding item was deleted
                renderQuestionList();
            } else {
                renderQuestionList();
            }
        }
    }

    document.getElementById('finalize-btn').addEventListener('click', () => {
        if (!saveCurrentQuestion()) return;

        const count = EXERCISE_QUESTIONS.length;
        document.getElementById('submission-q-count').textContent = count;
        
        if (count < 3) {
            document.getElementById('submission-warning').textContent = 'Warning: Recommended at least 3 questions.';
            document.getElementById('confirm-submit-btn').disabled = false;
        } else {
            document.getElementById('submission-warning').textContent = '';
        }

        submitModal.style.display = 'flex';
    });
    
    document.getElementById('cancel-submit-btn').addEventListener('click', () => {
        submitModal.style.display = 'none';
    });

    document.getElementById('confirm-submit-btn').addEventListener('click', () => {
        console.log('API: Updating exercise...', {metadata: EXERCISE_METADATA, questions: EXERCISE_QUESTIONS});
        alert(`UPDATE SUCCESS! Exercise "${EXERCISE_METADATA.title}" updated.`);
    });

    // --- AUTO-INITIALIZE ---
    if (INITIAL_PHP_DATA) {
        initializeBuilder(INITIAL_PHP_DATA);
    } else {
        // Should not happen in 'edit' mode, but fallback just in case
        setupModal.style.display = 'flex';
        renderOptions();
    }

</script>

<?php include_once "../app/views/partials/footer.view.php"; ?>