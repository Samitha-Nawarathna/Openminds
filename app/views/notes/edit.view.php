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
                        <label for="note-title">Title</label>
                        <input type="text" id="note-title" name="title" placeholder="Enter note title..." value="<?= htmlspecialchars($data['note']['title']) ?>" required>
                    </div>

                    <div class="input-group">
                        <label for="note-content">Content</label>
                        <textarea id="note-content" name="content" placeholder="Write your detailed note content here..." rows="15" required><?= htmlspecialchars($data['note']['content']) ?></textarea>
                    </div>
                    
                    <div class="input-group">
                        <label for="tags-input">Tags (Type and Enter, or Click Top Tags)</label>
                        <input type="hidden" id="hidden-tags-field" name="tags" value="">
                        
                        <div id="selected-tags-display" class="tags-display-area">
                            </div>
                        
                        <input type="text" id="tags-input" placeholder="e.g., Physics, Maths, design">
                    </div>
                    
                    <button type="submit" class="btn-save">Update Note</button>
                </div>

                <div class="sidebar-section">
                    
                    <div class="top-tags-container">
                        <h2>Top Tags</h2>
                        <div class="tags-list" id="top-tags-list">
                            <?php 
                            foreach ($data['top_tags'] as $tag): 
                            ?>
                                <span 
                                    class="tag-pill tag-clickable tag-<?= strtolower(str_replace(' ', '-', $tag['name'])) ?>" 
                                    data-tag-name="<?= htmlspecialchars($tag['name']) ?>"
                                >
                                    <?= htmlspecialchars($tag['name']) ?>
                                </span>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    
                    <div class="title-panel">
                        <h2>Topic</h2>
                        <div class="current-title-display">
                        </div>
                        <input type="text" id="create-title-input"  placeholder="Create or add title" value = "<?= htmlspecialchars($data['note']['topic']) ?>">
                    </div>

                </div>
            </div>
        </form>
    </div>

    <script>
        // Pass initial tags to JavaScript for pre-loading
        const INITIAL_TAGS = <?= json_encode($data['note']['tags']) ?>;
    </script>

<?php
    include_once "../app/views/partials/footer.view.php";
?>