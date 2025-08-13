<?php
    //guard from unnessesary accesses
    // if (!isset($_SESSION['user_id']))
    // {
    //     header('Location: '.ROOT.'login');
    //     exit;
    // }

    //setting page variables
    $title = 'Update Profile: Openminds';
    $filename = 'profileupdate';

    //put header
    include_once('../app/views/partials/header.view.php');

?>
    <div class="container">

    <form class="form-container" action="<?=ROOT?>profileupdate/update" method="post" enctype="multipart/form-data"> 
        <h1>Update Profile</h1>

        <img class='preview' src="<?=$data['image_url']?>" alt="profile picture" >
        <div class='button-secondary upload-section'><div class="button-text">Upload a photo</div> <input type='file' class='upload-box' name='image' id='fileInput' accept='image/*'></div>

        <div class="field">
        <label for="display_name">Display Name:</label>
        <div class="input-group">
            <input type="text" name="display_name" placeholder="<?=$data['display_name']?>" value="<?=$data['display_name']?>">
        </div>
        </div>

        <!-- <input type="button" class="button submit" value="Let's Go"> -->
        <input class="button submit" type="submit" value="Save changes">
    </form>

    <form class="update-password-wrapper" action="<?=ROOT?>profileupdate/change_password" method="post">
        <input type="submit" value="Change password" class="button change-password-btn btn-none">
    </form>
    </div>

<?php 
    include_once('../app/views/partials/footer.view.php');
?>