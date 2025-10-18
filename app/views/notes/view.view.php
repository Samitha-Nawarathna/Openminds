<?php

    $title = $data["note"]["title"]." | Openminds";
    $filename = "notes/view";

    include_once "../app/views/partials/header.view.php";

?>

<div class="note-viewer-wrapper">
        <div class="note-card">
            
            <div class="content-section">
                <input type="text" class="note-title-input" value="<?= htmlspecialchars($data['note']['title']) ?>" readonly>
                <textarea class="note-content-textarea" readonly><?= htmlspecialchars($data['note']['content']) ?></textarea>
                
                <div class="action-buttons-bottom">
                    <button class="btn-share button btn-primary"><a href="<?=ROOT?>/notes/share?id=<?=$data['note']['id']?>" class="no-style-link">Share</a></button>
                    <button class="btn-edit button btn-none"><a href="<?=ROOT?>/notes/edit?id=<?=$data['note']['id']?>" class="no-style-link">Edit</a></button>
                    <button class="btn-delete button btn-error"><a href="<?=ROOT?>/notes/delete?id=<?=$data['note']['id']?>" class="no-style-link">Delete</a></button>
                </div>
            </div>

            <div class="sidebar-section">
                
                <div class="tags-container">
                    <h2>Tags</h2>
                    <div class="tags-display">
                        <?php foreach ($data['note']['tags'] as $tag): ?>
                            <span class="tag-pill tag-<?= strtolower(str_replace(' ', '-', $tag)) ?>"><?= htmlspecialchars($tag) ?></span>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div class="focus-timer-container">
                    <h2>Set Focus Period</h2>
                    <div class="timer-controls">
                        <input type="number" id="timer-minutes-input" value="30" min="1" max="180">
                        <span class="unit-label">min</span>
                        <button id="timer-btn" class="btn-start">Start</button>
                    </div>
                    <div id="countdown-display">30:00</div>
                </div>
            </div>
        </div>
    </div>

<?php
    include_once "../app/views/partials/footer.view.php";
?>