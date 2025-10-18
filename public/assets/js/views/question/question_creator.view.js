document.addEventListener('DOMContentLoaded', () => {
    // Get references to the HTML elements
    const tagInput = document.getElementById('tag-input');
    const tagsContainer = document.getElementById('tags-display-container');
    const hiddenTagsInput = document.getElementById('hidden-tags-input');
    const form = document.getElementById('create-question-form');

    // Use an array to store the tags
    let tags = [];

    // --- Function to update the visual tag pills ---
    function updateTagsDisplay() {
        // Clear the container first
        tagsContainer.innerHTML = '';

        // Create and add a pill for each tag in the array
        tags.forEach(tag => {
            const tagElement = document.createElement('span');
            tagElement.classList.add('tag-pill');
            
            // Tag text
            const tagText = document.createTextNode(tag);
            
            // Remove button (x)
            const removeBtn = document.createElement('span');
            removeBtn.classList.add('remove-tag');
            removeBtn.innerHTML = '&times;'; // A nice 'x' character
            removeBtn.addEventListener('click', () => {
                removeTag(tag);
            });
            
            tagElement.appendChild(tagText);
            tagElement.appendChild(removeBtn);
            tagsContainer.appendChild(tagElement);
        });
    }

    // --- Function to update the hidden input's value ---
    function updateHiddenInput() {
        // Join the array elements with a comma for backend processing
        hiddenTagsInput.value = tags.join(',');
    }

    // --- Function to remove a tag ---
    function removeTag(tagToRemove) {
        // Filter the array to exclude the tag being removed
        tags = tags.filter(tag => tag !== tagToRemove);
        // Re-render the display and update the hidden input
        updateTagsDisplay();
        updateHiddenInput();
    }

    // --- Event listener for the tag input field ---
    tagInput.addEventListener('keydown', (event) => {
        // Check if the key pressed was 'Enter'
        if (event.key === 'Enter') {
            // Prevent the default behavior of submitting the form
            event.preventDefault();

            // Get, trim, and clean the input value
            const newTag = tagInput.value.trim().toLowerCase();

            // Add the tag if it's not empty and not already in the list
            if (newTag.length > 0 && !tags.includes(newTag)) {
                tags.push(newTag);
                updateTagsDisplay();
                updateHiddenInput();
            }

            // Clear the input field for the next tag
            tagInput.value = '';
        }
    });
    
    // --- Mock form submission to show the data ---
    form.addEventListener('submit', (event) => {
        // Prevent actual submission for this demonstration
        event.preventDefault();
        
        const formData = new FormData(form);
        console.log("--- Form Data Ready for Backend ---");
        for (let [key, value] of formData.entries()) {
            console.log(`${key}: ${value}`);
        }
        
        alert("Form data (including tags) logged to the console (F12)!");
    });
});