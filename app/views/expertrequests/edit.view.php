<?php  
    $title = "Edit Expert Request | Openminds";
    $filename = "expertrequests/edit";

    include_once "../app/views/partials/header.view.php";
?>

<div class="edit-wrapper">
    <form action="<?=ROOT?>expertrequest/update" id="detail_form" class="container" method="post" enctype="multipart/form-data">
    <h1 class="text-main">Edit Request</h1>
        
        <!-- Error message display -->
        <div class="message-wrapper" style="display:none; margin-bottom: 16px;">
            <div class="message" style="padding: 12px; background-color: #fee; color: #c33; border-radius: 8px; font-size: 0.9rem;"></div>
        </div>
        
        <input type="hidden" name="id" value="<?= htmlspecialchars($data['id']) ?>">
        
        <div class="subject-autocomplete-wrapper">
            <div class="input-group">
                <input type="text" placeholder="Subject Name" name="subject" id="subjectInput" autocomplete="off" value="<?= htmlspecialchars($data['subject'] ?? '') ?>">
            </div>
            <ul id="subject-suggestions" class="subject-suggestions" style="display:none;"></ul>
        </div>
        <p class="subject-hint">Start typing to see matching subjects from the database.</p>
        <div class="input-group">
            <textarea placeholder="Description" name="description" id="" cols="30" rows="10"><?= htmlspecialchars($data['description'] ?? '') ?></textarea>
        </div>
        
        <!-- CV Section -->
        <label class="field-label" style="display:block; margin-top:20px; margin-bottom:8px; font-weight:600; color:#888; text-align:left;">Curriculum Vitae (Mandatory)</label>
        <div class="upload-instructions" style="margin-bottom: 10px; font-size: 0.85rem; color: var(--color-gray-600); text-align:left;">
            <?= !empty($data['proof_link']) ? 'Update your CV if needed. (PDF only, Max 5MB)' : 'Please attach your CV. (PDF only, Max 5MB)' ?>
        </div>
        
        <!-- Existing CV Display -->
        <?php if (!empty($data['proof_link']) && file_exists($data['proof_link'])): ?>
        <div class="upload-wrapper" style="display:flex; margin-bottom:10px;">
            <div class="input-group filename">Current: CV.pdf</div>
        </div>
        <?php endif; ?>
        
        <div class='button-secondary upload-section'>
            <div class="button-text">
                <?php echo !empty($data['proof_link']) ? "Change CV" : "Upload CV"; ?>
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

        <!-- Existing files display -->
        <div id="existing-files-list">
            <?php 
            if (!empty($data['supporting_docs'])):
                foreach($data['supporting_docs'] as $doc): 
            ?>
            <div class="upload-wrapper" style="display:flex; margin-bottom:8px;">
                <div class="input-group filename">Existing: <?= htmlspecialchars($doc) ?></div>
                <button type="button" class="button btn-none btn-delete" onclick="markFileForDeletion('<?= htmlspecialchars($doc) ?>', this)">delete</button>
            </div>
            <?php 
                endforeach;
            endif; 
            ?>
        </div>
        
        <!-- Hidden input to track files marked for deletion -->
        <input type="hidden" name="files_to_delete" id="files-to-delete" value="">

        <!-- New file list container -->
        <div id="docs-file-list" style="display:flex; flex-direction:column; gap:8px; margin-bottom:10px;"></div>

        <!-- Upload button -->
        <div class='button-secondary upload-section' id="docs-upload-btn">
            <div class="button-text" id="docs-button-text">Add More Documents</div> 
            <input type='file' class='upload-box' name='supporting_docs[]' id='docsInput' multiple accept="application/pdf, image/png, image/jpeg">
        </div>

        <input type="submit" class="button" value="Update request">
    </form>
</div>

<script>
// Track files to delete
let filesToDelete = [];

function markFileForDeletion(filename, buttonElement) {
    filesToDelete.push(filename);
    document.getElementById('files-to-delete').value = JSON.stringify(filesToDelete);
    
    // Hide the file row
    buttonElement.closest('.upload-wrapper').style.display = 'none';
}
</script>

<?php

    include_once "../app/views/partials/footer.view.php";

?>