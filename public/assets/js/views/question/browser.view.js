import { ROOT } from "../../core/config.js";

// --- Configuration ---
const QUESTIONS_PER_LOAD = 10; // Number of questions to load per request
let currentTab = initial_tab;
let currentOffset = 0;
let isFilterActive = false;

// --- Mock AJAX function to replace later ---
/**
 * Simulates an AJAX call to the backend to fetch questions.
 * @param {string} tabType - 'all', 'your', or 'answered'.
 * @param {number} offset - The starting index for fetching.
 * @param {string} tagFilter - The tag to filter by.
 * @returns {Promise<Object>} Mock response data.
 */
/**
 * Fetches questions from the backend API.
 */
async function fetchQuestions(tabType, offset, tagFilter) {
    // console.log(`[AJAX] Fetching ${tabType} questions from offset ${offset} with tag: "${tagFilter}"`);

    const response = await fetch(`${ROOT}/question/api_filter`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            tab: tabType,
            offset: offset,
            limit: QUESTIONS_PER_LOAD,
            tag: tagFilter
        })
    });

    if (!response.ok) {
        throw new Error(`HTTP error! status: ${response.status}`);
    }

    const data = await response.json();

    if (data.status === 'error') {
        throw new Error(data.message || 'API Error');
    }

    return data;
}

// --- RENDERING FUNCTIONS ---

/**
 * Generates a consistent color class for a tag based on its name.
 */
function getTagColorClass(tagName) {
    let hash = 0;
    for (let i = 0; i < tagName.length; i++) {
        hash = tagName.charCodeAt(i) + ((hash << 5) - hash);
    }
    const index = Math.abs(hash % 8); // 8 colors defined in CSS
    return `tag-color-${index}`;
}

/**
 * Creates the HTML structure for a single question item.
 */
function createQuestionItem(question) {
    const item = document.createElement('div');
    item.classList.add('question-item');

    // Prevent navigation when clicking buttons
    item.onclick = (e) => {
        if (e.target.closest('button') || e.target.closest('a')) return;
        window.location.href = `${ROOT}/question/show?id=${question.id}`;
    };

    const isSolvedClass = question.is_solved ? 'solved' : '';
    const voteClass = question.user_vote_type ? `voted-${question.user_vote_type}` : '';

    // Generate Tags HTML
    let tagsHtml = '';
    if (question.tags && question.tags.length > 0) {
        tagsHtml = '<div class="qi-tags">';
        question.tags.forEach(tag => {
            const colorClass = getTagColorClass(tag);
            tagsHtml += `<span class="tag-pill ${colorClass}">${tag}</span>`;
        });
        tagsHtml += '</div>';
    }

    item.innerHTML = `
        <!-- Left: Vote Section -->
        <div class="qi-vote-section ${voteClass}">
            <button class="vote-btn up" data-id="${question.id}" title="Upvote">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="18 15 12 9 6 15"></polyline></svg>
            </button>
            <span class="vote-count">${question.vote_count}</span>
            <span class="vote-label">votes</span>
            <button class="vote-btn down" data-id="${question.id}" title="Downvote">
                 <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"></polyline></svg>
            </button>
        </div>

        <!-- Center: Content Section -->
        <div class="qi-content-section">
            <h3 class="qi-title">
                <a href="${ROOT}/question/show?id=${question.id}">${question.title}</a>
            </h3>
            ${tagsHtml}
            <div class="qi-meta">
                <span class="qi-author">Posted by <span class="author-name">${question.creator || 'Unknown'}</span></span>
                <!-- <span class="qi-time">${question.time_posted || ''}</span> --> 
            </div>
        </div>
        
        <!-- Right: Answer Section -->
        <div class="qi-answer-section ${isSolvedClass}">
             <span class="answer-count-val">${question.answer_count}</span>
             <span class="answer-label">answers</span>
        </div>
    `;

    return item;
}

/**
 * Clears the list and updates the global state before fetching new data.
 */
