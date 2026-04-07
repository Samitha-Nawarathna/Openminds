import { ROOT } from "../../core/config.js";

document.addEventListener('DOMContentLoaded', () => {
    // --- DOM Elements ---
    const listContainer = document.getElementById('exercises-list');
    const tabsContainer = document.getElementById('tabs-container');
    const loadMoreBtn = document.getElementById('load-more-btn');
    const roleHost = document.querySelector('.main-content-container');

    // Filter Elements
    const filterInput = document.getElementById('exercise-filter-input');
    const filterBtn = document.getElementById('filter-btn');
    const toggleAdvancedBtn = document.getElementById('toggle-advanced-btn');
    const advancedFilterPanel = document.getElementById('advanced-filter-panel');
    const subjectFilter = document.getElementById('subject-filter');
    const sortFilter = document.getElementById('sort-filter');
    const createBtn = document.querySelector('.btn-create');




    // ...existing code...
    const allTab = document.getElementById('all-tab');
    const createdTab = document.getElementById('created-tab');
    const createdSubtabs = document.getElementById('created-subtabs');
    const pendingTab = document.getElementById('pending-tab');
    const createdSubtabButtons = createdSubtabs ? Array.from(createdSubtabs.querySelectorAll('.created-subtab-btn')) : [];

    function hideEl(el) {
        if (el) el.classList.add('is-hidden-by-role');
    }

    function applyRoleVisibility(role) {
        const r = (role || 'student').trim().toLowerCase();

        // default: show all known controls first (if rendered)
        [createdTab, pendingTab, createBtn].forEach(el => {
            if (el) el.classList.remove('is-hidden-by-role');
        });

        if (r === 'student') {
            hideEl(createdTab);
            hideEl(pendingTab);
            hideEl(createBtn);
        } else if (r === 'mentor') {
            hideEl(pendingTab);
        } else if (r === 'expert') {
            // Can see both pendingTab and createBtn/createdTab
        } else if (r === 'admin') {
            // Can see both pendingTab and createBtn/createdTab
        }

        // if active tab is hidden, switch to "all"
        const active = document.querySelector('.tab-button.active');
        if (active && active.classList.contains('is-hidden-by-role') && allTab) {
            active.classList.remove('active');
            allTab.classList.add('active');
            currentTab = 'all';
        }
    }

    // --- State ---
    let currentTab = INITIAL_TAB;
    let currentCreatedSubtab = 'created_published';
    let offset = INITIAL_OFFSET;
    const limit = typeof INITIAL_LIMIT !== 'undefined' ? INITIAL_LIMIT : 5;

    const roleFromDom = (roleHost?.dataset?.userRole || USER_ROLE || 'student').trim().toLowerCase();
    const canViewPending = roleFromDom === 'expert' || roleFromDom === 'admin';

    if (!canViewPending && pendingTab) {
        pendingTab.remove();
    }

    if (!canViewPending && currentTab === 'pending') {
        currentTab = 'all';
    }

    applyRoleVisibility(roleFromDom);

    function getEffectiveTab() {
        return currentTab === 'created' ? currentCreatedSubtab : currentTab;
    }

    function toggleCreatedSubtabs() {
        if (!createdSubtabs) return;

        const shouldShow = currentTab === 'created' && roleFromDom !== 'student';
        createdSubtabs.classList.toggle('is-visible', shouldShow);
        createdSubtabs.classList.toggle('is-hidden-by-role', !shouldShow);
    }

    function setActiveCreatedSubtab(tabName) {
        currentCreatedSubtab = (tabName === 'created_draft') ? 'created_draft' : 'created_published';
        createdSubtabButtons.forEach((btn) => {
            const isActive = btn.getAttribute('data-created-subtab') === currentCreatedSubtab;
            btn.classList.toggle('active', isActive);
        });
    }









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

    function renderEmptyState() {
        const canCreate = !createBtn?.classList.contains('is-disabled');
        const createAction = canCreate
            ? `<a class="empty-state-link" href="${ROOT}/exercises/create">Create your first exercise</a>`
            : '';

        listContainer.innerHTML = `
            <div class="empty-state">
                <div class="empty-state-icon" aria-hidden="true">&#128218;</div>
                <p class="empty-state-message">No exercises found in this section</p>
                ${createAction}
            </div>
        `;
    }

    function renderExercises(exercises) {
        if (!Array.isArray(exercises) || exercises.length === 0) {
            if (offset === 0) {
                renderEmptyState();
            }
            return;
        }

        const fragment = document.createDocumentFragment();

        exercises.forEach(exercise => {
            // Create link wrapper
            const link = document.createElement('a');
            link.className = "no-style-link";

            // Determine endpoint based on tab/logic
            let endpoint = "attempt";
            if (currentTab === 'pending') endpoint = "expertreview";
            if (currentTab === 'created') {
                endpoint = currentCreatedSubtab === 'created_draft' ? 'edit' : 'mentorview';
            }
            if (currentTab === 'attempted') endpoint = "show";

            link.href = `${ROOT}/exercises/${endpoint}?id=${exercise.id}`;

            const statusText = String(exercise.status || '').toLowerCase();
            const showStatusTag = currentTab === 'created' && currentCreatedSubtab === 'created_draft';
            const statusTag = showStatusTag
                ? `<span class="status-pill status-${statusText || 'draft'}">${statusText || 'draft'}</span>`
                : '';

            link.innerHTML = `
                <div class="exercise-item" data-id="${exercise.id}">
                    <span class="exercise-title-list">${exercise.title}</span>
                    <span class="subject-pill" data-subject="${exercise.subject}">
                        ${exercise.subject}
                    </span>
                    ${statusTag}
                </div>
            `;
            fragment.appendChild(link);
        });

        listContainer.appendChild(fragment);

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
            tab: getEffectiveTab(),
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

            if (isInitialLoad && (!Array.isArray(data.exercises) || data.exercises.length === 0)) {
                loadMoreBtn.textContent = 'No More Exercises';
                loadMoreBtn.disabled = true;
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
            const nextTab = e.target.getAttribute('data-tab');
            if (nextTab === 'pending' && !canViewPending) {
                return;
            }

            document.querySelectorAll('.tab-button').forEach(btn => btn.classList.remove('active'));
            e.target.classList.add('active');

            currentTab = nextTab;
            toggleCreatedSubtabs();
            loadData(true);
        }
    });

    createdSubtabButtons.forEach((btn) => {
        btn.addEventListener('click', () => {
            const nextSubtab = btn.getAttribute('data-created-subtab');
            setActiveCreatedSubtab(nextSubtab);
            if (currentTab === 'created') {
                loadData(true);
            }
        });
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
    setActiveCreatedSubtab(currentCreatedSubtab);
    toggleCreatedSubtabs();
    applyDynamicPillColors();
    loadData(true);
});