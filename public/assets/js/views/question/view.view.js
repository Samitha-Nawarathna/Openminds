// --- JAVASCRIPT LOGIC ---

const CURRENT_USER_ID = 1; 

let questionData = {};
let answerList = [];
let hasMoreAnswers = true;
let totalAnswerCount = 0; // Will be set by INITIAL_DATA
let answersDisplayed = 0;

/**
 * Helper to show error messages as a pop-up.
 */
function showPopupError(message) {
    const popup = document.getElementById('error-popup');
    popup.textContent = `Error: ${message}`;
    popup.style.display = 'block';
    setTimeout(() => {
        popup.style.display = 'none';
    }, 3000);
}

// --- MOCK API RESPONSES (Used for demonstration of dynamic actions) ---
// Note: Initial load data is now provided by PHP, but we need the mock functions
// for dynamic operations (voting, posting, loading more).

const mockInitialAnswers = [
    { 
        id: 102,
        content: "Since an unbalanced BST effectively becomes a linked list in the worst case (e.g., sequentially inserted data), the Big O notation for search, insertion, and deletion becomes O(n).",
        author_id: 2,
        author_name: "You",
        author_role: "student",
        time_posted: "2025-10-31 10:15",
        vote_count: 10,
        user_voted: false,
        is_accepted: true 
    },
    { 
        id: 103,
        content: "Correct. To mitigate this risk, modern systems often rely on self-balancing trees like AVL or Red-Black trees, which guarantee O(log n) worst-case performance by performing rotations.",
        author_id: 3,
        author_name: "Dr. Smith",
        author_role: "admin",
        time_posted: "2025-10-31 10:30",
        vote_count: 25,
        user_voted: true,
        user_vote_type: 'up',
        is_accepted: false
    },
    { 
        id: 104,
        content: "It's important to remember that 'unbalanced' means skewed, but the average case for a randomly built BST remains O(log n). The O(n) is strictly the worst-case scenario.",
        author_id: 4,
        author_name: "Bob Expert",
        author_role: "expert",
        time_posted: "2025-10-31 10:45",
        vote_count: 5,
        user_voted: false,
        is_accepted: false
    },
    ...Array(9).fill(null).map((_, i) => ({
        id: 105 + i,
        content: `A student answer number ${i + 1}. The worst-case for an unbalanced BST is O(n), which is terrible for performance.`,
        author_id: 10 + i,
        author_name: `Student ${i + 1}`,
        author_role: i % 3 === 0 ? "mentor" : "student", 
        time_posted: "2025-10-31 11:00",
        vote_count: 1,
        user_voted: false,
        is_accepted: false
    }))
];


function mockApiLoadMore(offset) {
    return new Promise(resolve => {
        setTimeout(() => {
            const newAnswers = mockInitialAnswers.slice(offset, offset + 10);
            resolve({
                status: "success",
                has_more: (offset + newAnswers.length) < totalAnswerCount,
                answers: newAnswers
            });
        }, 500);
    });
}

function mockApiCall(endpoint, data) {
    return new Promise((resolve, reject) => {
        setTimeout(() => {
            if (Math.random() < 0.05) { 
                reject({ status: "error", message: "Network connection lost. Please try again." });
                return;
            }
            
            let response = { status: "success" };
            switch (endpoint) {
                case 'question/api/vote':
                case 'question/api/vote_answer':
                    const isUp = data.vote_type === 'up';
                    response = {
                        ...response,
                        new_vote_count: data.current_count + (isUp ? 1 : -1),
                        user_voted: true,
                        user_vote_type: data.vote_type
                    };
                    if (data.answer_id) response.answer_id = data.answer_id;
                    break;
                case 'question/api/answer':
                    response.answer = {
                        id: Date.now(),
                        content: data.content,
                        author_id: CURRENT_USER_ID,
                        author_name: "You",
                        author_role: "student",
                        time_posted: new Date().toISOString().substring(0, 16).replace('T', ' '),
                        vote_count: 0,
                        user_voted: false,
                        is_accepted: false
                    };
                    totalAnswerCount++;
                    break;
                case 'question/api/edit':
                    response.question = {
                        ...questionData,
                        title: data.title,
                        description: data.description,
                        tags: data.tags.split(',').map(t => t.trim()),
                    };
                    break;
                case 'question/api/edit_answer':
                    const originalAnswer = answerList.find(a => a.id === data.answer_id);
                    response.answer = {
                        ...originalAnswer,
                        content: data.content
                    };
                    break;
                case 'question/api/delete_answer':
                    response.answer_id = data.answer_id;
                    break;
                case 'question/api/accept_answer':
                    response.accepted_answer_id = data.answer_id;
                    break;
            }
            resolve(response);

        }, 500); 
    });
}

