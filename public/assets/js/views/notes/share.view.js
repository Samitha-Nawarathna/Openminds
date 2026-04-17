import { ROOT } from "../../core/config";

document.addEventListener('DOMContentLoaded', () => {
    // --- DOM Elements ---
    const form = document.getElementById('share-form');
    const sharedUsersList = document.getElementById('shared-users-list');
    const sharedUsersField = document.getElementById('shared-users-field');
    const searchInput = document.getElementById('user-search-input');
    const suggestionsList = document.getElementById('user-suggestions-list');

    // --- Configuration ---
    // const ROOT = ROOT || '/';

    // --- State ---
    const sharedUsers = new Map();

    // Initialize with PHP data
    // Note: The PHP data included a 'color' property which we now ignore.
    if (typeof INITIAL_SHARED_USERS !== 'undefined') {
        INITIAL_SHARED_USERS.forEach(user => {
            sharedUsers.set(user.id.toString(), user.name);
        });
    }

    // --- Utility Functions ---

    /** * Generates a simple, consistent color class for a user pill based on name. 
     * This implementation sums character codes of the username to get a consistent index.
     */
    function getUserPillClass(name) {
        const colors = ['red', 'blue', 'green', 'pink', 'purple', 'orange'];

        // Calculate a numeric hash based on the name string
        let sum = 0;
        for (let i = 0; i < name.length; i++) {
            sum += name.charCodeAt(i);
        }

        // Use the hash modulo the number of colors to get a consistent index
        const index = sum % colors.length;
        return 'user-pill-' + colors[index];
    }

    /** Updates the hidden field with a comma-separated list of User IDs. */
    function updateHiddenField() {
        sharedUsersField.value = Array.from(sharedUsers.keys()).join(',');
    }

    /** Renders or re-renders the list of user pills. */
    function renderSharedUsers() {
        sharedUsersList.innerHTML = '';

        sharedUsers.forEach((name, id) => {
            const pill = document.createElement('span');
            pill.classList.add('user-pill', getUserPillClass(name));
            pill.dataset.userId = id;
            pill.innerHTML = `${name} <span class="remove-user">&times;</span>`;

            pill.querySelector('.remove-user').addEventListener('click', () => {
                removeUser(id);
            });
            sharedUsersList.appendChild(pill);
        });

        updateHiddenField();
    }

    // --- Core User Management Functions ---

    /** Adds a user to the shared list and updates UI. */
    function addUser(userId, userName) {
        if (!sharedUsers.has(userId.toString())) {
            sharedUsers.set(userId.toString(), userName);
            renderSharedUsers();
            searchInput.value = '';
            suggestionsList.style.display = 'none';
        } else {
            console.warn(`${userName} is already shared with.`);
        }
    }

    /** Removes a user from the shared list and updates UI. */
    function removeUser(userId) {
        if (sharedUsers.delete(userId.toString())) {
            renderSharedUsers();
        }
    }

    // --- REAL AJAX SEARCH ---

    /** Renders the list of user suggestions. */
    function renderSuggestions(users) {
        suggestionsList.innerHTML = '';

        if (!users || users.length === 0) {
            suggestionsList.style.display = 'none';
            return;
        }

        users.forEach(user => {
            const listItem = document.createElement('li');
            listItem.className = 'suggestion-item';

            // User Avatar (Small)
            const avatar = document.createElement('img');
            // avatar.src = user.profile_picture ? (ROOT + user.profile_picture) : (ROOT + 'assets/images/placeholder.jpg');
            avatar.className = 'suggestion-avatar';
            // avatar.onerror = () => { avatar.src = ROOT + 'assets/images/placeholder.jpg'; };

            // User Info Container
            const info = document.createElement('div');
            info.className = 'suggestion-info';

            const username = document.createElement('div');
            username.textContent = user.username;
            username.className = 'suggestion-username';

            const displayName = document.createElement('div');
            displayName.textContent = user.display_name || '';
            displayName.className = 'suggestion-displayname';

            info.appendChild(username);
            info.appendChild(displayName);

            listItem.appendChild(avatar);
            listItem.appendChild(info);

            listItem.addEventListener('click', () => {
                addUser(user.id, user.username);
            });

            suggestionsList.appendChild(listItem);
        });

        suggestionsList.style.display = 'block';
    }

    /** Real AJAX call to sample users. */
    async function searchUsers(query) {
        if (!query) {
            suggestionsList.style.display = 'none';
            return;
        }

        try {
            const response = await fetch(`${ROOT}users/api/search_by_name`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ query: query })
            });
            const data = await response.json();

            // Filter out users who are already in the shared map
            const filteredResults = (data.users || []).filter(u => !sharedUsers.has(u.id.toString()));
            renderSuggestions(filteredResults);

        } catch (error) {
            console.error('Error searching users:', error);
        }
    }

    // --- Event Listeners ---

    searchInput.addEventListener('input', () => {
        const query = searchInput.value.trim();
        if (query.length >= 1) { // Reduced threshold for better UX
            searchUsers(query);
        } else {
            suggestionsList.style.display = 'none';
        }
    });

    searchInput.addEventListener('keydown', (e) => {
        if (e.key === 'Enter') {
            e.preventDefault();
            const firstSuggestion = suggestionsList.querySelector('li');
            if (firstSuggestion) {
                addUser(firstSuggestion.dataset.userId, firstSuggestion.dataset.userName);
            }
        }
    });

    document.addEventListener('click', (e) => {
        if (!searchInput.contains(e.target) && !suggestionsList.contains(e.target)) {
            suggestionsList.style.display = 'none';
        }
    });

    // Form Submission Handler
    form.addEventListener('submit', async (e) => {
        e.preventDefault();

        updateHiddenField();

        const noteId = form.elements['note_id'].value;
        const sharedIds = sharedUsersField.value.split(',').filter(id => id.length > 0);

        if (sharedIds.length === 0) {
            alert("Please select at least one user to share with.");
            return;
        }

        try {
            const response = await fetch(`${ROOT}notes/api/share`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    note_id: noteId,
                    share_with_user_ids: sharedIds
                })
            });

            const result = await response.json();

            if (result.status === 'success') {
                alert("Note shared successfully!");
                // Optionally redirect back to the note
                window.location.href = `${ROOT}notes/view/${noteId}`;
            } else {
                alert("Error sharing note: " + (result.message || "Unknown error"));
            }
        } catch (error) {
            console.error('Error submitting share:', error);
            alert("An error occurred while sharing the note.");
        }
    });

    // --- Initialization ---
    renderSharedUsers();
});