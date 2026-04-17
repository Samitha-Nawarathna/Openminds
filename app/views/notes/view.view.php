<?php

    $title = $data["note"]["title"]." | Openminds";
    $filename = "notes/view";
    $add_back = true;

    include_once "../app/views/partials/header.view.php";
    include "../app/views/partials/focus_timer.php";
    include_once '../app/views/partials/Rnavbar.view.php';


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
            
            <?php if ($data['note']['is_owner'] && !empty($data['note']['shared_with'])): ?>
                <div class="shared-with-section">
                    <span class="shared-with-label">Shared with:</span>
                    <span class="shared-user"><?= htmlspecialchars($data['note']['shared_with'][0]) ?></span>
                    <?php if (count($data['note']['shared_with']) > 1): ?>
                        <span class="more-link" onclick="openSharedUsersModal()">+<?= count($data['note']['shared_with']) - 1 ?> more</span>
                    <?php endif; ?>
                </div>

                <!-- Modal for more users -->
                <div id="sharedUsersModal" class="custom-modal">
                    <div class="custom-modal-content">
                        <div class="modal-header">
                            <h3>Shared With</h3>
                            <span class="close-modal" onclick="closeSharedUsersModal()">&times;</span>
                        </div>
                        <div class="modal-body">
                            <ul class="shared-users-list">
                                <?php foreach ($data['note']['shared_with'] as $username): ?>
                                    <li><?= htmlspecialchars($username) ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <div class="action-buttons-bottom">
                <?php if ($data['note']['is_owner']): ?>
                    <button class="btn-share button btn-primary"><a href="<?=ROOT?>/notes/share?note_id=<?=$data['note']['id']?>" class="no-style-link">Share</a></button>
                    <button class="btn-edit button btn-none"><a href="<?=ROOT?>/notes/edit/<?=$data['note']['id']?>" class="no-style-link">Edit</a></button>
                    
                    <form id="delete-note-form" action="<?=ROOT?>/notes/delete" method="POST" style="display: none;">
                        <input type="hidden" name="note_id" value="<?=$data['note']['id']?>">
                    </form>
                    <button class="btn-delete button btn-error"><a href="#" class="no-style-link">Delete</a></button>
                <?php endif; ?>
                <?php if($data['note']['is_shared']): ?>
                    <div class="shared-indicator">
                        <span class="shared-by-text">Shared by <strong><?= htmlspecialchars($data['note']['owner_name']) ?></strong></span>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        
        <style>
            .shared-indicator {
                padding: var(--space-xs) var(--space-sm);
                background-color: var(--color-blue-50);
                border-radius: var(--radius-md);
                border: 1px solid var(--color-blue-100);
                display: inline-block;
            }
            .shared-by-text {
                font-size: var(--font-size-sm);
                color: var(--color-primary);
            }

            /* Shared with section styles */
            .shared-with-section {
                margin: var(--space-md) 0;
                padding: var(--space-xs) 0;
                border-top: 1px solid var(--color-border);
                display: flex;
                align-items: center;
                gap: var(--space-xs);
                font-size: var(--font-size-sm);
            }
            .shared-with-label {
                color: var(--color-text-light);
                font-weight: 500;
            }
            .shared-user {
                color: var(--color-text-dark);
                font-weight: 600;
            }
            .more-link {
                color: var(--color-primary-accent);
                cursor: pointer;
                font-weight: 700;
                text-decoration: underline;
                transition: color 0.2s;
            }
            .more-link:hover {
                color: var(--color-blue-700);
            }

            /* Custom Modal Styles */
            .custom-modal {
                display: none;
                position: fixed;
                z-index: 2000;
                left: 0;
                top: 0;
                width: 100%;
                height: 100%;
                background-color: rgba(0,0,0,0.5);
                backdrop-filter: blur(4px);
            }
            .custom-modal-content {
                background-color: white;
                margin: 15% auto;
                padding: 0;
                border-radius: var(--radius-md);
                width: 300px;
                box-shadow: 0 10px 25px rgba(0,0,0,0.2);
                overflow: hidden;
            }
            .modal-header {
                padding: var(--space-sm) var(--space-md);
                background-color: var(--color-gray-100);
                border-bottom: 1px solid var(--color-border);
                display: flex;
                justify-content: space-between;
                align-items: center;
            }
            .modal-header h3 {
                margin: 0;
                font-size: var(--font-size-base);
            }
            .close-modal {
                font-size: 24px;
                font-weight: bold;
                cursor: pointer;
                color: var(--color-text-light);
            }
            .modal-body {
                padding: var(--space-md);
                max-height: 300px;
                overflow-y: auto;
            }
            .shared-users-list {
                list-style: none;
                padding: 0;
                margin: 0;
            }
            .shared-users-list li {
                padding: var(--space-xs) 0;
                border-bottom: 1px solid var(--color-gray-100);
                color: var(--color-text-dark);
                font-weight: 500;
            }
            .shared-users-list li:last-child {
                border-bottom: none;
            }
        </style>

        <script>
            function openSharedUsersModal() {
                document.getElementById('sharedUsersModal').style.display = 'block';
            }
            function closeSharedUsersModal() {
                document.getElementById('sharedUsersModal').style.display = 'none';
            }
            // Close modal when clicking outside
            window.onclick = function(event) {
                const modal = document.getElementById('sharedUsersModal');
                if (event.target == modal) {
                    modal.style.display = 'none';
                }
            }
        </script>
        
    </div>
</div>

<div style="position: fixed; top: 10px; right: 55vw; z-index: 999;">
    <button onclick="window.open_note(1)">Open Note 1</button>
    <button onclick="window.open_note(2)">Open Note 2</button>
</div>

<?php
    include_once "../app/views/partials/footer.view.php";
?>