// --- RENDERING FUNCTIONS (Omitted for brevity, assumed to be correct) ---

function renderHeader(item) {
    const roleClass = `role-${item.author_role.toLowerCase()}`;
    return `
        <div class="header-info">
            <div class="author-info">
                <span>${item.author_name}</span>
                <span class="author-role ${roleClass}">${item.author_role.toUpperCase()}</span>
            </div>
            <div class="time-posted">${item.time_posted}</div>
        </div>
    `;
}

function renderVoteControls(item, type) {
    const upVotedClass = item.user_voted && item.user_vote_type === 'up' ? 'voted-up' : '';
    const downVotedClass = item.user_voted && item.user_vote_type === 'down' ? 'voted-down' : '';
    const id = item.id;
    const fn = type === 'question' ? 'handleVoteQuestion' : 'handleVoteAnswer';

    return `
        <div class="vote-state">
            <button class="vote-btn btn-none ${upVotedClass}" onclick="${fn}(${id}, 'up', ${item.vote_count})">▲</button>
            <span id="${type}-vote-count-${id}" class="vote-count-display">${item.vote_count}</span>
            <button class="vote-btn btn-none ${downVotedClass}" onclick="${fn}(${id}, 'down', ${item.vote_count})">▼</button>
        </div>
    `;
}

function renderQuestionPanel() {
    const q = questionData;
    const container = document.getElementById('question-content-container');
    const isAuthor = q.author_id === CURRENT_USER_ID;

    const headerHtml = renderHeader(q);
    
    const tagsHtml = q.tags.map(tag => `<span class="tag-pill">${tag}</span>`).join('');

    const actionControls = document.getElementById('question-action-controls');
    if (isAuthor) {
        actionControls.innerHTML = `
            <button onclick="openEditQuestionModal()" class="btn-none" style="width:auto;">Edit</button>
            <button onclick="handleDeleteQuestion(${q.id})" class="btn-red">Delete</button>
        `;
    } else {
        actionControls.innerHTML = renderVoteControls(q, 'question');
    }

    container.innerHTML = `
        ${headerHtml}
        <h1 id="question-title">${q.title}</h1>
        <div class="tags-container">${tagsHtml}</div>
        <p id="question-description">${q.description}</p>
    `;
}

function renderAnswerCard(answer) {
    const isAuthor = answer.author_id === CURRENT_USER_ID;
    const isQuestionAuthor = questionData.author_id === CURRENT_USER_ID;
    const isAccepted = answer.is_accepted;

    const headerHtml = renderHeader(answer);
    const voteControlsHtml = renderVoteControls(answer, 'answer');

    let actionBtns = '';
    if (isAuthor) {
        actionBtns += `<button class="btn-none" onclick="openEditAnswerModal(${answer.id})">Edit</button>`;
        actionBtns += `<button class="btn-red" onclick="handleDeleteAnswer(${answer.id})" style="">Delete</button>`;
    }
    
    if (isQuestionAuthor && !isAccepted) {
        actionBtns += `<button onclick="handleAcceptAnswer(${answer.id})" class="btn-primary-small">Accept Answer</button>`;
    } else if (isQuestionAuthor && isAccepted) {
         actionBtns += `<button onclick="handleAcceptAnswer(${answer.id})" class="btn-primary-small">Un-accept</button>`;
    }


    return `
        <div class="answer-card ${isAccepted ? 'accepted' : ''}" id="answer-card-${answer.id}">
            ${isAccepted ? '<div class="accepted-badge">✅ Accepted</div>' : ''}
            ${headerHtml}
            <p id="answer-content-${answer.id}" style="margin-top: ${isAccepted ? '10px' : '0'};">${answer.content}</p>
            <div class="answer-footer">
                <div class="action-btns">${actionBtns}</div>
                <div class="vote-controls">${voteControlsHtml}</div>
            </div>
        </div>
    `;
}

