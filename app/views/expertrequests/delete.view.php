<?php

    //setting page variables
    $title = 'Confirm Deletion: Openminds';
    $filename = 'profiledelete';

    //put header
    include_once('../app/views/partials/header.view.php');

?>

<div class="profiledelete-container">
        <div class="content container">
        <p class='message'>Please confirm deletion</p>

        <div class="btns">
        <form action="<?=ROOT?>expertrequests/view" method="post">
            <input type="submit" value="Back" class="button btn-none">
        </form>
        <form action="<?=ROOT?>confirmdeletion/delete" method="post">
            <input type="submit" value="Confirm" class="button btn-error">
        </form>
        </div>
        </div>
    </div>
</div>



<?php 
    include_once('../app/views/partials/footer.view.php');
?>