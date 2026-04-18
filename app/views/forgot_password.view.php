<?php
    //setting page variables
    $title = 'Forgot Password: Openminds';
    $filename = 'forgot_password';
    $no_navbar = true;
    $add_back = true;

    //put header
    include_once('../app/views/partials/header.view.php');
?>

<div class="body-container">
    <div class="form-container container">
        <h1 class="heading">Change Password</h1>
        <p class="description">Enter your username or email and we'll send you an OTP to reset your password.</p>

        <?php if (!empty($data['message'])): ?>
            <div class="message error"><?= htmlspecialchars($data['message']) ?></div>
        <?php endif; ?>

        <form action="<?=ROOT?>forgotpassword/verify_email" method="post">
            <div class="input-group">
                <input type="text" placeholder="Username or Email" name="email_or_username" required value="<?= htmlspecialchars($_POST['email_or_username'] ?? '') ?>">
            </div>
            <input type="submit" class="button" value="Send OTP">
        </form>
        <a href="<?=ROOT?>login" class="link">Back to Log in</a>
    </div>
</div>

<?php 
    include_once('../app/views/partials/footer.view.php');
?>
