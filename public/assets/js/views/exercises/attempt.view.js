import { ROOT } from '../../core/config.js';

document.addEventListener('DOMContentLoaded', () => {
    // --- State Variables ---
    const allQuestions = ALL_QUESTIONS_DATA;
    let currentQuestionIndex = 0;
    const userAnswers = {}; // Stores { questionId: selectedOptionIndex }

    // --- DOM Elements ---
    const questionNumberEl = document.getElementById('question-number');
    const questionTextEl = document.getElementById('question-text-p');
    const optionsListEl = document.getElementById('options-list');
    const btnBack = document.getElementById('btn-back');
    const btnNext = document.getElementById('btn-next');

    /**
     * Renders a question and its options based on the given index.
     * @param {number} index - The index of the question in the allQuestions array.
     */

    const btnUpvote = document.getElementById('btn-upvote'); 
    const btnDownvote = document.getElementById('btn-downvote');
    const upvoteCountEl = document.getElementById('upvote-count');
    const downvoteCountEl = document.getElementById('downvote-count');

    // --- Utility Functions ---

    function formatCount(n) {
        if (n >= 1000) {
            return Math.round(n / 100) / 10 + 'k';
        }
        return n.toString();
    }

    // --- NEW: Mock AJAX Functions for Voting ---
    
    /**
     * @param {string} voteType - 'upvote' or 'downvote'
     * @returns {Promise<boolean>} - Resolves true if successful.
     */
    function mockSendVote(voteType) {
        console.log(`[MOCK AJAX] Sending vote for Exercise ID: ${EXERCISE_ID}, Type: ${voteType}`);
        
        return new Promise(resolve => {
            // Simulate network delay
            setTimeout(() => {
                // Simulate success
                console.log(`[MOCK AJAX] Vote received successfully: ${voteType}`);
                resolve(true); 
            }, 300);
        });
    }

    /**
     * Updates local counts and UI after a vote change.
     * @param {string} newStatus - 'upvote', 'downvote', or null (unvoted)
     */
    function updateVoteUI(newStatus) {
        // Step 1: Adjust local counts based on the transition
        
        // If the user is unvoting (e.g., clicks 'upvote' when already 'upvote')
        if (newStatus === userVoteStatus) {
            if (userVoteStatus === 'upvote') UPVOTE_COUNT--;
            if (userVoteStatus === 'downvote') DOWNVOTE_COUNT--;
            userVoteStatus = null;
        } 
        // If the user is changing their vote (e.g., from 'downvote' to 'upvote')
        else if (userVoteStatus !== null) {
            if (userVoteStatus === 'upvote') UPVOTE_COUNT--;
            if (userVoteStatus === 'downvote') DOWNVOTE_COUNT--;
            
            if (newStatus === 'upvote') UPVOTE_COUNT++;
            if (newStatus === 'downvote') DOWNVOTE_COUNT++;
            userVoteStatus = newStatus;
        } 
        // If the user is voting for the first time
        else {
            if (newStatus === 'upvote') UPVOTE_COUNT++;
            if (newStatus === 'downvote') DOWNVOTE_COUNT++;
            userVoteStatus = newStatus;
        }

        // Step 2: Update the DOM text and active classes
        
        // Update counts
        if (upvoteCountEl) upvoteCountEl.textContent = formatCount(UPVOTE_COUNT);
        if (downvoteCountEl) downvoteCountEl.textContent = formatCount(DOWNVOTE_COUNT);

        // Update button states (toggle active classes for styling)
        if (btnUpvote) btnUpvote.classList.toggle('active', userVoteStatus === 'upvote');
        if (btnDownvote) btnDownvote.classList.toggle('active', userVoteStatus === 'downvote');

        console.log(`New Counts: Upvote=${UPVOTE_COUNT}, Downvote=${DOWNVOTE_COUNT}. Status: ${userVoteStatus}`);
    }

    /**
     * Universal handler for vote button clicks.
     * @param {string} voteType - 'upvote' or 'downvote'
     */
    async function handleVoteClick(voteType) {
        // Determine the target status (null if unvoting, voteType if voting/changing)
        const targetStatus = (userVoteStatus === voteType) ? null : voteType;
        
        // Immediately update UI to show optimistic change (optional, but good UX)
        // A more robust app would wait for AJAX success before updating, or rollback on failure.
        const prevStatus = userVoteStatus;
        
        // We simulate the change first to get the new UI state
        updateVoteUI(targetStatus); 

        try {
            const success = await mockSendVote(targetStatus || 'unvote'); // 'unvote' is just a backend action

            if (!success) {
                // Rollback UI if the mock AJAX failed
                console.error("Vote failed, rolling back UI.");
                updateVoteUI(prevStatus);
            }
        } catch (error) {
            console.error("Network error during voting:", error);
            updateVoteUI(prevStatus); // Rollback
        }
    }
    function displayQuestion(index) {
        const question = allQuestions[index];
        
        // Update header and question text
        questionNumberEl.textContent = `Question ${index + 1} of ${allQuestions.length}`;
        questionTextEl.textContent = question.question_text;
        
        // Clear previous options
        optionsListEl.innerHTML = '';

        // Create and append new options
        question.options.forEach((option, optionIndex) => {
            const li = document.createElement('li');
            
            const input = document.createElement('input');
            input.type = 'radio';
            input.name = `question_${question.id}`;
            input.id = `q${question.id}_opt${optionIndex}`;
            input.value = optionIndex;
            
            const label = document.createElement('label');
            label.htmlFor = input.id;
            label.textContent = option;
            
            // Check if this answer was previously selected
            if (userAnswers[question.id] === optionIndex) {
                input.checked = true;
            }
            
            label.prepend(input);
            li.appendChild(label);
            optionsListEl.appendChild(li);
        });

        // Update button states
        btnBack.disabled = (index === 0);
        btnNext.textContent = (index === allQuestions.length - 1) ? 'Finish' : 'Next';
    }

    /**
     * Stores the selected answer for the current question.
     */
    function recordAnswer() {
        const currentQuestion = allQuestions[currentQuestionIndex];
        const selectedOption = optionsListEl.querySelector(`input[name="question_${currentQuestion.id}"]:checked`);
        
        if (selectedOption) {
            userAnswers[currentQuestion.id] = parseInt(selectedOption.value, 10);
        }
    }

    /**
     * Finishes the exercise and logs the answers.
     */
    function finishExercise() {
        recordAnswer(); // Record the final answer

        let form = document.getElementById('exercise-attempt-form');
        if (!form)
        {
            //create form
            form = document.createElement('form');
            form.id = 'exercise-attempt-form';
            form.method = 'POST';
            form.action = `${ROOT}/exercises/viewattempt`;
            //add hidden element for send id


            const inputId = document.createElement('input');
            inputId.type = 'hidden';
            inputId.name = 'exercise_id';
            inputId.value = EXERCISE_ID;
            form.appendChild(inputId);

            //add hidden element for answers
            const inputAnswers = document.createElement('input');
            inputAnswers.type = 'hidden';
            inputAnswers.name = 'answers_json';
            inputAnswers.value = JSON.stringify(userAnswers);
            form.appendChild(inputAnswers);

            document.body.appendChild(form);
        }

        form.submit();
        
        console.log("--- Exercise Attempt Finished ---");
        console.log("User Answers:", userAnswers);
        
        // Example: Calculate score
        // In a real app, you would have the correct answer index.
        const score = Object.keys(userAnswers).length; 
        
        alert(`Exercise complete! You answered ${score} out of ${allQuestions.length} questions. Check the console for your answers.`);
        
        // Optionally, disable buttons or redirect
        btnNext.disabled = true;
        btnBack.disabled = true;
    }

    // --- Event Listeners ---

    btnNext.addEventListener('click', () => {
        recordAnswer(); // Save the answer before moving
        
        if (currentQuestionIndex < allQuestions.length - 1) {
            currentQuestionIndex++;
            displayQuestion(currentQuestionIndex);
        } else {
            finishExercise();
        }
    });

    btnBack.addEventListener('click', () => {
        recordAnswer(); // Save the answer before moving
        
        if (currentQuestionIndex > 0) {
            currentQuestionIndex--;
            displayQuestion(currentQuestionIndex);
        }
    });

    // --- Initialization ---
    displayQuestion(0); // Display the first question on page load
});