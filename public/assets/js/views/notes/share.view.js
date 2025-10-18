document.addEventListener('DOMContentLoaded', () => {
    // --- DOM Elements ---
    const form = document.getElementById('share-form');
    const sharedUsersList = document.getElementById('shared-users-list');
    const sharedUsersField = document.getElementById('shared-users-field');
    const searchInput = document.getElementById('user-search-input');
    const suggestionsList = document.getElementById('user-suggestions-list');

    // --- State ---
    const sharedUsers = new Map(); 

    // Initialize with PHP data
    // Note: The PHP data included a 'color' property which we now ignore.
    INITIAL_SHARED_USERS.forEach(user => {
        sharedUsers.set(user.id, user.name);
    });

    // --- MOCK DATA ---
    const MOCK_USER_DB = [
        { id: 'u4', name: 'Charlie' },
        { id: 'u5', name: 'Eva' },
        { id: 'u6', name: 'Frank' },
        { id: 'u7', name: 'Grace' },
        { id: 'u8', name: 'Heidi' },
    ];
    
    MOCK_USER_DB.push(...INITIAL_SHARED_USERS);

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
            // CALL THE NEW FUNCTION HERE
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
        if (!sharedUsers.has(userId)) {
            sharedUsers.set(userId, userName);
            renderSharedUsers();
            searchInput.value = ''; 
            suggestionsList.style.display = 'none';
        } else {
            console.warn(`${userName} is already shared with.`);
        }
    }

    /** Removes a user from the shared list and updates UI. */
    function removeUser(userId) {
        if (sharedUsers.delete(userId)) {
            renderSharedUsers();
        }
    }
    
    // --- MOCK AJAX SEARCH ---

    /** Renders the list of user suggestions. */
    function renderSuggestions(users) {
        suggestionsList.innerHTML = '';
        
        if (users.length === 0) {
            suggestionsList.style.display = 'none';
            return;
        }

        users.forEach(user => {
            const listItem = document.createElement('li');
            listItem.textContent = user.name;
            listItem.dataset.userId = user.id;
            listItem.dataset.userName = user.name;
            
            listItem.addEventListener('click', () => {
                addUser(user.id, user.name);
            });

            suggestionsList.appendChild(listItem);
        });
        
        suggestionsList.style.display = 'block';
    }

    /** Simulates an AJAX call to filter users. */
    function mockSearchUsers(query) {
        query = query.toLowerCase();
        
        const filteredUsers = MOCK_USER_DB
            .filter(user => 
                user.name.toLowerCase().startsWith(query) && !sharedUsers.has(user.id)
            )
            .slice(0, 5); 
            
        renderSuggestions(filteredUsers);
    }

    // --- Event Listeners ---

    searchInput.addEventListener('input', () => {
        const query = searchInput.value.trim();
        if (query.length > 0) {
            mockSearchUsers(query);
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
            } else {
                alert(`User "${searchInput.value}" not found or already shared with.`);
            }
        }
    });

    document.addEventListener('click', (e) => {
        if (!searchInput.contains(e.target) && !suggestionsList.contains(e.target)) {
            suggestionsList.style.display = 'none';
        }
    });

    // Form Submission Handler (Mock)
    form.addEventListener('submit', (e) => {
        e.preventDefault();
        
        updateHiddenField();
        
        const noteId = form.elements['note_id'].value;
        const sharedIds = sharedUsersField.value;

        console.log("--- Share Note Submission ---");
        console.log(`Note ID: ${noteId}`);
        console.log(`User IDs to Share With: ${sharedIds}`);

        if (sharedIds.length > 0) {
             alert(`Successfully requested to share Note ID ${noteId} with IDs: ${sharedIds}`);
        } else {
            alert("No users selected to share with.");
        }
        
    });

    // --- Initialization ---
    renderSharedUsers();
});