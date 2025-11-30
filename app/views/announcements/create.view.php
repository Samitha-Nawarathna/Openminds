<?php
    $title = "Create | Openminds";
    $filename = "announcements/create";
    include_once "../app/views/partials/header.view.php";
?>

<div class="main-content-container" style="max-width: 800px; margin: 0 auto;">
    
    <div class="container">
        <h1 class="form-title">Create New Announcement</h1>

        <form id="create-form">
            <!-- Title -->
            <div class="input-group">
                <input type="text" name="title" placeholder="Enter announcement title" required>
            </div>

            <!-- Content -->
            <div class="input-group">
                <textarea name="content" rows="6" placeholder="Write your announcement content here..." required></textarea>
            </div>

            <!-- Style Selector -->
            <div class="display-group">
                <label style="font-size: var(--font-size-sm); color: var(--color-text-muted); margin-bottom: 8px;">Select Visual Style</label>
                <div class="style-selector">
                    <label class="style-option">
                        <input type="radio" name="style" value="primary-accent" checked>
                        <span class="tag-pill" data-style="primary-accent">Primary Accent</span>
                    </label>
                    <label class="style-option">
                        <input type="radio" name="style" value="warning">
                        <span class="tag-pill" data-style="warning">Warning</span>
                    </label>
                    <label class="style-option">
                        <input type="radio" name="style" value="info">
                        <span class="tag-pill" data-style="info">Info</span>
                    </label>
                </div>
            </div>

            <!-- Actions -->
            <div style="display: flex; gap: var(--space-sm); margin-top: var(--space-md);">
                <a href="<?=ROOT?>/announcements/admin" class="button-secondary" style="text-align: center;">Cancel</a>
                <button type="submit" class="button btn-primary">Publish Announcement</button>
            </div>
            
            <div class="error" id="form-error"></div>
        </form>
    </div>
</div>

<script>
    document.getElementById('create-form').addEventListener('submit', async (e) => {
        e.preventDefault();
        const formData = new FormData(e.target);
        const data = Object.fromEntries(formData.entries());
        const errorEl = document.getElementById('form-error');

        try {
            const response = await fetch('<?=ROOT?>/announcements/api/admin/create', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(data)
            });
            
            const result = await response.json();
            
            if (result.success) {
                // Redirect to dashboard on success
                window.location.href = '<?=ROOT?>/announcements/admin';
            } else {
                errorEl.textContent = result.message || 'Failed to create announcement';
                errorEl.style.display = 'block';
            }
        } catch (err) {
            errorEl.textContent = 'Network error occurred.';
            errorEl.style.display = 'block';
        }
    });
</script>

<?php include_once "../app/views/partials/footer.view.php"; ?>