<?php

    $title = "Create Topic | Openminds";
    $filename = "notes/create_title";
    $add_back = true;

    include_once "../app/views/partials/header.view.php";

?>

<div class="main-content-container"> 
        <section id="create-topic-section">
            <h2 class="title">Create Topic</h2>
            <label for="topic-name-input">Enter the topic name:</label>
            <input type="text" id="topic-name-input" placeholder="e.g., Quantum Physics Basics">
            <p id="validation-result" class="validation-message"></p>
            <button id="create-topic-btn" class="button button-primary" onclick="window.createTopic()" disabled>Create Topic</button>
        </section>

        </div>

    <div id="notes-modal" class="modal">
        <div class="modal-content">
            <button class="close-btn" onclick="window.closeNotesModal()">&times;</button>
            <section id="move-notes-section" class="move-notes-section">
                <h2>📂 Search Notes & Assign</h2>
                <div class="panels-container">
                    <div class="panel left-panel">
                        <h3>Available Notes</h3>
                        <div class="search-bar-container">
                            <input type="text" id="tag-input" placeholder="Type tag and press Enter" onkeydown="window.addTagOnEnter(event)">
                            <button class="btn-filter" onclick="window.searchNotes(true)">Search</button>
                        </div>

                        <div id="current-tags"></div>

                        <h4>Search Results:</h4>
                        <ul id="available-notes-list" class="notes-list">
                            <li class="note-item empty-state">Search for notes to assign.</li>
                        </ul>
                        <div id="load-more-container" class="text-center" style="padding-top: var(--space-sm);">
                            <button id="load-more-btn" class="button-secondary" onclick="window.loadMoreNotes()" style="width: 100%; display: none;">
                                Load More Notes
                            </button>
                            <p id="search-message" class="validation-message"></p>
                        </div>
                        
                    </div>

                    <div class="panel right-panel">
                        <h3>Notes to Move</h3>
                        <ul id="added-notes-list" class="notes-list">
                             <li class="note-item empty-state">Selected notes will appear here.</li>
                            </ul>
                    </div>
                </div>
                
                <div id="move-notes-btn-container">
                    <button id="move-notes-btn" class="button button-primary" onclick="window.moveNotesToNewTopic()">Continue without Moving Notes</button>
                </div>
                
            </section>
        </div>
    </div>
    
<script>

    const ROOT = "<?=ROOT?>/";

