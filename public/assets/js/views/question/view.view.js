import { ROOT } from "../../core/config.js";

// --- JAVASCRIPT LOGIC ---



let questionData = {};
let answerList = [];
let hasMoreAnswers = true;
let totalAnswerCount = 0;
let answersDisplayed = 0;

// --- EXPOSED GLOBAL FUNCTIONS (window.function_name = ...) ---

/**
 * Helper to show error messages as a pop-up. Exposed globally.
 */
window.showPopupError = function (message) {
    const popup = document.getElementById('error-popup');
    popup.textContent = `Error: ${message}`;
    popup.style.display = 'block';
    setTimeout(() => {
        popup.style.display = 'none';
    }, 3000);
}

/**
 * Opens a modal. Exposed globally.
 */
window.openModal = function (modalId) {
    document.getElementById(modalId).style.display = 'block';
}

/**
 * Closes a modal. Exposed globally.
 */
window.closeModal = function (modalId) {
    document.getElementById(modalId).style.display = 'none';
}

/**
 * Opens the Edit Question modal and pre-fills content. Exposed globally.
 */
window.openEditQuestionModal = function () {
    document.getElementById('edit-question-title').value = questionData.title;
    document.getElementById('edit-question-description').value = questionData.description;
    document.getElementById('edit-question-tags').value = questionData.tags.join(', ');
    window.openModal('edit-question-modal');
}

/**
 * Opens the Edit Answer modal and pre-fills content. Exposed globally.
 */
window.openEditAnswerModal = function (answerId) {
    const answer = answerList.find(a => a.id === answerId);
    if (answer) {
        document.getElementById('edit-answer-id').value = answerId;
        document.getElementById('edit-answer-content').value = answer.content;
        window.openModal('edit-answer-modal');
    }
}


// --- MOCK API RESPONSES ---

// Mock data removed.

// --- API HELPER ---

async function apiCall(endpoint, data) {
    try {
        const response = await fetch(`${ROOT}/${endpoint}`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(data)
        });

        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }

        const result = await response.json();

        if (result.status === 'error') {
            throw new Error(result.message || 'API Error');
        }

        return result;
    } catch (error) {
        console.error("API Call Error:", error);
        throw error;
    }
}

async function fetchMoreAnswers(offset) {
    return apiCall('question/api_load_answers', {
        question_id: questionData.id,
        offset: offset,
        limit: 10
    });
}

// Map frontend actions to backend endpoints
async function handleAction(action, data) {
    let endpoint = '';
    let payload = { ...data };

    switch (action) {
        case 'vote_question':
            endpoint = 'question/api_vote_question';
            payload = { q_id: data.question_id, votetype: data.vote_type === 'up' ? 'upvote' : 'downvote' };
            break;
        case 'vote_answer':
            endpoint = 'question/api_vote_answer';
            payload = { q_id: data.answer_id, votetype: data.vote_type === 'up' ? 'upvote' : 'downvote' };
            break;
        case 'create_answer':
            endpoint = 'question/api_create_answer';
            payload = { q_id: data.question_id, content: data.content };
            break;
        case 'edit_question':
            endpoint = 'question/api_edit_question';
            payload = { id: data.question_id, title: data.title, content: data.description, tags: data.tags };
            break;
        case 'edit_answer':
            endpoint = 'question/api_edit_answer';
            payload = { id: data.answer_id, content: data.content };
            break;
        case 'delete_question':
            endpoint = 'question/api_delete_question';
            payload = { id: data.question_id };
            break;
        case 'delete_answer':
            endpoint = 'question/api_delete_answer';
            payload = { id: data.answer_id };
            break;
        case 'accept_answer':
            endpoint = 'question/api_accept_answer';
            payload = { question_id: data.question_id, answer_id: data.answer_id };
            break;
    }

    return apiCall(endpoint, payload);
}

