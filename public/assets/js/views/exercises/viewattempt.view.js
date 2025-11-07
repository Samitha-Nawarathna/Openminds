document.addEventListener('DOMContentLoaded', () => {
    
    // --- Global Variables (Loaded from PHP view) ---
    // ATTEMPT_DETAILS_URL, VOTE_STATUS_URL, VOTE_SUBMIT_URL are defined in the PHP view
    
    let RESULTS_DATA = {}; // Holds the full results structure (details array)
    let currentQIndex = 0;
    
    // --- DOM Elements ---
    
    // Layout
    const mainContentEl = document.getElementById('main-exercise-content');
    const summaryModal = document.getElementById('summary-modal');

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
    const scoreFeedbackEl = document.getElementById('current-q-score');
    
    const prevBtn = document.getElementById('prev-btn');
    const nextBtn = document.getElementById('next-btn');

    // Modal
    const modalTitleEl = document.getElementById('modal-title');
    const finalScoreEl = document.getElementById('final-score');
    const modalDateEl = document.getElementById('modal-date');
    const startReviewBtn = document.getElementById('start-review-btn');
    
    // Vote Area
    const upvoteBtn = document.getElementById('upvote-btn');
    const downvoteBtn = document.getElementById('downvote-btn');
    const voteMessageEl = document.getElementById('vote-message');

    // --- A. DATA LOADING ---

    /** Fetches and stores the full attempt details from the API. */
    async function loadAttemptDetails() {
        try {
            const response = await fetch(ATTEMPT_DETAILS_URL);
            if (!response.ok) {
                throw new Error('Failed to fetch attempt details.');
            }
            RESULTS_DATA = await response.json();
            
            // Render the first question and show the modal
            if (RESULTS_DATA.details && RESULTS_DATA.details.length > 0) {
                renderQuestion();
                showResultsSummaryModal();
                loadVoteStatus(); // Load vote status concurrently
                mainContentEl.classList.remove('hidden');
            } else {
                alert('No results found for this attempt.');
            }

        } catch (error) {
            console.error('Data Loading Error:', error);
            alert('Error loading exercise results.');
        }
    }

    /** Loads and updates the user's vote status for the exercise. (Shared logic) */
    async function loadVoteStatus() {
        // ... (Re-use loadVoteStatus logic from attempt.view.js) ...
        try {
            const response = await fetch(VOTE_STATUS_URL);
            const data = await response.json();
            window.currentVoteStatus = data.current_vote_status || 'None';
            updateVoteUI();
        } catch (error) {
            console.error('Vote status error:', error);
        }
    }
    
    // --- B. RENDERING & UI ---

    /** Renders the current question's results based on RESULTS_DATA. */
    function renderQuestion() {
        if (!RESULTS_DATA.details || RESULTS_DATA.details.length === 0) return;

        const qData = RESULTS_DATA.details[currentQIndex];
        const totalQCount = RESULTS_DATA.details.length;
        
        // Update Header
        titleEl.textContent = RESULTS_DATA.exercise_title || 'Exercise Results';
        // Subject is not provided in mock attempt details, setting to default.
        subjectEl.textContent = RESULTS_DATA.subject || 'Review Mode'; 
        
        // Update Question Prompt
        promptEl.textContent = qData.prompt;
        
        // Update Options and Feedback
        optionsEl.innerHTML = qData.options.map(option => {
            let classes = '';
            
            // Use classes defined in the unified CSS
            if (option.is_correct) {
                classes += ' is-correct'; // Highlights the correct answer in green/success
            }
            if (option.was_selected && !option.is_correct) {
                 classes += ' user-wrong'; // Highlights the user's INCORRECT selection
            } else if (option.was_selected) {
                 classes += ' was-selected'; // Highlights the user's correct selection
            }
            
            return `
                <label class="option-label ${classes}">
                    <input type="${qData.options.filter(o => o.is_correct).length > 1 ? 'checkbox' : 'radio'}" 
                           data-option-id="${option.option_id}" 
                           disabled 
                           ${option.was_selected ? 'checked' : ''}>
                    ${option.text}
                </label>
            `;
        }).join('');
        
        // Update Explanation
        explanationTextEl.textContent = qData.explanation || 'No explanation provided.';
        explanationBox.style.display = 'block'; // Always show explanation in review mode

        // Update Control Bar
        progressEl.textContent = currentQIndex + 1;
        totalEl.textContent = totalQCount;
        
        scoreFeedbackEl.innerHTML = `${qData.user_score || 0} / ${qData.max_weight || 0}`;
        scoreFeedbackEl.className = 'feedback-area';
        if (qData.user_score >= qData.max_weight) {
            scoreFeedbackEl.classList.add('feedback-correct');
        } else if (qData.user_score > 0) {
            scoreFeedbackEl.style.color = 'var(--color-warning)'; // Partial credit
        } else {
            scoreFeedbackEl.classList.add('feedback-wrong');
        }

        updateNavigationButtons();
    }

    /** Shows the initial score summary modal. */
    function showResultsSummaryModal() {
        modalTitleEl.textContent = RESULTS_DATA.exercise_title || 'Review Results';
        
        // Update score badge color based on overall result (e.g., > 50% score)
        const scoreClass = (RESULTS_DATA.total_score / RESULTS_DATA.total_max_score) >= 0.5 ? 'passed' : 'failed';
        finalScoreEl.innerHTML = `${RESULTS_DATA.total_score || 0} / ${RESULTS_DATA.total_max_score || 0}`;
        finalScoreEl.classList.add('score-badge', scoreClass);
        
        // Attempt Date is not in mock data, use today for placeholder
        modalDateEl.textContent = new Date().toLocaleDateString(); 

        summaryModal.style.display = 'flex';
    }
    
    // --- C. NAVIGATION ---
    
    function goToNextQuestion() {
        if (currentQIndex < RESULTS_DATA.details.length - 1) {
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
        const totalQCount = RESULTS_DATA.details.length;
        
        // Previous Button
        prevBtn.disabled = currentQIndex === 0;
        
        // Next Button
        if (currentQIndex === totalQCount - 1) {
            nextBtn.textContent = 'End Review';
            nextBtn.disabled = true; // Optionally disable to prevent wrap-around
        } else {
            nextBtn.textContent = 'Next Question';
            nextBtn.disabled = false;
        }
    }

    // Function to update the vote buttons based on currentVoteStatus
    function updateVoteUI() {
        // ... (Re-use updateVoteUI logic from attempt.view.js) ...
        [upvoteBtn, downvoteBtn].forEach(btn => btn.classList.remove('voted'));
        voteMessageEl.textContent = ''; 

        if (window.currentVoteStatus === 'Upvoted') {
            upvoteBtn.classList.add('voted');
            voteMessageEl.textContent = 'You have upvoted this exercise.';
        } else if (window.currentVoteStatus === 'Downvoted') {
            downvoteBtn.classList.add('voted');
            voteMessageEl.textContent = 'You have downvoted this exercise.';
        }
    }
    
    // --- D. EVENT LISTENERS ---

    // Navigation
    prevBtn.addEventListener('click', goToPreviousQuestion);
    nextBtn.addEventListener('click', goToNextQuestion);
    
    // Modal Close
    startReviewBtn.addEventListener('click', () => {
        summaryModal.style.display = 'none';
    });

    // Vote Actions (Shared logic)
    upvoteBtn.addEventListener('click', () => submitVote('Upvoted'));
    downvoteBtn.addEventListener('click', () => submitVote('Downvoted'));

    // Initial load
    loadAttemptDetails();
    
    // Global function to close any modal
    window.closeModal = function(id) {
        document.getElementById(id).style.display = 'none';
    };
});