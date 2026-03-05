console.log("A");

// --- Global State ---
let selectedNotesToMove = [];
let currentSearchTags = [];
let isTopicCreated = false;
let currentTopicId = null; // Store the ID of the created topic
let currentNoteOffset = 0;
const NOTES_LIMIT = 5;

// Initial displayed notes (fetched from backend)
let displayedAvailableNotes = [];
let totalAvailableNotes = 0; // Track total for "Load More" logic

// --- Section 1: Create Topic Functions ---

/**
 * Updates the UI elements based on the topic creation state.
 */
function updateTopicCreationUI() {
    const subjectInput = document.getElementById('subject-name-input');
    const createBtn = document.getElementById('create-subject-btn');

    if (isTopicCreated) {
        // Topic created state
        subjectInput.disabled = true;
        createBtn.className = 'button button-secondary';
        createBtn.textContent = 'Undo Topic Creation';
        createBtn.setAttribute('onclick', 'undoTopicCreation()');
    } else {
        // Pre-creation state
        subjectInput.disabled = false;
        createBtn.className = 'button button-primary';
        createBtn.textContent = 'Create Subject';
        createBtn.setAttribute('onclick', 'createSubject()');
    }
}

/**
 * Validates the topic name input.
 * @param {string} subjectName 
 */
function validatesubjectName(subjectName) {
    const trimmedName = subjectName.trim();
    const createBtn = document.getElementById('create-subject-btn');
    const validationResult = document.getElementById('validation-result');

    if (trimmedName.length === 0) {
        validationResult.textContent = 'Topic name cannot be empty.';
        validationResult.style.color = 'var(--color-error)';
        createBtn.disabled = true;
    } else if (trimmedName.length < 3) {
        validationResult.textContent = 'Topic name must be at least 3 characters long.';
        validationResult.style.color = 'var(--color-error)';
        createBtn.disabled = true;
    } else {
        validationResult.textContent = 'Topic name is valid.';
        validationResult.style.color = 'var(--color-success)';
        createBtn.disabled = false;
    }
}

window.validatesubjectName = validatesubjectName;

/**
 * Creates a new topic via API and opens the modal.
 */
async function createSubject() {
    const subjectName = document.getElementById('subject-name-input').value;

    if (!document.getElementById('create-subject-btn').disabled && !isTopicCreated) {

        try {
            const formData = new FormData();
            formData.append('name', subjectName);

            // Use absolute path with ROOT
            const root = window.ROOT || ''; // Fallback if ROOT undefined
            const response = await fetch(`${root}topics/api/create`, {
                method: 'POST',
                body: JSON.stringify({ name: subjectName }),
                headers: {
                    'Content-Type': 'application/json'
                }
            });

            const result = await response.json();

            if (result.success) {
                console.log(`Topic created: "${result.name}" (ID: ${result.topic_id})`);
                currentTopicId = result.topic_id;
                isTopicCreated = true;
                updateTopicCreationUI();
                openNotesModal();

                // Initial search for notes when modal opens
                searchNotes(true);
            } else {
                alert(result.message || 'Failed to create topic.');
            }

        } catch (error) {
            console.error('Error creating topic:', error);
            alert('An error occurred while creating the topic.');
        }
    }
}

window.createSubject = createSubject;

/**
 * "Undoes" the creation (For now, just resets UI, does not delete from DB unless we add that API).
 * In a real flow, you might want to call a delete API or just let the user abandon the empty topic.
 * Here we revert UI state.
 */
function undoTopicCreation() {
    if (confirm("This will reset the form. The subject '" + document.getElementById('subject-name-input').value + "' has already been created in the background. Continue?")) {
        // Close the modal immediately
        closeNotesModal();

        isTopicCreated = false;
        currentTopicId = null;
        selectedNotesToMove = [];
        updateNotesPanels();

        document.getElementById('subject-name-input').value = '';
        validatesubjectName('');
        updateTopicCreationUI();
    }
}

window.undoTopicCreation = undoTopicCreation;

function openNotesModal() {
    document.getElementById('notes-modal').style.display = 'block';
    document.body.style.overflow = 'hidden';
}

