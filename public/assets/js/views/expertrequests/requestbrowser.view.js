import { get_content } from '../../ajax/expertrequests/requestbrowser.ajax.js';

// State Management - The "Source of Truth"
let state = {
    review: 'pending',
    subject: '',
    limit: 10,
    offset: 0,
    order_by: 'id',
    order_dir: 'DESC'
};

const btns = document.querySelectorAll('.tab-button');
const containers = document.querySelectorAll('.content-tab');
const searchInput = document.getElementById('exercise-filter-input');
const loadMoreBtn = document.getElementById('load-more-btn');

// Panel Elements
const filterPanel = document.getElementById('filter-panel');
const filterOverlay = document.getElementById('filter-overlay');
const openFilterBtn = document.getElementById('open-filter-btn');
const closeFilterBtn = document.getElementById('close-filter-btn');
const applyFiltersBtn = document.getElementById('apply-filters-btn');

// --- Initialization ---
async function refreshList(append = false) {
    if (!append) state.offset = 0;
    
    const content = await get_content(
        state.review, 
        state.subject, 
        state.limit, 
        state.offset,
        state.order_by,
        state.order_dir
    );
    
    const activeContainer = document.querySelector('.content-tab.active');
    
    if (append) {
        activeContainer.insertAdjacentHTML('beforeend', content);
    } else {
        activeContainer.innerHTML = content;
    }
    
    toggleLoadMoreButton(content);
}

// --- Side Panel Interaction ---
const togglePanel = (show) => {
    if (show) {
        filterOverlay.style.display = 'block';
        setTimeout(() => filterPanel.classList.add('active'), 10);
    } else {
        filterPanel.classList.remove('active');
        setTimeout(() => filterOverlay.style.display = 'none', 300);
    }
};

openFilterBtn.addEventListener('click', () => togglePanel(true));
closeFilterBtn.addEventListener('click', () => togglePanel(false));
filterOverlay.addEventListener('click', () => togglePanel(false));

// Apply Filters Logic
applyFiltersBtn.addEventListener('click', () => {
    state.order_by = document.getElementById('sort-column').value;
    state.order_dir = document.getElementById('sort-order').value;
    state.limit = parseInt(document.getElementById('filter-limit').value);
    
    togglePanel(false);
    refreshList(false);
});

// --- Main Page Logic ---

// Instant Search
let searchTimeout;
searchInput.addEventListener('input', () => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        state.subject = searchInput.value;
        refreshList(false);
    }, 300);
});

// Tab Switching
btns.forEach((btn, index) => {
    btn.addEventListener('click', async () => {
        btns.forEach(b => b.classList.remove('btn-primary'));
        btn.classList.add('btn-primary');
        
        containers.forEach(c => c.classList.remove('active'));
        containers[index].classList.add('active');

        state.review = btn.textContent.trim().toLowerCase();
        refreshList(false);
    });
});

// Load More
loadMoreBtn.addEventListener('click', () => {
    state.offset += state.limit;
    refreshList(true);
});

function toggleLoadMoreButton(content) {
    // Simple check: if we got no content or didn't hit the limit, hide button
    if (!content || (content.match(/class="profile-item/g) || []).length < state.limit) {
        loadMoreBtn.style.display = 'none';
    } else {
        loadMoreBtn.style.display = 'inline-block';
    }
}

refreshList();