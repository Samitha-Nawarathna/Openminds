<?php

    $title = $data["note"]["title"]." | Openminds";
    $filename = "notes/view";
    $add_back = true;

    include_once "../app/views/partials/header.view.php";
    include "../app/views/partials/focus_timer.php";



?>

<div class="note-viewer-wrapper">
    <div class="note-card">
        
        <div class="main-content-area">

            <div class="note-title-input"><?= htmlspecialchars($data['note']['title']) ?></div>
            
            <div class="tags-collapsible-container">
                <div id="tags-content" class="tags-collapsible-content.show">
                    <div class="tags-display">
                    <?php foreach ($data['note']['tags'] as $tag): ?>
                            <?php 
                                // NEW: Get the consistent colors for the current tag
                                $colors = generateTagColor($tag);
                            ?>
                            <span 
                                class="tag-pill" 
                                style="background-color: <?= $colors['bg'] ?>; color: <?= $colors['text'] ?>;">
                                <?= htmlspecialchars($tag) ?>
                            </span>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <!-- <div class="note-content-textarea"></div> -->
            <quill-editor 
                id="note-content-textarea"
                readonly
                height="400px"
                content="<?php echo htmlspecialchars($data['note']['content']) ?>"
                >
            </quill-editor>
            <div id="fixed-timer-container">    
            <div class="action-buttons-bottom">
                <button class="btn-share button btn-primary"><a href="<?=ROOT?>/notes/share?id=<?=$data['note']['id']?>" class="no-style-link">Share</a></button>
                <button class="btn-edit button btn-none"><a href="<?=ROOT?>/notes/edit/<?=$data['note']['id']?>" class="no-style-link">Edit</a></button>
                
                <form id="delete-note-form" action="<?=ROOT?>/notes/delete" method="POST" style="display: none;">
                    <input type="hidden" name="note_id" value="<?=$data['note']['id']?>">
                </form>
                <button class="btn-delete button btn-error"><a href="#" class="no-style-link">Delete</a></button>
            </div>
        </div>
        
    </div>
</div>

<div style="position: fixed; top: 10px; right: 55vw; z-index: 999;">
    <button onclick="window.open_note(1)">Open Note 1</button>
    <button onclick="window.open_note(2)">Open Note 2</button>
</div>

<?php
    include_once "../app/views/partials/footer.view.php";
?>