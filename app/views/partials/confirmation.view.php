<?php
    $message = $data['message'] ?? '';
    $display = 'none';
    $classname = 'message-wrapper';


    $content = 
    "<p class='message'>$message</p>
    <button class='button' onclick='dismissMessage()'>Back</button>
    <form class='confirmation-btn' action='' method='post'>
        <input type='submit' class='button btn-error' value='Delete'>
    </form>
    <form></>";

    include("../app/views/partials/popup.view.php");
?>


