<?php

    $title = "Notes | Openminds";
    $filename = "notes/note";

    include_once "../app/views/partials/header.view.php";

?>

<div class="main-content-container">
        
        <header class="note-browser-header">
            <h1 class="main-title">Notes</h1>
            <span class="tag-pill"><?= htmlspecialchars($data['browsing_topic_title']) ?></span>
        </header>
        
        <div class="filter-bar">
            <input type="text" id="note-filter-input" placeholder="enter a username/note title to filter">
            <button class="btn-filter" id="filter-btn">Filter</button>
            <a href="<?=ROOT?>/notes/create" class="btn-create">+ Create</a>
        </div>

        <div class="tabs-container" id="tabs-container">
            <button class="tab-button active" data-tab="created" id="created-tab">Created</button>
            <button class="tab-button" data-tab="shared" id="shared-tab">Shared</button>
        </div>

        <div class="list-container" id="notes-list">
            <?php 
            foreach ($data["initial_load"]['notes'] as $note) {
                echo '<a href="'.ROOT.'/notes/show?id='.htmlspecialchars($note['id']).'" class="no-style-link">';
                echo '<div class="note-item" data-id="' . htmlspecialchars($note['id']) . '">';
                echo '  <span class="note-title-list">' . htmlspecialchars($note['title']) . '</span>';
                echo '</div>';
                echo '</a>';
            }
            ?>
        </div>

        <div class="load-more-container">
            <button id="load-more-btn" class="btn-load-more">
                <?= $data["initial_load"]['has_more'] ? 'Load More' : 'No More Notes' ?>
            </button>
        </div>
    </div>

<?php
include_once "../app/views/partials/footer.view.php";
?>