function closeNotesModal() {
    document.getElementById('notes-modal').style.display = 'none';
    document.body.style.overflow = '';
}

window.closeNotesModal = closeNotesModal;


// --- Section 2: Notes Management Functions ---

function addTagOnEnter(event) {
    if (event.key === 'Enter') {
        event.preventDefault();
        const tagInput = document.getElementById('tag-input');
        const newTag = tagInput.value.trim().toLowerCase();

        if (newTag && !currentSearchTags.includes(newTag)) {
            currentSearchTags.push(newTag);
            tagInput.value = '';
            renderCurrentTags();
            searchNotes(true);
        }
    }
}

window.addTagOnEnter = addTagOnEnter;

function renderCurrentTags() {
    const tagsContainer = document.getElementById('current-tags');
    tagsContainer.innerHTML = '';

    currentSearchTags.forEach(tag => {
        const tagSpan = document.createElement('span');
        tagSpan.className = 'tag';
        tagSpan.innerHTML = `
            ${tag}
            <button class="tag-remove" onclick="removeSearchTag('${tag}')">&times;</button>
        `;
        tagsContainer.appendChild(tagSpan);
    });
}

function removeSearchTag(tagToRemove) {
    currentSearchTags = currentSearchTags.filter(tag => tag !== tagToRemove);
    renderCurrentTags();
    searchNotes(true);
}

window.removeSearchTag = removeSearchTag;

/**
 * Performs the note search/filter via API.
 * @param {boolean} resetPagination 
 */
async function searchNotes(resetPagination = false) {
    if (resetPagination) {
        currentNoteOffset = 0;
        displayedAvailableNotes = [];
    }

    const searchMessage = document.getElementById('search-message');
    const loadMoreBtn = document.getElementById('load-more-btn');
    if (searchMessage) searchMessage.textContent = 'Searching...';
    if (loadMoreBtn) loadMoreBtn.style.display = 'none';

    try {
        const root = window.ROOT || '';
        const response = await fetch(`${root}topics/api/search_notes`, {
            method: 'POST',
            body: JSON.stringify({
                tags: currentSearchTags,
                offset: currentNoteOffset,
                limit: NOTES_LIMIT,
                query: '' // Can be extended to support text search input if added
            }),
            headers: {
                'Content-Type': 'application/json'
            }
        });

        const result = await response.json();

        if (result.success) {
            if (resetPagination) {
                displayedAvailableNotes = result.notes || [];
            } else {
                displayedAvailableNotes.push(...(result.notes || []));
            }

            // Filter out notes that are already selected to move
            // This is a client-side filter to avoid showing selected notes in available list
            // Note: Ideally backend handles 'where_not_in' but client side is fine for small lists

            // Check if there are more
            totalAvailableNotes = displayedAvailableNotes.length + (result.has_more ? 1 : 0); // Simplified has_more check

            // Update offset
            if (result.notes.length > 0) {
                currentNoteOffset += result.notes.length;
            }

            renderAvailableNotes(result.has_more);
        } else {
            console.error("Search failed:", result.message);
        }

    } catch (error) {
        console.error('Error searching notes:', error);
    } finally {
        if (searchMessage) searchMessage.textContent = '';
    }
}

window.searchNotes = searchNotes;

function loadMoreData() {
    searchNotes(false);
}

window.loadMoreData = loadMoreData;
// Map the old mock name if used in HTML
window.mockLoadMoreData = loadMoreData;

/**
 * Renders the available notes in the left panel.
 */
function renderAvailableNotes(hasMore = false) {
    const listElement = document.getElementById('available-notes-list');
    const loadMoreBtn = document.getElementById('load-more-btn');
    listElement.innerHTML = '';

    // Filter out already selected notes
    const notesToShow = displayedAvailableNotes.filter(n => !selectedNotesToMove.some(sn => sn.id === n.id));

    if (notesToShow.length === 0) {
        listElement.innerHTML = `<li class="note-item caption">No notes found.</li>`;
    } else {
        notesToShow.forEach(note => {
            const listItem = document.createElement('li');
            listItem.className = 'note-item';
            // Assuming backend returns {id, title} - adapting variable names
            const title = note.title || note.name || 'Untitled Note';
            listItem.innerHTML = `
                <span>${title}</span>
                <button class="add-btn" onclick="addNoteToMove(${note.id})">+</button>
            `;
            listElement.appendChild(listItem);
        });
    }

    if (hasMore) {
        loadMoreBtn.style.display = 'block';
    } else {
        loadMoreBtn.style.display = 'none';
    }
}

