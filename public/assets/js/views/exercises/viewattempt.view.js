import { ROOT } from "../../core/config.js";

document.addEventListener('DOMContentLoaded', () => {
    
    // --- Global Variables (Loaded from PHP view) ---
    // ATTEMPT_DETAILS_URL, VOTE_STATUS_URL, VOTE_SUBMIT_URL are defined in the PHP view
    const ATTEMPT_DETAILS_URL = window.ATTEMPT_DETAILS_URL || '';
    const VOTE_STATUS_URL = window.VOTE_STATUS_URL || '';
    const VOTE_SUBMIT_URL = window.VOTE_SUBMIT_URL || '';

    let RESULTS_DATA = {}; // Holds the full results structure (details array)
    let currentQIndex = 0;
    let currentVoteStatus = 'None'; // Store user's current vote
    
    // --- DOM Elements ---
    
    // NEW: Loading Overlay
    const loadingOverlay = document.getElementById('loading-overlay');
    
    // Layout
    const mainContentEl = document.getElementById('main-exercise-content');
    const summaryModal = document.getElementById('summary-modal');

    // Main Content
    const titleEl = document.getElementById('exercise-title-hero') || document.getElementById('exercise-title');
    const subjectEl = document.getElementById('exercise-subject-hero') || document.getElementById('exercise-subject');
    const questionContainer = document.getElementById('question-container');
    const questionSubtextEl = document.getElementById('question-subtext');
    const progressFill = document.getElementById('progress-fill');
    const explanationBox = document.getElementById('explanation-box');

    // Control Bar
    const progressEl = document.getElementById('current-q-index');
    const totalEl = document.getElementById('total-q-count');
    const scoreFeedbackEl = document.getElementById('current-q-score');
    
    const prevBtn = document.getElementById('prev-btn');
    const nextBtn = document.getElementById('next-btn');

    // Modal
    const modalTitleEl = document.getElementById('modal-title');
    const finalScoreEl = document.getElementById('final-score');
    const startReviewBtn = document.getElementById('start-review-btn');
    const tryAgainBtn = document.getElementById('try-again-btn');
    const finishedBtn = document.getElementById('finished-btn');
    
    // Vote Area
    const upvoteBtn = document.getElementById('upvote-btn');
    const downvoteBtn = document.getElementById('downvote-btn');
    const voteMessageEl = document.getElementById('vote-message');
    const voteCountDisplay = document.getElementById('vote-count-display');

    // ---------------------------------------------------------------------
    // --- A. INITIALIZATION AND LOADING ---
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

    /** Fetches and stores the full attempt details from the API. */
    async function loadAttemptDetails() {
        try {
            if (!ATTEMPT_DETAILS_URL) {
                throw new Error('Attempt details endpoint is missing.');
            }
            showLoading('Loading your results...');
            
            const response = await fetch(ATTEMPT_DETAILS_URL);
            if (!response.ok) {
                throw new Error('Failed to fetch attempt details.');
            }
            RESULTS_DATA = await response.json();

            if (RESULTS_DATA.success === false || !RESULTS_DATA.details) {
                const msg = RESULTS_DATA.message || 'Results unavailable.';
                throw new Error(msg);
            }
            
            hideLoading();
            
            // Render the first question and show the modal
            if (RESULTS_DATA.details && RESULTS_DATA.details.length > 0) {
                renderQuestion();
                showResultsSummaryModal();
                loadVoteStatus(); // Load vote status concurrently
                mainContentEl.classList.remove('hidden');
            } else {
                voteMessageEl.innerHTML = '<span style="color: var(--color-error);">No results found for this attempt.</span>';
            }

        } catch (error) {
            console.error('Data Loading Error:', error);
            hideLoading();
            voteMessageEl.innerHTML = '<span style="color: var(--color-error);">Error loading exercise results. Please refresh the page.</span>';
        }
    }

    // ---------------------------------------------------------------------
    // --- B. VOTE MANAGEMENT ---
    // ---------------------------------------------------------------------

    /** Loads and updates the user's vote status for the exercise. */
    async function loadVoteStatus() {
        try {
            if (!VOTE_STATUS_URL) return;
            const response = await fetch(VOTE_STATUS_URL);
            const data = await response.json();
            currentVoteStatus = data.current_vote_status || 'None';
            
            // Update vote count if provided
            if (data.vote_count !== undefined) {
                voteCountDisplay.textContent = data.vote_count;
            }
            
            updateVoteUI();
        } catch (error) {
            console.error('Vote status error:', error);
            voteMessageEl.textContent = 'Unable to load vote status.';
        }
    }

    /** NEW: Submits a vote to the API */
    async function submitVote(voteType) {
        try {
            if (!VOTE_SUBMIT_URL) {
                voteMessageEl.textContent = 'Voting is not available for this exercise.';
                return;
            }
            // Prevent duplicate votes
            if (currentVoteStatus === voteType) {
                voteMessageEl.textContent = `You have already ${voteType.toLowerCase()} this exercise.`;
                return;
            }

            // Show loading state on button
            const clickedBtn = voteType === 'Upvoted' ? upvoteBtn : downvoteBtn;
            clickedBtn.classList.add('btn-loading');
            clickedBtn.disabled = true;
            
            const response = await fetch(VOTE_SUBMIT_URL, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ vote_type: voteType })
            });
            
            const data = await response.json();
            
            // Remove loading state
            clickedBtn.classList.remove('btn-loading');
            clickedBtn.disabled = false;
            
            if (response.ok) {
                currentVoteStatus = voteType;
                updateVoteUI();
                voteMessageEl.textContent = data.message || `Successfully ${voteType.toLowerCase()}!`;
                voteMessageEl.style.color = 'var(--color-success)';
                
                // Update vote count if provided
                if (data.vote_count !== undefined) {
                    voteCountDisplay.textContent = data.vote_count;
                }
            } else {
                voteMessageEl.textContent = data.message || 'Failed to submit vote.';
                voteMessageEl.style.color = 'var(--color-error)';
            }

        } catch (error) {
            console.error('Vote submission error:', error);
            voteMessageEl.textContent = 'An error occurred while submitting your vote.';
            voteMessageEl.style.color = 'var(--color-error)';
            
            // Remove loading state on error
            upvoteBtn.classList.remove('btn-loading');
            downvoteBtn.classList.remove('btn-loading');
            upvoteBtn.disabled = false;
            downvoteBtn.disabled = false;
        }
    }

    /** Updates the vote buttons based on currentVoteStatus */
    function updateVoteUI() {
        // Reset all buttons
        [upvoteBtn, downvoteBtn].forEach(btn => btn.classList.remove('voted'));
        voteMessageEl.textContent = '';
        voteMessageEl.style.color = 'var(--color-text-muted)';

        if (currentVoteStatus === 'Upvoted') {
            upvoteBtn.classList.add('voted');
            upvoteBtn.setAttribute('aria-pressed', 'true');
            downvoteBtn.setAttribute('aria-pressed', 'false');
            voteMessageEl.textContent = 'You have upvoted this exercise.';
        } else if (currentVoteStatus === 'Downvoted') {
            downvoteBtn.classList.add('voted');
            downvoteBtn.setAttribute('aria-pressed', 'true');
            upvoteBtn.setAttribute('aria-pressed', 'false');
            voteMessageEl.textContent = 'You have downvoted this exercise.';
        } else {
            upvoteBtn.setAttribute('aria-pressed', 'false');
            downvoteBtn.setAttribute('aria-pressed', 'false');
        }
    }
    
    // ---------------------------------------------------------------------
    // --- C. RENDERING & UI ---
    // ---------------------------------------------------------------------

    /** Renders the current question's results based on RESULTS_DATA. */
    function renderQuestion() {
        if (!RESULTS_DATA.details || RESULTS_DATA.details.length === 0) return;

        const qData = RESULTS_DATA.details[currentQIndex];
        const totalQCount = RESULTS_DATA.details.length;
        
        // Update Header
        titleEl.textContent = RESULTS_DATA.exercise_title || 'Exercise Results';
        subjectEl.textContent = RESULTS_DATA.subject || 'Review Mode'; 
        if (questionSubtextEl) {
            const difficultyLabel = qData.difficulty ?? qData.max_weight ?? 0;
            const resultLabel = qData.is_correct ? 'Correct' : 'Incorrect';
            questionSubtextEl.textContent = `Difficulty: ${difficultyLabel} • Result: ${resultLabel}`;
        }

        const progressPercent = Math.min(100, Math.round(((currentQIndex+1) / totalQCount) * 100));
        if (progressFill) {
            progressFill.style.width = `${progressPercent}%`;
        }
        
        // Clear and rebuild question container
        questionContainer.innerHTML = `
            <div class="question-prompt-block">
                <h2 id="question-prompt" class="question-title" role="heading" aria-level="2">${qData.prompt}</h2>
            </div>
            <div id="answer-options" class="answer-options-list" role="group" aria-label="Review of question ${currentQIndex + 1} answers"></div>
        `;

        // Determine input type based on correct answer count
        const correctCount = qData.options.filter(opt => opt.is_correct).length;
        const inputType = correctCount > 1 ? 'checkbox' : 'radio';
        const answersDiv = questionContainer.querySelector('#answer-options');
        
        // Build option elements with the new card UI
        qData.options.forEach((option, optIndex) => {
            const label = document.createElement('label');
            label.className = 'option-card';
            label.dataset.optionId = option.option_id;
            
            // Apply correctness classes
            if (option.is_correct) {
                label.classList.add('is-correct');
            }
            if (option.was_selected && !option.is_correct) {
                label.classList.add('user-wrong');
            }
            if (option.was_selected) {
                label.classList.add('was-selected');
            }
            
            const left = document.createElement('div');
            left.className = 'option-left';

            const stateIcon = document.createElement('span');
            stateIcon.className = 'state-icon';
            stateIcon.setAttribute('aria-hidden', 'true');
            if (option.is_correct) {
                stateIcon.textContent = '✓';
            }

            const input = document.createElement('input');
            input.type = inputType;
            input.name = `q_${qData.question_id}`;
            input.value = option.option_id;
            input.dataset.optionId = option.option_id;
            input.disabled = true;
            input.checked = option.was_selected || false;
            input.setAttribute('aria-label', `Option ${optIndex + 1}`);
            
            const text = document.createElement('span');
            text.className = 'option-text';
            text.textContent = option.text;
            
            const badge = document.createElement('span');
            badge.className = 'option-badge';
            
            
            left.appendChild(stateIcon);
            left.appendChild(input);
            left.appendChild(text);
            label.appendChild(left);
            label.appendChild(badge);
            answersDiv.appendChild(label);
        });
        
        // Update Explanation using Quill Editor
        explanationBox.innerHTML = '';
        
        const explainIcon = document.createElement('h3');
        explainIcon.className = 'explain-icon';
        explainIcon.textContent = 'Explanation';
        explainIcon.setAttribute('aria-label', 'Explanation');
        explanationBox.appendChild(explainIcon);
        
        const explanationEditor = document.createElement('quill-editor');
        explanationEditor.id = 'explanation-text';
        explanationEditor.setAttribute('readonly', '');
        explanationEditor.setAttribute('height', 'fit-content');
        explanationEditor.setAttribute('content', qData.explanation || 'No explanation provided.');
        explanationBox.appendChild(explanationEditor);
        
        explanationBox.style.display = 'block';

        // Update Control Bar
        progressEl.textContent = currentQIndex + 1;
        totalEl.textContent = totalQCount;
        
        // Update score feedback with appropriate styling
        const currentScore = Math.abs((qData.user_score || 0) - (qData.max_weight || 0)) < 0.0001
            ? (qData.max_weight || 0)
            : (qData.user_score || 0);
        scoreFeedbackEl.innerHTML = `${currentScore} / ${qData.max_weight || 0}`;
        scoreFeedbackEl.className = 'feedback-area';
        
        if (qData.user_score >= qData.max_weight) {
            scoreFeedbackEl.classList.add('feedback-correct');
            scoreFeedbackEl.innerHTML += '<span class="sr-only">Correct answer</span>';
        } else if (qData.user_score > 0) {
            scoreFeedbackEl.style.color = 'var(--color-warning)';
            scoreFeedbackEl.innerHTML += '<span class="sr-only">Partial credit</span>';
        } else {
            scoreFeedbackEl.classList.add('feedback-wrong');
            scoreFeedbackEl.innerHTML += '<span class="sr-only">Incorrect answer</span>';
        }

        updateNavigationButtons();
        
        window.scrollTo({ top: 0, behavior: 'smooth' });
    }

    /** Shows the initial score summary modal. */
    function showResultsSummaryModal() {
        modalTitleEl.textContent = 'Quiz Complete!';
        
        // Update the score text in paragraph
        const scoreTextEl = document.getElementById('score-text');
        const rawScore = RESULTS_DATA.raw_score ?? RESULTS_DATA.total_score ?? 0;
        const maxScore = RESULTS_DATA.max_score ?? RESULTS_DATA.total_max_score ?? 0;
        const displayRawScore = Math.abs(rawScore - maxScore) < 0.0001 ? maxScore : rawScore;
        const percentageScore = RESULTS_DATA.percentage_score ?? (maxScore ? (rawScore / maxScore) * 100 : 0);

        if (scoreTextEl) {
            scoreTextEl.textContent = `${displayRawScore} out of ${maxScore}`;
        }
        
        // Update score badge color based on overall result (e.g., > 50% score)
        const scoreClass = percentageScore >= 50 ? 'passed' : 'failed';
        finalScoreEl.textContent = `${displayRawScore} / ${maxScore}`;
        
        // Clear existing classes and add new ones
        finalScoreEl.className = 'score-badge';
        finalScoreEl.classList.add(scoreClass);

        summaryModal.classList.remove('hidden');
        summaryModal.style.display = 'flex';
        
        // NEW: Focus management for accessibility
        modalTitleEl.focus();
    }
    
    // ---------------------------------------------------------------------
    // --- D. NAVIGATION ---
    // ---------------------------------------------------------------------
    
    function goToNextQuestion() {
        if (currentQIndex < RESULTS_DATA.details.length - 1) {
            currentQIndex++;
            renderQuestion();
        } else {
            // On last question, show the summary modal
            showResultsSummaryModal();
            mainContentEl.classList.add('hidden');
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
            nextBtn.textContent = 'End Review ✓';
            nextBtn.disabled = false; // Keep enabled so user can click to show modal
        } else {
            nextBtn.textContent = 'Next Question  >';
            nextBtn.disabled = false;
        }
    }

    // ---------------------------------------------------------------------
    // --- E. EVENT LISTENERS ---
    // ---------------------------------------------------------------------

    // Navigation
    prevBtn.addEventListener('click', goToPreviousQuestion);
    nextBtn.addEventListener('click', goToNextQuestion);
    
    // Modal Actions
    startReviewBtn.addEventListener('click', () => {
        summaryModal.style.display = 'none';
        summaryModal.classList.add('hidden');
        // NEW: Focus management
        questionContainer.focus();
    });
    
    // NEW: Try Again button - redirects to attempt page for same exercise
    tryAgainBtn.addEventListener('click', () => {
        const exerciseId = RESULTS_DATA.exercise_id;
        if (exerciseId) {
            window.location.href = ROOT + 'exercises/attempt?id=' + exerciseId;
        }
    });
    
    // NEW: Finished button - redirects to exercises list
    finishedBtn.addEventListener('click', () => {
        window.location.href = ROOT + 'exercises';
    });

    // Vote Actions
    upvoteBtn.addEventListener('click', () => submitVote('Upvoted'));
    downvoteBtn.addEventListener('click', () => submitVote('Downvoted'));

    // NEW: Keyboard shortcuts for better UX
    document.addEventListener('keydown', (e) => {
        // Only activate shortcuts when not typing in inputs
        if (e.target.tagName === 'INPUT' || e.target.tagName === 'TEXTAREA') return;
        
        // Don't activate if modal is open
        if (!summaryModal.classList.contains('hidden')) {
            if (e.key === 'Escape') {
                summaryModal.style.display = 'none';
                summaryModal.classList.add('hidden');
            }
            return;
        }
        
        // Arrow keys for navigation
        if (e.key === 'ArrowLeft' && !prevBtn.disabled) {
            goToPreviousQuestion();
        } else if (e.key === 'ArrowRight' && !nextBtn.disabled) {
            goToNextQuestion();
        }
    });

    // Initial load
    loadAttemptDetails();
    
    // Global function to close any modal (for external calls if needed)
    window.closeModal = function(id) {
        const modal = document.getElementById(id);
        if (modal) {
            modal.style.display = 'none';
            modal.classList.add('hidden');
        }
    };
});