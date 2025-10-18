import { ROOT } from '../../core/config.js';

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
    function mockFetchTopics(offset, filterName) {
        console.log(`[MOCK AJAX] Fetching topics from offset ${offset} with filter: "${filterName}"`);
        
        return new Promise(resolve => {
            setTimeout(() => {
                // --- MOCK DATA GENERATION ---
                // This simulates the server dynamically generating data.
                let mockTopics = [];
                let hasMore = true;
                
                // Use a counter to easily simulate different data on subsequent loads
                const startId = offset + 1;
                const endId = offset + ITEMS_PER_LOAD;

                if (offset === 0) {
                    // For a fresh load (when filtering)
                    // We can't generate the *exact* PHP data, so we simplify:
                    mockTopics = [
                        { id: 't1', name: 'Science' }, { id: 't2', name: 'Maths' },
                        { id: 't3', name: 'Linear algebra' }, { id: 't4', name: 'Calculus' },
                        { id: 't5', name: 'Integration' }
                    ];
                    // Simulate that the filter found a short list
                    hasMore = mockTopics.length >= ITEMS_PER_LOAD; 
                } else if (offset === 10) {
                    // Simulate loading more data
                    mockTopics = [
                        { id: 't11', name: 'Cosmology (Lazy Load 1)' },
                        { id: 't12', name: 'Topology (Lazy Load 2)' }
                    ];
                    hasMore = false; // Last load
                } else {
                    hasMore = false;
                }
                
                resolve({
                    topics: mockTopics,
                    has_more: hasMore
                });

            }, 400); // Simulate network latency
        });
    }

    // --- RENDERING FUNCTIONS ---

    /** Redirect on topic click (mock) */
    function viewTopic(topicId) {
        window.location.href = ROOT+"notes/view_notes?id="+topicId;
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