function addNoteToMove(noteId) {
    // Find note in displayed list
    const note = displayedAvailableNotes.find(n => n.id === noteId);
    if (note && !selectedNotesToMove.some(n => n.id === noteId)) {
        selectedNotesToMove.push(note);
        renderAvailableNotes(); // Re-render to hide added note
        renderAddedNotes();
    }
}

window.addNoteToMove = addNoteToMove;

function removeNoteToMove(noteId) {
    selectedNotesToMove = selectedNotesToMove.filter(note => note.id !== noteId);
    renderAvailableNotes(); // Re-render to show removed note
    renderAddedNotes();
}

window.removeNoteToMove = removeNoteToMove;

function renderAddedNotes() {
    const listElement = document.getElementById('added-notes-list');
    const moveBtn = document.getElementById('move-notes-btn');
    listElement.innerHTML = '';

    selectedNotesToMove.forEach(note => {
        const title = note.title || note.name || 'Untitled Note';
        const listItem = document.createElement('li');
        listItem.className = 'note-item selected';
        listItem.innerHTML = `
            <span>${title}</span>
            <button class="remove-btn" onclick="removeNoteToMove(${note.id})">&times;</button>
        `;
        listElement.appendChild(listItem);
    });

    if (selectedNotesToMove.length > 0) {
        moveBtn.innerText = "Move " + selectedNotesToMove.length + " Notes to Topic";
    } else {
        moveBtn.innerText = "Finish (No Notes Added)";
    }

    if (listElement.children.length === 0) {
        listElement.innerHTML = `<li class="note-item caption">No notes added yet.</li>`;
    }
}

function updateNotesPanels() {
    // Just re-render
    renderAvailableNotes();
    renderAddedNotes();
}

/**
 * Triggers the note moving process via API.
 */
async function moveNotesToNewTopic() {
    if (!currentTopicId) {
        alert("Topic ID missing. Please create a topic first.");
        return;
    }

    if (selectedNotesToMove.length > 0) {
        try {
            const noteIds = selectedNotesToMove.map(n => n.id);
            const root = window.ROOT || '';
            const response = await fetch(`${root}topics/api/move_notes_to_topic`, {
                method: 'POST',
                body: JSON.stringify({
                    new_topic_id: currentTopicId,
                    note_ids: noteIds
                }),
                headers: {
                    'Content-Type': 'application/json'
                }
            });

            const result = await response.json();

            if (result.success) {
                alert(`Successfully moved ${result.notes_moved_count} notes!`);

                // Finalize flow
                closeNotesModal();

                // Reset state
                selectedNotesToMove = [];
                isTopicCreated = false;
                currentTopicId = null;
                document.getElementById('subject-name-input').value = '';
                updateTopicCreationUI();

                // Maybe reload page to show new topic?
                window.location.reload();

            } else {
                alert(`Failed to move notes: ${result.message}`);
            }

        } catch (error) {
            console.error('Error moving notes:', error);
            alert('An error occurred while moving notes.');
        }
    } else {
        // No notes to move, just finish
        closeNotesModal();
        window.location.reload();
    }
}

window.moveNotesToNewTopic = moveNotesToNewTopic;

// --- Initialization ---
document.addEventListener('DOMContentLoaded', () => {
    // Initial setup
    renderAddedNotes();
    updateTopicCreationUI();

    document.getElementById('subject-name-input').addEventListener('input', (e) => {
        validatesubjectName(e.target.value);

    });

    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && document.getElementById('notes-modal').style.display === 'block') {
            closeNotesModal();
        }
    });
});