document.addEventListener('DOMContentLoaded', () => {
    // --- DOM Elements ---
    const form = document.getElementById('exercise-editor-form');
    const questionsListContainer = document.getElementById('questions-list');
    const questionOrderField = document.getElementById('question-order-field');
    const btnAddQuestion = document.getElementById('btn-add-question');
    const exerciseTitleInput = document.getElementById('exercise-title-input'); // NEW
    
    // Tag Elements
    const tagsInput = document.getElementById('tags-input');
    const selectedTagsDisplay = document.getElementById('selected-tags-display');
    const hiddenTagsField = document.getElementById('hidden-tags-field');

    // Modal Elements
    const modal = document.getElementById('question-modal');
    const closeBtn = document.querySelector('.close-button');
    const btnModalSave = document.getElementById('btn-modal-save');
    const modalQuestionText = document.getElementById('modal-question-text');
    const modalOptionsText = document.getElementById('modal-options-text');
    const modalQuestionId = document.getElementById('modal-question-id');
    const modalTitleText = document.getElementById('modal-title-text');

    // --- State ---
    let questionsState = INITIAL_QUESTIONS_DATA || []; // Starts empty for creator
    let selectedTags = new Set(INITIAL_TAGS || []);     // Starts empty for creator
    let dragSrcEl = null; 

    // --- Utility Functions ---

    function generateUUID() {
        return 'q_' + Math.random().toString(36).substring(2, 9) + Date.now().toString(36);
    }

    /** Updates the hidden field and button text. */
    function updateQuestionOrder() {
        const order = questionsState.map(q => q.id).join(',');
        questionOrderField.value = order;
        
        questionsListContainer.querySelectorAll('.question-panel').forEach((panel, index) => {
            panel.querySelector('.question-number').textContent = `Question ${index + 1}`;
        });
        
        // NEW: Update 'Add Question' button text
        btnAddQuestion.textContent = questionsState.length === 0 ? 
                                     'add first question' : 
                                     'add another question';
    }

    // --- Tag Management Functions (UNCHANGED) ---

    function updateHiddenTagsField() {
        hiddenTagsField.value = Array.from(selectedTags).join(',');
    }

    function renderTag(tagName) {
        // ... (tag rendering logic)
        const tagPill = document.createElement('span');
        tagPill.classList.add('tag-pill');
        tagPill.innerHTML = `${tagName}<span class="tag-removal" data-tag="${tagName}">&times;</span>`;
        
        tagPill.querySelector('.tag-removal').addEventListener('click', (e) => {
            const tagToRemove = e.target.dataset.tag;
            removeTag(tagToRemove);
        });
        
        selectedTagsDisplay.appendChild(tagPill);
    }

    function addTag(tagName) {
        tagName = tagName.trim();
        if (!tagName || selectedTags.has(tagName)) return;

        selectedTags.add(tagName);
        renderTag(tagName);
        updateHiddenTagsField();
    }

    function removeTag(tagName) {
        if (selectedTags.delete(tagName)) {
            updateTagsDisplayArea(); 
            updateHiddenTagsField();
        }
    }

    function updateTagsDisplayArea() {
        selectedTagsDisplay.innerHTML = '';
        selectedTags.forEach(renderTag);
    }
    
    // --- Rendering and DOM Management ---

    function createQuestionPanel(q, index) {
        const panel = document.createElement('div');
        panel.className = 'question-panel';
        panel.id = `question-${q.id}`;
        panel.dataset.questionId = q.id;
        panel.draggable = true;

        const optionsHTML = q.options.map(opt => 
            `<li><label><input type="radio" disabled>${opt}</label></li>`
        ).join('');
        
        const answerPreviewHTML = `
            <div class="answer-preview">
                <span class="preview-type">Type: Multiple Choice</span>
                <ul>${optionsHTML}</ul>
            </div>`;

        panel.innerHTML = `
            <div class="question-header">
                <span class="question-number">Question ${index + 1}</span>
                <div class="panel-actions">
                    <button type="button" class="btn-edit" data-id="${q.id}">Edit</button>
                    <button type="button" class="btn-delete" data-id="${q.id}">Delete</button>
                </div>
            </div>
            <p class="question-text">${q.question_text}</p>
            ${answerPreviewHTML}
        `;
        return panel;
    }

    function renderQuestionList() {
        questionsListContainer.innerHTML = '';
        questionsState.forEach((q, index) => {
            questionsListContainer.appendChild(createQuestionPanel(q, index));
        });
        attachPanelListeners();
        updateQuestionOrder();
    }

    // --- Modal/Edit Functions ---

    function openModal(question) {
        modalQuestionId.value = question.id || '';
        modalQuestionText.value = question.question_text || '';
        modalOptionsText.value = (question.options || []).join('\n');
        
        modalTitleText.textContent = question.id ? 'Edit' : 'Add New';
        modal.style.display = 'block';
    }

    function closeModal() {
        modal.style.display = 'none';
    }

    // Save button handler inside the modal (UNCHANGED)
    btnModalSave.addEventListener('click', () => {
        const id = modalQuestionId.value;
        const text = modalQuestionText.value.trim();
        const type = 'multiple_choice'; 
        const optionsText = modalOptionsText.value.trim();
        
        if (!text) {
            alert("Question text cannot be empty.");
            return;
        }

        const options = optionsText.split('\n').map(o => o.trim()).filter(o => o.length > 0);
        if (options.length < 2) {
             alert("Multiple choice questions require at least two options.");
             return;
        }
        
        let questionIndex = questionsState.findIndex(q => q.id === id);

        const newQuestionData = {
            id: id || generateUUID(),
            question_text: text,
            answer_type: type,
            options: options,
        };

        if (questionIndex !== -1) {
            questionsState[questionIndex] = { ...questionsState[questionIndex], ...newQuestionData };
        } else {
            questionsState.push(newQuestionData);
        }

        renderQuestionList();
        closeModal();
    });

    // --- Drag and Drop Handlers (UNCHANGED) ---

    function handleDragStart(e) { /* ... */ }
    function handleDragEnd() { /* ... */ }
    function handleDragOver(e) { /* ... */ }
    function handleDragEnter(e) { /* ... */ }
    function handleDrop(e) { 
        if (e.stopPropagation) e.stopPropagation();
        
        if (dragSrcEl !== this) {
            const dragId = e.dataTransfer.getData('text/plain');
            const targetEl = this;
            const targetId = targetEl.dataset.questionId;
            const dragIndex = questionsState.findIndex(q => q.id === dragId);
            const targetIndex = questionsState.findIndex(q => q.id === targetId);

            if (dragIndex === -1 || targetIndex === -1) return;

            const [draggedItem] = questionsState.splice(dragIndex, 1);
            
            const rect = targetEl.getBoundingClientRect();
            const isBefore = e.clientY < rect.top + rect.height / 2;
            
            let newIndex = targetIndex;
            if (!isBefore) {
                newIndex = targetIndex + 1;
            }

            if (dragIndex < targetIndex && isBefore) newIndex = targetIndex;
            
            questionsState.splice(newIndex, 0, draggedItem);

            renderQuestionList();
        }

        return false;
    }

    function attachPanelListeners() {
        questionsListContainer.querySelectorAll('.question-panel').forEach(panel => {
            // Drag listeners
            panel.addEventListener('dragstart', handleDragStart);
            panel.addEventListener('dragenter', handleDragEnter);
            panel.addEventListener('dragover', handleDragOver);
            panel.addEventListener('dragleave', handleDragEnd); 
            panel.addEventListener('drop', handleDrop);
            panel.addEventListener('dragend', handleDragEnd);

            // Action button listeners
            panel.querySelector('.btn-edit').addEventListener('click', function() {
                const qId = this.dataset.id;
                const question = questionsState.find(q => q.id === qId);
                openModal(question); 
            });
            
            panel.querySelector('.btn-delete').addEventListener('click', function() {
                if (confirm('Are you sure you want to delete this question?')) {
                    const qId = this.dataset.id;
                    questionsState = questionsState.filter(q => q.id !== qId);
                    renderQuestionList();
                }
            });
        });
    }

    // --- Global Event Listeners ---

    tagsInput.addEventListener('keydown', (e) => {
        if (e.key === 'Enter' || e.key === ',') {
            e.preventDefault();
            const inputVal = tagsInput.value.trim();
            if (inputVal) {
                inputVal.split(',').forEach(tag => addTag(tag.trim()));
                tagsInput.value = '';
            }
        }
    });

    btnAddQuestion.addEventListener('click', () => {
        // Default new question structure
        const newQuestion = { id: null, question_text: '', options: ['Option A', 'Option B'] }; 
        openModal(newQuestion);
    });
    
    closeBtn.addEventListener('click', closeModal);
    window.addEventListener('click', (e) => {
        if (e.target === modal) {
            closeModal();
        }
    });


    // Final submit handler (UPDATED VALIDATION)
    form.addEventListener('submit', (e) => {
        e.preventDefault();
        
        // --- NEW Validation Checks ---
        if (exerciseTitleInput.value.trim() === '') {
            alert("The Exercise Title is required.");
            exerciseTitleInput.focus();
            return;
        }

        if (questionsState.length === 0) {
            alert("You must add at least one question to the exercise.");
            return;
        }
        // --- End Validation Checks ---
        
        updateQuestionOrder(); 
        updateHiddenTagsField(); 

        console.log("--- New Exercise Submission Data ---");
        // Note: exercise_id will be 'new' and should be handled by the backend
        console.log("Exercise ID (Placeholder):", form.elements['exercise_id'].value); 
        console.log("Exercise Title:", exerciseTitleInput.value);
        console.log("Subject Name:", document.getElementById('exercise-subject-input').value);
        console.log("Tags:", hiddenTagsField.value);
        console.log("Question Order:", questionOrderField.value);
        console.log("Full Question State:", questionsState);

        let hiddenQuestionsField = document.getElementById('hidden-questions-field');

        if (!hiddenQuestionsField) {
            hiddenQuestionsField = document.createElement('input');
            hiddenQuestionsField.type = 'hidden';
            hiddenQuestionsField.name = 'questions_data';
            hiddenQuestionsField.id = 'hidden-questions-field';
            form.appendChild(hiddenQuestionsField);
        }

        // Serialize questionsState (array of arrays or objects)
        hiddenQuestionsField.value = JSON.stringify(questionsState);


        form.submit();
        
        // alert("Exercise creation request sent successfully! (Check console for submitted data)");
        
        // In a real application, you'd send an AJAX POST request to the 'create-new-exercise' endpoint here.
    });

    // --- Initialization ---

    updateTagsDisplayArea();
    updateHiddenTagsField();
    
    // No initial questions to render, but call this to set the button text
    updateQuestionOrder(); 
    attachPanelListeners(); 
});