// --- Global State and Mock Data ---
let selectedNotesToMove = [];
let currentSearchTags = [];
let isTopicCreated = false; // Topic creation state
let currentNoteOffset = 0; // Offset for pagination
const NOTES_LIMIT = 5; // Limit per batch

// MOCK data: increased to simulate 'next batch'
const mockNotesData = [
    { id: 1, name: "Introduction to Calculus", tags: ["math", "calculus", "university"] },
    { id: 2, name: "Advanced CSS Flexbox", tags: ["webdev", "css", "frontend"] },
    { id: 3, name: "Python for Data Science", tags: ["python", "data", "programming"] },
    { id: 4, name: "The History of Rome", tags: ["history", "rome", "ancient"] },
    { id: 5, name: "Network Security Protocols", tags: ["security", "networking", "it"] },
    { id: 6, name: "Fluid Dynamics Basics", tags: ["physics", "engineering"] },
    { id: 7, name: "Responsive Web Design", tags: ["webdev", "css", "frontend"] },
    { id: 8, name: "Machine Learning with Python", tags: ["python", "data", "ml"] },
    { id: 9, name: "Renaissance Art History", tags: ["history", "art"] },
    { id: 10, name: "Cryptography Essentials", tags: ["security", "math"] },
];
let displayedAvailableNotes = [];
let currentSearchMatch = []; // Variable to hold the full set of notes matching search tags

// --- Section 1: Create Topic Functions ---

/**
 * Updates the UI elements based on the topic creation state.
 */
function updateTopicCreationUI() {
    const topicInput = document.getElementById('topic-name-input');
    const createBtn = document.getElementById('create-topic-btn');

    if (isTopicCreated) {
        // Topic created state
        topicInput.disabled = true;
        createBtn.className = 'button button-secondary';
        createBtn.textContent = 'Undo Topic Creation';
        createBtn.setAttribute('onclick', 'undoTopicCreation()');
    } else {
        // Pre-creation state
        topicInput.disabled = false;
        createBtn.className = 'button button-primary';
        createBtn.textContent = 'Create Topic';
        createBtn.setAttribute('onclick', 'createTopic()');
        // Re-validate to set initial disabled state
        // validateTopicName(topicInput.value); 
    }
}

/**
 * Validates the topic name input and updates the UI accordingly.
 * @param {string} topicName - The current value of the topic name input.
 */
function validateTopicName(topicName) {
    const trimmedName = topicName.trim();
    const createBtn = document.getElementById('create-topic-btn');
    const validationResult = document.getElementById('validation-result');

    if (trimmedName.length === 0) {
        validationResult.textContent = 'Topic name cannot be empty.';
        validationResult.style.color = 'var(--color-error)';
        createBtn.disabled = true;
    } else if (trimmedName.length < 5) {
        validationResult.textContent = 'Topic name must be at least 5 characters long.';
        validationResult.style.color = 'var(--color-error)';
        createBtn.disabled = true;
    } else {
        validationResult.textContent = 'Topic name is valid.';
        validationResult.style.color = 'var(--color-success)';
        createBtn.disabled = false;
    }
}

window.validateTopicName = validateTopicName;

/**
 * MOCK Function to create a new topic and open the modal.
 */
function createTopic() {
    const topicName = document.getElementById('topic-name-input').value;
    
    if (!document.getElementById('create-topic-btn').disabled && !isTopicCreated) {
        console.log(`MOCK: Creating new topic: "${topicName}"`);
        
        isTopicCreated = true;
        updateTopicCreationUI();
        
        // Open the modal after successful mock creation
        openNotesModal();
    }
}

window.createTopic = createTopic;

/**
 * MOCK Function to undo the creation of a topic and close the modal.
 */
function undoTopicCreation() {
    console.log(`MOCK: Undoing creation of topic: "${document.getElementById('topic-name-input').value}"`);
    
    // Close the modal immediately on undo
    closeNotesModal();

    isTopicCreated = false;
    // Clear notes to move when undoing the topic
    selectedNotesToMove = []; 
    updateNotesPanels(); 

    document.getElementById('topic-name-input').value = ''; // Clear input on undo
    validateTopicName(''); // Reset validation message
    updateTopicCreationUI();
}

