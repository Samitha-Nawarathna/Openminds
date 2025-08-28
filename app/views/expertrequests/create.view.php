<?php  
    $title = "Create Expert Request | Openminds";
    $filename = "expertrequests/create";

    include_once "../app/views/partials/header.view.php";
?>

<div class="edit-wrapper">
    <form action="<?=ROOT?>expertrequest/create" id="detail_form" class="container" method="post" enctype="multipart/form-data">
        <div class="input-group">
            <input type="text" placeholder="Subject Name" name="subject" value="<?= htmlspecialchars($data['subject'] ?? '') ?>">
        </div>
        <div class="input-group">
            <textarea placeholder="Description" name="description" id="" cols="30" rows="10" value = "<?= htmlspecialchars($data['description'] ?? '') ?>"></textarea>
        </div>
        <div class='button-secondary upload-section'><div class="button-text"><?php if (isset($data['file_url'])) {
            echo "Update the file";
        }else
        {
            echo "Upload a file";
        }?></div> <input type='file' class='upload-box' name='file' id='fileInput' accept="application/pdf"></div>

        <div class="upload-wrapper">
            <div class="input-group filename">
                Document.pdf
            </div>
            <input type="button" class="button btn-none btn-delete" value="delete">
        </div>

        <input type="submit" class="button" value="Send request">
    </form>
</div>

<?php

    include_once "../app/views/partials/footer.view.php";

?>