<?php

    $title = "Create | Openminds";
    $filename = "notes/create";

    include_once "../app/views/partials/header.view.php";

?>

<div class="creator-wrapper">
        <form action="<?=ROOT?>/notes/create" method="POST" id="note-create-form">
            <div class="main-card-layout">
                
                <div class="input-section">
                    
                    <div class="input-group">
                        <label for="note-title">Title</label>
                        <input type="text" id="note-title" name="title" placeholder="Enter note title..." required>
                    </div>

                    <div class="input-group">
                        <label for="note-content">Content</label>
                        <textarea id="note-content" name="content" placeholder="Write your detailed note content here..." rows="15" required></textarea>
                    </div>
                    
                    <div class="input-group">
                        <label for="tags-input">Tags (Type and Enter, or Click Top Tags)</label>
                        <input type="hidden" id="hidden-tags-field" name="tags" value="">
                        
                        <div id="selected-tags-display" class="tags-display-area">
                            </div>
                        
                        <input type="text" id="tags-input" placeholder="e.g., Physics, Maths, design">
                    </div>
                    
                    <button type="submit" class="btn-save">Save</button>
                </div>

                <div class="sidebar-section">
                    
                    <div class="top-tags-container">
                        <h2>Top Tags</h2>
                        <div class="tags-list" id="top-tags-list">
                            <?php 
                            // Render mock top tags
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

                    <div class="sidebar-section">
                    
                    <div class="top-tags-container">
                        </div>
                    
                                    <div class="title-panel">
                                        <h2>Topic</h2>
                                            <input type="text" id="create-title-input" placeholder="Create or add title">
                                    </div>
                                    </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <?php
include_once "../app/views/partials/footer.view.php";
?>