/**
 * Opens the notes management modal.
 */
function openNotesModal() {
    document.getElementById('notes-modal').style.display = 'block';
    // Prevent scrolling on the main page
    document.body.style.overflow = 'hidden'; 
}

/**
 * Closes the notes management modal.
 */
function closeNotesModal() {
    document.getElementById('notes-modal').style.display = 'none';
    // Restore scrolling on the main page
    document.body.style.overflow = ''; 
}

window.closeNotesModal = closeNotesModal;

// --- Section 2: Notes Management Functions ---

/**
 * Handles adding a tag to the search list when 'Enter' is pressed.
 * @param {Event} event - The keyboard event.
 */
function addTagOnEnter(event) {
    if (event.key === 'Enter') {
        event.preventDefault(); // Stop the default 'Enter' action
        const tagInput = document.getElementById('tag-input');
        const newTag = tagInput.value.trim().toLowerCase();
        
        if (newTag && !currentSearchTags.includes(newTag)) {
            currentSearchTags.push(newTag);
            tagInput.value = '';
            renderCurrentTags();
            searchNotes(true); // Search with new tag, reset pagination
        }
    }
}

window.addTagOnEnter = addTagOnEnter;

/**
 * Renders the list of current search tags.
 */
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

/**
 * Removes a tag from the search list.
 * @param {string} tagToRemove - The tag to remove.
 */
function removeSearchTag(tagToRemove) {
    currentSearchTags = currentSearchTags.filter(tag => tag !== tagToRemove);
    renderCurrentTags();
    searchNotes(true); // Re-run search without the removed tag, reset pagination
}

window.removeSearchTag = removeSearchTag; 

/**
 * MOCK Function to perform the note search/filter.
 * @param {boolean} resetPagination - Whether to reset the offset/displayed notes.
 */
function searchNotes(resetPagination = false) {
    console.log("MOCK: Searching notes with tags:", currentSearchTags);

    let filteredNotes = [];

    if (currentSearchTags.length === 0) {
        // If no tags, include all mock notes
        filteredNotes = mockNotesData;
    } else {
        // Filter notes where ALL currentSearchTags are present in the note's tags
        filteredNotes = mockNotesData.filter(note => 
            currentSearchTags.every(tag => note.tags.includes(tag))
        );
    }
    
    // Store all notes matching the current search for pagination
    currentSearchMatch = filteredNotes.filter(note => 
        !selectedNotesToMove.some(n => n.id === note.id)
    );

    if (resetPagination) {
        currentNoteOffset = 0;
        displayedAvailableNotes = [];
    }

    // Call mockLoadMoreData to get the first/next batch
    mockLoadMoreData(); 
}

window.searchNotes = searchNotes;

/**
 * MOCK function to simulate loading the next batch of notes.
 */
function mockLoadMoreData() {
    const nextBatch = currentSearchMatch.slice(currentNoteOffset, currentNoteOffset + NOTES_LIMIT);
    
    // Add the new batch to the currently displayed notes
    displayedAvailableNotes.push(...nextBatch);

    // Update the offset for the next load
    currentNoteOffset += nextBatch.length;

    renderAvailableNotes();
}

window.mockLoadMoreData = mockLoadMoreData;
/**
 * Renders the available notes in the left panel.
 */
