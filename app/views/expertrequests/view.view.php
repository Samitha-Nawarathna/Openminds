<?php  
    $title = "View Expert Request | Openminds";
    $filename = "expertrequests/view";

    include_once "../app/views/partials/header.view.php";
?>

<div class="view-wrapper">
    <div class="container request-content">
        <div class="display-group">
            <?= htmlspecialchars($data['subject'] ?? '') ?>
        </div>
        <div class="display-group">
            <?= htmlspecialchars($data['description'] ?? '') ?>
        </div>

        <div class="upload-wrapper">
            <div class="input-group">
                Document.pdf
            </div>
            <input type="button" class="button btn-none btn-delete" value="download">
        </div>

</div>
</div>

<?php

    include_once "../app/views/partials/footer.view.php";

?>