document.addEventListener('DOMContentLoaded', () => {
    // --- DOM Elements ---
    const form = document.getElementById('note-update-form'); // Changed ID to update-form
    const noteIdField = document.getElementById('note-id-field'); // New Element
    const tagsInput = document.getElementById('tags-input');
    const selectedTagsDisplay = document.getElementById('selected-tags-display');
    const hiddenTagsField = document.getElementById('hidden-tags-field');
    const topTagsList = document.getElementById('top-tags-list');

    // --- State ---
    // INITIALIZATION: Use the global INITIAL_TAGS variable passed from PHP
    let selectedTags = new Set(INITIAL_TAGS || []);

    // --- Utility Functions (Identical to Creator script) ---

    function getTagClass(tagName) {
        return tagName.toLowerCase().replace(/\s/g, '-');
    }

    function renderTag(tagName) {
        const tagPill = document.createElement('span');
        tagPill.classList.add('tag-pill');
        tagPill.innerHTML = `${tagName}<span class="tag-removal" data-tag="${tagName}">&times;</span>`;
        
        tagPill.querySelector('.tag-removal').addEventListener('click', (e) => {
            const tagToRemove = e.target.dataset.tag;
            removeTag(tagToRemove);
        });
        
        selectedTagsDisplay.appendChild(tagPill);
    }

    function addTag(tagName) {
        tagName = tagName.trim();
        if (!tagName || selectedTags.has(tagName)) return;

        selectedTags.add(tagName);
        renderTag(tagName);
        updateHiddenField();
        updateTopTagState(tagName, true);
    }

    function removeTag(tagName) {
        if (selectedTags.delete(tagName)) {
            updateDisplayArea(); 
            updateHiddenField();
            updateTopTagState(tagName, false);
        }
    }

    function updateDisplayArea() {
        selectedTagsDisplay.innerHTML = '';
        selectedTags.forEach(renderTag);
    }

    function updateHiddenField() {
        hiddenTagsField.value = Array.from(selectedTags).join(',');
    }
    
    function updateTopTagState(tagName, isSelected) {
        const topTagPill = topTagsList.querySelector(`.tag-clickable[data-tag-name="${tagName}"]`);
        if (topTagPill) {
            topTagPill.classList.toggle('tag-selected', isSelected);
        }
    }


    // --- Event Handlers (Identical to Creator script, except submission) ---
    
    // Manual Tag Entry
    tagsInput.addEventListener('keydown', (e) => {
        if (e.key === 'Enter' || e.key === ',') {
            e.preventDefault();
            const inputVal = tagsInput.value.trim();
            if (inputVal) {
                inputVal.split(',').forEach(tag => addTag(tag.trim()));
                tagsInput.value = '';
            }
        }
    });

    // Click Handler for Top Tags
    topTagsList.addEventListener('click', (e) => {
        const target = e.target.closest('.tag-clickable');
        if (!target) return;

        const tagName = target.dataset.tagName;
        
        if (selectedTags.has(tagName)) {
            removeTag(tagName);
        } else {
            addTag(tagName);
        }
    });

    // 3. Form Submission Handler (Mock AJAX)
    form.addEventListener('submit', (e) => {
        e.preventDefault();
        
        updateHiddenField();
        
        const noteId = noteIdField.value; // Capture the Note ID
        const title = document.getElementById('note-title').value;
        const tags = hiddenTagsField.value;

        console.log("--- Update Note Data Ready for Backend ---");
        console.log(`NOTE ID: ${noteId} (REQUIRED FOR UPDATE)`);
        console.log(`Title: ${title}`);
        console.log(`Tags: ${tags}`);

        // --- MOCK AJAX CALL ---
        alert(`Note ID ${noteId} updated successfully! (Mock submission)`);
        // In a real application, redirect to the updated note's view:
        // window.location.href = `/note/${noteId}`;
    });


    // --- Initialization on Load ---

    // 1. Render pre-selected tags in the display area
    updateDisplayArea();
    updateHiddenField(); 
    
    // 2. Mark pre-selected tags in the Top Tags list as 'selected'
    selectedTags.forEach(tag => updateTopTagState(tag, true));

});