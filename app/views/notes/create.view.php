<?php

    $title = "Create | Openminds";
    $filename = "notes/create";

    include_once "../app/views/partials/header.view.php";

?>


<div class="creator-wrapper">
    <!-- <h2>Create Note</h2> -->
    <form action="<?=ROOT?>/notes/create" method="POST" id="note-create-form">
        
        <div class="main-card-layout"> 
            <div class="input-section">
                
                <div class="input-group">
                    <input type="text" id="note-title" class="note-data" name="title" placeholder="Title" required>
                </div>

                <div class="input-group">
                    <?php 
                        $editor_id = "editor";
                        $hidden_input_name = "content";
                        $initial_content = json_encode([]);
                        $read_only = false;
                        $placeholder_text = "Start writing your note here...";
                        include "../app/views/partials/text_editor.php"; 
                    ?>
                </div>
                <input type="hidden" name="<?php echo $hidden_input_name; ?>" id="hidden_input_<?php echo $editor_id; ?>">
                <input type="hidden" id="hidden-tags-field" name="tags" value="">
                <input type="hidden" id="hidden-topic-field" name="topic" value="">

                <button id = 'open-metadata-modal-btn' type="button" class="button">Continue to Save</button>
            </div>

            </div>
    </form>
</div>

<div id="metadata-modal" class="modal-overlay">
    <div class="modal-content">
        <span class="close-btn" id="close-metadata-modal-btn">&times;</span>
        <h2>Details</h2>

        <div class="input-group">
            <label for="modal-topic-input">Topic</label>
            <input type="text" id="modal-topic-input" placeholder="Enter or select topic name">
        </div>

        <div class="input-group">
            <label for="modal-tags-input">Tags (Type and Enter)</label>
            <div id="modal-selected-tags-display" class="tags-display-area">
                </div>
            <input type="text" id="modal-tags-input" placeholder="e.g., Physics, Maths, design">
        </div>

        <div class="top-tags-container">
            <h3>Top Tags</h3>
            <div class="tags-list" id="modal-top-tags-list">
                </div>
        </div>

        <button type="button" class="button" id="save-note-metadata-btn">Save</button>

    </div>
</div>

    

<?php
    include_once "../app/views/partials/footer.view.php";
?>