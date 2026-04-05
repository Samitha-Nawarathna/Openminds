import { get_content } from '../ajax/profilebrowser.ajax.js';

let btns = document.querySelectorAll('.tab-button');
let cards = document.querySelectorAll('.content-tab');

let currentIndex = 0;
let state = {
    banned: 0,
    searchTerm: '',
    limit: 10,
    offset: 0,
    searchTimeout: null,
    isLoading: false,
    roles: [],
    subject: '',
    dateStart: null,
    dateEnd: null,
    orderBy: 'created_at',
    orderDir: 'DESC',
    baseFilters: {
        'select': ['profile_id', 'username', 'display_name', 'role_name', 'subject_name', 'created_at', 'banned'],
        'where_not': { 'display_name': 'Guest User' }
    }
};

function showSkeleton(container, append = false) {
    const skeletonHTML = `<div class="skeleton-loader"></div>`.repeat(3);
    if (append) {
        container.insertAdjacentHTML('beforeend', `<div id="loading-spinner">${skeletonHTML}</div>`);
    } else {
        container.innerHTML = skeletonHTML;
    }
}

function loadProfiles(appendMode = false) {
    if (state.isLoading) return;
    state.isLoading = true;

    const container = cards[currentIndex];
    const loadMoreBtn = document.getElementById('load-more-btn');

    if (!appendMode) {
        showSkeleton(container);
        loadMoreBtn.style.display = 'none';
    } else {
        showSkeleton(container, true);
    }

    let backendParams = {
        ...state.baseFilters,
        offset: state.offset,
        limit: state.limit,
        order_by: state.orderBy,
        order_dir: state.orderDir,
        where: { 'banned': state.banned },
        like: {},
        range: {}
    };

    if (state.searchTerm) backendParams.like['display_name'] = state.searchTerm;
    if (state.roles.length > 0) backendParams.where['role_name'] = state.roles;
    if (state.subject) backendParams.where['subject_name'] = state.subject;
    if (state.dateStart && state.dateEnd) backendParams.range['created_at'] = [state.dateStart, state.dateEnd];

    get_content(backendParams, appendMode)
        .then((res) => {
            const spinner = document.getElementById('loading-spinner');
            if (spinner) spinner.remove();

            if (appendMode) {
                container.insertAdjacentHTML('beforeend', res);
            } else {
                container.innerHTML = res;
            }

            // Simple logic to hide load more if we likely reached the end
            const resultsCount = (res.match(/class="profile-item"/g) || []).length;
            loadMoreBtn.style.display = resultsCount < state.limit ? 'none' : 'block';
        })
        .finally(() => {
            state.isLoading = false;
        });
}

// Sidebar Toggles
document.getElementById('filter-toggle-btn')?.addEventListener('click', () => {
    document.getElementById('filter-drawer').classList.add('active');
});

document.getElementById('close-sidebar')?.addEventListener('click', () => {
    document.getElementById('filter-drawer').classList.remove('active');
});

// Search input
document.getElementById('exercise-filter-input')?.addEventListener('input', (e) => {
    state.searchTerm = e.target.value;
    state.offset = 0;
    clearTimeout(state.searchTimeout);
    state.searchTimeout = setTimeout(() => loadProfiles(), 400);
});

// Tabs
btns.forEach((btn, index) => {
    btn.addEventListener('click', () => {
        if (index === currentIndex) return;
        btns[currentIndex].classList.replace('btn-primary', 'btn-none');
        btn.classList.replace('btn-none', 'btn-primary');

        cards[currentIndex].classList.remove('active');
        cards[index].classList.add('active');

        currentIndex = index;
        state.banned = index; // 0 for active, 1 for banned
        state.offset = 0;
        loadProfiles();
    });
});

// Filters
document.querySelectorAll('.role-filter').forEach(cb => {
    cb.addEventListener('change', () => {
        state.roles = Array.from(document.querySelectorAll('.role-filter:checked')).map(c => c.value);
        state.offset = 0;
        loadProfiles();
    });
});

document.getElementById('subject-filter')?.addEventListener('change', (e) => {
    state.subject = e.target.value;
    state.offset = 0;
    loadProfiles();
});

document.getElementById('sort-by')?.addEventListener('change', (e) => {
    const [col, dir] = e.target.value.split('-');
    state.orderBy = col; state.orderDir = dir;
    state.offset = 0;
    loadProfiles();
});

document.getElementById('load-more-btn')?.addEventListener('click', () => {
    state.offset += state.limit;
    loadProfiles(true);
});

document.getElementById('clear-filters')?.addEventListener('click', () => {
    document.querySelectorAll('.role-filter').forEach(cb => cb.checked = false);
    document.getElementById('sort-by').value = 'created_at-DESC';
    if (document.getElementById('subject-filter')) {
        document.getElementById('subject-filter').value = '';
    }
    state.roles = [];
    state.subject = '';
    state.offset = 0;
    loadProfiles();
});

window.onload = loadProfiles;