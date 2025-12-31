
<?php
    $get_message = $_GET['message'] ?? '';
    $message = $data['message'] ?? $get_message;
    $display = 'none';
    $classname = 'message-wrapper';

    if ($message)
    {
        $display = 'block';
    }

    $content = "<p class='message'>$message</p><button class='button' onclick='dismissMessage()'>Back</button>";

    include("../app/views/partials/popup.view.php");
?>


