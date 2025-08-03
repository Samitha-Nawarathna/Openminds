<?php

    if (isset($_SESSION['user_id']))
    {
        header('Location: '.ROOT.'dashboard');
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
    <link rel="stylesheet" href="<?php echo ROOT?>assets/css/register.view.css">
    <link rel="stylesheet" href="<?php echo ROOT?>assets/css/partials/message.view.css">
</head>
<body>
    <div class="body-container">
        <div class="form-container container">
            <h1 class="heading">Register</h1>

        <form action="<?=ROOT?>register/register" method="post">
            <div class="input-group">
            <input type="email" placeholder="Email" name="email" value="<?= htmlspecialchars($data['email'] ?? '') ?>">
            </div>
            <div class="input-group">
            <input type="text" placeholder="Username" name="username" value="<?= htmlspecialchars($data['username'] ?? '') ?>">
            </div>
            <div class="input-group">
            <input type="password" placeholder="Password" name="password" value="<?= htmlspecialchars($data['password'] ?? '') ?>">
            </div>
            <div class="input-group">
            <input type="password" placeholder="Confirm Password" name="confirm_password" value="<?= htmlspecialchars($data['password'] ?? '') ?>">
            </div>
            <input type="submit" class="button" value="Register">
        </form>
        <a href="<?php ROOT ?>login" class="link">Already have an account?</a>
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