
<?php
    //guard from unnessesary accesses
    if (!isset($_SESSION['user_id']))
    {
        header('Location: '.ROOT.'profile');
        exit;
    }

    $reset_mode = $data['reset_mode'] ?? false;

    //setting page variables
    $title = ($reset_mode ? 'Reset Password' : 'Change Password') . ': Openminds';
    $filename = 'change_pword';
    $no_navbar = true;
    $add_back = true;

    //put header
    include_once('../app/views/partials/header.view.php');

?>

    <div class="body-container">
        <div class="form-container container">
            <h1 class="heading"><?= $reset_mode ? 'Reset Password' : 'Change Password' ?></h1>

        <form action="<?= $reset_mode ? ROOT.'profileupdate/process_reset' : ROOT.'profileupdate/change_password' ?>" method="post">
            <?php if (!$reset_mode): ?>
                <div class="input-group">
                    <input type="password" placeholder="Current Password" name="current_password" required value="<?= htmlspecialchars($data['current_password'] ?? '') ?>">
                </div>
            <?php endif; ?>
            <div class="input-group">
                <input type="password" placeholder="New Password" name="new_password" required value="<?= htmlspecialchars($data['new_password'] ?? '') ?>">
            </div>
            <div class="input-group">
                <input type="password" placeholder="Confirm New Password" name="confirm_new_password" required value="<?= htmlspecialchars($data['confirm_new_password'] ?? '') ?>">
            </div>            
            <input type="submit" class="button" value="<?= $reset_mode ? 'Reset Password' : 'Update Password' ?>">
        </form>
        </div>

    </div>
<?php 
    include_once('../app/views/partials/footer.view.php');
?>