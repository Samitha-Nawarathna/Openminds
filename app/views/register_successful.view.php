<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register</title>

    <link rel="stylesheet" href="<?php echo ROOT?>assets/css/core.css">
    <link rel="stylesheet" href="<?php echo ROOT?>assets/css/register.view.css">
    <link rel="stylesheet" href="<?php echo ROOT?>assets/css/register_successful.view.css">
    <link rel="stylesheet" href="<?php echo ROOT?>assets/css/partials/message.view.css">
</head>
<body>
    <div id="message-content">
        <div class="background"></div>
        <div class="container message-container">
            <p class="message">Successfully registered!</p>
            
            <form action="<?=ROOT?>login" method="post">
                <input type="submit" class="button" value="Log in">
            </form>
        </div>
    </div>

    </div>
</body>
</html>