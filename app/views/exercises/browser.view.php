<?php

    $title = "Exercises | Openminds";
    $filename = "exercises/browser";

    include_once "../app/views/partials/header.view.php";

?>

<div class="main-content-container">
        
        <header class="exercise-browser-header">
            <h1 class="main-title">Exercises</h1>
        </header>
        
        <div class="filter-bar">
            <input type="text" id="exercise-filter-input" placeholder="enter a username name">
            <button class="btn-filter" id="filter-btn">filter</button>
            <a href="<?=ROOT?>/exercises/create" class="btn-create">+ Create</a>
        </div>

        <div class="tabs-container" id="tabs-container">
            <button class="tab-button <?= $data['initial_tab'] == 'all' ? 'active' : '' ?>" data-tab="all" id="all-tab">All</button>
            <button class="tab-button <?= $data['initial_tab'] == 'created' ? 'active' : '' ?>" data-tab="created" id="created-tab">created by you</button>
            <button class="tab-button <?= $data['initial_tab'] == 'attempted' ? 'active' : '' ?>" data-tab="attempted" id="attempted-tab">attempt by you</button>
        </div>

        <div class="list-container" id="exercises-list">
            <?php 
            // UPDATED: Accessing exercises via $data array
            foreach ($data['initial_exercises'] as $exercise) {

                echo '<a class = "no-style-link" href="' . ROOT . '/exercises/attempt?id=' . htmlspecialchars($exercise['id']) . '">';
                echo '<div class="exercise-item" data-id="' . htmlspecialchars($exercise['id']) . '">';                
                echo '  <span class="exercise-title-list">' . htmlspecialchars($exercise['title']) . '</span>';
                echo '  <span class="subject-pill" data-subject="' . htmlspecialchars($exercise['subject']) . '">';
                echo '      ' . htmlspecialchars($exercise['subject']) . '';
                echo '  </span>';
                echo '</div>';
                echo '</a>';
            }
            ?>
        </div>

        <div class="load-more-container">
            <button id="load-more-btn" class="btn-load-more">
                <?= $data['initial_has_more'] ? 'Load More' : 'No More Exercises' ?>
            </button>
        </div>
    </div>

    <script>
        // Pass essential state data to JavaScript
        const INITIAL_OFFSET = <?= $data['initial_limit'] ?>; // The next offset to start from
        const INITIAL_TAB = '<?= $data['initial_tab'] ?>';
    </script>


<?php
    include_once "../app/views/partials/footer.view.php";
?>