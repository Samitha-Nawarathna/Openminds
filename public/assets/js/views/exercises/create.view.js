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
            <quill-editor 
                id="option-text-${index}"
                name="content"
                content="${text}"
                placeholder="Option ${index + 1} text"
                storage-key="demo-editor-2"
                height="250px">
            </quill-editor> 
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