// --- RENDERING FUNCTIONS (Kept internal/local) ---

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
    // Uses the global functions defined below
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
        // Uses global functions defined below
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
        <quill-editor 
            id="question-description"
            readonly
            height="fit-content"
        ></quill-editor>
    `;

    // Set content programmatically to avoid attribute escaping issues
    const descriptionEditor = document.getElementById('question-description');
    if (descriptionEditor) {
        descriptionEditor.value = q.description;
    }
}

function renderAnswerCard(answer) {
    const isAuthor = answer.author_id === CURRENT_USER_ID;
    const isQuestionAuthor = questionData.author_id === CURRENT_USER_ID;
    const isAccepted = answer.is_accepted;

    const headerHtml = renderHeader(answer);
    const voteControlsHtml = renderVoteControls(answer, 'answer');

    let actionBtns = '';
    // Uses global functions defined below
    if (isAuthor) {
        actionBtns += `<button class="btn-none" onclick="openEditAnswerModal(${answer.id})">Edit</button>`;
        actionBtns += `<button class="btn-red" onclick="handleDeleteAnswer(${answer.id})" style="">Delete</button>`;
    }

    // Uses global function defined below
    if (isQuestionAuthor) {
        const actionText = isAccepted ? 'Un-accept' : 'Accept Answer';
        actionBtns += `<button onclick="handleAcceptAnswer(${answer.id})" class="btn-primary-small">${actionText}</button>`;
    }


    return `
        <div class="answer-card ${isAccepted ? 'accepted' : ''}" id="answer-card-${answer.id}">
            ${isAccepted ? '<div class="accepted-badge">✅ Accepted</div>' : ''}
            ${headerHtml}
                <quill-editor 
                    id="answer-content-${answer.id}"
                    readonly
                    height="fit-content"
                    style="margin-top: ${isAccepted ? '10px' : '0'};"
                ></quill-editor>
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

    // Set content for all answer editors
    answerList.forEach(answer => {
        const el = document.getElementById(`answer-content-${answer.id}`);
        if (el) {
            el.value = answer.content;
        }
    });
}

// --- ACTION HANDLERS (EXPOSED GLOBALLY) ---

window.handleVoteQuestion = async function (id, type, currentCount) {
    try {
        // const response = await mockApiCall('question/api/vote', { question_id: id, vote_type: type, current_count: currentCount });
        const response = await handleAction('vote_question', { question_id: id, vote_type: type });
        if (response.status === 'success') {
            // Reload page or update UI?
            // Real API doesn't return new count usually unless tailored? 
            // My api_vote_question returns {status:'success'} only.
            // Client side update:
            // const isUp = type === 'up';
            // Simple UI update logic (might drift from server but ok for now)
            // Ideally server returns new state.
            // For now, let's just toggle locally or reload.
            // But wait, my api_vote_question logic toggles.
            // If I vote up and I had down, it invalidates down and adds up (+2).
            // If I had nothing, +1.
            // If I had up, -1 (remove).
            // Using a reload is safest until we have a smart response.
            location.reload();
        }
    } catch (error) {
        window.showPopupError(error.message || "Failed to vote on question.");
    }
}

window.handleVoteAnswer = async function (id, type, currentCount) {
    try {
        const response = await handleAction('vote_answer', { answer_id: id, vote_type: type });
        if (response.status === 'success') {
            location.reload(); // Reload for accurate count
        }
    } catch (error) {
        window.showPopupError(error.message || "Failed to vote on answer.");
    }
}

window.handleAcceptAnswer = async function (answerId) {
    const answerToToggle = answerList.find(a => a.id === answerId);
    const shouldAccept = !answerToToggle.is_accepted;

    if (!confirm(`Are you sure you want to ${shouldAccept ? 'mark this' : 'unmark the'} answer?`)) return;

    try {
        const response = await handleAction('accept_answer', {
            question_id: questionData.id,
            answer_id: shouldAccept ? answerId : null
        });

        if (response.status === 'success') {
            location.reload();
        }
    } catch (error) {
        window.showPopupError(error.message || "Failed to toggle accepted status.");
    }
}

window.handleCreateAnswer = async function (event) {
    event.preventDefault();
    let content = document.getElementById('new-answer-content').value;

    try {
        const response = await handleAction('create_answer', { question_id: questionData.id, content: content });

        if (response.status === 'success') {
            location.reload(); // API returns answer_id but reloading is easier to refresh list
        }
    } catch (error) {
        window.showPopupError(error.message || "Failed to post answer.");
    }
}

window.handleEditQuestion = async function (event) {
    event.preventDefault();
    const title = document.getElementById('edit-question-title').value;
    const description = document.getElementById('edit-question-description').value;
    const tags = document.getElementById('edit-question-tags').value;

    try {
        const response = await handleAction('edit_question', { question_id: questionData.id, title, description, tags });

        if (response.status === 'success') {
            location.reload();
        }
    } catch (error) {
        window.showPopupError(error.message || "Failed to edit question.");
    }
}

window.handleEditAnswer = async function (event) {
    event.preventDefault();
    const answerId = parseInt(document.getElementById('edit-answer-id').value);
    const content = document.getElementById('edit-answer-content').value;

    try {
        const response = await handleAction('edit_answer', { answer_id: answerId, content });

        if (response.status === 'success') {
            location.reload();
        }
    } catch (error) {
        window.showPopupError(error.message || "Failed to edit answer.");
    }
}

window.handleDeleteQuestion = function (id) {
    if (!confirm("WARNING: Are you sure you want to delete this entire question?")) return;

    handleAction('delete_question', { question_id: id })
        .then(response => {
            if (response.status === 'success') {
                window.location.href = `${ROOT}/question`;
            }
        })
        .catch(error => {
            window.showPopupError(error.message || "Failed to delete question.");
        });
}