async function loadQuestions(tabType, offset) {
    currentTab = tabType;
    currentOffset = 0;

    // Update active tab styling
    document.querySelectorAll('.tab-button').forEach(btn => {
        btn.classList.remove('active');
    });
    const activeTab = document.querySelector(`.tab-button[data-tab="${tabType}"]`);
    if (activeTab) activeTab.classList.add('active');

    // Show a loading state
    const listContainer = document.getElementById('questions-list');
    listContainer.innerHTML = '<div class="loading">Loading questions...</div>';

    // Clear filter for a new tab load (optional, maybe we want to keep it?)
    // Keeping filter clearing for now to match original behavior
    if (!isFilterActive) {
        // document.getElementById('tag-filter-input').value = ''; 
    }
    // Logic update: If we switched tabs, we probably want to keep the search term or clear it? 
    // Original cleared it. Let's respect original flow but check input.

    const tagFilter = document.getElementById('tag-filter-input').value;

    // Fetch data
    try {
        const response = await fetchQuestions(tabType, offset, tagFilter);
        listContainer.innerHTML = ''; // Clear loading state

        if (response.questions.length === 0) {
            listContainer.innerHTML = '<div class="no-results">No questions found.</div>';
        } else {
            response.questions.forEach(q => {
                listContainer.appendChild(createQuestionItem(q));
            });
        }

        // Set the next offset
        currentOffset = response.questions.length; // Basic offset logic, simplistic

        // Update 'Load More' button visibility
        let loadMoreBtn = document.getElementById('load-more-btn');

        if (response.has_more) {
            loadMoreBtn.style.display = 'inline-block';
            loadMoreBtn.textContent = 'Load More';
            loadMoreBtn.disabled = false;
        } else {
            loadMoreBtn.style.display = 'none';
        }

    } catch (error) {
        listContainer.innerHTML = '<div class="error">Failed to load questions.</div>';
        document.getElementById('load-more-btn').style.display = 'none';
        console.error("Error loading questions:", error);
    }
}

/**
 * Appends the next set of questions to the list.
 */
async function loadMore() {
    const tagFilter = document.getElementById('tag-filter-input').value;
    const loadMoreBtn = document.getElementById('load-more-btn');

    loadMoreBtn.textContent = 'Loading...';
    loadMoreBtn.disabled = true;

    try {
        const response = await fetchQuestions(currentTab, currentOffset, tagFilter);

        response.questions.forEach(q => {
            document.getElementById('questions-list').appendChild(createQuestionItem(q));
        });

        // Update offset
        currentOffset += response.questions.length;

        // Update 'Load More' button visibility
        if (response.has_more) {
            loadMoreBtn.textContent = 'Load More';
            loadMoreBtn.disabled = false;
        } else {
            loadMoreBtn.style.display = 'none';
        }

    } catch (error) {
        loadMoreBtn.textContent = 'Failed to Load';
        loadMoreBtn.disabled = false;
        console.error("Error loading more questions:", error);
    }
}

/**
 * Handles filtering by tag, resets the offset, and calls loadQuestions.
 */
function filterQuestions() {
    isFilterActive = true;
    // When filtering, we essentially perform a new load from offset 0
    loadQuestions(currentTab, 0);
}

// Initialize the view on page load
window.onload = function () {
    loadQuestions(initial_tab, 0);
};

// Wait until DOM is fully ready
document.addEventListener("DOMContentLoaded", () => {

    // --- Filter Button ---
    const filterBtn = document.getElementById("filter-btn");
    filterBtn.addEventListener("click", () => {
        filterQuestions();
    });

    const searchInput = document.getElementById("tag-filter-input");
    searchInput.addEventListener("keypress", (e) => {
        if (e.key === 'Enter') {
            filterQuestions();
        }
    });

    // --- Advanced Filter Toggle ---
    const advancedFilterBtn = document.getElementById("advanced-filter-btn");
    const advancedFilterDropdown = document.getElementById("advanced-filter-dropdown");

    if (advancedFilterBtn && advancedFilterDropdown) {
        advancedFilterBtn.addEventListener("click", () => {
            const isHidden = advancedFilterDropdown.style.display === "none";
            advancedFilterDropdown.style.display = isHidden ? "flex" : "none";
            // advancedFilterBtn.classList.toggle("active", isHidden);
        });
    }

    // --- Tab Buttons ---
    const tabs = document.querySelectorAll(".tab-button");
    tabs.forEach(tab => {
        tab.addEventListener("click", () => {
            // remove active class from all
            tabs.forEach(t => t.classList.remove("active"));
            // add active to clicked
            tab.classList.add("active");

            const tabType = tab.dataset.tab;
            loadQuestions(tabType, 0);
        });
    });

    // --- Load More Button ---
    const loadMoreBtn = document.getElementById("load-more-btn");
    loadMoreBtn.addEventListener("click", () => {
        loadMore();
    });

    // --- Vote Buttons Delegation ---
    document.getElementById('questions-list').addEventListener('click', async (e) => {
        if (e.target.classList.contains('vote-btn')) {
            e.stopPropagation(); // Stop navigation
            const btn = e.target;
            const qId = btn.dataset.id;
            const type = btn.classList.contains('up') ? 'upvote' : 'downvote';

            // Optimistic UI Update or Call API
            // For now, let's just log or stub. 
            // Ideally call `api_vote_question`

            try {
                // Example call
                const response = await fetch(`${ROOT}/question/api_vote_question`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ q_id: qId, votetype: type })
                });

                if (response.ok) {
                    // Reload specific item or just refresh all? Refreshing all is easiest for now.
                    // loadQuestions(currentTab, 0); // Too disruptive
                    // In real app, update numbers in place.
                    console.log('Vote registered');
                    // Simple reload for MVP consistency
                    loadQuestions(currentTab, 0);
                }
            } catch (err) {
                console.error(err);
            }
        }
    });

});