function renderAnswersList() {
    const listContainer = document.getElementById('answers-list');
    const countHeader = document.getElementById('answer-count-header');
    const loadMoreBtn = document.getElementById('load-more-btn');

    countHeader.textContent = `${totalAnswerCount} Answers`;
    listContainer.innerHTML = answerList.map(renderAnswerCard).join('');
    
    loadMoreBtn.textContent = `Load More Answers (${answerList.length}/${totalAnswerCount})`;
    loadMoreBtn.style.display = hasMoreAnswers ? 'block' : 'none';
    
    answersDisplayed = answerList.length;
}

// --- ACTION HANDLERS (Unchanged logic) ---
async function handleVoteQuestion(id, type, currentCount) {
    try {
        const response = await mockApiCall('question/api/vote', { question_id: id, vote_type: type, current_count: currentCount });
        if (response.status === 'success') {
            questionData.vote_count = response.new_vote_count;
            questionData.user_voted = response.user_voted;
            questionData.user_vote_type = response.user_vote_type;
            renderQuestionPanel();
        }
    } catch (error) {
        showPopupError(error.message || "Failed to vote on question.");
    }
}

async function handleVoteAnswer(id, type, currentCount) {
    try {
        const response = await mockApiCall('question/api/vote_answer', { answer_id: id, vote_type: type, current_count: currentCount });
        if (response.status === 'success') {
            const answer = answerList.find(a => a.id === id);
            if (answer) {
                answer.vote_count = response.new_vote_count;
                answer.user_voted = response.user_voted;
                answer.user_vote_type = response.user_vote_type;
                renderAnswersList();
            }}
      } catch (error) {
        showPopupError(error.message || "Failed to vote on answer.");
    }
}


async function handleAcceptAnswer(answerId) {
    const answerToToggle = answerList.find(a => a.id === answerId);
    const shouldAccept = !answerToToggle.is_accepted;
    
    if (!confirm(`Are you sure you want to ${shouldAccept ? 'mark this' : 'unmark the'} answer?`)) return;

    try {
        const response = await mockApiCall('question/api/accept_answer', { 
            question_id: questionData.id, 
            answer_id: shouldAccept ? answerId : null
        });

        if (response.status === 'success') {
            answerList.forEach(a => a.is_accepted = false);
            
            if (shouldAccept) {
                const newAccepted = answerList.find(a => a.id === response.accepted_answer_id);
                if (newAccepted) newAccepted.is_accepted = true;
            }
            
            renderAnswersList();
        }
    } catch (error) {
        showPopupError(error.message || "Failed to toggle accepted status.");
    }
}

async function handleCreateAnswer(event) {
    event.preventDefault();
    const content = document.getElementById('new-answer-content').value;

    try {
        const response = await mockApiCall('question/api/answer', { question_id: questionData.id, content: content });

        if (response.status === 'success' && response.answer) {
            answerList.unshift(response.answer); 
            renderAnswersList();
            closeModal('create-answer-modal');
            document.getElementById('create-answer-form').reset();
        }
    } catch (error) {
        showPopupError(error.message || "Failed to post answer.");
    }
}

async function handleEditQuestion(event) {
    event.preventDefault();
    const title = document.getElementById('edit-question-title').value;
    const description = document.getElementById('edit-question-description').value;
    const tags = document.getElementById('edit-question-tags').value;

    try {
        const response = await mockApiCall('question/api/edit', { question_id: questionData.id, title, description, tags });

        if (response.status === 'success' && response.question) {
            questionData = response.question;
            renderQuestionPanel();
            closeModal('edit-question-modal');
        }
    } catch (error) {
        showPopupError(error.message || "Failed to edit question.");
    }
}

async function handleEditAnswer(event) {
    event.preventDefault();
    const answerId = parseInt(document.getElementById('edit-answer-id').value);
    const content = document.getElementById('edit-answer-content').value;

    try {
        const response = await mockApiCall('question/api/edit_answer', { answer_id: answerId, content });

        if (response.status === 'success' && response.answer) {
            const index = answerList.findIndex(a => a.id === answerId);
            if (index !== -1) {
                answerList[index] = response.answer;
                renderAnswersList();
                closeModal('edit-answer-modal');
            }
        }
    } catch (error) {
        showPopupError(error.message || "Failed to edit answer.");
    }
}

