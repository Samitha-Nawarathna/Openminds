document.addEventListener('DOMContentLoaded', () => {
    // --- MOCK DATA and COLOR TOKENS ---
    const MOCK_TOP_TAGS = [
        { name: 'Physics' },
        { name: 'History' },
        { name: 'Web Dev' },
        { name: 'Maths' },
        { name: 'React' },
        { name: 'Design' }
    ];
    
    // Light color tokens from the design system's core.css for random tag coloring
    const LIGHT_COLOR_TOKENS = [
        // Using light/muted tokens from the design system
        '--color-blue-100',  // Light Blue background
        '--color-green-100', // Light Green background
        '--color-yellow-50', // Light Yellow background
        '--color-gray-100',  // Very Light Gray background
        '--color-red-100'    // Light Red background
    ];
    
    // Dark color tokens for text on light backgrounds
    const DARK_TEXT_TOKENS = {
        '--color-blue-100': '--color-gray-900',
        '--color-green-100': '--color-gray-900',
        '--color-yellow-50': '--color-gray-900', 
        '--color-gray-100': '--color-gray-900',
        '--color-red-100': '--color-gray-900'
    };

    // --- DOM Elements ---
    const form = document.getElementById('note-create-form');
    const openModalBtn = document.getElementById('open-metadata-modal-btn');
    const modalOverlay = document.getElementById('metadata-modal');
    const closeModalBtn = document.getElementById('close-metadata-modal-btn');
    const finalSaveBtn = document.getElementById('save-note-metadata-btn');

    // Modal Specific Elements
    const modalTagsInput = document.getElementById('modal-tags-input');
    const modalTagsDisplay = document.getElementById('modal-selected-tags-display');
    const modalTopicInput = document.getElementById('modal-topic-input');
    const modalTopTagsList = document.getElementById('modal-top-tags-list');
    
    // Hidden Fields in Main Form
    const hiddenTagsField = document.getElementById('hidden-tags-field');
    const hiddenTopicField = document.getElementById('hidden-topic-field');

    const noteTitle = document.getElementById("note-title");
    const noteContent = document.getElementById("editor");

    noteTitle.addEventListener('keydown', (e) => {
        if (e.key === 'Enter' && !(noteTitle.value.trim() === ''))
        {
            noteContent.setAttribute("placeholder", "start writing...")
        }
    });

    noteTitle.addEventListener('focus', (e) => {
        if (noteContent.value === '')
        {
            noteContent.setAttribute("placeholder", "");
        }
    });

    noteContent.addEventListener('focus', (e) => {
        noteContent.setAttribute("placeholder", "start writing...");
    });


    modalTagsDisplay.style.display = 'none';
    // --- State ---
    let selectedTags = new Set(); 
    let currentTopic = '';


    // --- Core Tag Management Functions ---
    
    /** Renders the tag pill in the display area. */
    function renderTag(tagName) {
        const tagPill = document.createElement('span');
        tagPill.classList.add('tag-pill', `current-title-pill`);
        
        // Find the corresponding top tag to get its assigned color for consistency
        const topTagElement = modalTopTagsList.querySelector(`.tag-clickable[data-tag-name="${tagName.replace(/([\[\].\(\)])/g, '\\$1')}"]`);
        if (topTagElement) {
            // Copy the random color style from the top tag if it exists
            tagPill.style.backgroundColor = 'var(--color-blue-100)';
            tagPill.style.color = 'var(--color-gray-900)';
        } else {
            // Default coloring for manually entered tags that aren't in MOCK_TOP_TAGS
            tagPill.style.backgroundColor = 'var(--color-blue-100)';
            tagPill.style.color = 'var(--color-blue-700)';
        }
        
        tagPill.innerHTML = `${tagName}<span class="tag-removal" data-tag="${tagName}">&times;</span>`;
        
        // Add listener for removal
        tagPill.querySelector('.tag-removal').addEventListener('click', (e) => {
            const tagToRemove = e.target.dataset.tag;
            removeTag(tagToRemove);
        });
        
        modalTagsDisplay.appendChild(tagPill);
    }

    /** Adds a new tag to the set and updates the DOM/state. */
    function addTag(tagName) {
        tagName = tagName.trim();
        if (!tagName || selectedTags.has(tagName)) return;

        selectedTags.add(tagName);
        if (selectedTags.size > 0) {
            modalTagsDisplay.style.display = 'flex';
        }else
        {
            modalTagsDisplay.style.display = 'none';
        }
        updateDisplayArea(); // Re-render all selected tags
        updateTopTagState(tagName, true);
    }

    /** Removes a tag from the set and updates the DOM/state. */
    function removeTag(tagName) {
        if (selectedTags.delete(tagName)) {
            if (selectedTags.size > 0) {
                modalTagsDisplay.style.display = 'flex';
            }else
            {
                modalTagsDisplay.style.display = 'none';
            }
            updateDisplayArea(); 
            updateTopTagState(tagName, false);
        }
    }

    /** Clears and re-renders the selected tags display area. */
    function updateDisplayArea() {
        modalTagsDisplay.innerHTML = '';
        selectedTags.forEach(renderTag);
    }
    
    /** Updates the appearance of a tag in the Top Tags list. */
    function updateTopTagState(tagName, isSelected) {
        const escapedTagName = tagName.replace(/([\[\].\(\)])/g, '\\$1'); 
        const topTagPill = modalTopTagsList.querySelector(`.tag-clickable[data-tag-name="${escapedTagName}"]`);
        
        if (topTagPill) {
            topTagPill.classList.toggle('tag-selected', isSelected);
        }
    }

    // --- Modal Control Functions ---
    
    function openModal() {
        modalOverlay.classList.add('open');
        modalTopicInput.focus();
        
        // Ensure initial tag and topic state is loaded if needed
        if (modalTopTagsList.children.length === 0) {
            renderTopTags(MOCK_TOP_TAGS);
        }
        updateDisplayArea(); // Re-render selected tags
        selectedTags.forEach(tag => updateTopTagState(tag, true)); // Sync top tag state
    }

    function closeModal() {
        modalOverlay.classList.remove('open');
    }

    // --- Initialization and Data Rendering ---

    /** Populates the Top Tags list using the mock data, assigning a random color. */
    function renderTopTags(tags) {
        modalTopTagsList.innerHTML = ''; 

        tags.forEach(tag => {
            const tagName = tag.name;
            const tagPill = document.createElement('span');
            tagPill.classList.add('tag-pill', 'tag-clickable'); 
            tagPill.dataset.tagName = tagName;
            tagPill.textContent = tagName;
            
            // --- NEW: Apply random light color from the design system ---
            const randomBgToken = LIGHT_COLOR_TOKENS[Math.floor(Math.random() * LIGHT_COLOR_TOKENS.length)];
            const textToken = DARK_TEXT_TOKENS[randomBgToken];
            
            tagPill.style.backgroundColor = `var(${randomBgToken})`;
            tagPill.style.color = `var(${textToken})`;
            
            tagPill.addEventListener('click', () => {
                if (selectedTags.has(tagName)) {
                    removeTag(tagName);
                } else {
                    addTag(tagName);
                }
            });

            modalTopTagsList.appendChild(tagPill);
        });
    }

    // --- Event Listeners ---
    
    // 1. Open Modal
    openModalBtn.addEventListener('click', openModal);

    // 2. Close Modal
    closeModalBtn.addEventListener('click', closeModal);
    modalOverlay.addEventListener('click', (e) => {
        if (e.target === modalOverlay) closeModal();
    });

    // 3. Manual Tag Entry Handler (in modal)
    modalTagsInput.addEventListener('keydown', (e) => {
        if (e.key === 'Enter' || e.key === ',') {
            e.preventDefault();
            const inputVal = modalTagsInput.value.trim();
            if (inputVal) {
                inputVal.split(',').forEach(tag => addTag(tag.trim()));
                modalTagsInput.value = ''; 
            }
        }
    });

    // 4. Final Save/Submission Handler (in modal)
    finalSaveBtn.addEventListener('click', (e) => {
        // Step 1: Transfer data from modal fields to main form's hidden fields
        hiddenTagsField.value = Array.from(selectedTags).join(',');
        hiddenTopicField.value = modalTopicInput.value.trim();
        
        // Step 2: Close the modal
        closeModal();


            // Step 3: Programmatically submit the main form
            form.submit();

            console.log("--- FINAL FORM SUBMISSION TRIGGERED ---");
            console.log(`Hidden Tags: ${hiddenTagsField.value}`);
            console.log(`Hidden Topic: ${hiddenTopicField.value}`);


        
 
    });
    
    // Initial call to render top tags on load (for the modal)
    renderTopTags(MOCK_TOP_TAGS);
});