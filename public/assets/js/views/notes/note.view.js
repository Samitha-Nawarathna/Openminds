import { ROOT } from '../../core/config.js';

document.addEventListener('DOMContentLoaded', () => {
    // --- Global Configuration & State ---
    const ITEMS_PER_LOAD = 10;
    let currentTab = 'created';
    let currentOffset = 0;

    // --- DOM Elements ---
    const loadMoreBtn = document.getElementById('load-more-btn');
    const listContainer = document.getElementById('notes-list');
    const filterInput = document.getElementById('note-filter-input');
    const filterBtn = document.getElementById('filter-btn');
    const tabButtons = document.querySelectorAll('.tab-button');

    // Initialize the offset based on the initial PHP render
    currentOffset = listContainer.children.length;

    // --- API FUNCTIONS ---

    async function apiPinNote(noteId) {
        try {
            const response = await fetch(`${ROOT}/notes/api/pin/${noteId}`, {
                method: 'POST'
            });
            const data = await response.json();
            if (data.success) {
                window.location.reload();
            } else {
                alert('Failed to pin note: ' + (data.message || 'Unknown error'));
            }
        } catch (error) {
            console.error('Error pinning note:', error);
            alert('An error occurred while pinning the note.');
        }
    }

    async function apiUnpinNote(noteId) {
        try {
            const response = await fetch(`${ROOT}/notes/api/unpin/${noteId}`, {
                method: 'POST'
            });
            const data = await response.json();
            if (data.success) {
                window.location.reload();
            } else {
                alert('Failed to unpin note: ' + (data.message || 'Unknown error'));
            }
        } catch (error) {
            console.error('Error unpinning note:', error);
            alert('An error occurred while unpinning the note.');
        }
    }

    // --- REAL API function ---
    async function fetchNotes(tabType, offset, filterTerm) {
        console.log(`[API CALL] Fetching ${tabType} notes from offset ${offset} with filter: "${filterTerm}"`);

        try {
            // Get topic_id from URL if available
            const urlPath = window.location.pathname;
            const topicIdMatch = urlPath.match(/\/notes\/list\/(\d+)/);
            const topicId = topicIdMatch ? topicIdMatch[1] : '';

            const response = await fetch(`${ROOT}notes/api/load_more?type=${tabType}&offset=${offset}&filter=${encodeURIComponent(filterTerm)}&topic_id=${topicId}`);
            if (!response.ok) throw new Error('Network response was not ok');

            const data = await response.json();
            return {
                notes: data.notes || [],
                has_more: data.has_more
            };
        } catch (error) {
            console.error("Error fetching notes:", error);
            return { notes: [], has_more: false };
        }
    }

    // --- RENDERING FUNCTIONS ---

    /** Navigates to a note view (mock) */
    function viewNote(noteId) {
        window.location.href = ROOT + `notes/view/${noteId}`;
    }

    /** Creates the HTML structure for a single note item. */
    function createNoteItem(note, tabType) {
        const item = document.createElement('div');
        item.dataset.id = note.id;

        const ownerBadge = (tabType === 'shared' && note.owner_name) ? `<span class="tag-pill">by ${note.owner_name}</span>` : '';

        item.innerHTML = `
        <a href="${ROOT}notes/view/${note.id}" class="no-style-link note-item">
          <span class="note-title-list">${note.title}</span>
          <div class="icons">
            ${ownerBadge}
            <span class="pin-icon" data-id="${note.id}">pin</span>
          </div>
        </a>
      `;

        return item;
    }

    /** Loads and replaces the list based on tab selection or filter. */
    async function loadNotes(tabType, offset) {
        currentTab = tabType;
        currentOffset = 0;

        // Update active tab styling
        tabButtons.forEach(btn => btn.classList.remove('active'));
        const activeTab = document.querySelector(`.tab-button[data-tab="${tabType}"]`);
        if (activeTab) activeTab.classList.add('active');

        listContainer.innerHTML = '<div class="loading">Loading notes...</div>';
        loadMoreBtn.style.display = 'none';

        const filterTerm = filterInput.value.trim();

        try {
            const response = await fetchNotes(tabType, offset, filterTerm);
            listContainer.innerHTML = '';

            if (response.notes.length === 0) {
                listContainer.innerHTML = '<div class="loading">No notes found.</div>';
            } else {
                response.notes.forEach(n => listContainer.appendChild(createNoteItem(n, tabType)));

                currentOffset = response.notes.length;

                loadMoreBtn.textContent = 'Load More';
                loadMoreBtn.disabled = false;
                loadMoreBtn.style.display = response.has_more ? 'block' : 'none';
            }

        } catch (error) {
            listContainer.innerHTML = '<div class="error">Failed to load notes.</div>';
            console.error("Error loading notes:", error);
        }
    }

    /** Appends the next set of notes to the list. */
    async function loadMore() {
        const filterTerm = filterInput.value.trim();

        loadMoreBtn.textContent = 'Loading...';
        loadMoreBtn.disabled = true;

        try {
            const response = await fetchNotes(currentTab, currentOffset, filterTerm);

            response.notes.forEach(n => listContainer.appendChild(createNoteItem(n, currentTab)));

            currentOffset += response.notes.length;

            if (response.has_more) {
                loadMoreBtn.textContent = 'Load More';
                loadMoreBtn.disabled = false;
            } else {
                loadMoreBtn.style.display = 'none';
            }

        } catch (error) {
            loadMoreBtn.textContent = 'Failed to Load';
            loadMoreBtn.disabled = false;
            console.error("Error loading more notes:", error);
        }
    }

    // --- EVENT DELEGATION ---

    document.addEventListener('click', (e) => {
        // 1. PIN ICON CLICK
        if (e.target.classList.contains('pin-icon')) {
            e.preventDefault();
            e.stopPropagation();
            // Try to find data-id on the icon itself or parent container
            const id = e.target.dataset.id || e.target.closest('.note-item')?.dataset.id;
            if (id) apiPinNote(id);
            return;
        }

        // 2. UNPIN ICON CLICK
        if (e.target.classList.contains('unpin-icon')) {
            e.preventDefault();
            e.stopPropagation();
            const id = e.target.dataset.id;
            if (id) apiUnpinNote(id);
            return;
        }
    });

    // --- EVENT LISTENERS ---

    // 1. Tab Listeners
    tabButtons.forEach(btn => {
        btn.addEventListener('click', (e) => loadNotes(e.currentTarget.dataset.tab, 0));
    });

    // 2. Filter Button Listener
    filterBtn.addEventListener('click', () => {
        // Reloads the current tab with the filter applied
        loadNotes(currentTab, 0);
    });

    // 3. Load More Button Listener
    loadMoreBtn.addEventListener('click', loadMore);

    // // 4. Note Action Buttons (Mock Handlers)
    // window.handleNoteEdit = (id) => alert(`Editing note: ${id}`);
    // window.handleNoteDelete = (id) => confirm(`Are you sure you want to delete note: ${id}?`);
    // document.querySelector('.btn-share').addEventListener('click', () => alert("Sharing feature activated (mock)."));


});