function handleDeleteQuestion(id) {
    if (!confirm("WARNING: Are you sure you want to delete this entire question?")) return;
    
    mockApiCall('question/api/delete', { question_id: id })
        .then(response => {
            if (response.status === 'success') {
                showPopupError("Question deleted successfully. (Mock action: No redirect)");
            }
        })
        .catch(error => {
            showPopupError(error.message || "Failed to delete question.");
        });
}

async function handleDeleteAnswer(id) {
    if (!confirm("Are you sure you want to delete this answer?")) return;
    
    try {
        const response = await mockApiCall('question/api/delete_answer', { answer_id: id });
        if (response.status === 'success') {
            answerList = answerList.filter(a => a.id !== id);
            totalAnswerCount--;
            renderAnswersList();
        }
    } catch (error) {
        showPopupError(error.message || "Failed to delete answer.");
    }
}

async function loadMoreAnswers() {
    const offset = answerList.length;
    try {
        const response = await mockApiLoadMore(offset);
        if (response.status === 'success') {
            answerList.push(...response.answers);
            hasMoreAnswers = response.has_more;
            renderAnswersList();
        }
    } catch (error) {
        showPopupError(error.message || "Failed to load more answers.");
    }
}

// --- MODAL FUNCTIONS (Unchanged logic) ---

function openModal(modalId) {
    document.getElementById(modalId).style.display = 'block';
}

function closeModal(modalId) {
    document.getElementById(modalId).style.display = 'none';
}

function openEditQuestionModal() {
    document.getElementById('edit-question-title').value = questionData.title;
    document.getElementById('edit-question-description').value = questionData.description;
    document.getElementById('edit-question-tags').value = questionData.tags.join(', ');
    openModal('edit-question-modal');
}

function openEditAnswerModal(answerId) {
    const answer = answerList.find(a => a.id === answerId);
    if (answer) {
        document.getElementById('edit-answer-id').value = answerId;
        document.getElementById('edit-answer-content').value = answer.content;
        openModal('edit-answer-modal');
    }
}


// --- SCROLL INTERACTION LOGIC (FIXED: Smooth Transition with Hysteresis) ---

const TRANSITION_DURATION = 300; // Match CSS transition duration (0.3s)
const SCROLL_BUFFER_PX = 50; // Hysteresis buffer in pixels to prevent glitching

