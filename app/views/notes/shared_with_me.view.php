<?php
    $title = "Shared with Me | Openminds";
    $filename = "notes/note";

    include_once "../app/views/partials/header.view.php";
    include "../app/views/partials/focus_timer.php";
    include_once '../app/views/partials/Rnavbar.view.php';
?>

<div class="main-content-container">
    <header class="page-header">
        <img src="<?=ROOT?>assets/images/note.png" alt="Notes" class="title-icon">
        <div class="header-text">
            <h1 class="main-title">Shared <span class="title-suffix">with Me</span></h1>
            <p class="page-description">Notes shared with you by other students and experts</p>
        </div>
    </header>    

    <div class="note-browser-container">
        <div class="list-container" id="notes-list">
            <h3>Shared Notes</h3>
            <?php if (empty($data['notes'])): ?>
                <div class="loading">No notes have been shared with you yet.</div>
            <?php else: ?>
                <?php foreach ($data['notes'] as $note): ?>
                    <a class="no-style-link" href="<?=ROOT?>/notes/view/<?= $note->id ?>">
                        <div class="note-item">
                            <div class="note-info">
                                <span class="note-title-list"><?= htmlspecialchars($note->title) ?></span>
                                <span class="owner-badge">by <?= htmlspecialchars($note->owner_name) ?></span>
                            </div>
                            <span class="note-date"><?= date('M j, Y', strtotime($note->created_at)) ?></span>
                        </div>
                    </a>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<style>
/* Scoped styles for shared notes */
.owner-badge {
    font-size: 0.75rem;
    background-color: var(--color-blue-100);
    color: var(--color-primary);
    padding: 2px 8px;
    border-radius: 12px;
    margin-left: 10px;
    font-weight: 500;
}

.note-info {
    display: flex;
    align-items: center;
}

.note-date {
    font-size: 0.85rem;
    color: var(--color-gray-400);
}

.note-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    background-color: white;
    padding: 15px 20px;
    border-radius: 8px;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
    transition: transform 0.1s, box-shadow 0.1s;
}

.note-item:hover {
    transform: translateY(-1px);
    box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
}

.note-title-list {
    font-weight: 500;
    color: var(--color-text);
}
</style>

<?php
include_once "../app/views/partials/footer.view.php";
?>
