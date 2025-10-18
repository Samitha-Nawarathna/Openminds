<?php

$title = 'Question browser';
$filename = 'notes/title';

include_once '../app/views/partials/header.view.php';

?>

<div class="main-content-container">
        <h1>Topics</h1>

        <div class="filter-bar">
            <input type="text" id="topic-filter-input" placeholder="enter a username/name">
            <button class="btn-filter" id="filter-btn">Filter</button> 
            <a href="<?=ROOT?>/notes/create_title" class="btn-create">+ Create</a>
        </div>

        <div class="list-container" id="topics-list">
            <?php 
            // Initial render of topics using PHP
            foreach ($data["initial_load"]['topics'] as $topic) {
                // Added data-id for JS event listener
                echo '<div class="topic-item" data-id="' . htmlspecialchars($topic['id']) . '">' . htmlspecialchars($topic['name']) . '</div>';
            }
            ?>
        </div>

        <div class="load-more-container">
            <button id="load-more-btn" class="btn-load-more">
                <?= $data["initial_load"]['has_more'] ? 'Load More' : 'No More Topics' ?>
            </button>
        </div>
    </div>

<?php

include_once '../app/views/partials/footer.view.php';

?>