function handleScroll() {
    const questionPanel = document.getElementById('question-panel');
    const body = document.body;
    const filler = document.getElementById('void-filler');

    if (!questionPanel || !body || !filler) return;

    // Use scrollHeight for a more reliable measure of the full content height
    const questionHeight = questionPanel.scrollHeight; 
    const viewportHeight = window.innerHeight;

    // Calculate the base trigger position (20vh from bottom of question content)
    const baseTriggerPosition = questionHeight - (viewportHeight * 0.2); 
    
    // THRESHOLDS for stability (Hysteresis)
    const ACTIVATION_THRESHOLD = baseTriggerPosition;
    const DEACTIVATION_THRESHOLD = baseTriggerPosition - SCROLL_BUFFER_PX;

    const isSplitActive = body.classList.contains('split-active');
    
    if (window.scrollY > ACTIVATION_THRESHOLD) {
        // --- TRANSITION IN (To Fixed State) ---
        if (!isSplitActive) {
            
            // 1. CAPTURE & INVERT (Lock Position Visually - FLIP technique)
            const rect = questionPanel.getBoundingClientRect();

            // Temporarily disable transitions
            questionPanel.style.transition = 'none';
            filler.style.transition = 'none';

            // Lock Question Panel visually in place (Position Matching)
            questionPanel.style.position = 'fixed';
            questionPanel.style.top = '0';
            questionPanel.style.left = '0';
            questionPanel.style.right = 'unset';
            // Calculate the translation to keep it exactly where it is now
            questionPanel.style.transform = `translateY(${rect.top}px) translateX(${rect.left}px)`;

            // Set Filler height to fill the void
            filler.style.display = 'block';
            filler.style.height = `${questionHeight}px`; // Match the panel's height

            // Force browser repaint to apply position/transform immediately
            void questionPanel.offsetWidth; 
            
            // 2. PLAY (Animate to Final State)
            
            // Re-enable transitions
            questionPanel.style.transition = 'transform 0.3s ease-in-out, box-shadow 0.3s, border-left 0.3s';
            filler.style.transition = 'height 0.3s ease-in-out';
            
            // Add active class (for final styles: right: 0, left: unset, border-left)
            body.classList.add('split-active');
            
            // Animate the transform (Q-panel moves to top-right corner)
            questionPanel.style.transform = 'translateY(0) translateX(50vw)';

            // Animate the filler's height down to 0 (Answers thread moves up smoothly)
            setTimeout(() => {
                filler.style.height = '0';
            }, 10);
        }
        
    } else if (window.scrollY < DEACTIVATION_THRESHOLD) { 
        // --- TRANSITION OUT (To Flow State) - Only trigger if scrolled significantly back ---
        if (isSplitActive) {

            // 1. CAPTURE & INVERT (Prepare to revert)
            
            // Temporarily disable transitions
            questionPanel.style.transition = 'none';
            filler.style.transition = 'none';
            
            // Set the current fixed state as the START point of the reverse animation
            questionPanel.style.transform = 'translateY(0) translateX(50vw)';
            
            // Set the filler to its target height (the Q-panel's original height) 
            filler.style.height = `${questionHeight}px`; 

            // Remove active class (to use the flow CSS position)
            body.classList.remove('split-active'); 
            
            // Force browser repaint
            void questionPanel.offsetWidth; 

            // 2. PLAY (Animate back to initial state)

            // Re-enable transitions
            questionPanel.style.transition = 'transform 0.3s ease-in-out';
            filler.style.transition = 'height 0.3s ease-in-out';
            
            // Animate Q-panel back to its flow position (transform: none)
            questionPanel.style.transform = 'none';
            
            // 3. Cleanup: After transition, remove inline styles and filler
            setTimeout(() => {
                // Remove inline styles to return control to the CSS flow/rules
                questionPanel.style.position = '';
                questionPanel.style.top = '';
                questionPanel.style.left = '';
                questionPanel.style.right = '';
                questionPanel.style.transform = '';
                questionPanel.style.transition = ''; // Restore initial CSS transition property
                
                filler.style.display = 'none';
                filler.style.height = '0';
                filler.style.transition = '';
            }, TRANSITION_DURATION);
        }
    }
}


// --- INITIALIZATION ---

async function initPage() {
    // Use the global data object injected by PHP
    if (typeof INITIAL_DATA === 'undefined' || !INITIAL_DATA) {
        console.error("Initial data not found. Cannot initialize page.");
        return;
    }
    
    questionData = INITIAL_DATA.question;
    answerList = INITIAL_DATA.answers.list;
    hasMoreAnswers = INITIAL_DATA.answers.has_more;
    totalAnswerCount = INITIAL_DATA.totalAnswerCount;

    renderQuestionPanel();
    renderAnswersList();
    
    // Initialize scroll listener after content is rendered
    window.addEventListener('scroll', handleScroll);
    // Run once on load in case the content is already past the trigger point
    handleScroll();
}

window.handleEditAnswer = handleEditAnswer;
window.handleEditQuestion = handleEditQuestion;
window.handleCreateAnswer = handleCreateAnswer;
window.closeModal = closeModal;
window.openModal = openModal;
window.openEditQuestionModal = openEditQuestionModal;
window.openEditAnswerModal = openEditAnswerModal;
window.handleAcceptAnswer = handleAcceptAnswer;
window.handleVoteAnswer = handleVoteAnswer;
window.handleAcceptAnswer = handleAcceptAnswer;
window.handleDeleteAnswer = handleDeleteAnswer;
window.handleDeleteQuestion = handleDeleteQuestion;
window.loadMoreAnswers = loadMoreAnswers;

// Close modals when clicking outside
window.onclick = function(event) {
    if (event.target.classList.contains('modal')) {
        event.target.style.display = "none";
    }
}

initPage();