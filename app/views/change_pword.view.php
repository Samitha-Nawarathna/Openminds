
<?php
    //guard from unnessesary accesses
    if (!isset($_SESSION['user_id']))
    {
        header('Location: '.ROOT.'profile');
        exit;
    }

    //setting page variables
    $title = 'Change Password: Openminds';
    $filename = 'change_pword';
    $no_navbar = true;
    $add_back = true;

    //put header
    include_once('../app/views/partials/header.view.php');

?>

    <div class="body-container">
        <div class="form-container container">
            <h1 class="heading">Change Password</h1>

        <form action="<?=ROOT?>profileupdate/change_password" method="post">
            <div class="input-group">
                <input type="password" placeholder="Current Password" name="current_password" value="<?= htmlspecialchars($data['current_password'] ?? '') ?>">
            </div>
            <div class="input-group">
                <input type="password" placeholder="New Password" name="new_password" value="<?= htmlspecialchars($data['new_password'] ?? '') ?>">
            </div>
            <div class="input-group">
                <input type="password" placeholder="Confirm New Password" name="confirm_new_password" value="<?= htmlspecialchars($data['new_password'] ?? '') ?>">
            </div>            
            <input type="submit" class="button" value="Update Password">
        </form>
        </div>

    </div>
<?php 
    include_once('../app/views/partials/footer.view.php');
?>