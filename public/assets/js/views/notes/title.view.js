import { ROOT } from '../../core/config.js';

function scrollCards(direction) {
    const wrapper = document.getElementById('cardsWrapper');
    // Distance to scroll (e.g., width of 3 cards + gap)
    const scrollDistance = wrapper.offsetWidth / 2;

    // Calculate the new scroll position
    const newScrollLeft = wrapper.scrollLeft + (direction * scrollDistance);

    // Perform the smooth scroll
    wrapper.scrollTo({
        left: newScrollLeft,
        behavior: 'smooth' // Makes the scroll animated
    });
}

// Optional: You could add a 'left' button and logic for showing/hiding buttons
// based on scroll position, but this covers the core requirement.

document.addEventListener('DOMContentLoaded', () => {
    // --- Global Configuration & State ---
    const ITEMS_PER_LOAD = 10;
    let currentOffset = 0; // The initial offset is determined by the PHP file

    // --- DOM Elements ---
    const loadMoreBtn = document.getElementById('load-more-btn');
    const listContainer = document.getElementById('subjects-list');
    const filterInput = document.getElementById('topic-filter-input');
    const filterBtn = document.getElementById('filter-btn');

    // Initialize the offset based on the initial PHP render (if available)
    currentOffset = listContainer.children.length;

    // --- API FUNCTIONS ---

    async function apiPinTopic(topicId) {
        try {
            const response = await fetch(`${ROOT}topics/api/pin/${topicId}`, {
                method: 'POST'
            });
            const data = await response.json();
            if (data.success) {
                window.location.reload(); // Simple refresh to update lists
            } else {
                alert('Failed to pin topic: ' + (data.message || 'Unknown error'));
            }
        } catch (error) {
            console.error('Error pinning topic:', error);
            alert('An error occurred while pinning the topic.');
        }
    }

    async function apiUnpinTopic(topicId) {
        try {
            const response = await fetch(`${ROOT}topics/api/unpin/${topicId}`, {
                method: 'POST'
            });
            const data = await response.json();
            if (data.success) {
                window.location.reload();
            } else {
                alert('Failed to unpin topic: ' + (data.message || 'Unknown error'));
            }
        } catch (error) {
            console.error('Error unpinning topic:', error);
            alert('An error occurred while unpinning the topic.');
        }
    }

    async function apiDeleteTopic(topicId) {
        if (!confirm('Are you sure you want to delete this subject?')) return;
        try {
            const response = await fetch(`${ROOT}topics/api/delete/${topicId}`, {
                method: 'POST'
            });
            const data = await response.json();
            if (data.success) {
                window.location.reload();
            } else {
                alert('Failed to delete topic: ' + (data.message || 'Unknown error'));
            }
        } catch (error) {
            console.error('Error deleting topic:', error);
            alert('An error occurred while deleting the topic.');
        }
    }

    async function apiRenameTopic(topicId, currentName) {
        const newName = prompt('Enter new topic name:', currentName);
        if (!newName || newName.trim() === '' || newName === currentName) return;
        
        try {
            const response = await fetch(`${ROOT}topics/api/rename/${topicId}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ name: newName.trim() })
            });
            const data = await response.json();
            if (data.success) {
                // Alternatively, we could just update the DOM element, but reload is consistent with pin/unpin/delete
                window.location.reload(); 
            } else {
                alert('Failed to rename topic: ' + (data.message || 'Unknown error'));
            }
        } catch (error) {
            console.error('Error renaming topic:', error);
            alert('An error occurred while renaming the topic.');
        }
    }

    // --- MOCK AJAX function (Updated to use search endpoint if available or stick to load_more) ---
    // Keeping mockFetchTopics for load more functionality as requested, but logic remains same.
    async function mockFetchTopics(offset, filterName) {
        console.log(`[API CALL] Fetching topics from offset ${offset} with filter: "${filterName}"`);

        try {
            // Using search mechanism or load_more 
            const response = await fetch(`${ROOT}topics/api/load_more?offset=${offset}&filter=${encodeURIComponent(filterName)}`);
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            const data = await response.json();
            return {
                topics: data.topics,
                has_more: data.has_more
            };
        } catch (error) {
            console.error("Error fetching topics:", error);
            return {
                topics: [],
                has_more: false
            };
        }
    }

    // --- RENDERING FUNCTIONS ---

    /** Redirect on topic click */
    function viewTopic(topicId) {
        window.location.href = ROOT + "/notes/list/" + topicId;
    }

    /** Creates the HTML structure for a single topic item. */
    /** Creates the HTML structure for a single topic item. */
    function createTopicItem(topic) {
        const item = document.createElement('div');
        item.classList.add('topic-item');
        item.dataset.id = topic.id;

        // 1. Topic Info (Link)
        const link = document.createElement('a');
        link.href = `${ROOT}/notes/list/${topic.id}`;
        link.className = 'topic-info';

        const nameSpan = document.createElement('span');
        nameSpan.className = 'topic-name';
        nameSpan.textContent = topic.name;

        const countSpan = document.createElement('span');
        countSpan.className = 'note-count';
        const count = topic.note_count !== undefined ? topic.note_count : 0;
        countSpan.textContent = `${count} ${count === 1 ? 'Note' : 'Notes'}`;

        link.appendChild(nameSpan);
        link.appendChild(countSpan);
        item.appendChild(link);

        // 2. Topic Actions
        const actionsDiv = document.createElement('div');
        actionsDiv.className = 'topic-actions';

        const renameBtn = document.createElement('button');
        renameBtn.className = 'action-btn rename-btn';
        renameBtn.title = 'Rename Topic';
        renameBtn.dataset.id = topic.id;
        renameBtn.dataset.name = topic.name;
        renameBtn.style.color = '#666';
        renameBtn.style.stroke = '#666';
        renameBtn.innerHTML = `<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="pointer-events: none;"><path d="M12 20h9"></path><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"></path></svg>`;

        const deleteBtn = document.createElement('button');
        deleteBtn.className = 'action-btn delete-btn';
        deleteBtn.title = 'Delete Topic';
        deleteBtn.dataset.id = topic.id;
        deleteBtn.style.color = 'red';
        deleteBtn.style.stroke = 'red';
        deleteBtn.innerHTML = `<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="pointer-events: none;"><path d="M3 6h18"></path><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path><line x1="10" y1="11" x2="10" y2="17"></line><line x1="14" y1="11" x2="14" y2="17"></line></svg>`;

        const pinBtn = document.createElement('button');
        pinBtn.className = 'action-btn pin-btn';
        pinBtn.title = 'Pin Topic';
        pinBtn.dataset.id = topic.id;

        // Pin Icon SVG
        pinBtn.innerHTML = `<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="pointer-events: none;"><line x1="12" y1="17" x2="12" y2="22"></line><path d="M5 17h14v-1.76a2 2 0 0 0-1.11-1.79l-1.78-.9A2 2 0 0 1 15 10.76V6h1a2 2 0 0 0 0-4H8a2 2 0 0 0 0 4h1v4.76a2 2 0 0 1-1.11 1.79l-1.78.9A2 2 0 0 0 5 15.24Z"></path></svg>`;

        actionsDiv.appendChild(renameBtn);
        actionsDiv.appendChild(deleteBtn);
        actionsDiv.appendChild(pinBtn);
        item.appendChild(actionsDiv);

        return item;
    }

    /** Clears the list and updates with new data (used for Filter or initial load). */
    async function loadTopics(offset, isInitialLoad) {
        const filterName = filterInput.value.trim();

        // Only show loading if it's not the initial PHP-rendered content
        if (!isInitialLoad) {
            listContainer.innerHTML = '<div class="loading">Loading topics...</div>';
            loadMoreBtn.style.display = 'none';
        }

        try {
            const response = await mockFetchTopics(offset, filterName);

            // If it's not the initial load, clear the container
            if (!isInitialLoad) {
                listContainer.innerHTML = '';
            }

            if (response.topics.length === 0 && offset === 0) {
                listContainer.innerHTML = '<div class="loading">No topics found.</div>';
            } else {
                response.topics.forEach(t => listContainer.appendChild(createTopicItem(t)));

                // Update offset
                currentOffset = offset + response.topics.length;

                // Update 'Load More' button visibility
                loadMoreBtn.textContent = 'Load More';
                loadMoreBtn.disabled = false;
                loadMoreBtn.style.display = response.has_more ? 'block' : 'none';
            }

        } catch (error) {
            listContainer.innerHTML = '<div class="error">Failed to load topics.</div>';
            loadMoreBtn.style.display = 'none';
            console.error("Error loading topics:", error);
        }
    }

    /** Appends the next set of topics to the list. */
    async function loadMore() {
        const filterName = filterInput.value.trim();

        loadMoreBtn.textContent = 'Loading...';
        loadMoreBtn.disabled = true;

        try {
            const response = await mockFetchTopics(currentOffset, filterName);

            response.topics.forEach(t => listContainer.appendChild(createTopicItem(t)));

            // Update offset
            currentOffset += response.topics.length;

            // Update 'Load More' button visibility
            if (response.has_more) {
                loadMoreBtn.textContent = 'Load More';
                loadMoreBtn.disabled = false;
            } else {
                loadMoreBtn.textContent = 'No More Topics';
                loadMoreBtn.style.display = 'none';
            }

        } catch (error) {
            loadMoreBtn.textContent = 'Failed to Load';
            loadMoreBtn.disabled = false;
            console.error("Error loading more topics:", error);
        }
    }

    // --- EVENT DELEGATION ---

    // Handle clicks for Pin/Unpin and Navigation
    document.addEventListener('click', (e) => {
        // 1. PIN ICON CLICK
        if (e.target.classList.contains('pin-btn') || e.target.closest('.pin-btn')) {
            e.preventDefault();
            e.stopPropagation();
            const btn = e.target.classList.contains('pin-btn') ? e.target : e.target.closest('.pin-btn');
            const id = btn.dataset.id;
            apiPinTopic(id);
            return;
        }

        // 2. UNPIN ICON CLICK
        if (e.target.classList.contains('unpin-icon')) {
            e.preventDefault();
            e.stopPropagation();
            const id = e.target.dataset.id;
            apiUnpinTopic(id);
            return;
        }

        // 3. DELETE ICON CLICK
        if (e.target.classList.contains('delete-btn') || e.target.closest('.delete-btn')) {
            e.preventDefault();
            e.stopPropagation();
            const btn = e.target.classList.contains('delete-btn') ? e.target : e.target.closest('.delete-btn');
            const id = btn.dataset.id;
            apiDeleteTopic(id);
            return;
        }

        // 4. RENAME ICON CLICK
        if (e.target.classList.contains('rename-btn') || e.target.closest('.rename-btn')) {
            e.preventDefault();
            e.stopPropagation();
            const btn = e.target.classList.contains('rename-btn') ? e.target : e.target.closest('.rename-btn');
            const id = btn.dataset.id;
            const currentName = btn.dataset.name;
            apiRenameTopic(id, currentName);
            return;
        }

        // 3. TOPIC ITEM CLICK (Navigation) for dynamically added items
        // Note: The <a> tag inside creates distinct navigation behavior.
        // We generally rely on the <a> tag href, but if we want JS nav:
        // const topicItem = e.target.closest('.topic-item');
        // if (topicItem && !e.target.closest('a')) { // If clicked on div padding
        //     viewTopic(topicItem.dataset.id);
        // }
    });

    // --- EVENT LISTENERS ---

    // 1. Filter Button Listener
    filterBtn.addEventListener('click', () => {
        // Reset offset and load fresh data (true for non-initial load)
        loadTopics(0, false);
    });

    // 2. Load More Button Listener
    loadMoreBtn.addEventListener('click', loadMore);

    // 3. Initial Load check
    if (currentOffset < ITEMS_PER_LOAD && listContainer.children.length > 0) {
        // Safety check
    }


});