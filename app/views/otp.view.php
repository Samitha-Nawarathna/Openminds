<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register</title>

    <link rel="stylesheet" href="<?php echo ROOT?>assets/css/core.css">
    <link rel="stylesheet" href="<?php echo ROOT?>assets/css/otp.view.css">
    <link rel="stylesheet" href="<?php echo ROOT?>assets/css/partials/message.view.css">
</head>
<body>
    <div class="body-container">
        <div class="form-container container">
            <h1 class="heading">Enter OTP code</h1>

        <form action="<?=ROOT?>otppage/verify" method="post">
            <div class="input-group">
            <input type="text" placeholder="OTP" name="otp">
            </div>
            <input type="submit" class="button" value="Register">
        </form>
        <a href="<?=ROOT ?>otppage/generate" class="link">resend</a>
        </div>

    </div>
    <?php

    if (isset($data["message"])) {
        $message = $data["message"];
        include_once('../app/views/partials/message.view.php');
    }

    ?>
</body>
</html>