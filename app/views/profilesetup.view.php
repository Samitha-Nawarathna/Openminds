<?php
    //guard from unnessesary accesses
    if (!isset($_SESSION['user_data']))
    {
        header('Location: '.ROOT.'dashboard');
        exit;
    }

    //setting page variables
    $title = 'Set up your profile';
    $filename = 'profilesetup';

    //put header
    include_once('../app/views/partials/header.view.php');

?>

    <form class="container form-container" action="<?=ROOT?>profilesetup/set" method="post" enctype="multipart/form-data"> 
        <h1>Setup Profile</h1>

        <img class='preview' src="<?=$data['default_image_url']?>" alt="profile picture" >
        <div class='button-secondary upload-section'><div class="button-text">Upload a photo</div> <input type='file' class='upload-box' name='image' id='fileInput' accept='image/*'></div>

        <div class="field">
        <label for="display_name">Display Name:</label>
        <div class="input-group">
            <input type="text" name="display_name" placeholder="<?=$data['username']?>" value="<?=$data['username']?>">
        </div>
        </div>

        <!-- <input type="button" class="button submit" value="Let's Go"> -->
        <input class="button submit" type="submit" value="Let's Go">
    </form>

    <?php
        include_once('../app/views/partials/upload.view.php');
        include_once('../app/views/partials/footer.view.php');
    ?>