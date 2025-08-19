
<?php
    //guard from unnessesary accesses
    if (isset($_SESSION['user_id']))
    {
        header('Location: '.ROOT.'dashboard');
        exit;
    }

    //setting page variables
    $title = 'Login: Openminds';
    $filename = 'login';

    //put header
    include_once('../app/views/partials/header.view.php');

?>

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
    include_once('../app/views/partials/footer.view.php');
?>