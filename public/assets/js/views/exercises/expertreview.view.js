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
    const promptEl = document.getElementById('question-prompt');
    const optionsEl = document.getElementById('answer-options');
    const explanationBox = document.getElementById('explanation-box');
    const explanationTextEl = document.getElementById('explanation-text');

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
        
        // Update Question Prompt
        promptEl.textContent = qData.prompt;
        
        // Update Options: Highlighting only the true correct answer(s)
        optionsEl.innerHTML = qData.options.map(option => {
            // In review mode, we only care if the option is the correct one (is-correct)
            const classes = option.is_correct ? ' is-correct' : ''; 
            
            return `
                <label class="option-label ${classes}">
                    <input type="${qData.options.filter(o => o.is_correct).length > 1 ? 'checkbox' : 'radio'}" 
                           data-option-id="${option.option_id}" 
                           disabled 
                           ${option.is_correct ? 'checked' : ''}>
                    ${option.text}
                </label>
            `;
        }).join('');
        
        // Update Explanation (Always shown)
        explanationTextEl.textContent = qData.explanation || 'No explanation provided for this question.';
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
        // Use the initial action modal for details, or define a new one if needed
        modalEl.classList.remove('hidden');
    }


    // --- D. EVENT LISTENERS ---

    detailsLink.addEventListener('click', showDetailsModal);

    // Navigation
    prevBtn.addEventListener('click', goToPreviousQuestion);
    nextBtn.addEventListener('click', goToNextQuestion);
    
    // Modal Action Handlers: All now call the PHP-defined function which manages the confirmation modal
    approveModalBtn.addEventListener('click', () => window.handleReviewAction('approve'));
    rejectModalBtn.addEventListener('click', () => window.handleReviewAction('reject'));
    
    // Control Bar Action Handlers
    approveBtn.addEventListener('click', () => window.handleReviewAction('approve'));
    rejectBtn.addEventListener('click', () => window.handleReviewAction('reject'));

    // Initial load
    loadExerciseData();
    
    // Global function to close any modal (Ensures it's defined here too for local use)
    if (!window.closeModal) {
        window.closeModal = function(id) {
            const modal = document.getElementById(id);
            if (modal) {
                modal.classList.add('hidden');
            }
        };
    }
});