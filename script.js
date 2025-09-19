
        // --- View Switching Logic ---
        const bodyEl = document.body;
        const notesView = document.getElementById('notes-view');
        const notesTitlesView = document.getElementById('notes-titles-view');
        const createRequestView = document.getElementById('create-request-view');
        const shareModalView = document.getElementById('share-modal-view');
        const shareModalCard = document.getElementById('share-modal-card');

        const noteItems = document.querySelectorAll('.note-item');
        const backButtonDesktop = document.getElementById('back-to-main-desktop');
        const backButtonMobile = document.getElementById('back-to-main-mobile');
        const createButtons = document.querySelectorAll('.create-button');
        const saveRequestButton = document.getElementById('save-request-button');
        const cancelRequestButton = document.getElementById('cancel-request-button');
        const finalShareButton = document.getElementById('final-share-button');


        function hideAllViews() {
            notesView.style.display = 'none';
            notesTitlesView.style.display = 'none';
            createRequestView.style.display = 'none';
            bodyEl.classList.remove('form-view-active');
        }

        function showNotesTitles() {
            hideAllViews();
            notesTitlesView.style.display = 'block';
        }

        function showMainNotes() {
            hideAllViews();
            shareModalView.style.display = 'none'; // Ensure modal is also hidden
            notesView.style.display = 'block';
        }

        function showCreateRequest() {
            hideAllViews();
            createRequestView.style.display = 'block';
            bodyEl.classList.add('form-view-active');
        }

        function showShareModal() {
            shareModalView.style.display = 'flex';
        }
        
        function hideShareModal() {
            shareModalView.style.display = 'none';
        }

        // --- Event Listeners ---
        noteItems.forEach(item => item.addEventListener('click', showNotesTitles));
        backButtonDesktop.addEventListener('click', showNotesTitles);
        backButtonMobile.addEventListener('click', showNotesTitles);
        createButtons.forEach(button => {
    // Check if the button is the specific one with id="create"
    if (button.id === 'create1') {
        // If it is, make it show the notes view
        button.addEventListener('click', showMainNotes);
    }if (button.id === 'create2') {
        // If it is, make it show the notes titles view
        button.addEventListener('click', showCreateRequest);
    } else {
        // For all other create buttons (e.g., the mobile one), show the create form
        button.addEventListener('click', showCreateRequest);
    }
});

                    // <--------things i added-------->
        document.getElementById('create1').addEventListener('click', showMainNotes);
        document.getElementById('create2').addEventListener('click', showCreateRequest);

        cancelRequestButton.addEventListener('click', showNotesTitles);
        
        // Save button now opens the share modal
        saveRequestButton.addEventListener('click', showShareModal);

        // Final Share button closes modal and returns to main notes screen
        finalShareButton.addEventListener('click', () => {
            hideShareModal();
            showMainNotes();
        });

        // Clicking outside the modal card closes the modal
        shareModalView.addEventListener('click', (event) => {
            // Check if the click is on the dark overlay itself, not the card
            if (event.target === shareModalView) {
                hideShareModal();
                showMainNotes();
            }
        });

