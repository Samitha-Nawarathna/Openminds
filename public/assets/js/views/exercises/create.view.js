import {ROOT} from '../../core/config.js';

const MOCK_API_SAVE_URL = ROOT + 'exercises/api/save_draft';
const MOCK_API_SUBMIT_URL = ROOT + 'api/exercises/create';
const API_SUBJECTS_URL = ROOT + 'exercises/api/subjects';

// Convert Quill delta JSON string/object to plain text so UI stays readable.
function quillJsonToPlainText(input) {
    try {
        const data = typeof input === 'string' ? JSON.parse(input) : input;
        if (!data || !Array.isArray(data.ops)) return typeof input === 'string' ? input : '';
        return data.ops.map(op => {
            if (typeof op.insert === 'string') return op.insert;
            if (typeof op.insert === 'object') return '[embedded content]';
            return '';
        }).join('');
    } catch (err) {
        return typeof input === 'string' ? input : '';
    }
}

// Helper to safely read content from Quill-editor-like elements, normalizing JSON to text.
function getEditorContent(id) {
    const el = document.getElementById(id);
    if (!el) return '';

    let content = '';

    // Check if this is a quill-editor web component
    if (el.tagName.toLowerCase() === 'quill-editor') {
        // Try to get text content from Quill instance
        if (el.quill && el.quill.getText) {
            content = el.quill.getText().trim();
        } else if (el.editor && el.editor.getText) {
            content = el.editor.getText().trim();
        } else if (el.value !== undefined) {
            content = el.value;
        } else {
            content = '';
        }
    } else {
        // For regular inputs
        if (el.value !== undefined) {
            content = el.value;
        } else if (el.getAttribute && el.getAttribute('content') !== null) {
            content = el.getAttribute('content');
        } else {
            content = el.innerText || '';
        }
    }

    // Detect Quill JSON and convert for readability.
    if (typeof content === 'string' && content.includes('{"ops":')) {
        content = quillJsonToPlainText(content);
    }
    return content;
}

// Helper to set content on Quill-like editors or inputs, normalizing JSON before display.
// Supports both standard inputs and quill-editor web components with multiple fallback approaches.
function setEditorContent(id, content) {
    const el = document.getElementById(id);
    if (!el) return;

    // Normalize content: convert Quill JSON to plain text if detected
    if (typeof content === 'string' && content.includes('{"ops":')) {
        content = quillJsonToPlainText(content);
    }

    // Ensure content is not null/undefined (use empty string as default)
    const safeContent = content || '';

    // Check if this is a quill-editor web component
    if (el.tagName.toLowerCase() === 'quill-editor') {
        // Use the Quill component's clear() method and setText() for text content
        if (el.clear && typeof el.clear === 'function') {
            el.clear(); // Clear any existing content
        }
        if (el.quill && el.quill.setText) {
            el.quill.setText(safeContent); // Set as plain text
        } else if (el.editor && el.editor.setText) {
            el.editor.setText(safeContent);
        } else {
            // Fallback: try value property
            el.value = safeContent;
        }
    } else {
        // For regular inputs, use standard approaches
        if (el.value !== undefined) {
            el.value = safeContent;
        }
        if (el.setAttribute) {
            el.setAttribute('content', safeContent);
        }
        if (el.innerText !== undefined) {
            el.innerText = safeContent;
        }
        if (el.textContent !== undefined) {
            el.textContent = safeContent;
        }
    }
} 

// --- GLOBAL STATE ---
let EXERCISE_METADATA = {};
let EXERCISE_QUESTIONS = [];
let currentQIndex = -1; // -1 means no question is selected/being edited.
let nextQId = 1;
const INITIAL_DATA = window.EXERCISE_INITIAL_DATA || {};
const IS_EDIT_MODE = Boolean(window.EXERCISE_IS_EDIT_MODE);

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

