<?php

include_once('../app/views/partials/message.view.php');
include_once "../app/views/partials/note_modal.php";
// include_once "../app/views/partials/text_editor.php";


?>

<script>
    window.showPopupError = function(message) {
    const popup = document.getElementById('error-popup');
    popup.textContent = `Error: ${message}`;
    popup.style.display = 'block';
    setTimeout(() => {
        popup.style.display = 'none';
    }, 3000);
}

    window.showPopupSuccess = function(message) {
    const popup = document.getElementById('error-popup');
    popup.style.backgroundColor = 'var(--color-green)';
    popup.textContent = `Success: ${message}`;
    popup.style.display = 'block';
    setTimeout(() => {
        popup.style.display = 'none';
    }, 3000);
}

function prepareAllQuillData() {
    // Check if the registry exists
    if (window.quillSubmitPrep) {
        // Iterate through every registered editor and call its preparation function
        for (const editorId in window.quillSubmitPrep) {
            if (typeof window.quillSubmitPrep[editorId] === 'function') {
                window.quillSubmitPrep[editorId]();
            }
        }
    }
}

window.prepareAllQuillData = prepareAllQuillData;
</script>



</body>
</html>