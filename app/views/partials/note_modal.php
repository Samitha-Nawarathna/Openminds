<script type="module">
    /**
     * Abstract Note Modal Manager
     * Manages the state, fetching, and display of multiple notes in a fixed, tabbed modal.
     */
    const ROOT = '<?= ROOT ?>';

    class AbstractNoteModal {
        constructor() {
            // DOM Elements
            this.modal = document.getElementById('abstract-note-modal');
            this.tabsContainer = document.getElementById('note-tabs-container');
            this.contentArea = document.getElementById('note-content-area');
            
            
            // State
            this.openNotes = []; // Array of note objects
            this.activeNoteId = null;
            this.apiUrl = 'notes/api/get_by_id/'; // API endpoint

            this.initEventListeners();
        }

        initEventListeners() {
            // Event listeners for tabs (click to switch, click 'x' to close)
            this.tabsContainer.addEventListener('click', (e) => {
                const closeButton = e.target.closest('.close-tab');
                const tabButton = e.target.closest('.note-tab');
                
                if (closeButton) {
                    // Close tab action
                    // The note ID is stored on the tab button, so we grab it from the closest tab
                    const noteId = closeButton.closest('.note-tab').dataset.id;
                    e.stopPropagation(); // Prevent the tab switch event from also firing
                    this.closeNote(noteId);
                } else if (tabButton) {
                    // Switch tab action
                    const noteId = tabButton.dataset.id;
                    this.switchTab(noteId);
                }
            });
        }

        /**
         * Public method to open a note by its ID.
         * 1. Checks if note is already open.
         * 2. Fetches note data via AJAX.
         * 3. Adds to state and renders.
         * @param {number|string} noteId 
         */
        async openNote(noteId) {
            noteId = String(noteId);

            // 1. Check if already open
            if (this.openNotes.some(note => String(note.id) === noteId)) {
                this.switchTab(noteId);
                return;
            }

            // 2. Fetch note data (Assumes 'ROOT' is globally defined for AJAX paths)
            try {
                const response = await fetch(`${ROOT}/${this.apiUrl}/${noteId}`);
                if (!response.ok) {
                    throw new Error('Failed to fetch note data.');
                }
                const data = await response.json();

                if (data.status === 'success' && data.note) {
                    const newNote = data.note;
                    newNote.id = String(newNote.id); 
                    
                    // 3. Add to state
                    this.openNotes.push(newNote);
                    this.activeNoteId = newNote.id;

                    // 4. Render and show modal
                    this.renderModal();
                } else {
                    console.error("API Error:", data.message || "Unknown error fetching note.");
                }
            } catch (error) {
                console.error(`Error opening note ${noteId}:`, error);
                alert("Could not load note content. Please try again.");
            }
        }

        /**
         * Switches the currently active note tab.
         * @param {string} noteId 
         */
        switchTab(noteId) {
            if (this.activeNoteId === noteId) return;
            this.activeNoteId = noteId;
            this.renderModal();
        }

        /**
         * Closes a note tab by its ID.
         * @param {string} noteId 
         */
        closeNote(noteId) {
            const index = this.openNotes.findIndex(note => note.id === noteId);
            if (index === -1) return;

            // 1. Remove note from array
            this.openNotes.splice(index, 1);

            // 2. Determine new active tab
            if (this.openNotes.length > 0) {
                // If the closed tab was active, switch to the nearest one (the last one in the array)
                if (this.activeNoteId === noteId) {
                    this.activeNoteId = this.openNotes[this.openNotes.length - 1].id;
                }
            } else {
                // No notes left, hide the modal
                if (window.close_note)
                {
                    window.close_note();
                }
                this.activeNoteId = null;
            }

            // 3. Render
            this.renderModal();
        }

        /**
         * Renders the tabs and the content of the active note.
         */
        renderModal() {
        if (this.openNotes.length === 0) {
            // State 1: No notes open
            // Hides the modal by applying the CSS class for sliding out
            this.modal.classList.remove('open'); 
            
            // Clear content (optional, but good practice)
            // We keep tabsContainer and contentArea cleared when hidden
            setTimeout(() => {
                this.tabsContainer.innerHTML = '';
                this.contentArea.innerHTML = '';
            }, 300); // Wait for transition to complete before clearing

            return;
        }

        // States 2 & 3: One or multiple notes open
        // Opens the modal by applying the CSS class for sliding in
        this.modal.classList.add('open'); 

        const activeNote = this.openNotes.find(note => note.id === this.activeNoteId);

        // 1. Render Tabs
        this.tabsContainer.innerHTML = this.openNotes.map(note => `
            <button class="note-tab ${note.id === this.activeNoteId ? 'active' : ''}" 
                    data-id="${note.id}" 
                    title="${note.title}">
                
                <span class="tab-title">${note.title}</span> 
                
                <span class="close-tab" data-id="${note.id}">&times;</span>
            </button>
        `).join('');

        // 2. Render Content
        if (activeNote) {
            this.contentArea.innerHTML = this.generateNoteContentHTML(activeNote);
            this.setupCollapsibleTags(); 
        } else {
            this.contentArea.innerHTML = '<h2>Select a note tab above.</h2>';
        }
    }

        /**
         * Generates the HTML structure for a single note's content.
         * @param {object} note 
         */
        generateNoteContentHTML(note) {

            note.content = "In mathematics and physics, a vector space (also called a linear space) is a set whose elements, often called vectors, can be added together and multiplied ('scaled') by numbers called scalars. The operations of vector addition and scalar multiplication must satisfy certain requirements, called vector axioms. Real vector spaces and complex vector spaces are kinds of vector spaces based on different kinds of scalars: real numbers and complex numbers. Scalars can also be, more generally, elements of any field. Vector spaces generalize Euclidean vectors, which allow modeling of physical quantities (such as forces and velocity) that have not only a magnitude, but also a direction. The concept of vector spaces is fundamental for linear algebra, together with the concept of matrices, which allows computing in vector spaces. This provides a concise and synthetic way for manipulating and studying systems of linear equations. Vector spaces are characterized by their dimension, which, roughly speaking, specifies the number of independent directions in the space. This means that, for two vector spaces over a given field and with the same dimension, the properties that depend only on the vector-space structure are exactly the same (technically the vector spaces are isomorphic). A vector space is finite-dimensional if its dimension is a natural number. Otherwise, it is infinite-dimensional, and its dimension is an infinite cardinal. Finite-dimensional vector spaces occur naturally in geometry and related areas. Infinite-dimensional vector spaces occur in many areas of mathematics. For example, polynomial rings are countably infinite-dimensional vector spaces, and many function spaces have the cardinality of the continuum as a dimension. Many vector spaces that are considered in mathematics are also endowed with other structures. This is the case of algebras, which include field extensions, polynomial rings, associative algebras and Lie algebras. This is also the case of topological vector spaces, which include function spaces, inner product spaces, normed spaces, Hilbert spaces and Banach spaces.";
            const tagsHtml = note.tags.map(tag => 

            
                `<span class="tag-pill tag-${tag.toLowerCase().replace(' ', '-')}">${tag}</span>`
            ).join('');

            return `
                <div class="abstract-note-viewer-wrapper">
                    
                    <input type="text" class="note-title-input" value="${note.title}" readonly>
                    
                    <div class="tags-collapsible-container">
                        <div id="tags-content-${note.id}" class="tags-collapsible-content.show">
                            <div class="tags-display">
                                ${tagsHtml}
                            </div>
                        </div>
                    </div>

                    <textarea class="note-content-textarea" readonly>${note.content}</textarea>

                                <div class="action-buttons-bottom">
                <button class="btn-view button btn-none"><a href="<?=ROOT?>/notes/view/<?=$data['note']['id']?>" class="no-style-link">View in note viewer</a></button>
            </div>
                </div>
            `;
        }

        /**
         * Sets up the event listener for the collapsible tags in the current active content.
         */
        setupCollapsibleTags() {
            const tagsToggleBtn = this.contentArea.querySelector('.tags-toggle-btn');
            if (tagsToggleBtn) {
                tagsToggleBtn.addEventListener('click', () => {
                    const contentId = tagsToggleBtn.getAttribute('aria-controls');
                    const tagsContent = document.getElementById(contentId);
                    
                    if (tagsContent) {
                        const isExpanded = tagsToggleBtn.getAttribute('aria-expanded') === 'true';
                        tagsToggleBtn.setAttribute('aria-expanded', !isExpanded);
                        tagsContent.classList.toggle('show');
                        
                        const icon = tagsToggleBtn.querySelector('.toggle-icon');
                        if (icon) {
                            icon.textContent = isExpanded ? '▼' : '▲';
                        }
                    }
                });
            }
        }
    }

    // Global initialization
    document.addEventListener('DOMContentLoaded', () => {
        // Initialize the manager and make it globally accessible for triggering from other parts of the app
        window.abstractNoteModalManager = new AbstractNoteModal();
    });
