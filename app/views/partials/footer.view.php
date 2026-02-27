<?php

include_once('../app/views/partials/message.view.php');
include_once "../app/views/partials/note_modal.php";


?>
 <!-- <?php if (!empty($add_back)): ?>
    <a href="<?=ROOT?>/back" class="back-btn">← Back</a>
<?php endif; ?>

<style>
    .back-btn {
    position: fixed;
    top: 15px;
    left: 15px;

    padding: 8px 14px;
    background: var(--color-bg, #ffffff);
    border: 2px solid var(--color-text, #000000);
    border-radius: 8px;

    font-size: 14px;
    font-weight: 600;
    text-decoration: none;
    color: var(--color-text, #000000);

    z-index: 9999;
    cursor: pointer;
    user-select: none;

    box-shadow: 0 2px 6px rgba(0,0,0,0.15);
    transition: transform 0.15s ease, background 0.2s ease;
}

.back-btn:hover {
    transform: translateY(-2px);
    background: var(--color-hover, #f0f0f0);
}

.back-btn:active {
    transform: translateY(0);
}

</style> -->

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