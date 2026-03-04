<?php

    $title = "Share | Openminds";
    $filename = "notes/share";

    include_once "../app/views/partials/header.view.php";
    include "../app/views/partials/focus_timer.php";
    include_once '../app/views/partials/Rnavbar.view.php';

?>

<?php
    // Helper to determine pill color class
    function getUserPillClass($name) {
        $colors = ['red', 'blue', 'green', 'pink', 'purple', 'orange'];
        // Simple hash-based assignment for consistency
        $index = crc32($name) % count($colors);
        return 'user-pill-' . $colors[$index];
    }

?>

<div class="share-wrapper">
        <form action="<?= htmlspecialchars($data['form_action_url']) ?>" method="POST" id="share-form">
            
            <input type="hidden" name="note_id" value="<?= htmlspecialchars($data['note_id']) ?>">
            <input type="hidden" name="shared_users" id="shared-users-field" value="">

            <div class="share-card">
                <header class="share-header">
                    Share <span class="note-title-span"><?= htmlspecialchars($data['note_title']) ?></span> with
                </header>

                <div class="shared-users-list" id="shared-users-list">
                    <?php 
                    foreach ($data['initial_shared_users'] as $user): 
                        $className = getUserPillClass($user['name']);
                    ?>
                        <span 
                            class="user-pill <?= $className ?>" 
                            data-user-id="<?= htmlspecialchars($user['id']) ?>"
                            data-user-name="<?= htmlspecialchars($user['name']) ?>"
                        >
                            <?= htmlspecialchars($user['name']) ?> <span class="remove-user">&times;</span>
                        </span>
                    <?php endforeach; ?>
                </div>

                <div class="user-input-container">
                    <input type="text" id="user-search-input" placeholder="Type user name to add">
                    
                    <ul class="user-suggestions-list" id="user-suggestions-list" style="display:none;">
                        </ul>
                </div>
                
                <button type="submit" class="btn-share-submit">Share</button>
            </div>
        </form>
    </div>

    <script>
        // Pass initial user data to JavaScript
        const INITIAL_SHARED_USERS = <?= json_encode($data['initial_shared_users']) ?>;
    </script>

<?php
    include_once "../app/views/partials/footer.view.php";
?>