</script>


<style>
    /* --- Abstract Note Viewer Modal Styles --- */
.abstract-note-modal-container {
    /* Required for fixed right-half, 100vh position */
    position: fixed;
    top: var(--space-md); 
    right: var(--space-md); 
    width: calc(50vw - var(--space-md)); 
    height: calc(100vh - (2 * var(--space-md))); 
    z-index: 500; 
    overflow: hidden;
    
    /* Display properties */
    background-color: var(--color-surface);
    border: 2px solid var(--color-border);
    box-shadow: var(--shadow-xl); 
    display: flex; /* CRUCIAL: Must be 'flex' or 'block' for transition to work */
    flex-direction: column;
    overflow: hidden;
    border-radius: var(--radius-lg); 

    transition: transform 0.3s ease-out, visibility 0.3s ease-out;
    
    transform: translateX(100%);
    visibility: hidden;
}

.abstract-note-modal-container.open {
    /* Slide back into view */
    transform: translateX(0);
    visibility: visible;
}


.note-tabs-container {
    display: flex;
    flex-wrap: nowrap;
    overflow-x: auto; 
    border-bottom: 1px solid var(--color-border);
    background-color: var(--color-secondary-background);
    padding: 0 var(--space-xs);
    white-space: nowrap; 
    flex-shrink: 0;
    
    /* REFINEMENT: Reduce height by reducing vertical padding on tabs, not here */
}

