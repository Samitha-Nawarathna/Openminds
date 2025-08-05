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
    <link rel="stylesheet" href="<?php echo ROOT?>assets/css/login.view.css">
    <link rel="stylesheet" href="<?php echo ROOT?>assets/css/partials/message.view.css">
</head>
<body>
    <div class="body-container">
        <div class="form-container container">
            <h1 class="heading">Log in</h1>

        <form action="<?=ROOT?>login/login" method="post">
            <div class="input-group">
            <input type="text" placeholder="Username or Email" name="username" value="<?= htmlspecialchars($data['username'] ?? '') ?>">
            </div>
            <div class="input-group">
            <input type="password" placeholder="Password" name="password" value="<?= htmlspecialchars($data['password'] ?? '') ?>">
            </div>
            <input type="submit" class="button" value="Log in">
        </form>
        <a href="<?=ROOT?>register" class="link">New user? Register</a>
        </div>

    </div>
    <?php

    if (isset($data["message"])) {
        $message = $data["message"];
        include_once('../app/views/partials/message.view.php');
    }

    ?>
    <script src="<?=ROOT?>/assets/js/login.view.js"></script>
</body>
</html>