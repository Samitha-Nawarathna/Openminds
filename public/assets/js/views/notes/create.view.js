document.addEventListener('DOMContentLoaded', () => {
    // --- DOM Elements ---
    const form = document.getElementById('note-create-form');
    const tagsInput = document.getElementById('tags-input');
    const selectedTagsDisplay = document.getElementById('selected-tags-display');
    const hiddenTagsField = document.getElementById('hidden-tags-field');
    const topTagsList = document.getElementById('top-tags-list');

    // --- State ---
    let selectedTags = new Set();

    // --- Utility Functions ---

    /** Standardizes tag name (e.g., removes spaces and lowercase) for CSS class. */
    function getTagClass(tagName) {
        return tagName.toLowerCase().replace(/\s/g, '-');
    }

    /** Renders the tag pill in the display area. */
    function renderTag(tagName) {
        const tagPill = document.createElement('span');
        tagPill.classList.add('tag-pill', `current-title-pill`);
        tagPill.innerHTML = `${tagName}<span class="tag-removal" data-tag="${tagName}">&times;</span>`;
        
        // Add listener for removal
        tagPill.querySelector('.tag-removal').addEventListener('click', (e) => {
            const tagToRemove = e.target.dataset.tag;
            removeTag(tagToRemove);
        });
        
        selectedTagsDisplay.appendChild(tagPill);
    }

    /** Adds a new tag to the set and updates the DOM/hidden field. */
    function addTag(tagName) {
        tagName = tagName.trim();
        if (!tagName || selectedTags.has(tagName)) return;

        selectedTags.add(tagName);
        renderTag(tagName);
        updateHiddenField();
        updateTopTagState(tagName, true);
    }

    /** Removes a tag from the set and updates the DOM/hidden field. */
    function removeTag(tagName) {
        if (selectedTags.delete(tagName)) {
            // Re-render the display area to reflect the change
            updateDisplayArea(); 
            updateHiddenField();
            updateTopTagState(tagName, false);
        }
    }

    /** Clears and re-renders the selected tags display area. */
    function updateDisplayArea() {
        selectedTagsDisplay.innerHTML = '';
        selectedTags.forEach(renderTag);
    }

    /** Updates the hidden input field with a comma-separated list of tags. */
    function updateHiddenField() {
        hiddenTagsField.value = Array.from(selectedTags).join(',');
    }
    
    /** Updates the appearance of a tag in the Top Tags list. */
    function updateTopTagState(tagName, isSelected) {
        const topTagPill = topTagsList.querySelector(`.tag-clickable[data-tag-name="${tagName}"]`);
        if (topTagPill) {
            topTagPill.classList.toggle('tag-selected', isSelected);
        }
    }


    // --- Event Handlers ---
    
    // 1. Manual Tag Entry Handler (on Enter or comma input)
    tagsInput.addEventListener('keydown', (e) => {
        console.log(e.key);
        // If user presses Enter or a comma
        if (e.key === 'Enter' || e.key === ',') {
            e.preventDefault();
            const inputVal = tagsInput.value.trim();
            if (inputVal) {
                // Split by comma in case the user pasted or typed multiple tags
                inputVal.split(',').forEach(tag => addTag(tag.trim()));
                tagsInput.value = ''; // Clear the input field
            }
        }
    });

    // 2. Click Handler for Top Tags
    topTagsList.addEventListener('click', (e) => {
        const target = e.target.closest('.tag-clickable');
        if (!target) return;

        const tagName = target.dataset.tagName;
        
        if (selectedTags.has(tagName)) {
            // If already selected (clicked the striked-out pill), remove it
            removeTag(tagName);
        } else {
            // If not selected, add it
            addTag(tagName);
        }
    });

    // 3. Form Submission Handler (Mock AJAX)
    form.addEventListener('submit', (e) => {
        e.preventDefault();
        
        // Ensure the hidden field is up to date one last time
        updateHiddenField();
        
        const title = document.getElementById('note-title').value;
        const content = document.getElementById('note-content').value;
        const tags = hiddenTagsField.value;

        console.log("--- New Note Data Ready for Backend ---");
        console.log(`Title: ${title}`);
        console.log(`Content Snippet: ${content.substring(0, 50)}...`);
        console.log(`Tags: ${tags}`);

        // --- MOCK AJAX CALL ---
        // alert("Note saved successfully! (Mock submission)");
        // In a real application, redirect to the created note's view:
        // window.location.href = '/note/newly-created-id';
        form.submit();
    });

    // Initialize all top tags to the unselected state on load
    topTagsList.querySelectorAll('.tag-clickable').forEach(pill => {
        pill.classList.remove('tag-selected');
    });
});