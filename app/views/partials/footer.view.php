<?php

include_once('../app/views/partials/message.view.php');
include_once "../app/views/partials/note_modal.php";


?>

<script>
    window.showPopupError = function(message) {
    const popup = document.getElementById('error-popup');
    popup.textContent = `Error: ${message}`;
    popup.style.display = 'block';
    setTimeout(() => {
        popup.style.display = 'none';
    }, 3000);

    window.showPopupSuccess = function(message) {
    const popup = document.getElementById('error-popup');
    popup.style.backgroundColor = 'var(--color-green)';
    popup.textContent = `Success: ${message}`;
    popup.style.display = 'block';
    setTimeout(() => {
        popup.style.display = 'none';
    }, 3000);
}
</script>



</body>
</html>