window.handleDeleteAnswer = async function (id) {
    if (!confirm("Are you sure you want to delete this answer?")) return;

    try {
        const response = await handleAction('delete_answer', { answer_id: id });
        if (response.status === 'success') {
            location.reload();
        }
    } catch (error) {
        window.showPopupError(error.message || "Failed to delete answer.");
    }
}

window.loadMoreAnswers = async function () {
    // const offset = answerList.length;
    // Real offset should be calculated or just simple pagination
    // Since we reload on everything, answerList might change.
    // For pagination, we might need to keep track of loaded answers.
    // api_load_answers is stateless? No, it uses offset.
    // Since we are appending, we use current list length.

    const offset = answerList.length;
    try {
        const response = await fetchMoreAnswers(offset);
        if (response.status === 'success') {
            answerList.push(...response.answers);
            hasMoreAnswers = response.has_more;
            renderAnswersList();
        }
    } catch (error) {
        window.showPopupError(error.message || "Failed to load more answers.");
    }
}

// --- SCROLL INTERACTION LOGIC (EXPOSED GLOBALLY FOR EVENT LISTENER) ---

// const TRANSITION_DURATION = 300; 
// const SCROLL_BUFFER_PX = 50; 

// window.handleScroll = function() {
//     const questionPanel = document.getElementById('question-panel');
//     const body = document.body;
//     const filler = document.getElementById('void-filler');

//     if (!questionPanel || !body || !filler) return;

//     const questionHeight = questionPanel.scrollHeight; 
//     const viewportHeight = window.innerHeight;

//     const baseTriggerPosition = questionHeight - (viewportHeight * 0.2); 

//     const ACTIVATION_THRESHOLD = baseTriggerPosition;
//     const DEACTIVATION_THRESHOLD = baseTriggerPosition - SCROLL_BUFFER_PX;

//     const isSplitActive = body.classList.contains('split-active');

//     if (window.scrollY > ACTIVATION_THRESHOLD) {
//         // --- TRANSITION IN (To Fixed State) ---
//         if (!isSplitActive) {

//             const rect = questionPanel.getBoundingClientRect();

//             questionPanel.style.transition = 'none';
//             filler.style.transition = 'none';

//             questionPanel.style.position = 'fixed';
//             questionPanel.style.top = '0';
//             questionPanel.style.left = '0';
//             questionPanel.style.right = 'unset';
//             questionPanel.style.transform = `translateY(${rect.top}px) translateX(${rect.left}px)`;

//             filler.style.display = 'block';
//             filler.style.height = `${questionHeight}px`;

//             void questionPanel.offsetWidth; 

//             questionPanel.style.transition = 'transform 0.3s ease-in-out, box-shadow 0.3s, border-left 0.3s';
//             filler.style.transition = 'height 0.3s ease-in-out';

//             body.classList.add('split-active');

//             questionPanel.style.transform = 'translateY(0) translateX(50vw)';

//             setTimeout(() => {
//                 filler.style.height = '0';
//             }, 10);
//         }

//     } else if (window.scrollY < DEACTIVATION_THRESHOLD) { 
//         // --- TRANSITION OUT (To Flow State) ---
//         if (isSplitActive) {

//             questionPanel.style.transition = 'none';
//             filler.style.transition = 'none';

//             questionPanel.style.transform = 'translateY(0) translateX(50vw)';

//             filler.style.height = `${questionHeight}px`; 

//             body.classList.remove('split-active'); 

//             void questionPanel.offsetWidth; 

//             questionPanel.style.transition = 'transform 0.3s ease-in-out';
//             filler.style.transition = 'height 0.3s ease-in-out';

//             questionPanel.style.transform = 'none';

//             setTimeout(() => {
//                 questionPanel.style.position = '';
//                 questionPanel.style.top = '';
//                 questionPanel.style.left = '';
//                 questionPanel.style.right = '';
//                 questionPanel.style.transform = '';
//                 questionPanel.style.transition = ''; 

//                 filler.style.display = 'none';
//                 filler.style.height = '0';
//                 filler.style.transition = '';
//             }, TRANSITION_DURATION);
//         }
//     }
// }


// // --- INITIALIZATION ---

function initPage() {
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

    //     // Initialize scroll listener using the global function
    //     window.addEventListener('scroll', window.handleScroll);
    //     // Run once on load
    //     window.handleScroll();
}

// Close modals when clicking outside using an anonymous global function
window.onclick = function (event) {
    if (event.target.classList.contains('modal')) {
        window.closeModal(event.target.id);
    }
}

// Start the application
initPage();