/* Tab button refinement for a lower height */
.note-tab {
    display: flex; 
    align-items: center;
    justify-content: space-between; 
    
    /* REFINED: Reduced vertical padding for lower height */
    padding: var(--space-xs) var(--space-md); 
    
    border: none;
    border-right: 1px solid var(--color-border);
    background-color: transparent;
    cursor: pointer;
    font-size: var(--font-size-sm);
    color: var(--color-placeholder);
    transition: background-color 0.2s, color 0.2s;
    flex-shrink: 0; 
    max-width: 250px; 
    min-width: 50px; 
    overflow: hidden;
}

/* NEW: Styles for the title text wrapper */
.tab-title {
    flex-grow: 1; /* Allow the title to take up space */
    min-width: 1px; /* Required for truncation to work in flex containers */
    overflow: hidden; /* Truncate text */
    text-overflow: ellipsis; /* Use ellipsis for long strings */
    white-space: nowrap; /* Keep title on a single line */
    margin-right: var(--space-sm); /* Small space between text and 'x' */
}

.note-tab.active {
    background-color: var(--color-surface);
    border-bottom: 2px solid var(--color-primary); 
    color: var(--color-text);
    font-weight: 600;
}

.close-tab {
    /* Ensure the 'x' button never shrinks */
    flex-shrink: 0; 
    margin-left: 0; /* Margin is now on the title span */
    font-weight: bold;
    color: var(--color-placeholder);
    transition: color 0.2s;
}

.close-tab:hover {
    color: var(--color-error);
}

.action-buttons-bottom {
    display: flex;
    gap: var(--space-sm); 
    justify-content: flex-end;
    flex-shrink: 0; /* Ensure this fixed area doesn't shrink */
}


.btn-view
{
    width: fit-content;
}
/* ... (rest of CSS remains the same) ... */

    .note-tab:hover {
        background-color: var(--color-secondary-background-dark); /* Assume a darker token */
    }

    .note-tab.active {
    background-color: var(--color-surface);
    border-bottom: 2px solid var(--color-primary); 
    color: var(--color-text);
    font-weight: 600;
}


    .note-content-area {
        flex-grow: 1; 
        padding: var(--space-lg);
        overflow-y: hidden; 
    }

    .close-tab {
        margin-left: var(--space-sm);
        font-weight: bold;
        color: var(--color-placeholder);
        transition: color 0.2s;
    }

    .close-tab:hover {
        color: var(--color-error);
    }



    /* --- Note Content Styles (Reused classes for consistency) --- */

    .note-title-input,
    .note-content-textarea {
        width: 100%;
        padding: var(--space-sm);
        padding-left: 0;
        /* border: 1px solid var(--color-border); */
        border-radius: var(--radius-md); 
        font-size: var(--font-size-base);
        box-sizing: border-box;
        /* margin-bottom: var(--space-md); */
        resize: none;
        font-family: inherit;
        color: var(--color-text);
        /* background-color: var(--color-surface);  */
    }

    .note-title-input {
        font-size: var(--font-size-lg);
        font-weight: 600;
        height: auto;
    }

    .note-content-textarea {
        height: 350px; 
        line-height: 1.6;
    }

    /* Collapsible Tags (for note display within the modal) */
    .tags-collapsible-container {
        margin-bottom: var(--space-md);
        /* border: 1px solid var(--color-border); */
        border-radius: var(--radius-sm);
        overflow: hidden; 
    }

    .tags-toggle-btn {
        display: flex;
        justify-content: space-between;
        align-items: center;
        width: 100%;
        padding: var(--space-sm);
        background: var(--color-secondary-background);
        border: none;
        cursor: pointer;
        font-weight: 600;
        font-size: var(--font-size-base);
        text-align: left;
    }

    .tags-collapsible-content {
        max-height: 0;
        overflow: hidden;
        transition: max-height 0.3s ease-out;
    }

    .tags-collapsible-content.show {
        max-height: 500px; 
        padding: var(--space-sm);
        border-top: 1px solid var(--color-border);
    }

    .tags-display {
        display: flex;
        flex-wrap: wrap;
        gap: var(--space-xs); 
    }

    .tag-pill {
        padding: 5px 10px; 
        border-radius: 12px;
        font-size: 0.9em;
        font-weight: 500;
        white-space: nowrap;
        box-sizing: border-box;
    }

    /* Ensure tag color classes are available */
    .tag-physics { background-color: #ff9999; color: #cc0000; }
    .tag-psychology { background-color: #ccccff; color: #6600cc; }
    .tag-maths { background-color: #99ff99; color: #008000; }
    .tag-design { background-color: #ffcc99; color: #cc6600; }
    .tag-quantum-computing { background-color: #a0e0ff; color: #0056b3; }

</style>


<div id="abstract-note-modal" class="abstract-note-modal-container">
    
    <div id="note-tabs-container" class="note-tabs-container">
        </div>

    <div id="note-content-area" class="note-content-area">
        </div>
    
</div>