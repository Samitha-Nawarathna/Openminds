<?php

    $title = "Edit | Openminds";
    $filename = "notes/edit";

    include_once "../app/views/partials/header.view.php";
    // show($data);
    // exit;

?>

<div class="creator-wrapper">
    <form action="<?=ROOT?>/notes/edit?id=<?= htmlspecialchars($data['note']['id']) ?>" method="POST" id="note-update-form">
        
        <input type="hidden" name="note_id" id="note-id-field" value="<?= htmlspecialchars($data['note']['id']) ?>">
        
        <div class="main-card-layout">
            
            <div class="input-section">
                
                <div class="input-group">
                    <input type="text" id="note-title" class="note-data" name="title" placeholder="Title" value="<?= htmlspecialchars($data['note']['title']) ?>" required>
                </div>

                <div class="input-group">
                    <textarea id="note-content" name="content" placeholder="Write your detailed note content here..." rows="18" required><?= htmlspecialchars($data['note']['content']) ?></textarea>
                </div>
                
                <input type="hidden" id="hidden-tags-field" name="tags" value="">
                <input type="hidden" id="hidden-topic-field" name="topic" value="">

                <button type="button" class="button" id="open-metadata-modal-btn">Continue to Update</button>
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
                <?php 
                // Re-using the PHP loop to render top tags so JS can attach listeners
                foreach ($data['top_tags'] as $tag): 
                ?>
                    <span 
                        class="tag-pill tag-clickable" 
                        data-tag-name="<?= htmlspecialchars($tag['name']) ?>"
                    >
                        <?= htmlspecialchars($tag['name']) ?>
                    </span>
                <?php endforeach; ?>
            </div>
        </div>

        <button type="button" class="button" id="save-note-metadata-btn">Update Note</button>

    </div>
</div>

<script>
    // Pass initial data to JavaScript for pre-loading state
    const INITIAL_TAGS = <?= json_encode($data['note']['tags']) ?>;
    const INITIAL_TOPIC = "<?= htmlspecialchars($data['note']['topic']) ?>";
</script>

<?php
    include_once "../app/views/partials/footer.view.php";
?>