<?php  
    $title = "Create Expert Request | Openminds";
    $filename = "expertrequests/create";

    include_once "../app/views/partials/header.view.php";
?>

<div class="edit-wrapper">
    <form action="<?=ROOT?>expertrequest/create" id="detail_form" class="container" method="post" enctype="multipart/form-data">
    <h1 class="text-main">Create Request</h1>
        <div class="input-group">
            <input type="text" placeholder="Subject Name" name="subject" value="<?= htmlspecialchars($data['subject'] ?? '') ?>">
        </div>
        <div class="input-group">
            <textarea placeholder="Description" name="description" id="" cols="30" rows="10" value = "<?= htmlspecialchars($data['description'] ?? '') ?>"></textarea>
        </div>
        <!-- CV Section -->
        <label class="field-label" style="display:block; margin-top:20px; margin-bottom:8px; font-weight:600; color:#888; text-align:left;">Curriculum Vitae (Mandatory)</label>
        <div class="upload-instructions" style="margin-bottom: 10px; font-size: 0.85rem; color: var(--color-gray-600); text-align:left;">
            Please attach your CV to demonstrate your expertise. (PDF only, Max 5MB)
        </div>
        
        <div class='button-secondary upload-section'>
            <div class="button-text">
                <?php echo isset($data['cv_url']) ? "Change CV" : "Upload CV"; ?>
            </div> 
            <input type='file' class='upload-box' name='cv' id='cvInput' accept="application/pdf">
        </div>

        <div class="upload-wrapper" id="cv-wrapper" style="display:none;">
            <div class="input-group filename" id="cv-filename"></div>
            <button type="button" class="button btn-none btn-delete" id="btn-delete-cv">delete</button>
        </div>

        <!-- Supporting Documents Section -->
        <label class="field-label" style="display:block; margin-top:24px; margin-bottom:8px; font-weight:600; color:#888; text-align:left;">Supporting Documents (Optional)</label>
        <div class="upload-instructions" style="margin-bottom: 10px; font-size: 0.85rem; color: var(--color-gray-600); text-align:left;">
            Attach certificates, portfolios, or other proof. (PDF, JPG, PNG)
        </div>

        <!-- Individual file list container -->
        <div id="docs-file-list" style="display:flex; flex-direction:column; gap:8px; margin-bottom:10px;"></div>

        <!-- Upload button (will change to "add another file" after first selection) -->
        <div class='button-secondary upload-section' id="docs-upload-btn">
            <div class="button-text" id="docs-button-text">Upload Documents</div> 
            <input type='file' class='upload-box' name='supporting_docs[]' id='docsInput' multiple accept="application/pdf, image/png, image/jpeg">
        </div>

        <input type="submit" class="button" value="Send request">
    </form>
</div>

<?php

    include_once "../app/views/partials/footer.view.php";

?>