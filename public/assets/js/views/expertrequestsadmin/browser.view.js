import { get_content } from '../../ajax/expertrequestsadmin/browser.ajax.js';

// UI Elements
const searchInput = document.getElementById('exercise-filter-input');
const tabButtons = document.querySelectorAll('.tab-button');
const contentContainer = document.getElementById('results-target');
const filterToggleBtn = document.getElementById('filter-toggle-btn');
const filterModal = document.getElementById('advanced-filter-modal');
const applyFiltersBtn = document.getElementById('apply-advanced-filters');
const loadMoreBtn = document.getElementById('load-more-btn');

// State Management
let tableState = {
    review: 'pending', // Default tab
    search: '',
    subject: '',
    sort: 'request_id',
    dir: 'DESC',
    limit: 10,
    offset: 0
};

let debounceTimer;

/**
 * Refreshes the table. 
 * If append is true, it adds to the list (pagination), otherwise it replaces.
 */
async function refreshTable(append = false) {
    if (!append) {
        tableState.offset = 0;
        contentContainer.innerHTML = '<div class="loader">Refreshing results...</div>';
    }

    const data = await get_content(tableState);
    
    if (append) {
        const tempDiv = document.createElement('div');
        tempDiv.innerHTML = data.html;
        while (tempDiv.firstChild) contentContainer.appendChild(tempDiv.firstChild);
    } else {
        contentContainer.innerHTML = data.html;
    }

    // Toggle Load More button based on backend metadata
    loadMoreBtn.parentElement.style.display = data.has_more ? 'block' : 'none';
}

// 1. Toggle Filter Modal
filterToggleBtn.addEventListener('click', () => {
    filterModal.classList.toggle('hidden');
});

// 2. Search Input (Debounced)
searchInput.addEventListener('input', (e) => {
    clearTimeout(debounceTimer);
    debounceTimer = setTimeout(() => {
        tableState.search = e.target.value;
        refreshTable();
    }, 450);
});

// 3. Tab Switching (Status Filter)
tabButtons.forEach(btn => {
    btn.addEventListener('click', () => {
        tabButtons.forEach(b => b.classList.remove('active'));
        btn.classList.add('active');
        tableState.review = btn.dataset.status;
        refreshTable();
    });
});

// 4. Advanced Filters Application
applyFiltersBtn.addEventListener('click', () => {
    tableState.subject = document.getElementById('subject-filter').value;
    tableState.sort = document.getElementById('sort-by').value;
    tableState.dir = document.getElementById('sort-dir').value;
    
    filterModal.classList.add('hidden'); // Close modal
    refreshTable();
});

// 5. Load More Pagination
loadMoreBtn.addEventListener('click', () => {
    tableState.offset += tableState.limit;
    refreshTable(true);
});

// Initial Load
document.addEventListener('DOMContentLoaded', () => {
    refreshTable();
});