(function() {
    const STATE = {
        currentTopicId: null,
        currentTopicName: '',
        searchTags: [],
        searchQuery: '',
        nextOffset: 0,
        notesToMove: new Set(), // Store IDs of notes selected to move
        loading: false
    };

    /**
     * Generic wrapper for making AJAX requests.
     * @param {string} endpoint The API endpoint (e.g., 'topic/api_create')
     * @param {Object} data The data payload (will be stringified to JSON)
     * @returns {Promise<Object>} The JSON response body
     */
    async function apiRequest(endpoint, data = {}, method = 'POST') {
        try {
            if (method === 'GET') {
                if (Object.keys(data).length > 0) {
                    const params = new URLSearchParams(data).toString();
                    endpoint += `?${params}`;
                }

                // For GET requests, data is sent as query parameters
                data = null;

                //fetch the get request
                const response = await fetch(endpoint, {
                    method: 'GET',
                    headers: {
                        'Accept': 'application/json'
                    }
                });

                // console.log("endpoint: ", endpoint);
                // console.log(await response.text()); // Consume response to avoid memory leaks

                return await response.json();

            }


            const response = await fetch(endpoint, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                body: JSON.stringify(data)
            });

            console.log("data: ", JSON.stringify(data));
            // console.log(await response.text()); // Consume response to avoid memory leaks
            return await response.json();

        } catch (error) {
            console.error('API Request Error:', error);
            return { success: false, message: 'Network or server error.' };
        }
    }

    /**
     * STEP 1: Client-side validation check. Calls backend for availability.
     */
    window.validateTopicName = async function(name) {
        // const name = document.getElementById('topic-name-input');
        const inputField = document.getElementById('topic-name-input');
        const validationResult = document.getElementById('validation-result');
        const createButton = document.getElementById('create-topic-btn');
        const topicName = name.trim();

        if (topicName.length < 3) {
            validationResult.className = 'validation-message invalid';
            validationResult.textContent = 'Topic name must be at least 3 characters.';
            createButton.disabled = true;
            return;
        }

        if (topicName.length > 100) {
            validationResult.className = 'validation-message invalid';
            validationResult.textContent = 'Topic name is too long (max 100 chars).';
            createButton.disabled = true;
            return;
        }

        console.log("Name: ", topicName);

        // Call the backend API for availability check
        const result = await apiRequest(`${ROOT}topics/api/is_name_available/${topicName}`, {}, 'GET');

        if (result.available) {
            validationResult.className = 'validation-message valid';
            validationResult.textContent = result.message;
            createButton.disabled = false;
        } else {
            validationResult.className = 'validation-message invalid';
            validationResult.textContent = result.message;
            createButton.disabled = true;
        }
    }

    document.getElementById('topic-name-input').addEventListener('input', function() {
        window.validateTopicName(this.value);
    });

    /**
     * STEP 2: Create the topic and show the modal on success.
     */
    window.createTopic = async function() {
        const topicName = document.getElementById('topic-name-input').value.trim();
        const createButton = document.getElementById('create-topic-btn');
        const validationResult = document.getElementById('validation-result');

        if (createButton.disabled || STATE.loading) return;

        STATE.loading = true;
        createButton.textContent = 'Creating...';
        validationResult.textContent = '';

        const result = await apiRequest(`${ROOT}topics/api/create`, { name: topicName });
        
        STATE.loading = false;
        createButton.textContent = 'Create Topic';

        if (result.success) {
            STATE.currentTopicId = result.topic_id;
            STATE.currentTopicName = result.name;

            validationResult.className = 'validation-message valid';
            validationResult.textContent = `Topic '${result.name}' created successfully!`;
            
            // Show the modal and run the initial note search
            document.getElementById('notes-modal').style.display = 'flex';
            document.getElementById('move-notes-btn').textContent = `Move ${STATE.notesToMove.size} Notes and Continue`;
            searchNotes(true); // Initial search
        } else {
            validationResult.className = 'validation-message invalid';
            validationResult.textContent = result.message || 'Creation failed.';
        }
    }

    /**
     * Modal Management and Note Search Logic
     */
    
    // Renders the list of tags
    function renderTags() {
        const tagContainer = document.getElementById('current-tags');
        tagContainer.innerHTML = '';
        STATE.searchTags.forEach(tag => {
            const tagEl = document.createElement('span');
            tagEl.className = 'tag';
            tagEl.textContent = tag;
            const removeBtn = document.createElement('span');
            removeBtn.innerHTML = '&times;';
            removeBtn.onclick = () => window.removeTag(tag);
            tagEl.appendChild(removeBtn);
            tagContainer.appendChild(tagEl);
        });
    }

    // Adds a tag from the input box
    window.addTagOnEnter = function(event) {
        if (event.key === 'Enter') {
            const input = event.target;
            const tag = input.value.trim().toLowerCase();
            if (tag && !STATE.searchTags.includes(tag)) {
                STATE.searchTags.push(tag);
                renderTags();
                input.value = '';
                searchNotes(true); // Re-run search after adding tag
            }
            event.preventDefault();
        }
    }
    
    // Removes a tag from the search criteria
    window.removeTag = function(tag) {
        STATE.searchTags = STATE.searchTags.filter(t => t !== tag);
        renderTags();
        searchNotes(true); // Re-run search after removing tag
    }

    // Handles the core note search functionality (initial search or 'Load More')
    // const result = await apiRequest(`${ROOT}topics/api/search_notes`, {
    //     tags: STATE.searchTags,
    //     query: STATE.searchQuery,
    //     offset: STATE.nextOffset,
    //     limit: 10 // Page size
    // });
    window.searchNotes = async function(isNewSearch = false) {
        if (STATE.loading) return;
        
        const noteList = document.getElementById('available-notes-list');
        const loadMoreBtn = document.getElementById('load-more-btn');
        const searchMessage = document.getElementById('search-message');
        
        if (isNewSearch) {
            STATE.nextOffset = 0;
            noteList.innerHTML = '<li class="note-item empty-state">Searching...</li>';
            loadMoreBtn.style.display = 'none';
        }
        
        STATE.loading = true;
        searchMessage.textContent = 'Loading...';
        loadMoreBtn.disabled = true;

        const queryInput = document.getElementById('tag-input');
        STATE.searchQuery = queryInput.value.trim();
        
        const result = await apiRequest(`${ROOT}topics/api/search_notes`, {
            tags: STATE.searchTags,
            query: STATE.searchQuery,
            offset: STATE.nextOffset,
            limit: 10 // Page size
        });

        STATE.loading = false;
        searchMessage.textContent = '';
        loadMoreBtn.disabled = false;
        
        if (result.success) {
            // If it's a new search, clear the "Searching..." message
            if (isNewSearch) {
                noteList.innerHTML = '';
                if (result.notes.length === 0) {
                     noteList.innerHTML = '<li class="note-item empty-state">No notes found matching criteria.</li>';
                }
            } else {
                 // Remove the "Searching..." placeholder if it somehow survived a previous call
                const placeholder = noteList.querySelector('.empty-state');
                if (placeholder) placeholder.remove();
            }

            result.notes.forEach(note => {
                // Only add notes that haven't been selected yet
                if (!STATE.notesToMove.has(note.id)) {
                    noteList.appendChild(createNoteListItem(note, true));
                }
            });

            STATE.nextOffset = result.next_offset;
            loadMoreBtn.style.display = result.has_more ? 'block' : 'none';
        } else {
            noteList.innerHTML = '<li class="note-item empty-state">Error loading notes.</li>';
            searchMessage.textContent = result.message || 'An error occurred during search.';
        }
    }

    // Maps the 'Load More Notes' button to the search function
    window.loadMoreNotes = function() {
        searchNotes(false);
    }
    
    // Creates an HTML list item for a note
    function createNoteListItem(note, available) {
        const li = document.createElement('li');
        li.className = 'note-item';
        li.setAttribute('data-note-id', note.id);
        li.innerHTML = `
            <span>${note.title}</span>
            <span class="note-tags">${note.tags.map(t => `<span class="tag tag-small">${t}</span>`).join('')}</span>
            <button class="${available ? 'add-btn' : 'remove-btn'}" 
                    onclick="window.${available ? 'addNoteToMove' : 'removeNoteToMove'}(${note.id}, '${note.title}')">
                ${available ? 'Add' : 'Remove'}
            </button>
        `;
        return li;
    }

    // Moves a note from the Available list to the To Move list
    window.addNoteToMove = function(id, title) {
        if (STATE.notesToMove.has(id)) return;
        
        STATE.notesToMove.add(id);
        const availableList = document.getElementById('available-notes-list');
        const addedList = document.getElementById('added-notes-list');
        const noteElement = availableList.querySelector(`[data-note-id="${id}"]`);

        // Remove from available list and re-add to added list
        if (noteElement) noteElement.remove();
        
        // Remove empty state from target list
        addedList.querySelectorAll('.empty-state').forEach(el => el.remove());
        
        const note = { id: id, title: title, tags: [] }; // Tags can be omitted here for simplicity
        addedList.appendChild(createNoteListItem(note, false));
        
        // Update the 'Continue' button text
        document.getElementById('move-notes-btn').textContent = `Move ${STATE.notesToMove.size} Notes and Continue`;
    }

    // Moves a note from the To Move list back to the Available list
    window.removeNoteToMove = function(id, title) {
        if (!STATE.notesToMove.has(id)) return;

        STATE.notesToMove.delete(id);
        const addedList = document.getElementById('added-notes-list');
        const noteElement = addedList.querySelector(`[data-note-id="${id}"]`);
        
        if (noteElement) noteElement.remove();

        // If the note was part of the original search results, re-add it to the Available list
        // For simplicity in this mock, we just re-run the search to refresh the available list
        searchNotes(true); 
        
        // Re-add empty state if needed
        if (addedList.children.length === 0) {
            const li = document.createElement('li');
            li.className = 'note-item empty-state';
            li.textContent = 'Selected notes will appear here.';
            addedList.appendChild(li);
        }

        // Update the 'Continue' button text
        document.getElementById('move-notes-btn').textContent = `Move ${STATE.notesToMove.size} Notes and Continue`;
    }


    /**
     * STEP 3: Final action to move notes and complete the flow.
     */
    window.moveNotesToNewTopic = async function() {
        const moveButton = document.getElementById('move-notes-btn');
        if (STATE.loading) return;
        
        STATE.loading = true;
        moveButton.textContent = 'Processing...';

        const noteIds = Array.from(STATE.notesToMove);

        // STEP 3: Final action to move notes and complete the flow.
        const result = await apiRequest(`${ROOT}topics/api/move_notes_to_topic`, {
            new_topic_id: STATE.currentTopicId,
            note_ids: noteIds
        });

        STATE.loading = false;
        
        if (result.success) {
            // Success: Redirect or show confirmation
            alert(`Topic '${STATE.currentTopicName}' created. ${result.notes_moved_count} notes were assigned. Redirecting...`);
            // In a real app, you would redirect: 
            // window.location.href = `/topic/${STATE.currentTopicId}/dashboard`;
            window.closeNotesModal(); // Close modal on success for this example
        } else {
            // Error handling
            alert(`Error moving notes: ${result.message}`);
            moveButton.textContent = 'Retry Move & Continue';
        }
    }
    
    // Closes the modal and resets state (useful if the user cancels)
    window.closeNotesModal = function() {
        document.getElementById('notes-modal').style.display = 'none';
        // You might want to reset STATE here if the creation process is being abandoned.
    }

})();
</script>

<?php
    include_once "../app/views/partials/footer.view.php";
?>