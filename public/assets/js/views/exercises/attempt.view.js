import { ROOT } from "../../core/config.js";

document.addEventListener('DOMContentLoaded', () => {
    // --- Global Variables (Loaded from PHP view) ---
    // API_URL (Full exercise data), SUBMIT_URL (Final score submission)
    // VOTE_STATUS_URL, VOTE_SUBMIT_URL
    
    let EXERCISE_DATA = {}; // Holds the full exercise structure (questions, options, answers, explanations)
    let currentQIndex = 0;
    let userAnswers = {}; // {q_id: [option_id_1, option_id_2], ...}
    let currentVoteStatus = 'None'; // User's current vote status
    let questionIsChecked = false; // State to track if the current question has been checked

    // --- DOM Elements ---
    
    // Main Content
    const mainContentEl = document.getElementById('main-exercise-content');
    const titleEl = document.getElementById('exercise-title');
    const subjectEl = document.getElementById('exercise-subject');
    const promptEl = document.getElementById('question-prompt');
    const optionsEl = document.getElementById('answer-options');
    const explanationBox = document.getElementById('explanation-box');
    const explanationTextEl = document.getElementById('explanation-text');

    // Control Bar
    const progressEl = document.getElementById('current-q-index');
    const totalEl = document.getElementById('total-q-count');
    const feedbackEl = document.getElementById('feedback-area');
    const checkBtn = document.getElementById('check-btn');
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
    const upvoteBtn = document.getElementById('upvote-btn');
    const downvoteBtn = document.getElementById('downvote-btn');
    const upvoteCountEl = document.getElementById('upvote-count');
    const downvoteCountEl = document.getElementById('downvote-count');
    const voteMessageEl = document.getElementById('vote-message');


    // ---------------------------------------------------------------------
    // --- A. INITIALIZATION AND MODAL MANAGEMENT ---
    // ---------------------------------------------------------------------

    /** Fetches the full exercise data and initializes the page */
    async function loadExerciseData() {
        try {
            const response = await fetch(API_URL);
            if (!response.ok) throw new Error('Failed to fetch exercise data');
            EXERCISE_DATA = await response.json();
            
            // Populate header and progress bar
            titleEl.textContent = EXERCISE_DATA.title;
            subjectEl.textContent = `Subject: ${EXERCISE_DATA.subject}`;
            totalEl.textContent = EXERCISE_DATA.questions.length;

            // Populate the Modal (using mock meta as EXERCISE_DATA is basic mock)
            populateModalDetails(EXERCISE_DATA);

        } catch (error) {
            console.error('Error loading exercise:', error);
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
            upvotes: 45, // Initial dummy count
            downvotes: 5, // Initial dummy count
        };
        
        modalTitleEl.textContent = mockMeta.title;
        modalSubjectEl.textContent = mockMeta.subject;

        modalTagsEl.innerHTML = mockMeta.tags.map(tag => `<span class="tag-pill">${tag}</span>`).join(' ');
        modalCreatorNameEl.textContent = mockMeta.creator_name;
        modalCreatorRoleEl.textContent = mockMeta.creator_role;
        modalDateEl.textContent = mockMeta.created_at;

        upvoteCountEl.textContent = mockMeta.upvotes;
        downvoteCountEl.textContent = mockMeta.downvotes;
        
        // Fetch and display current user vote status
        fetchVoteStatus();
    }

    /** Handles the Start Assessment button click */
    function startAssessment() {
        modalEl.classList.add('hidden');
        mainContentEl.classList.remove('hidden');
        if (EXERCISE_DATA.questions.length > 0) {
            renderQuestion(currentQIndex);
            checkBtn.disabled = false;
        }
    }

    /** Opens the Details modal (called by the link in the control bar) */
    function showDetailsModal(e) {
        e.preventDefault();
        modalEl.classList.remove('hidden');
    }

    // ---------------------------------------------------------------------
    // --- B. VOTE MANAGEMENT (API) ---
    // ---------------------------------------------------------------------

    /** Fetches the user's current vote status and updates buttons */
    async function fetchVoteStatus() {
        try {
            const response = await fetch(VOTE_STATUS_URL);
            if (response.ok) {
                const data = await response.json();
                currentVoteStatus = data.current_vote_status;
                updateVoteButtons();
            }
        } catch (error) {
            console.error("Could not fetch vote status.", error);
        }
    }

    /** Updates the visual state of the upvote/downvote buttons */
    function updateVoteButtons() {
        upvoteBtn.classList.remove('voted');
        downvoteBtn.classList.remove('voted');
        voteMessageEl.textContent = '';

        if (currentVoteStatus === 'Upvoted') {
            upvoteBtn.classList.add('voted');
            voteMessageEl.textContent = 'You have upvoted this.';
        } else if (currentVoteStatus === 'Downvoted') {
            downvoteBtn.classList.add('voted');
            voteMessageEl.textContent = 'You have downvoted this.';
        }
    }

    /** Submits a vote via API */
    async function submitVote(voteType) {
        const isRemoveVote = currentVoteStatus === voteType;
        const newVoteType = isRemoveVote ? 'None' : voteType;

        try {
            const response = await fetch(VOTE_SUBMIT_URL, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ vote_type: newVoteType })
            });
            
            if (response.ok) {
                const data = await response.json();
                
                // IMPORTANT: Update local state and counts based on the intended action
                // (Note: In a real system, the API should return the new total counts)
                
                // Adjust counts locally for mock environment
                const oldStatus = currentVoteStatus;
                if (oldStatus === 'Upvoted') upvoteCountEl.textContent = parseInt(upvoteCountEl.textContent) - 1;
                if (oldStatus === 'Downvoted') downvoteCountEl.textContent = parseInt(downvoteCountEl.textContent) - 1;
                
                currentVoteStatus = data.current_vote_status;

                if (currentVoteStatus === 'Upvoted') upvoteCountEl.textContent = parseInt(upvoteCountEl.textContent) + 1;
                if (currentVoteStatus === 'Downvoted') downvoteCountEl.textContent = parseInt(downvoteCountEl.textContent) + 1;

                updateVoteButtons();
            } else {
                console.error("Vote submission failed.");
            }
        } catch (error) {
            console.error("Vote API Error:", error);
        }
    }

    // ---------------------------------------------------------------------
    // --- C. QUESTION RENDERING AND STATE MANAGEMENT (Client-Side) ---
    // ---------------------------------------------------------------------

    /** Renders the question UI based on the index */
    function renderQuestion(index) {
        questionIsChecked = false;
        explanationBox.classList.add('hidden');
        feedbackEl.innerHTML = '';
        
        // Button State Reset
        checkBtn.classList.remove('hidden');
        checkBtn.disabled = true;
        explainBtn.classList.add('hidden');
        nextBtn.classList.add('hidden');
        submitBtn.classList.add('hidden');
        optionsEl.innerHTML = ''; 

        const question = EXERCISE_DATA.questions[index];
        if (!question) return;
        
        // Determine input type
        const correctCount = question.options.filter(opt => opt.is_correct).length;
        const inputType = correctCount > 1 ? 'checkbox' : 'radio';

        promptEl.textContent = `${index + 1}. ${question.prompt}`;
        progressEl.textContent = index + 1;

        question.options.forEach(option => {
            const label = document.createElement('label');
            label.className = 'option-label';
            
            const input = document.createElement('input');
            input.type = inputType;
            input.name = `q_${question.question_id}`;
            input.value = option.option_id;
            input.dataset.optionId = option.option_id;

            // Restore user selection if already attempted
            const savedAnswers = userAnswers[question.question_id] || [];
            if (savedAnswers.includes(option.option_id)) {
                input.checked = true;
            }

            // Listener to enable check button
            input.addEventListener('change', () => {
                if (!questionIsChecked) {
                    checkBtn.disabled = false;
                }
            });

            label.appendChild(input);
            label.appendChild(document.createTextNode(option.text));
            optionsEl.appendChild(label);
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
        
        const question = EXERCISE_DATA.questions[currentQIndex];
        const selectedInputs = Array.from(optionsEl.querySelectorAll(`input[name="q_${question.question_id}"]:checked`));
        const selectedOptionIds = selectedInputs.map(input => parseInt(input.value));
        
        if (!recheck) {
            userAnswers[question.question_id] = selectedOptionIds; // Save answer only if it's the first check
        }
        
        // Determine correctness
        let isCorrect = true;

        optionsEl.querySelectorAll('.option-label').forEach(label => {
            const input = label.querySelector('input');
            const optionId = parseInt(input.dataset.optionId);
            const option = question.options.find(o => o.option_id === optionId);
            
            // Apply Correctness Styles
            if (option.is_correct) {
                label.classList.add('is-correct'); 
            }
            
            // Check for missed correct answers or wrong selections
            const isUserSelected = selectedOptionIds.includes(optionId);

            if (option.is_correct && !isUserSelected) {
                isCorrect = false; // Missed a correct answer
            }
            
            if (isUserSelected && !option.is_correct) {
                label.classList.add('user-wrong'); // Selected a wrong answer
                isCorrect = false; 
            }
            
            // Disable inputs after checking
            input.disabled = true;
        });

        // Set feedback message
        feedbackEl.innerHTML = isCorrect 
            ? '<span class="feedback-correct">✅ Correct!</span>' 
            : '<span class="feedback-wrong">❌ Incorrect.</span>';

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
    }

    /** Moves to the next question or submits */
    function goToNextQuestion() {
        if (currentQIndex < EXERCISE_DATA.questions.length - 1) {
            currentQIndex++;
            renderQuestion(currentQIndex);
        }
    }

    /** Shows the explanation box */
    function showExplanation() {
        const question = EXERCISE_DATA.questions[currentQIndex];
        explanationTextEl.textContent = question.explanation;
        explanationBox.classList.remove('hidden');
    }

    // ---------------------------------------------------------------------
    // --- D. FINAL SUBMISSION (API) ---
    // ---------------------------------------------------------------------

    /** Submits the final answers to the scoring API */
    async function submitAssessment() {
        submitBtn.disabled = true;
        feedbackEl.textContent = 'Submitting...';

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
                // alert(`Assessment Submitted! Score: ${result.total_score} / ${result.total_max_score}\nAttempt ID: ${result.attempt_id}`);
                // Real app: Redirect to results page, e.g., window.location.href = \`/exercises/viewattempt?id=\${result.attempt_id}\`;
                window.location.href = ROOT + 'exercises/viewattempt/'+ result.attempt_id + '/' + EXERCISE_DATA.id;
            } else {
                alert(`Submission failed: ${result.message}`);
            }

        } catch (error) {
            console.error('Submission Error:', error);
            alert('An unexpected error occurred during submission.');
        } finally {
             submitBtn.disabled = false;
        }
    }


    // ---------------------------------------------------------------------
    // --- E. EVENT LISTENERS ---
    // ---------------------------------------------------------------------
    
    // Attempt Actions
    checkBtn.addEventListener('click', () => checkQuestion(false));
    nextBtn.addEventListener('click', goToNextQuestion);
    explainBtn.addEventListener('click', showExplanation);
    submitBtn.addEventListener('click', submitAssessment);

    // Modal/Details Actions
    startBtn.addEventListener('click', startAssessment);
    detailsLink.addEventListener('click', showDetailsModal);
    
    // Vote Actions
    upvoteBtn.addEventListener('click', () => submitVote('Upvoted'));
    downvoteBtn.addEventListener('click', () => submitVote('Downvoted'));

    // Initial load when the script runs
    loadExerciseData();
});