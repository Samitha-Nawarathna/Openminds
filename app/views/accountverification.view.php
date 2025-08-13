
<?php

    //setting page variables
    $title = 'Verify Account: Openminds';
    $filename = 'accountverification';

    //put header
    include_once('../app/views/partials/header.view.php');

?>

    <div class="verification-container body-container">
        <div class="form-container container">
            <h1 class="heading">Verify Account</h1>

        <form action="<?=ROOT?>accountverification/verify" method="post">
            <div class="input-group">
            <input type="text" placeholder="Username or Email" name="username" value="<?= htmlspecialchars($data['username'] ?? '') ?>">
            </div>
            <div class="input-group">
            <input type="password" placeholder="Password" name="password" value="<?= htmlspecialchars($data['password'] ?? '') ?>">
            </div>
            <input type="submit" class="button" value="Verify">
        </form>
        </div>

    </div>
<?php 
    include_once('../app/views/partials/footer.view.php');
?>