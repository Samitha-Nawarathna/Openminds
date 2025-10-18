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

    // --- MOCK AJAX function ---
    function mockFetchNotes(tabType, offset, filterTerm) {
        console.log(`[MOCK AJAX] Fetching ${tabType} notes from offset ${offset} with filter: "${filterTerm}"`);
        
        return new Promise(resolve => {
            setTimeout(() => {
                let mockNotes = [];
                let hasMore = true;
                
                // Simplified mock logic based on tab and offset
                if (tabType === 'created') {
                    if (offset === 0) {
                        // Simulating a fresh load after filter/tab change
                        mockNotes = [
                            { id: 'c1', title: 'Calculus Basics', tag: 'Maths' },
                            { id: 'c2', title: 'Quantum Fields', tag: 'Physics' }
                        ];
                        hasMore = true;
                    } else if (offset === 10) {
                        // Simulating the 'Load More' action
                        mockNotes = [
                            { id: 'c11', title: '11th Created Note (Lazy Load)', tag: 'Test' }
                        ];
                        hasMore = false;
                    }
                } else if (tabType === 'shared') {
                    // Shared notes, usually a smaller list, less frequent lazy load
                    mockNotes = [
                        { id: 's1', title: 'Shared: General Relativity', tag: 'Physics' },
                        { id: 's2', title: 'Shared: Python Tips', tag: 'CS' }
                    ];
                    hasMore = false;
                }
                
                // Apply filter locally for mock visual check (backend would do this)
                if (filterTerm) {
                    const term = filterTerm.toLowerCase();
                    mockNotes = mockNotes.filter(n => n.title.toLowerCase().includes(term));
                }

                resolve({
                    notes: mockNotes,
                    has_more: hasMore
                });
            }, 400);
        });
    }

    // --- RENDERING FUNCTIONS ---

    /** Navigates to a note view (mock) */
    function viewNote(noteId) {
        
        window.location.href = ROOT + `/notes/show?id=${noteId}`;
    }

    /** Creates the HTML structure for a single note item. */
    function createNoteItem(note) {
        const item = document.createElement('div');
        item.classList.add('note-item');
        item.dataset.id = note.id; 

        item.innerHTML = `
            <a href="${ROOT}/notes/show?id=${note.id}" class="no-style-link">
                <span class="note-title-list">${note.title}</span>
                <span class="tag-pill tag-${note.tag.toLowerCase().replace(/\s/g, '-')}" >${note.tag}</span>
            </a>
        `;
        
        // Add event listener
        item.addEventListener('click', () => viewNote(note.id));

        return item;
    }

    /** Loads and replaces the list based on tab selection or filter. */
    async function loadNotes(tabType, offset) {
        currentTab = tabType;
        currentOffset = 0;
        
        // Update active tab styling
        tabButtons.forEach(btn => btn.classList.remove('active'));
        document.querySelector(`.tab-button[data-tab="${tabType}"]`).classList.add('active');

        listContainer.innerHTML = '<div class="loading">Loading notes...</div>';
        loadMoreBtn.style.display = 'none';
        
        const filterTerm = filterInput.value.trim();

        try {
            const response = await mockFetchNotes(tabType, offset, filterTerm);
            listContainer.innerHTML = ''; 

            if (response.notes.length === 0) {
                listContainer.innerHTML = '<div class="loading">No notes found.</div>';
            } else {
                response.notes.forEach(n => listContainer.appendChild(createNoteItem(n)));
                
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
            const response = await mockFetchNotes(currentTab, currentOffset, filterTerm);
            
            response.notes.forEach(n => listContainer.appendChild(createNoteItem(n)));

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