async function loadSubjectOptions() {
    const subjectSelect = document.getElementById('exercise-subject-input');
    if (!subjectSelect) return;

    // Keep placeholder and clear old dynamic options.
    subjectSelect.innerHTML = '<option value="">Select a subject</option>';

    try {
        const res = await fetch(API_SUBJECTS_URL, {headers: {'Accept': 'application/json'}});
        const result = await res.json();

        if (!res.ok || !result.success || !Array.isArray(result.subjects)) {
            throw new Error(result.message || 'Failed to load subjects.');
        }

        result.subjects.forEach((subject) => {
            const option = document.createElement('option');
            option.value = String(subject.id);
            option.textContent = subject.name;
            subjectSelect.appendChild(option);
        });

        // Restore previously selected value after reload when possible.
        if (EXERCISE_METADATA.subject) {
            subjectSelect.value = String(EXERCISE_METADATA.subject);
        }
    } catch (err) {
        console.error('Could not load subjects', err);
        const option = document.createElement('option');
        option.value = '';
        option.textContent = 'Unable to load subjects';
        subjectSelect.appendChild(option);
        subjectSelect.disabled = true;
    }
}

function normalizeInitialQuestion(question, index) {
    const options = Array.isArray(question.options) ? question.options : [];

    return {
        id: question.id ? parseInt(question.id) : index + 1,
        question_text: question.question_text || question.prompt || '',
        explanation: question.explanation || '',
        weight: question.weight ?? 1,
        options: options.map((option) => ({
            text: option.text ?? option.answer_text ?? '',
            isCorrect: Boolean(option.isCorrect ?? option.is_correct),
        })),
    };
}

function hydrateInitialData() {
    const metadata = INITIAL_DATA.metadata || {};
    const questions = Array.isArray(INITIAL_DATA.questions) ? INITIAL_DATA.questions : [];

    if (!metadata.title) {
        return false;
    }

    EXERCISE_METADATA = {
        id: metadata.id || '',
        title: metadata.title || '',
        subjectId: metadata.subjectId || '',
        subject: metadata.subject || '',
        description: metadata.description || '',
        tags: metadata.tags || '',
    };

    EXERCISE_QUESTIONS = questions.map((question, index) => normalizeInitialQuestion(question, index));
    nextQId = EXERCISE_QUESTIONS.reduce((maxId, question) => Math.max(maxId, question.id || 0), 0) + 1;

    const subjectSelect = document.getElementById('exercise-subject-input');
    document.getElementById('exercise-title-input').value = EXERCISE_METADATA.title;
    if (subjectSelect) {
        subjectSelect.value = String(EXERCISE_METADATA.subjectId || '');
    }
    document.getElementById('exercise-description-input').value = EXERCISE_METADATA.description;
    document.getElementById('exercise-tags-input').value = EXERCISE_METADATA.tags;

    setupModal.style.display = 'none';
    mainBuilderContent.style.display = 'flex';
    controlBar.style.display = 'flex';

    renderQuestionList();
    if (EXERCISE_QUESTIONS.length > 0) {
        loadQuestion(0);
    } else {
        addNewQuestion();
    }

    updateBuilderUI(true);
    return true;
}

// --- STEP 1: SETUP MODAL LOGIC ---

