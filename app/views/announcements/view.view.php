<?php
    $title = "View Announcement | Openminds";
    $filename = "announcements/view";
    include_once "../app/views/partials/header.view.php";
    
    // NOTE: This view assumes the Controller has already passed the 
    // $data['announcement'] object to this view.
    $announcement = $data['announcement'] ?? null;
    $id = $announcement['id'] ?? 0;
?>

<div class="main-content-container" style="max-width: 800px; margin: 0 auto;">
    
    <?php if ($announcement): ?>
    <div class="container">
        <h1 class="form-title">Announcement Details</h1>
        
        <!-- Announcement Content Display -->
        <div class="announcement-display-card">
            
            <!-- Title -->
            <h2 class="announcement-title-view"><?= htmlspecialchars($announcement['title']) ?></h2>

            <!-- Content -->
            <div class="announcement-content-view">
                <?= nl2br(htmlspecialchars($announcement['content'])) ?>
            </div>

            <!-- Metadata Group (Restructured for better alignment) -->
            <div class="metadata-group">
                <div class="metadata-item">
                    <label>Visual Style:</label>
                    <span class="tag-pill" data-style="<?= htmlspecialchars($announcement['style']) ?>">
                        <?= str_replace('-', ' ', htmlspecialchars($announcement['style'])) ?>
                    </span>
                </div>
                
                <!-- Status -->
                <?php if (isset($announcement['is_active'])): ?>
                    <div class="metadata-item">
                        <label>Visibility:</label>
                        <span class="status-badge-view" data-status="<?= $announcement['is_active'] == 1 ? 'active' : 'hidden' ?>">
                            <?= $announcement['is_active'] == 1 ? 'Active (Visible)' : 'Hidden (Draft)' ?>
                        </span>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Actions -->
        <div style="display: flex; gap: var(--space-sm); margin-top: var(--space-xl); width:100%">
            <!-- Cancel / Go Back Button -->
            <a href="<?=ROOT?>/announcements/admin/load_all" class="button-secondary no-style-link" style="text-align:center;">Back</a>
            
            <!-- Edit Button -->
            <a href="<?=ROOT?>/announcements/edit/<?= $id ?>" class="button btn-primary no-style-link" style="text-align:center;">Edit</a>
            
            <!-- Delete Button (Triggers Modal) -->
            <button id="delete-btn" class="button btn-error">Delete</button>
        </div>
    </div>
    <?php else: ?>
        <div class="container">
            <h3>Announcement not found</h3>
            <a href="<?=ROOT?>/announcements/admin/load_all">Go Back</a>
        </div>
    <?php endif; ?>
</div>

<!-- Simple Confirmation Modal for Delete -->
<div id="delete-modal" class="modal-overlay" style="display: none;">
    <div class="container modal-content" style="max-width: 400px; text-align: center;">
        <h2>Delete Announcement?</h2>
        <p style="color: var(--color-text-muted); margin-bottom: var(--space-md);">This action cannot be undone.</p>
        <div class="btns" style="width: 100%; justify-content: center; display: flex; gap: 10px;">
            <button class="button-secondary" onclick="closeModal()">Cancel</button>
            <button class="button btn-error" id="confirm-delete-btn">Delete</button>
        </div>
    </div>
</div>

<script>
    const ROOT = '<?=ROOT?>';
    const API_ROOT = `${ROOT}/announcements/api`;
    const announcementId = <?= $id ?>;
    let deleteTargetId = null;

    // --- Modal Functions ---
    function openDeleteModal(id) {
        deleteTargetId = id;
        document.getElementById('delete-modal').style.display = 'flex';
    }

    function closeModal() {
        document.getElementById('delete-modal').style.display = 'none';
        deleteTargetId = null;
    }

    // --- Event Listeners ---
    document.addEventListener('DOMContentLoaded', () => {
        // Only attach delete listener if an announcement was found
        if (announcementId > 0) {
            document.getElementById('delete-btn').addEventListener('click', () => {
                openDeleteModal(announcementId);
            });
        }

        document.getElementById('confirm-delete-btn').addEventListener('click', async () => {
            if (!deleteTargetId) return;
            
            // Perform the DELETE API call
            try {
                const res = await fetch(`${API_ROOT}/admin/delete/${deleteTargetId}`, { method: 'POST' });
                const json = await res.json();
                
                closeModal();
                
                if (json.success) {
                    // Redirect back to the admin list on successful deletion
                    window.location.href = `${ROOT}/announcements/admin`;
                } else {
                    console.error('Delete failed:', json.message || 'Unknown error.');
                    // Simple error display (using a basic message box/alert replacement for now)
                    alert('Error: ' + (json.message || 'Failed to delete announcement.'));
                }
            } catch (e) {
                closeModal();
                console.error("Network error during delete:", e);
                alert('Network error occurred during deletion.');
            }
        });
    });
    
    // Simple alert replacement for error display
    function alert(message) {
        // Implement a custom modal/toast for non-critical alerts if needed. 
        // For now, logging to console and a simple implementation is sufficient.
        console.warn("User Message:", message);
    }
</script>

<style>
    /* Styling for the View Page */
    .announcement-display-card {
        padding: var(--space-lg);
        background: var(--color-surface);
        border: 1px solid var(--color-border);
        border-radius: var(--radius-lg);
        box-shadow: var(--shadow-sm);
    }

    .announcement-title-view {
        font-size: var(--font-size-lg);
        font-weight: 700;
        margin-bottom: var(--space-md);
        color: var(--color-text);
    }

    .announcement-content-view {
        font-size: var(--font-size-md);
        line-height: 1.6;
        color: var(--color-text-muted);
        white-space: pre-wrap; /* Preserves line breaks from nl2br */
    }
    
    /* --- NEW STYLES FOR METADATA GROUPING --- */
    .metadata-group {
        display: flex;
        gap: var(--space-lg); /* Space between the two groups (Style and Status) */
        margin-top: var(--space-md);
        flex-wrap: wrap;
        padding-top: var(--space-sm);
        border-top: 1px dashed var(--color-border-light);
    }
    .metadata-item {
        display: flex;
        align-items: center;
        gap: var(--space-xs); /* Space between label and pill/badge */
    }
    .metadata-item label {
        font-size: var(--font-size-sm);
        color: var(--color-text-muted);
        font-weight: 600; /* Make label slightly bolder for distinction */
        margin: 0; 
    }

    .status-badge-view { 
        font-size: var(--font-size-sm); 
        padding: 4px 10px; 
        border-radius: 6px; 
        margin-left: 0; /* Removed old margin-left */
        font-weight: 500;
        display: inline-block;
    }
    .status-badge-view[data-status="active"] {
        background-color: var(--color-green-100);
        color: var(--color-green-700);
    }
    .status-badge-view[data-status="hidden"] {
        background-color: var(--color-yellow-100);
        color: var(--color-yellow-700);
    }

    /* Modal Overlay Styles */
    .modal-overlay {
        position: fixed; top: 0; left: 0; width: 100%; height: 100%;
        background: rgba(0,0,0,0.6); z-index: 1000;
        display: flex; justify-content: center; align-items: center;
        backdrop-filter: blur(3px);
    }
    .modal-content {
        background-color: var(--color-background);
        padding: 30px;
        border-radius: 12px;
        box-shadow: var(--shadow-lg);
        animation: fadeIn 0.3s ease-out;
    }

    @keyframes fadeIn {
        from { opacity: 0; transform: scale(0.95); }
        to { opacity: 1; transform: scale(1); }
    }
</style>

<?php include_once "../app/views/partials/footer.view.php"; ?>