<?php

    $title = "Edit Announcement | Openminds";
    $filename = "announcements/edit";
    include_once "../app/views/partials/header.view.php";
    
    // NOTE: This view assumes the Controller has already passed the 
    // $data['announcement'] object to this view.
    $announcement = $data['announcement'] ?? null;
?>

<div class="main-content-container" style="max-width: 800px; margin: 0 auto;">
    
    <?php if ($announcement): ?>
    <div class="container">
        <h1 class="form-title">Edit Announcement</h1>

        <form id="edit-form">
            <!-- Title -->
            <div class="input-group">
                <input type="text" name="title" value="<?= htmlspecialchars($announcement['title']) ?>" placeholder="Enter title" required>
            </div>

            <!-- Content -->
            <div class="input-group">
                <textarea name="content" rows="6" required><?= htmlspecialchars($announcement['content']) ?></textarea>
            </div>

            <!-- Style Selector -->
            <div class="display-group">
                <label style="font-size: var(--font-size-sm); color: var(--color-text-muted); margin-bottom: 8px;">Visual Style</label>
                <div class="style-selector">
                    <label class="style-option">
                        <input type="radio" name="style" value="primary-accent" <?= $announcement['style'] == 'primary-accent' ? 'checked' : '' ?>>
                        <span class="tag-pill" data-style="primary-accent">Primary Accent</span>
                    </label>
                    <label class="style-option">
                        <input type="radio" name="style" value="warning" <?= $announcement['style'] == 'warning' ? 'checked' : '' ?>>
                        <span class="tag-pill" data-style="warning">Warning</span>
                    </label>
                    <label class="style-option">
                        <input type="radio" name="style" value="info" <?= $announcement['style'] == 'info' ? 'checked' : '' ?>>
                        <span class="tag-pill" data-style="info">Info</span>
                    </label>
                </div>
            </div>

            <!-- Actions -->
            <div style="display: flex; gap: var(--space-sm); margin-top: var(--space-md);">
                <a href="<?=ROOT?>/announcements/admin" class="button-secondary" style="text-align: center;">Cancel</a>
                <button type="submit" class="button btn-primary">Save Changes</button>
            </div>

            <div class="error" id="form-error"></div>
            <div class="intro" id="form-success" style="color: var(--color-success); display: none;">Saved successfully!</div>
        </form>
    </div>
    <?php else: ?>
        <div class="container">
            <h3>Announcement not found</h3>
            <a href="<?=ROOT?>/announcements/admin">Go Back</a>
        </div>
    <?php endif; ?>
</div>

<script>
    document.getElementById('edit-form').addEventListener('submit', async (e) => {
        e.preventDefault();
        const formData = new FormData(e.target);
        const data = Object.fromEntries(formData.entries());
        const errorEl = document.getElementById('form-error');
        const successEl = document.getElementById('form-success');
        const id = <?= $announcement['id'] ?? 0 ?>;

        errorEl.style.display = 'none';
        successEl.style.display = 'none';

        try {
            const response = await fetch(`<?=ROOT?>/announcements/api/admin/edit/${id}`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(data)
            });
            
            const result = await response.json();
            
            if (result.success) {
                successEl.textContent = 'Changes saved successfully!';
                successEl.style.display = 'block';
                // Optional: Redirect after delay
                // setTimeout(() => window.location.href = '<?=ROOT?>/announcements/admin', 1000);
            } else {
                errorEl.textContent = result.message || 'Failed to update';
                errorEl.style.display = 'block';
            }
        } catch (err) {
            errorEl.textContent = 'Network error occurred.';
            errorEl.style.display = 'block';
        }
    });
</script>

<?php include_once "../app/views/partials/footer.view.php"; ?>