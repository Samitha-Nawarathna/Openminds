import { ROOT } from "../../core/config.js";

document.addEventListener('DOMContentLoaded', () => {
    // --- Global Variables (Loaded from PHP view) ---
    // API_URL (Full exercise data), SUBMIT_URL (Final score submission)
    // VOTE_STATUS_URL, VOTE_SUBMIT_URL (NOW REMOVED FROM THIS PAGE - will be on results)
    
    const API_URL = window.EXERCISE_API_URL || '';
    let SUBMIT_URL = window.EXERCISE_SUBMIT_URL || '';
    let EXERCISE_DATA = {}; // Holds the full exercise structure (questions, options, answers, explanations)
    let currentQIndex = 0;
    let userAnswers = {}; // {q_id: [option_id_1, option_id_2], ...}
    let currentVoteStatus = 'None'; // User's current vote status
    let questionIsChecked = false; // State to track if the current question has been checked

    // --- DOM Elements ---
    
    // NEW: Loading Overlay
    const loadingOverlay = document.getElementById('loading-overlay');
    
    // Main Content
    const mainContentEl = document.getElementById('main-exercise-content');
    const titleEl = document.getElementById('exercise-title-hero') || document.getElementById('exercise-title');
    const subjectEl = document.getElementById('exercise-subject-hero') || document.getElementById('exercise-subject');
    const questionContainer = document.getElementById('question-container');
    const questionSubtextEl = document.getElementById('question-subtext');
    const progressFill = document.getElementById('progress-fill');
    const explanationBox = document.getElementById('explanation-box');

    // Control Bar
    const controlBar = document.getElementById('control-bar');
    const progressEl = document.getElementById('current-q-index');
    const totalEl = document.getElementById('total-q-count');
    const feedbackEl = document.getElementById('feedback-area');
    const checkBtn = document.getElementById('check-btn');
    const prevBtn = document.getElementById('prev-btn'); // NEW
    const nextBtn = document.getElementById('next-btn');
    const explainBtn = document.getElementById('explain-btn');
    const submitBtn = document.getElementById('submit-btn');
    const detailsLink = document.getElementById('details-link');

    // Modal Elements
    const modalEl = document.getElementById('details-modal');
    const startBtn = document.getElementById('start-btn');
    const modalTitleEl = document.getElementById('modal-title');
    const modalSubjectEl = document.getElementById('modal-subject');
    const modalTagsEl = document.getElementById('modal-tags');
    const modalCreatorNameEl = document.getElementById('modal-creator-name');
    const modalCreatorRoleEl = document.getElementById('modal-creator-role');
    const modalDateEl = document.getElementById('modal-date');

    // NEW: Confirmation Modal Elements
    const confirmationModal = document.getElementById('confirmation-modal');
    const confirmSubmitBtn = document.getElementById('confirm-submit-btn');
    const confirmCancelBtn = document.getElementById('confirm-cancel-btn');

    // NOTE: Vote buttons removed from this page - will be implemented on results page


    // ---------------------------------------------------------------------
    // --- A. INITIALIZATION AND MODAL MANAGEMENT ---
    // ---------------------------------------------------------------------

    /** NEW: Shows loading overlay */
    function showLoading(message = 'Loading...') {
        if (loadingOverlay) {
            const textEl = loadingOverlay.querySelector('p');
            if (textEl) textEl.textContent = message;
            loadingOverlay.classList.remove('hidden');
        }
    }

    /** NEW: Hides loading overlay */
    function hideLoading() {
        if (loadingOverlay) {
            loadingOverlay.classList.add('hidden');
        }
    }

    /** NEW: Shows inline loading state in feedback area */
    function showInlineLoading(message = 'Processing...') {
        feedbackEl.innerHTML = `<span class="loading-spinner"></span>${message}`;
    }

    /** Fetches the full exercise data and initializes the page */
    async function loadExerciseData() {
        try {
            if (!API_URL) {
                throw new Error('Exercise data endpoint is missing.');
            }
            showLoading('Loading exercise data...');
            
            const response = await fetch(API_URL);
            if (!response.ok) throw new Error('Failed to fetch exercise data');
            EXERCISE_DATA = await response.json();

            if (EXERCISE_DATA.success === false || !EXERCISE_DATA.questions) {
                const msg = EXERCISE_DATA.message || 'Exercise is unavailable.';
                throw new Error(msg);
            }
            
            // Populate header and progress bar
            titleEl.textContent = EXERCISE_DATA.title;
            subjectEl.textContent = EXERCISE_DATA.subject ? `Subject: ${EXERCISE_DATA.subject}` : '';
            totalEl.textContent = EXERCISE_DATA.questions.length;

            // Populate the Modal (using mock meta as EXERCISE_DATA is basic mock)
            populateModalDetails(EXERCISE_DATA);

            hideLoading();

        } catch (error) {
            console.error('Error loading exercise:', error);
            hideLoading();
            feedbackEl.innerHTML = '<span style="color: var(--color-error);">Failed to load exercise. Please refresh the page.</span>';
            modalTitleEl.textContent = "Error loading exercise details.";
        }
    }

    /** Fills the modal with metadata and prepares vote tracking */
    function populateModalDetails(data) {
        // NOTE: These metadata values are mock values not strictly in the current EXERCISE_DATA mock 
        // but represent the expected fields from the database.
        const mockMeta = {
            title: data.title,
            subject: data.subject,
            tags: ["Finance", "Basic", "Level 1"],
            creator_name: "John Doe",
            creator_role: "Expert",
            created_at: "2025-10-25",
        };
        
        modalTitleEl.textContent = mockMeta.title;
        modalSubjectEl.textContent = mockMeta.subject;

        modalTagsEl.innerHTML = mockMeta.tags.map(tag => `<span class="tag-pill">${tag}</span>`).join(' ');
        modalCreatorNameEl.textContent = mockMeta.creator_name;
        modalCreatorRoleEl.textContent = mockMeta.creator_role;
        modalDateEl.textContent = mockMeta.created_at;

        // NOTE: Vote UI removed from this page - will be on results page
    }

    /** Handles the Start Assessment button click */
    function startAssessment() {
        startBtn.textContent = "Continue...";
        modalEl.classList.add('hidden');
        mainContentEl.classList.remove('hidden');
        
        // NEW: Set focus to first question for accessibility
        if (EXERCISE_DATA.questions.length > 0) {
            renderQuestion(currentQIndex);
            checkBtn.disabled = false;
            questionContainer.focus();
        }
    }

    /** Opens the Details modal (called by the link in the control bar) */
    function showDetailsModal(e) {
        e.preventDefault();
        modalEl.classList.remove('hidden');
        // NEW: Focus management for accessibility
        modalTitleEl.focus();
    }

    // ---------------------------------------------------------------------
    // --- B. VOTE MANAGEMENT (REMOVED - Will be implemented on results page) ---
    // ---------------------------------------------------------------------
    // Vote functionality removed from attempt page per UX improvement #4


    // ---------------------------------------------------------------------
    // --- C. QUESTION RENDERING AND STATE MANAGEMENT (Client-Side) ---
    // ---------------------------------------------------------------------

    /** Renders the question UI based on the index */
    function renderQuestion(index) {
        questionIsChecked = false;
        explanationBox.classList.add('hidden');
        feedbackEl.innerHTML = '';
        controlBar.classList.remove('state-correct', 'state-wrong');
        
        // Button State Reset
        checkBtn.classList.remove('hidden');
        checkBtn.disabled = true;
        explainBtn.classList.add('hidden');
        nextBtn.classList.add('hidden');
        submitBtn.classList.add('hidden');
        nextBtn.textContent = 'Continue';
        submitBtn.textContent = 'Continue';
        
        // NEW: Show/Hide Previous Button
        if (index > 0) {
            prevBtn.classList.remove('hidden');
        } else {
            prevBtn.classList.add('hidden');
        }

        const question = EXERCISE_DATA.questions[index];
        if (!question) return;
        
        const totalQuestions = EXERCISE_DATA.questions.length || 1;
        const progressPercent = Math.min(100, Math.round((index / totalQuestions) * 100));
        if (progressFill) {
            progressFill.style.width = `${progressPercent}%`;
        }

        // Determine input type
        const correctCount = question.options.filter(opt => opt.is_correct).length;
        const inputType = correctCount > 1 ? 'checkbox' : 'radio';

        // Build question container structure
        questionContainer.innerHTML = `
            <div class="question-prompt-block">
                <h2 id="question-prompt" class="question-title" role="heading" aria-level="2">${question.prompt}</h2>
            </div>
            <div id="answer-options" class="answer-options-list" role="${inputType === 'radio' ? 'radiogroup' : 'group'}" aria-label="Answer options for question ${index + 1}"></div>
        `;

        if (questionSubtextEl) {
            questionSubtextEl.textContent = 'Select the correct answer';
        }

        // Update progress display
        progressEl.textContent = index + 1;
        totalEl.textContent = totalQuestions;

        // Build option elements
        const answersDiv = questionContainer.querySelector('#answer-options');
        const savedAnswers = userAnswers[question.question_id] || [];
        
        question.options.forEach((option, optIndex) => {
            const label = document.createElement('label');
            label.className = 'option-card';
            label.dataset.optionId = option.option_id;

            const left = document.createElement('div');
            left.className = 'option-left';

            const stateIcon = document.createElement('span');
            stateIcon.className = 'state-icon';
            stateIcon.setAttribute('aria-hidden', 'true');

            const input = document.createElement('input');
            input.type = inputType;
            input.name = `q_${question.question_id}`;
            input.value = option.option_id;
            input.dataset.optionId = option.option_id;
            input.setAttribute('aria-label', `Option ${optIndex + 1}`);

            // Restore user selection if already attempted
            if (savedAnswers.includes(option.option_id)) {
                input.checked = true;
                label.classList.add('is-selected');
            }

            // Listener to enable check button and update selected UI
            input.addEventListener('change', () => {
                if (!questionIsChecked) {
                    checkBtn.disabled = false;
                }
                answersDiv.querySelectorAll('.option-card').forEach(card => card.classList.remove('is-selected'));
                label.classList.add('is-selected');
            });

            const text = document.createElement('span');
            text.className = 'option-text';
            text.textContent = option.text;

            const badge = document.createElement('span');
            badge.className = 'option-badge';
            badge.textContent = `PRESS ${optIndex + 1}`;

            left.appendChild(stateIcon);
            left.appendChild(input);
            left.appendChild(text);
            label.appendChild(left);
            label.appendChild(badge);
            answersDiv.appendChild(label);
        });

        // If user already answered this (e.g., navigated back), re-display the results
        if (userAnswers[question.question_id]) {
            showPostCheckState();
            checkQuestion(true); // Re-run logic to apply colors/feedback without resaving answers
        }
    }

    /** Saves answer and displays feedback */
    function checkQuestion(recheck = false) {
        if (!recheck && questionIsChecked) return; 

        questionIsChecked = true;
        const optionsEl = document.getElementById('answer-options');

        const question = EXERCISE_DATA.questions[currentQIndex];
        const selectedInputs = Array.from(optionsEl.querySelectorAll(`input[name="q_${question.question_id}"]:checked`));
        const selectedOptionIds = selectedInputs.map(input => parseInt(input.value));
        
        if (!recheck) {
            userAnswers[question.question_id] = selectedOptionIds; // Save answer only if it's the first check
        }
        
        // Determine correctness
        let isCorrect = true;

        optionsEl.querySelectorAll('.option-card').forEach(label => {
            const input = label.querySelector('input');
            const optionId = parseInt(label.dataset.optionId);
            const option = question.options.find(o => o.option_id === optionId);
            const isUserSelected = selectedOptionIds.includes(optionId);
            const stateIcon = label.querySelector('.state-icon');

            label.classList.remove('is-correct', 'user-wrong', 'was-selected');
            if (stateIcon) stateIcon.textContent = '';

            if (option.is_correct) {
                label.classList.add('is-correct');
                if (stateIcon) stateIcon.textContent = '✓';
            }

            if (isUserSelected) {
                label.classList.add('was-selected');
            }

            if (option.is_correct && !isUserSelected) {
                isCorrect = false; // Missed a correct answer
            }
            
            if (isUserSelected && !option.is_correct) {
                label.classList.add('user-wrong');
                isCorrect = false; 
            }
            
            // Disable inputs after checking
            input.disabled = true;
        });

        // Set feedback message to match reference UI
        const feedbackMessage = isCorrect ? 'Great work!' : "Oops! That’s not correct.";
        feedbackEl.textContent = feedbackMessage;
        feedbackEl.className = `feedback-area ${isCorrect ? 'feedback-correct' : 'feedback-wrong'}`;
        controlBar.classList.remove('state-correct', 'state-wrong');
        controlBar.classList.add(isCorrect ? 'state-correct' : 'state-wrong');

        showExplanation();

        // Transition buttons if this is the initial check
        if (!recheck) {
            showPostCheckState();
        }
    }

    /** Manages the button state after the user clicks Check Answer */
    function showPostCheckState() {
        checkBtn.classList.add('hidden');
        explainBtn.classList.remove('hidden');
        
        // Show Next or Submit button
        if (currentQIndex < EXERCISE_DATA.questions.length - 1) {
            nextBtn.classList.remove('hidden');
            submitBtn.classList.add('hidden');
        } else {
            nextBtn.classList.add('hidden');
            submitBtn.classList.remove('hidden');
        }
        
        // NEW: Keep Previous button visible if not on first question
        if (currentQIndex > 0) {
            prevBtn.classList.remove('hidden');
        }
    }

    /** NEW: Moves to the previous question */
    function goToPreviousQuestion() {
        if (currentQIndex > 0) {
            currentQIndex--;
            renderQuestion(currentQIndex);
            // Scroll to top for better UX
            window.scrollTo({ top: 0, behavior: 'smooth' });
        }
    }

    /** Moves to the next question or submits */
    function goToNextQuestion() {
        if (currentQIndex < EXERCISE_DATA.questions.length - 1) {
            currentQIndex++;
            renderQuestion(currentQIndex);
            // Scroll to top for better UX
            window.scrollTo({ top: 0, behavior: 'smooth' });
        }
    }

    /** Shows the explanation box */
    function showExplanation() {
        const question = EXERCISE_DATA.questions[currentQIndex];
        explanationBox.innerHTML = `
            <h3 class="explain-icon" aria-label="Explanation">📖</h3>
            <quill-editor 
                id="explanation-text"
                readonly
                height="fit-content"
                content="${question.explanation}"
            >
            </quill-editor>
        `;
        explanationBox.classList.remove('hidden');
    }

    // ---------------------------------------------------------------------
    // --- D. FINAL SUBMISSION (API) ---
    // ---------------------------------------------------------------------

    /** NEW: Shows confirmation modal before final submission */
    function showConfirmationModal() {
        confirmationModal.classList.remove('hidden');
        // NEW: Focus management for accessibility
        confirmSubmitBtn.focus();
    }

    /** NEW: Hides confirmation modal */
    function hideConfirmationModal() {
        confirmationModal.classList.add('hidden');
    }

    /** Submits the final answers to the scoring API */
    async function submitAssessment() {
        // Hide confirmation modal
        hideConfirmationModal();
        
        // NEW: Disable submit button and show loading state
        submitBtn.disabled = true;
        submitBtn.classList.add('btn-loading');
        showInlineLoading('Submitting your assessment...');

        if (!SUBMIT_URL) {
            feedbackEl.innerHTML = '<span style="color: var(--color-error);">Submission endpoint missing.</span>';
            submitBtn.disabled = false;
            submitBtn.classList.remove('btn-loading');
            return;
        }

        // Ensure the last question is saved before submitting
        if (!questionIsChecked) {
            // Check the last question (client-side) before packing data
            checkQuestion(false); 
        }

        const submissionPayload = {
            exercise_id: EXERCISE_DATA.id,
            answers: Object.keys(userAnswers).map(qId => ({
                question_id: parseInt(qId),
                selected_option_ids: userAnswers[qId]
            }))
        };
        
        try {
            const response = await fetch(SUBMIT_URL, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(submissionPayload)
            });
            
            const result = await response.json();
            
            if (response.ok) {
                // Success feedback
                feedbackEl.innerHTML = '<span class="feedback-correct">✅ Submitted successfully! Redirecting...</span>';
                
                // Redirect to results page (where voting will now happen)
                setTimeout(() => {
                    window.location.href = ROOT + 'exercises/viewattempt/' + EXERCISE_DATA.id + '/' + result.attempt_id;
                }, 1000);
            } else {
                // Error handling
                feedbackEl.innerHTML = `<span style="color: var(--color-error);">Submission failed: ${result.message}</span>`;
                submitBtn.disabled = false;
                submitBtn.classList.remove('btn-loading');
            }

        } catch (error) {
            console.error('Submission Error:', error);
            feedbackEl.innerHTML = '<span style="color: var(--color-error);">An unexpected error occurred. Please try again.</span>';
            submitBtn.disabled = false;
            submitBtn.classList.remove('btn-loading');
        }
    }


    // ---------------------------------------------------------------------
    // --- E. EVENT LISTENERS ---
    // ---------------------------------------------------------------------
    
    // Attempt Actions
    checkBtn.addEventListener('click', () => checkQuestion(false));
    prevBtn.addEventListener('click', goToPreviousQuestion); // NEW
    nextBtn.addEventListener('click', goToNextQuestion);
    explainBtn.addEventListener('click', showExplanation);
    
    // NEW: Submit button now shows confirmation modal first
    submitBtn.addEventListener('click', showConfirmationModal);
    
    // NEW: Confirmation modal actions
    confirmSubmitBtn.addEventListener('click', submitAssessment);
    confirmCancelBtn.addEventListener('click', hideConfirmationModal);
    
    // NEW: Close confirmation modal with Escape key
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && !confirmationModal.classList.contains('hidden')) {
            hideConfirmationModal();
        }
    });

    // Modal/Details Actions
    startBtn.addEventListener('click', startAssessment);
    detailsLink.addEventListener('click', showDetailsModal);
    
    // NOTE: Vote actions removed - will be on results page

    // NEW: Keyboard shortcuts for better UX
    document.addEventListener('keydown', (e) => {
        // Only activate shortcuts when not typing in inputs
        if (e.target.tagName === 'INPUT' || e.target.tagName === 'TEXTAREA') return;
        
        // Enter key = Check Answer or Next Question
        if (e.key === 'Enter' && !checkBtn.classList.contains('hidden') && !checkBtn.disabled) {
            checkQuestion(false);
        } else if (e.key === 'Enter' && !nextBtn.classList.contains('hidden')) {
            goToNextQuestion();
        }
        
        // Arrow keys for navigation
        if (e.key === 'ArrowLeft' && !prevBtn.classList.contains('hidden')) {
            goToPreviousQuestion();
        } else if (e.key === 'ArrowRight' && !nextBtn.classList.contains('hidden')) {
            goToNextQuestion();
        }
    });

    // Initial load when the script runs
    loadExerciseData();
});