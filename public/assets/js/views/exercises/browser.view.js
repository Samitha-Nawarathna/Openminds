import {ROOT} from "../../core/config.js";

document.addEventListener('DOMContentLoaded', () => {
    // --- DOM Elements ---
    const listContainer = document.getElementById('exercises-list');
    const tabsContainer = document.getElementById('tabs-container');
    const loadMoreBtn = document.getElementById('load-more-btn');
    
    // Filter Elements
    const filterInput = document.getElementById('exercise-filter-input');
    const filterBtn = document.getElementById('filter-btn');
    const toggleAdvancedBtn = document.getElementById('toggle-advanced-btn');
    const advancedFilterPanel = document.getElementById('advanced-filter-panel');
    const subjectFilter = document.getElementById('subject-filter');
    const sortFilter = document.getElementById('sort-filter');
    const createBtn = document.querySelector('.btn-create');

    // --- State ---
    let currentTab = INITIAL_TAB; 
    let offset = INITIAL_OFFSET; 
    const limit = typeof INITIAL_LIMIT !== 'undefined' ? INITIAL_LIMIT : 5; 

    // --- Dynamic Color Generation ---
    function getRandomPastelColorPair(subject) {
        let hash = 0;
        for (let i = 0; i < subject.length; i++) {
            hash = subject.charCodeAt(i) + ((hash << 5) - hash);
        }
        const h = hash % 360; 
        const s = 60 + (hash % 20); 
        const l = 85 + (hash % 10); 
        const background = `hsl(${h}, ${s}%, ${l}%)`;
        const textL = l - 60; 
        const textColor = `hsl(${h}, ${s}%, ${textL}%)`;
        return { background, color: textColor };
    }
    
    function applyDynamicPillColors() {
        const pills = document.querySelectorAll('.subject-pill');
        const colorCache = {}; 

        pills.forEach(pill => {
            const subject = pill.getAttribute('data-subject');
            if (subject) {
                if (!colorCache[subject]) {
                    colorCache[subject] = getRandomPastelColorPair(subject);
                }
                const colors = colorCache[subject];
                pill.style.backgroundColor = colors.background;
                pill.style.color = colors.color;
            }
        });
    }

    // --- UI Logic: Advanced Panel Toggle ---
    toggleAdvancedBtn.addEventListener('click', () => {
        advancedFilterPanel.classList.toggle('hidden');
        toggleAdvancedBtn.textContent = advancedFilterPanel.classList.contains('hidden') ? 'Advanced' : 'Hide';
    });

    function setCreateButtonState(canCreate) {
        if (!createBtn) return;

        const allowed = Boolean(canCreate);
        if (allowed) {
            if (createBtn.dataset.href) {
                createBtn.setAttribute('href', createBtn.dataset.href);
            }
            createBtn.classList.remove('is-disabled');
            createBtn.removeAttribute('aria-disabled');
            createBtn.removeAttribute('tabindex');
        } else {
            if (!createBtn.dataset.href) {
                createBtn.dataset.href = createBtn.getAttribute('href') || '';
            }
            createBtn.removeAttribute('href');
            createBtn.classList.add('is-disabled');
            createBtn.setAttribute('aria-disabled', 'true');
            createBtn.setAttribute('tabindex', '-1');
        }
    }

    // --- Data Fetching Logic ---

    function renderExercises(exercises) {
        exercises.forEach(exercise => {
            const item = document.createElement('div');
            // Create link wrapper
            const link = document.createElement('a');
            link.className = "no-style-link";
            
            // Determine endpoint based on tab/logic
            let endpoint = "attempt";
            if (currentTab === 'pending') endpoint = "expertreview";
            
            link.href = `${ROOT}/exercises/${endpoint}?id=${exercise.id}`;
            
            link.innerHTML = `
                <div class="exercise-item" data-id="${exercise.id}">
                    <span class="exercise-title-list">${exercise.title}</span>
                    <span class="subject-pill" data-subject="${exercise.subject}">
                        ${exercise.subject}
                    </span>
                </div>
            `;
            listContainer.appendChild(link);
        });
        
        applyDynamicPillColors();
    }

    /** * REAL fetch from backend Controller 
     */
    async function fetchExercises(params) {
        // Construct query parameters
        const searchParams = new URLSearchParams();
        for (const key in params) {
            if (params[key] !== null && params[key] !== '') {
                searchParams.append(key, params[key]);
            }
        }

        // Call the endpoint (Requires ExercisesController::filter method)
        const response = await fetch(`${ROOT}/exercises/filter?${searchParams.toString()}`);
        
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        
        return await response.json();
    }

    /** Handles loading data */
    async function loadData(isInitialLoad = false) {
        if (isInitialLoad) {
            offset = 0;
            listContainer.innerHTML = '';
        }

        loadMoreBtn.textContent = 'Loading...';
        loadMoreBtn.disabled = true;

        // Collect all UI state
        const requestParams = {
            tab: currentTab,
            offset: offset,
            limit: limit,
            q: filterInput.value.trim(),
            subject: subjectFilter.value,
            sort: sortFilter.value
        };

        try {
            const data = await fetchExercises(requestParams);
            
            // Backend should return { exercises: [], has_more: bool }
            renderExercises(data.exercises);

            if (data.permissions && Object.prototype.hasOwnProperty.call(data.permissions, 'can_create')) {
                setCreateButtonState(data.permissions.can_create);
            }

            const hasMore = data.has_more;
            loadMoreBtn.textContent = hasMore ? 'Load More' : 'No More Exercises';
            loadMoreBtn.disabled = !hasMore;

            if (hasMore) {
                offset += limit;
            }

        } catch (error) {
            console.error("Failed to load exercises:", error);
            loadMoreBtn.textContent = 'Error';
            // Optional: Show error in UI
            // listContainer.innerHTML += `<p style="color:red">Error loading data.</p>`; 
        }
    }


    // --- Event Listeners ---

    // 1. Tab Switching
    tabsContainer.addEventListener('click', (e) => {
        if (e.target.classList.contains('tab-button')) {
            document.querySelectorAll('.tab-button').forEach(btn => btn.classList.remove('active'));
            e.target.classList.add('active');
            
            currentTab = e.target.getAttribute('data-tab');
            loadData(true); 
        }
    });

    // 2. Load More
    loadMoreBtn.addEventListener('click', () => loadData(false));

    // 3. Search triggers
    filterBtn.addEventListener('click', () => loadData(true));
    filterInput.addEventListener('keydown', (e) => {
        if (e.key === 'Enter') loadData(true);
    });
    
    // 4. Advanced Filters triggers
    subjectFilter.addEventListener('change', () => loadData(true));
    sortFilter.addEventListener('change', () => loadData(true));

    // --- Initialization ---
    applyDynamicPillColors();
    loadData(true);
});