function renderAvailableNotes() {
    const listElement = document.getElementById('available-notes-list');
    const loadMoreBtn = document.getElementById('load-more-btn');
    listElement.innerHTML = '';

    if (displayedAvailableNotes.length === 0 && currentSearchTags.length > 0) {
        listElement.innerHTML = `<li class="note-item caption">No notes found for tags: ${currentSearchTags.join(', ')}.</li>`;
    } else if (displayedAvailableNotes.length === 0 && currentSearchTags.length === 0) {
        listElement.innerHTML = `<li class="note-item caption">No notes available.</li>`;
    } else {
        displayedAvailableNotes.forEach(note => {
            const listItem = document.createElement('li');
            listItem.className = 'note-item';
            listItem.innerHTML = `
                <span>${note.name}</span>
                <button class="add-btn" onclick="addNoteToMove(${note.id})">+</button>
            `;
            listElement.appendChild(listItem);
        });
    }

    // Show/Hide Load More button
    if (currentNoteOffset < currentSearchMatch.length) {
        loadMoreBtn.style.display = 'block';
        loadMoreBtn.textContent = `Load More Notes (${currentSearchMatch.length - currentNoteOffset} remaining)`;
    } else {
        loadMoreBtn.style.display = 'none';
    }
}

/**
 * Moves a note from the available list to the selected list.
 * @param {number} noteId - The ID of the note to move.
 */
function addNoteToMove(noteId) {
    const note = mockNotesData.find(n => n.id === noteId);
    if (note && !selectedNotesToMove.some(n => n.id === noteId)) {
        selectedNotesToMove.push(note);
        updateNotesPanels();
        console.log(`Note added: ${note.name}`);
    }
}

window.addNoteToMove = addNoteToMove;

/**
 * Removes a note from the selected list and back to available.
 * @param {number} noteId - The ID of the note to remove.
 */
function removeNoteToMove(noteId) {
    selectedNotesToMove = selectedNotesToMove.filter(note => note.id !== noteId);
    updateNotesPanels();
    console.log(`Note removed: ID ${noteId}`);
}

window.removeNoteToMove = removeNoteToMove;

/**
 * Renders the selected notes in the right panel.
 */
function renderAddedNotes() {
    const listElement = document.getElementById('added-notes-list');
    const moveBtn = document.getElementById('move-notes-btn');
    listElement.innerHTML = '';

    selectedNotesToMove.forEach(note => {
        const listItem = document.createElement('li');
        listItem.className = 'note-item selected';
        listItem.innerHTML = `
            <span>${note.name}</span>
            <button class="remove-btn" onclick="removeNoteToMove(${note.id})">&times;</button>
        `;
        listElement.appendChild(listItem);
    });

    // Enable/Disable the move button
    if (selectedNotesToMove.length > 0)
    {
        moveBtn.innerText = "Move " + selectedNotesToMove.length + " Notes to Topic";

    }else
    {
        moveBtn.innerText = "Continue witout Moving Notes";
    }

    if (listElement.children.length === 0) {
         listElement.innerHTML = `<li class="note-item caption">No notes added yet.</li>`;
    }
}

/**
 * Updates both the available and added notes panels.
 */
function updateNotesPanels() {
    // Re-run the search to filter out the added notes from the available list
    // and reset pagination so the available list is correctly filtered
    searchNotes(true); 
    renderAddedNotes();
}

/**
 * MOCK Function to trigger the note moving process.
 */
function moveNotesToNewTopic() {
    if (selectedNotesToMove.length > 0) {
        const topicName = document.getElementById('topic-name-input').value;
        console.log(`MOCK: Moving ${selectedNotesToMove.length} notes to topic: "${topicName}"`);
        alert(`MOCK: Successfully moved ${selectedNotesToMove.length} notes to "${topicName}"!`);
        
        // Close the modal after the action is complete
        closeNotesModal();

        // Reset the state after moving
        selectedNotesToMove = [];
        updateNotesPanels();
        
        // Reset the topic creation state
        undoTopicCreation(); 
    }
}

window.moveNotesToNewTopic = moveNotesToNewTopic;

// --- Initialization ---
document.addEventListener('DOMContentLoaded', () => {
    // Initial load: search for all notes (empty tags), which will trigger pagination
    searchNotes(true); 
    renderAddedNotes(); // Initialize the empty added list
    updateTopicCreationUI(); // Initialize topic creation UI
    
    // Handle closing the modal with the ESC key
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && document.getElementById('notes-modal').style.display === 'block') {
            closeNotesModal();
        }
    });
});