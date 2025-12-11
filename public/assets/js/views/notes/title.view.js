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
    const listContainer = document.getElementById('topics-list');
    const filterInput = document.getElementById('topic-filter-input');
    const filterBtn = document.getElementById('filter-btn');

    // Initialize the offset based on the initial PHP render (if available)
    currentOffset = listContainer.children.length;
    
    // --- MOCK AJAX function ---
    // Simulates an AJAX call to the backend to fetch topics.
    // NOTE: This logic needs to be a bit more robust than the previous mock 
    // since we're no longer relying on PHP for the first load.
    async function mockFetchTopics(offset, filterName) {
        console.log(`[API CALL] Fetching topics from offset ${offset} with filter: "${filterName}"`);
        
        try {
            const response = await fetch(`${ROOT}/topics/api/load_more?offset=${offset}&filter=${encodeURIComponent(filterName)}`);
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

    /** Redirect on topic click (mock) */
    function viewTopic(topicId) {
        window.location.href = ROOT+"notes/list/"+topicId;
    }

    /** Creates the HTML structure for a single topic item. */
    function createTopicItem(topic) {
        const item = document.createElement('div');
        item.classList.add('topic-item');
        item.textContent = topic.name;
        item.dataset.id = topic.id; // Store ID in data attribute

        // Add event listener to the created element
        item.addEventListener('click', () => viewTopic(topic.id));

        return item;
    }

    /** Clears the list and updates with new data (used for Filter or initial load). */
    async function loadTopics(offset, isInitialLoad) {
        const filterName = filterInput.value.trim();
        console.log(filterName);
        
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
            console.log(response);
            
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

    // --- EVENT LISTENERS ---

    // 1. Filter Button Listener
    filterBtn.addEventListener('click', () => {
        // Reset offset and load fresh data (true for non-initial load)
        loadTopics(0, false); 
    });

    // 2. Load More Button Listener
    loadMoreBtn.addEventListener('click', loadMore);

    // 3. Topic Item Listeners (Handles items rendered by PHP)
    // Add event listeners to items initially rendered by PHP
    document.querySelectorAll('.topic-item').forEach(item => {
        item.addEventListener('click', () => viewTopic(item.dataset.id));
    });

    // 4. Initial Load check
    // If the PHP didn't render the maximum amount, assume the "Load More" check should be run
    if (currentOffset < ITEMS_PER_LOAD && listContainer.children.length > 0) {
        // Since we don't know the exact count from PHP, this is a safety check.
        // In a real app, the PHP would pass the total count to JS.
        // For this mock, we rely on the PHP's initial has_more setting.
    }

    
});