// Function to handle the actual saving of form data and closing the modal
function updateMetadata() {
    EXERCISE_METADATA = {
        id: EXERCISE_METADATA.id || '',
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

function letterForIndex(index) {
    const letters = ['A', 'B', 'C', 'D', 'E', 'F'];
    return letters[index] || `#${index + 1}`;
}

function generateOptionHtml(index, text = '', isCorrect = false) {
    const type = 'checkbox'; // single-choice
    const safeText = (text || '').replace(/"/g, '&quot;');
    return `
        <div class="option-item">
            <div class="option-label">
                <div>${letterForIndex(index)}</div>
                <input type="${type}" name="correct-option" id="option-correct-${index}" value="${index}" ${isCorrect ? 'checked' : ''}>
            </div>
            <quill-editor 
                id="option-text-${index}"
                name="content"
                content="${safeText}"
                placeholder="Option ${index + 1} text"
                storage-key="demo-editor-2"
                height="90px">
            </quill-editor>
            <button type="button" onclick="removeOption(this, ${index})" class="btn-red">🗑️</button>
        </div>
    `;
}

// Render at least 4 options (A-D) so all are visible without extra scrolling.
function renderOptions(options = []) {
    const padded = [...options];
    while (padded.length < 4) {
        padded.push({text: '', is_correct: padded.length === 0});
    }

    optionsContainer.innerHTML = padded.map((opt, index) => {
        const text = opt.text ?? opt.answer_text ?? '';
        const isCorrect = opt.isCorrect ?? opt.is_correct ?? false;
        return generateOptionHtml(index, text, isCorrect);
    }).join('');
} 

window.removeOption = function(el, index) {
    // Keep a minimum of 4 options visible per requirements.
    if (optionsContainer.children.length <= 4) {
        alert('An exercise must keep at least four options.');
        return;
    }
    el.parentElement.remove();
}

document.getElementById('add-option-btn').addEventListener('click', () => {
    const nextIndex = optionsContainer.children.length;
    optionsContainer.insertAdjacentHTML('beforeend', generateOptionHtml(nextIndex));
});

function saveCurrentQuestion() {
    // UI-only validation: read current form state from DOM
    const qId = document.getElementById('current-q-id').value;
    const prompt = getEditorContent('q-prompt-input').trim();
    const explanation = getEditorContent('q-explanation-input').trim();
    const weight = parseInt(document.getElementById('q-weight-input').value);
    
    // VALIDATE: prompt, explanation, weight >= 1
    if (!prompt  || isNaN(weight) || weight < 1) {
        alert('Please fill out the prompt, explanation, and weight (>=1).');
        return false; // Validation failed, do not save
    }

    // Collect options from DOM (minimum 4 required)
    const options = Array.from(optionsContainer.children).map((div, index) => ({
        answer_text: (div.querySelector(`#option-text-${index}`) ? getEditorContent(`option-text-${index}`) : '').trim(),
        is_correct: !!div.querySelector(`input[name="correct-option"]`).checked,
    }));
    
    // VALIDATE: at least one correct option selected
    if (options.filter(o => o.is_correct).length === 0) {
        alert('Please select at least one correct answer.');
        return false; // Validation failed, do not save
    }

    // Create question object with ID tracking
    const newQ = {
        id: qId ? parseInt(qId) : nextQId++,
        question_text: prompt,
        explanation,
        weight,
        options,
    };

    // Update or Insert logic: qId field determines operation
    if (qId) {
        // CASE UPDATE: qId exists = this question was previously saved
        // Update the existing question in place at its current index
        EXERCISE_QUESTIONS[currentQIndex] = newQ;
    } else {
        // CASE INSERT: no qId = new question being created
        // Add to array, update currentQIndex to track this new position
        EXERCISE_QUESTIONS.push(newQ);
        currentQIndex = EXERCISE_QUESTIONS.length - 1;
        // CRITICAL: Set qId field immediately so NEXT save is UPDATE not INSERT
        // This prevents duplicates when user re-saves the same question
        document.getElementById('current-q-id').value = newQ.id;
    }
    
    // Refresh UI to reflect saved state
    renderQuestionList();
    updateBuilderUI(true);
    return true; // Save succeeded
}

function upsertCurrentQuestionDraftState() {
    const qIdRaw = document.getElementById('current-q-id').value;
    const prompt = getEditorContent('q-prompt-input').trim();
    const explanation = getEditorContent('q-explanation-input').trim();
    const weightInput = parseInt(document.getElementById('q-weight-input').value, 10);
    const weight = Number.isFinite(weightInput) && weightInput > 0 ? weightInput : 1;

    const options = Array.from(optionsContainer.children).map((div, index) => ({
        answer_text: (div.querySelector(`#option-text-${index}`) ? getEditorContent(`option-text-${index}`) : '').trim(),
        is_correct: !!div.querySelector(`input[name="correct-option"]`)?.checked,
    }));

    const hasAnyContent =
        prompt !== '' ||
        explanation !== '' ||
        options.some((opt) => opt.answer_text !== '');

    if (!qIdRaw && !hasAnyContent) {
        return;
    }

    const draftQuestion = {
        id: qIdRaw ? parseInt(qIdRaw, 10) : nextQId++,
        question_text: prompt,
        explanation,
        weight,
        options,
    };

    if (qIdRaw && currentQIndex >= 0 && currentQIndex < EXERCISE_QUESTIONS.length) {
        EXERCISE_QUESTIONS[currentQIndex] = draftQuestion;
    } else {
        EXERCISE_QUESTIONS.push(draftQuestion);
        currentQIndex = EXERCISE_QUESTIONS.length - 1;
        document.getElementById('current-q-id').value = String(draftQuestion.id);
    }

    renderQuestionList();
}

function buildDraftPayload() {
    return {
        metadata: {
            id: EXERCISE_METADATA.id || '',
            title: EXERCISE_METADATA.title || '',
            subject: EXERCISE_METADATA.subject || '',
            description: EXERCISE_METADATA.description || '',
            tags: EXERCISE_METADATA.tags || '',
        },
        questions: EXERCISE_QUESTIONS.map((q) => ({
            id: q.id,
            question_text: quillJsonToPlainText(q.question_text || q.prompt || ''),
            explanation: quillJsonToPlainText(q.explanation || ''),
            weight: q.weight ?? q.difficulty ?? 1,
            difficulty: q.difficulty ?? q.weight ?? 1,
            options: (q.options || []).map((opt) => ({
                answer_text: quillJsonToPlainText(opt.answer_text ?? opt.text ?? ''),
                is_correct: Boolean(opt.is_correct ?? opt.isCorrect),
            })),
        })),
    };
}

async function persistDraft() {
    upsertCurrentQuestionDraftState();

    const payload = buildDraftPayload();
    const res = await fetch(MOCK_API_SAVE_URL, {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify(payload),
    });

    const result = await res.json();
    if (!res.ok || !result.success) {
        throw new Error(result.message || 'Failed to save draft');
    }

    if (result.id) {
        EXERCISE_METADATA.id = String(result.id);
    }

    return result;
}

function loadQuestion(index) {
    // UI State: Load a saved question from EXERCISE_QUESTIONS into the editor
    // Called when user clicks a question from left panel
    // After loading, all editor inputs remain fully editable
    
    currentQIndex = index;

    renderQuestionList();

    const q = EXERCISE_QUESTIONS[index];
    
    // Set hidden qId field so next save is UPDATE not INSERT
    document.getElementById('current-q-id').value = q.id;
    
    // Load question data into editor fields
    // setEditorContent() handles JSON normalization automatically
    setEditorContent('q-prompt-input', q.question_text ?? q.prompt ?? '');
    setEditorContent('q-explanation-input', q.explanation ?? '');
    document.getElementById('q-weight-input').value = q.weight ?? 1;
    document.getElementById('current-q-title').textContent = `Question Editor: Q${index + 1}`;
    
    // Re-render option editors with this question's option data
    renderOptions(q.options ?? []);
    updateBuilderUI();
} 

window.loadQuestion = loadQuestion;

function addNewQuestion() {
    // UI State: Create a fresh new question editor
    // First, save current question if one exists
    if (currentQIndex !== -1 && !saveCurrentQuestion()) {
         return; // Don't proceed if save fails (user stays on current question)
    }
    
    // Mark as new question mode (empty qId = INSERT on next save, not UPDATE)
    currentQIndex = -1;
    document.getElementById('current-q-id').value = '';
    document.getElementById('current-q-title').textContent = `Question Editor: New`;
    
    // Reset form to clear all HTML inputs
    qEditorForm.reset();
    
    // IMPORTANT: Explicitly clear Quill editor content via setEditorContent()
    // form.reset() alone does NOT clear quill-editor web components
    // Without this, old question text persists visually in editor
    setEditorContent('q-prompt-input', '');
    setEditorContent('q-explanation-input', '');
    
    // Render 4 blank options (minimum required by system)
    renderOptions();
    updateBuilderUI();
}

function renderQuestionList() {
    qListContainer.innerHTML = EXERCISE_QUESTIONS.map((q, index) => {
        const preview = (q.question_text ?? q.prompt ?? '').replace(/\s+/g, ' ');
        const short = preview.length > 30 ? preview.substring(0, 30) + '...' : preview;
        return `
        <div class="question-item ${index === currentQIndex ? 'active' : ''}" onclick="window.loadQuestion(${index})">
            <span>Q${index + 1}: ${short}</span>
            <button type="button" class="btn-none" onclick="event.stopPropagation(); deleteQuestion(${index})">🗑️</button>
        </div>
    `;
    }).join('');
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
    // Save & Add New button: UI-only behavior
    // 1. Validate and save current question
    // 2. If save fails, show error and do NOT proceed
    // 3. If save succeeds, immediately open fresh blank editor
    // Previous questions stay in memory, fully editable (never locked)
    
    if (!saveCurrentQuestion()) {
        return; // Validation failed, user stays on current question for correction
    }
    
    // Save succeeded, now create fresh blank question editor
    addNewQuestion();
});

document.getElementById('prev-q-btn').addEventListener('click', () => {
    // Previous button: Save-before-navigate pattern
    // 1. Save current question (return if validation fails)
    // 2. If currentQIndex > 0, load previous question for editing
    // Previous questions remain fully editable—no data locked
    
    if (!saveCurrentQuestion()) {
        return; // Validation failed, stay on current question
    }
    
    if (currentQIndex > 0) {
        loadQuestion(currentQIndex - 1);
    }
});

document.getElementById('save-draft-btn').addEventListener('click', async () => {
    updateMetadata();

    const saveBtn = document.getElementById('save-draft-btn');
    const original = saveBtn.textContent;
    saveBtn.disabled = true;
    saveBtn.textContent = 'Saving...';

    try {
        await persistDraft();
        document.getElementById('draft-status').textContent = 'Draft Saved';
        document.getElementById('draft-status').style.color = 'var(--color-success)';
    } catch (err) {
        console.error('Draft save failed', err);
        alert(err.message || 'Failed to save draft.');
        document.getElementById('draft-status').textContent = 'Save failed';
        document.getElementById('draft-status').style.color = 'var(--color-error)';
    } finally {
        saveBtn.disabled = false;
        saveBtn.textContent = original;
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

document.getElementById('confirm-submit-btn').addEventListener('click', async () => {
    if (!EXERCISE_METADATA.title) {
        alert('Please provide exercise details before submitting.');
        return;
    }

    const submitBtn = document.getElementById('confirm-submit-btn');
    submitBtn.disabled = true;
    submitBtn.textContent = 'Submitting...';

    // Frontend-only cleanup: ensure plain text is sent (no raw Quill JSON).
    const cleanedQuestions = EXERCISE_QUESTIONS.map(q => ({
        id: q.id,
        question_text: quillJsonToPlainText(q.question_text),
        explanation: quillJsonToPlainText(q.explanation),
        weight: q.weight ?? q.difficulty ?? 1,
        difficulty: q.difficulty ?? q.weight ?? 1,
        options: (q.options || []).map(opt => ({
            answer_text: quillJsonToPlainText(opt.answer_text ?? opt.text ?? ''),
            is_correct: Boolean(opt.is_correct ?? opt.isCorrect)
        }))
    }));

    const payload = {
        metadata: {
            id: EXERCISE_METADATA.id || '',
            title: EXERCISE_METADATA.title,
            subject: EXERCISE_METADATA.subject,
            description: EXERCISE_METADATA.description || null,
            tags: EXERCISE_METADATA.tags || ''
        },
        questions: cleanedQuestions
    };

    try {
        const res = await fetch(MOCK_API_SUBMIT_URL, {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify(payload)
        });

        const result = await res.json();
        //redirecting
        if (res.status === 201 || (result && result.success)) {
            if (result.id) {
                EXERCISE_METADATA.id = String(result.id);
            }
            alert(`SUCCESS! Exercise "${EXERCISE_METADATA.title}" submitted with ${cleanedQuestions.length} questions.`);
            setTimeout(() => {
                window.location.href = ROOT + 'exercises';
                }, 800);

        } else {
            alert('Error: ' + (result.message || 'Unknown error from server.'));
            submitBtn.disabled = false;
            submitBtn.textContent = 'Submit for Review';
        }
    } catch (err) {
        console.error('Submission error', err);
        alert('Submission failed due to a network error.');
        const submitBtn = document.getElementById('confirm-submit-btn');
        submitBtn.disabled = false;
        submitBtn.textContent = 'Submit for Review';
    }
});


// Initial setup: Render options on load
async function initializeView() {
    renderOptions();
    await loadSubjectOptions();

    if (IS_EDIT_MODE && hydrateInitialData()) {
        return;
    }

    updateBuilderUI();
}

initializeView();
