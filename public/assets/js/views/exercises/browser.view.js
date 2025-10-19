import {ROOT} from "../../core/config.js";

document.addEventListener('DOMContentLoaded', () => {
    // --- DOM Elements ---
    const listContainer = document.getElementById('exercises-list');
    const tabsContainer = document.getElementById('tabs-container');
    const loadMoreBtn = document.getElementById('load-more-btn');
    const filterInput = document.getElementById('exercise-filter-input');
    const filterBtn = document.getElementById('filter-btn');

    // --- State (Initialized using global constants from PHP) ---
    let currentTab = INITIAL_TAB; 
    let offset = INITIAL_OFFSET; // Start the offset from the number of items initially loaded
    const limit = 5; // Items to load per request

    // --- Core Feature: Dynamic Color Generation (NO CHANGES) ---

    /** Generates a random pastel-like color pair (light background, dark text). */
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
    
    /** Applies the random colors to all subject pills on the page. */
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


    // --- General List/UI Logic (NO CHANGES) ---

    /** Renders the list of exercises into the container. */
    function renderExercises(exercises) {
        exercises.forEach(exercise => {
            const item = document.createElement('div');
            // item.className = 'exercise-item';
            item.setAttribute('data-id', exercise.id);

            let endpoint = "attempt";

            if (currentTab === 'pending') {
                endpoint = "expertreview";
            }

            item.innerHTML = `
                <a class="no-style-link" href=${ROOT}/exercises/${endpoint}?id=${exercise.id}>
                    <div class="exercise-item">
                        <span class="exercise-title-list">${exercise.title}</span>
                        <span class="subject-pill" data-subject="${exercise.subject}">
                            ${exercise.subject}
                        </span>
                    </div>
                </a>
            `;
            listContainer.appendChild(item);
        });
        
        applyDynamicPillColors();
    }

    /** Mock AJAX call to simulate fetching data from the backend. */
    function fetchExercises(tab, currentOffset, currentLimit, filterText = '') {
        // --- MOCK BACKEND DATA LOGIC ---
        const all_exercises = [
            { id: 1, title: 'what is lagrangian method?', 'subject': 'Physics', 'relation': 'Created' },
            { id: 1, title: 'how Jacobian related to gradient?', 'subject': 'Maths', 'relation': 'Created' },
            { id: 1, title: 'solve in Hamiltonian mechanics?', 'subject': 'Physics', 'relation': 'Attempted' },
            { id: 1, title: 'what does this operator do?', 'subject': 'Quantum Computing', 'relation': 'Created' },
            { id: 1, title: 'how shadow work described by jung?', 'subject': 'Psychology', 'relation': 'Attempted' },
            { id: 1, title: 'how to solve this in linear algebra?', 'subject': 'Maths', 'relation': 'Created' },
            { id: 1, title: 'Load More: Wave-particle duality', 'subject': 'Physics', 'relation': 'Created' },
            { id: 1, title: 'Load More: Linear Regression', 'subject': 'Maths', 'relation': 'Created' },
            { id: 1, title: 'Load More: Cognitive Dissonance', 'subject': 'Psychology', 'relation': 'Attempted' },
        ];
        
        let filteredData = all_exercises.filter(e => {
            let matchesTab = true;
            if (tab === 'created') matchesTab = e.relation === 'Created';
            if (tab === 'attempted') matchesTab = e.relation === 'Attempted';
            
            let matchesFilter = true;
            if (filterText) {
                const query = filterText.toLowerCase();
                matchesFilter = e.title.toLowerCase().includes(query) || e.subject.toLowerCase().includes(query);
            }
            return matchesTab && matchesFilter;
        });

        const exercises = filteredData.slice(currentOffset, currentOffset + currentLimit);
        const hasMore = filteredData.length > (currentOffset + currentLimit);
        
        return Promise.resolve({ exercises, hasMore });
    }

    /** Handles loading data, used by both 'Load More' and tab switching. */
    async function loadData(isInitialLoad = false) {
        if (isInitialLoad) {
            offset = 0;
            listContainer.innerHTML = '';
        }

        loadMoreBtn.textContent = 'Loading...';
        loadMoreBtn.disabled = true;

        const filterQuery = filterInput.value.trim();

        try {
            const { exercises, hasMore } = await fetchExercises(currentTab, offset, limit, filterQuery);

            renderExercises(exercises);

            loadMoreBtn.textContent = hasMore ? 'Load More' : 'No More Exercises';
            loadMoreBtn.disabled = !hasMore;

            if (hasMore) {
                offset += limit;
            }

        } catch (error) {
            console.error("Failed to load exercises:", error);
            loadMoreBtn.textContent = 'Error Loading';
            loadMoreBtn.disabled = true;
        }
    }


    // --- Event Listeners (NO CHANGES) ---

    // 1. Tab Switching
    tabsContainer.addEventListener('click', (e) => {
        if (e.target.classList.contains('tab-button')) {
            document.querySelectorAll('.tab-button').forEach(btn => btn.classList.remove('active'));
            e.target.classList.add('active');
            
            currentTab = e.target.getAttribute('data-tab');
            console.log(currentTab);
            loadData(true); // True for initial load/reset
        }
    });

    // 2. Load More Button
    loadMoreBtn.addEventListener('click', () => loadData(false));

    // 3. Filter Button
    filterBtn.addEventListener('click', () => loadData(true));
    filterInput.addEventListener('keydown', (e) => {
        if (e.key === 'Enter') {
            loadData(true);
        }
    });

    // --- Initialization ---

    // Apply colors to the items rendered by PHP on page load
    applyDynamicPillColors();
});