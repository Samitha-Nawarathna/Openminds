<?php

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ".ROOT."index.php");
    exit;
}

?>

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
            <h1 class="heading">Enter otp code</h1>

            <div class="introduction">
                <p>an OTP code was sent to <span style="color:var(--color-error)"><?=$data['email']?></span>, will expires in <span style="color:var(--color-error)">05:00</span> min.</p>
            </div>

        <form action="<?=ROOT?>otppage/verify" method="post">
            <div class="input-group">
            <input type="text" placeholder="OTP" name="otp">
            </div>
            <input type="submit" class="button" value="Submit">
        </form>
        <form action="<?=ROOT ?>otppage/generate" method="post">
            <input type="submit" value="Resend" class='link'>
        </form>
        </div>

    </div>
    <?php

    if (isset($data["message"])) {
        $message = $data["message"];
        include_once('../app/views/partials/message.view.php');
    }

    ?>

    <script src="<?=ROOT?>assets/js/otp.view.js"></script>
</body>
</html>