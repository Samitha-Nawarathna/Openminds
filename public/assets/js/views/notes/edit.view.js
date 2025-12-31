document.addEventListener('DOMContentLoaded', () => {
    // --- COLOR TOKENS (Copied from create.view.js) ---
    const LIGHT_COLOR_TOKENS = [
        '--color-blue-100',
        '--color-green-100',
        '--color-yellow-50',
        '--color-gray-100',
        '--color-red-100'
    ];
    
    const DARK_TEXT_TOKENS = {
        '--color-blue-100': '--color-gray-900',
        '--color-green-100': '--color-gray-900',
        '--color-yellow-50': '--color-gray-900', 
        '--color-gray-100': '--color-gray-900',
        '--color-red-100': '--color-gray-900'
    };

    // --- DOM Elements ---
    const form = document.getElementById('note-update-form'); 
    const hiddenTagsField = document.getElementById('hidden-tags-field');
    const hiddenTopicField = document.getElementById('hidden-topic-field'); // New hidden topic field

    // Modal Control Elements
    const openModalBtn = document.getElementById('open-metadata-modal-btn');
    const modalOverlay = document.getElementById('metadata-modal');
    const closeModalBtn = document.getElementById('close-metadata-modal-btn');
    const finalSaveBtn = document.getElementById('save-note-metadata-btn');

    // Modal Specific Elements
    const modalTagsInput = document.getElementById('modal-tags-input');
    const modalTagsDisplay = document.getElementById('modal-selected-tags-display');
    const modalTopicInput = document.getElementById('modal-topic-input');
    const modalTopTagsList = document.getElementById('modal-top-tags-list');
    
    // --- State (Initialized with existing note data) ---
    let selectedTags = new Set(INITIAL_TAGS || []);
    let currentTopic = INITIAL_TOPIC || '';
    
    // Map to store random colors for consistency across Top Tags and Selected Tags
    const tagColorMap = new Map(); 

    // --- Utility Functions ---

    /** Gets a random color and stores it in the map if it doesn't exist. */
    function getTagColors(tagName) {
        if (tagColorMap.has(tagName)) {
            return tagColorMap.get(tagName);
        }

        const randomBgToken = LIGHT_COLOR_TOKENS[Math.floor(Math.random() * LIGHT_COLOR_TOKENS.length)];
        const textToken = DARK_TEXT_TOKENS[randomBgToken];
        const colors = { 
            bg: `var(${randomBgToken})`,
            text: `var(${textToken})`
        };
        tagColorMap.set(tagName, colors);
        return colors;
    }

    /** Renders the tag pill in the modal display area. */
    function renderTag(tagName) {
        const tagPill = document.createElement('span');
        tagPill.classList.add('tag-pill');
        
        const colors = getTagColors(tagName);
        tagPill.style.backgroundColor = colors.bg;
        tagPill.style.color = colors.text;
        
        tagPill.innerHTML = `${tagName}<span class="tag-removal" data-tag="${tagName}">&times;</span>`;
        
        tagPill.querySelector('.tag-removal').addEventListener('click', (e) => {
            const tagToRemove = e.target.dataset.tag;
            removeTag(tagToRemove);
        });
        
        modalTagsDisplay.appendChild(tagPill);
    }

    function addTag(tagName) {
        tagName = tagName.trim();
        if (!tagName || selectedTags.has(tagName)) return;

        selectedTags.add(tagName);
        updateDisplayArea(); // Re-render all selected tags
        updateTopTagState(tagName, true);
        updateModalDisplayVisibility();
    }

    function removeTag(tagName) {
        if (selectedTags.delete(tagName)) {
            updateDisplayArea(); 
            updateTopTagState(tagName, false);
            updateModalDisplayVisibility();
        }
    }

    function updateDisplayArea() {
        modalTagsDisplay.innerHTML = '';
        selectedTags.forEach(renderTag);
    }
    
    function updateModalDisplayVisibility() {
        modalTagsDisplay.style.display = selectedTags.size > 0 ? 'flex' : 'none';
    }

    /** Updates the appearance of a tag in the Top Tags list. */
    function updateTopTagState(tagName, isSelected) {
        const escapedTagName = tagName.replace(/([\[\].\(\)])/g, '\\$1'); 
        const topTagPill = modalTopTagsList.querySelector(`.tag-clickable[data-tag-name="${escapedTagName}"]`);
        
        if (topTagPill) {
            topTagPill.classList.toggle('tag-selected', isSelected);
        }
    }
    
    function updateHiddenFields() {
        hiddenTagsField.value = Array.from(selectedTags).join(',');
        hiddenTopicField.value = modalTopicInput.value.trim();
    }

    // --- Modal Control Functions ---
    
    function renderTopTags(tagsList) {
        Array.from(tagsList.children).forEach(tagPill => {
            const tagName = tagPill.dataset.tagName;
            const colors = getTagColors(tagName);
            
            // Apply the random color for display consistency
            tagPill.style.backgroundColor = colors.bg;
            tagPill.style.color = colors.text;
            
            // Attach click handler (since PHP rendered the elements)
            tagPill.addEventListener('click', () => {
                if (selectedTags.has(tagName)) {
                    removeTag(tagName);
                } else {
                    addTag(tagName);
                }
            });
        });
    }


    function openModal() {
        modalOverlay.classList.add('open');
        
        // 1. Load initial state into modal inputs
        modalTopicInput.value = currentTopic;
        
        // 2. Render all top tags and attach click handlers only once
        if (modalTopTagsList.children.length > 0 && modalTopTagsList.dataset.initialized !== 'true') {
            renderTopTags(modalTopTagsList);
            modalTopTagsList.dataset.initialized = 'true';
        }

        // 3. Sync display and state
        updateDisplayArea(); 
        updateModalDisplayVisibility();
        selectedTags.forEach(tag => updateTopTagState(tag, true)); // Mark selected top tags
        modalTopicInput.focus();
    }

    function closeModal() {
        modalOverlay.classList.remove('open');
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

    // 4. Topic Input Handler (Update state on change)
    modalTopicInput.addEventListener('input', (e) => {
        currentTopic = e.target.value.trim();
    });

    // 5. Final Save/Submission Handler (in modal)
    finalSaveBtn.addEventListener('click', (e) => {
        // Step 1: Transfer data from modal fields to main form's hidden fields
        updateHiddenFields();
        
        // Step 2: Close the modal
        closeModal();
        
        // Step 3: Programmatically submit the main form (Update)
        form.submit();

        console.log("--- FINAL FORM SUBMISSION TRIGGERED (UPDATE) ---");
    });
    
    // --- Initial setup on page load ---
    updateHiddenFields(); // Ensure hidden fields are populated with initial data
});