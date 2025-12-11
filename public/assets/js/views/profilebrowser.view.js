import { get_content } from '../ajax/profilebrowser.ajax.js';

let btns = document.querySelectorAll('.tab-button');
let cards = document.querySelectorAll('.content-tab');

// ----------------------------------------------------
// 1. STATE MANAGEMENT
// ----------------------------------------------------
let index2type = {0: "active", 1: "banned"};
let currentIndex = 0;

// Global state object to manage all dynamic parameters
let state = {
  type: index2type[currentIndex], 
  searchTerm: '', 
  limit: 10,
  offset: 0,
  searchTimeout: null, // For debouncing the search input
  
  // Base filters that are always included in the backend request
  baseFilters: {
    'select': ['profile_id', 'username', 'display_name', 'role_name', 'subject_name', 'created_at'],
    'where_not': { 'display_name': 'Guest User' },
    'order_by': 'subject_name',
    'order_dir': 'ASC',
  }
};

// ----------------------------------------------------
// 2. CORE DATA LOADER FUNCTION
// ----------------------------------------------------

// Core function to construct parameters, fetch content, and render/append it
function loadProfiles() {
    // 1. Start with base filters
    let backendParams = {
        ...state.baseFilters,
        offset: state.offset,
        limit: state.limit,
        // Ensure 'where' clause is always initialized for dynamic filtering
        'where': { ...state.baseFilters.where }, 
    };

    // 2. Add dynamic filters based on the current tab (state.type)
    // NOTE: This assumes your database has a 'status' column (active/banned).
    if (state.type === 'banned') {
        // Example: When on the 'banned' tab, filter by status='banned'
        backendParams['where']['banned'] = 1;
    } else {
        // Example: When on the 'active' tab, filter by status='active' AND role='expert'
        backendParams['where']['banned'] = 0; 
    }

    // 3. Add dynamic search term (Username Search)
    if (state.searchTerm) {
        // Uses the 'like' filter in Model.php to search the 'username' column
        backendParams['like'] = {
            'username': state.searchTerm
        };
    }
    
    // Determine if we are loading the first page or appending content (for Load More)
    const appendMode = state.offset > 0;
    
    // Show loading indicator here before the API call if needed
    
    // Call the AJAX function to fetch data
    get_content(backendParams, appendMode) 
        .then(content => {
            const currentCard = cards[currentIndex];
            if (appendMode) {
                // Append results for "Load More"
                currentCard.insertAdjacentHTML('beforeend', content);
            } else {
                // Overwrite content for initial load, tab switch, or new search
                currentCard.innerHTML = content;
            }
        });
}

// ----------------------------------------------------
// 3. EVENT LISTENERS
// ----------------------------------------------------

let primary = 'btn-primary';
let none = 'btn-none';

// A. Tab Navigation Event Handling
btns.forEach(btn => {
  btn.addEventListener("click", () => {
    // Remove active styles from all buttons
    btns.forEach(el => {
      el.classList.remove(primary);
      el.classList.add(none);
    });
    // Add active styles to the clicked button
    btn.classList.add(primary);
    btn.classList.remove(none);
    
    const targetIndex = parseInt(btn.dataset.index);
    if (targetIndex === currentIndex) return;

    // Transition logic (reusing existing animation class)
    const direction = targetIndex > currentIndex ? 1 : -1;
    const currentCard = cards[currentIndex];
    const nextCard = cards[targetIndex];

    nextCard.classList.add("incoming");
    
    // 1. Update the state for the new tab
    state.type = index2type[targetIndex];
    state.offset = 0; // RESET PAGINATION
    state.searchTerm = document.getElementById('exercise-filter-input').value; // Keep current search term

    // 2. Load profiles with the new state
    loadProfiles(); 

    // Handle card transition animation completion
    nextCard.classList.add(`move-${direction > 0 ? 'left' : 'right'}`);
    currentCard.classList.add(`move-${direction > 0 ? 'left' : 'right'}`);

    // Set new current index
    currentIndex = targetIndex;
  });
});

// B. Search Input Event Handling
const searchInput = document.getElementById('exercise-filter-input');

if (searchInput) {
    searchInput.addEventListener('input', () => {
        // 1. Update the state with the new search term
        state.searchTerm = searchInput.value;
        state.offset = 0; // RESET PAGINATION on new search
        
        // 2. Debounce: Wait 300ms after user stops typing
        clearTimeout(state.searchTimeout);
        state.searchTimeout = setTimeout(() => {
            loadProfiles(); 
        }, 300);
    });
}

// C. Load More Button Event Handling
const loadMoreBtn = document.getElementById('load-more-btn');

if (loadMoreBtn) {
    loadMoreBtn.addEventListener('click', () => {
        state.offset += state.limit; // Increase the offset by the current limit (10)
        loadProfiles(); // Fetch the next page and ensure it appends
    });
}

// ----------------------------------------------------
// 4. INITIALIZATION
// ----------------------------------------------------

// Initial load of the "active" profiles
loadProfiles();