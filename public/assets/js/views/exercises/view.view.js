document.addEventListener('DOMContentLoaded', () => {
    
    // --- Global Variables (Loaded from PHP view) ---
    // REVIEW_API_URL, APPROVE_URL, REJECT_URL, EXERCISE_ID are defined in the PHP view
    
    let EXERCISE_DATA = {}; // Holds the full exercise structure (questions array)
    let currentQIndex = 0;
    
    // --- DOM Elements ---
    
    // Layout
    const mainContentEl = document.getElementById('main-exercise-content');
    const actionModal = document.getElementById('action-modal');

    const modalEl = document.getElementById('action-modal');


    // Main Content
    const titleEl = document.getElementById('exercise-title');
    const subjectEl = document.getElementById('exercise-subject');
    const questionContainer = document.getElementById('question-container');
    const explanationBox = document.getElementById('explanation-box');

    // Control Bar
    const progressEl = document.getElementById('current-q-index');
    const totalEl = document.getElementById('total-q-count');
    
    const prevBtn = document.getElementById('prev-btn');
    const nextBtn = document.getElementById('next-btn');

    const detailsLink = document.getElementById('details-link');


    // Modal
    const modalTitleEl = document.getElementById('modal-title');
    const modalCreatorEl = document.getElementById('modal-creator');

    // Action Buttons (Modal and Control Bar)
    const approveModalBtn = document.getElementById('approve-modal-btn');
    const rejectModalBtn = document.getElementById('reject-modal-btn');
    const approveBtn = document.getElementById('approve-btn');
    const rejectBtn = document.getElementById('reject-btn');

    // --- A. DATA LOADING ---

    /** Fetches and stores the full exercise data for review. */
    async function loadExerciseData() {
        try {
            const response = await fetch(REVIEW_API_URL);
            if (!response.ok) {
                throw new Error('Failed to fetch exercise details for review.');
            }
            EXERCISE_DATA = await response.json();
            
            // Render the first question and show the modal
            if (EXERCISE_DATA.questions && EXERCISE_DATA.questions.length > 0) {
                renderQuestion();
                showActionModal();
                mainContentEl.classList.remove('hidden');
            } else {
                alert('No content found for this exercise.');
            }

        } catch (error) {
            console.error('Data Loading Error:', error);
            alert('Error loading exercise for review.');
        }
    }
    
    // --- B. RENDERING & UI ---

    /** Renders the current question's details (master content). */
    function renderQuestion() {
        if (!EXERCISE_DATA.questions || EXERCISE_DATA.questions.length === 0) return;

        const qData = EXERCISE_DATA.questions[currentQIndex];
        const totalQCount = EXERCISE_DATA.questions.length;
        
        // Update Header
        titleEl.textContent = EXERCISE_DATA.title || 'Exercise Review';
        subjectEl.textContent = EXERCISE_DATA.subject || 'Expert Review Mode'; 
        
        // Clear and rebuild question container
        questionContainer.innerHTML = '';
        
        // Create Question Prompt using Quill Editor
        const promptEditor = document.createElement('quill-editor');
        promptEditor.id = 'question-prompt';
        promptEditor.setAttribute('readonly', '');
        promptEditor.setAttribute('height', 'fit-content');
        promptEditor.className = 'question-prompt';
        promptEditor.setAttribute('content', `${currentQIndex + 1}. ${qData.prompt}`);
        questionContainer.appendChild(promptEditor);
        
        // Create options container
        const answersDiv = document.createElement('div');
        answersDiv.id = 'answer-options';
        answersDiv.className = 'answer-options-list';
        answersDiv.style.margin = "var(--space-sm)";
        questionContainer.appendChild(answersDiv);
        
        // Determine input type based on correct answer count
        const correctCount = qData.options.filter(opt => opt.is_correct).length;
        const inputType = correctCount > 1 ? 'checkbox' : 'radio';
        
        // Build option elements with Quill editors
        qData.options.forEach(option => {
            const label = document.createElement('label');
            label.className = 'option-label';
            label.style.display = 'flex';
            
            // Apply correctness class - only highlighting the correct answer(s)
            if (option.is_correct) {
                label.classList.add('is-correct');
            }
            
            // Create input element
            const input = document.createElement('input');
            input.type = inputType;
            input.name = `q_${qData.question_id}`;
            input.value = option.option_id;
            input.dataset.optionId = option.option_id;
            input.disabled = true;
            input.checked = option.is_correct || false;
            
            label.appendChild(input);
            
            // Create answer text using Quill Editor
            const answer = document.createElement('quill-editor');
            answer.id = `answer-prompt-${option.option_id}`;
            answer.setAttribute('readonly', '');
            answer.setAttribute('height', 'fit-content');
            answer.className = 'answer-prompt';
            answer.setAttribute('content', option.text);
            answer.style.border = 'none';
            
            label.appendChild(answer);
            answersDiv.appendChild(label);
        });
        
        // Update Explanation using Quill Editor (Always shown)
        explanationBox.innerHTML = '';
        
        const explainIcon = document.createElement('h3');
        explainIcon.className = 'explain-icon';
        explainIcon.textContent = '📖';
        explanationBox.appendChild(explainIcon);
        
        const explanationEditor = document.createElement('quill-editor');
        explanationEditor.id = 'explanation-text';
        explanationEditor.setAttribute('readonly', '');
        explanationEditor.setAttribute('height', 'fit-content');
        explanationEditor.setAttribute('content', qData.explanation || 'No explanation provided for this question.');
        explanationBox.appendChild(explanationEditor);
        
        explanationBox.style.display = 'block';

        // Update Control Bar
        progressEl.textContent = currentQIndex + 1;
        totalEl.textContent = totalQCount;
        
        updateNavigationButtons();
    }

    /** Shows the initial review action modal. */
    function showActionModal() {
        modalTitleEl.textContent = `Review: ${EXERCISE_DATA.title || 'Pending Exercise'}`;
        modalCreatorEl.textContent = EXERCISE_DATA.creator_name || 'Unknown'; // Assuming creator_name is in EXERCISE_DATA
        actionModal.style.display = 'flex';
    }
    
    // --- C. NAVIGATION & ACTIONS ---
    
    function goToNextQuestion() {
        if (currentQIndex < EXERCISE_DATA.questions.length - 1) {
            currentQIndex++;
            renderQuestion();
        }
    }
    
    function goToPreviousQuestion() {
        if (currentQIndex > 0) {
            currentQIndex--;
            renderQuestion();
        }
    }
    
    function updateNavigationButtons() {
        const totalQCount = EXERCISE_DATA.questions.length;
        
        // Previous Button
        prevBtn.disabled = currentQIndex === 0;
        
        // Next Button
        if (currentQIndex === totalQCount - 1) {
            nextBtn.textContent = 'End Review';
            // Final button might switch to a 'Finish' or 'Back to List' action, for now we disable.
            nextBtn.disabled = true; 
        } else {
            nextBtn.textContent = 'Next Question';
            nextBtn.disabled = false;
        }
    }

    function showDetailsModal(e) {
        e.preventDefault();
        modalEl.classList.remove('hidden');
    
    }


    // --- D. EVENT LISTENERS ---

    detailsLink.addEventListener('click', showDetailsModal);

    // Navigation
    prevBtn.addEventListener('click', goToPreviousQuestion);
    nextBtn.addEventListener('click', goToNextQuestion);
    
    // REMOVED REDUNDANT: approveModalBtn.addEventListener('click', () => { window.closeModal('action-modal'); });
    
    // Modal Action Handlers
    approveModalBtn.addEventListener('click', () => window.handleReviewAction('approve'));
    rejectModalBtn.addEventListener('click', () => window.handleReviewAction('reject'));
    
    // Control Bar Action Handlers
    approveBtn.addEventListener('click', () => window.handleReviewAction('approve'));
    rejectBtn.addEventListener('click', () => window.handleReviewAction('reject'));

    // Initial load
    loadExerciseData();
    
    // Global function to close any modal
    window.closeModal = function(id) {
        document.getElementById(id).classList.add('hidden');
    };
});