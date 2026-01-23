<?php

$title = 'Question browser';
$filename = 'question/browser';

include_once '../app/views/partials/header.view.php';

?>

<?php

// --- MOCK DATA SETUP ---

$data = [
    'current_user_id' => 1, // Used for 'Your Questions' and 'You Answered' tabs
    'initial_tab' => 'all', // The tab to be loaded first
];

// Helper function to generate mock question data based on type
// Helper function removed. Using real API.

?>


<div class="main-content-container">
    <header>
    <h1 class="main-title">Questions</h1>
    </header>

    <div class="filter-bar">
        <input type="text" id="tag-filter-input" placeholder="enter a tag name">
        <button class="btn-filter" id="filter-btn">Filter</button>
        <a href="<?= ROOT ?>/question/create" class="btn-create">+ Create</a>
    </div>

    <div class="tabs-container" id="tabs-container">
        <button class="tab-button active" data-tab="all">All</button>
        <button class="tab-button" data-tab="your">Your Questions</button>
        <button class="tab-button" data-tab="answered">You answered</button>
    </div>

    <div class="list-container" id="questions-list"></div>

    <div class="load-more-container">
        <button id="load-more-btn" class="btn-load-more">Load More</button>
    </div>

</div>

<script>
    let initial_tab = '<?=$data['initial_tab']?>';
    // let mock_questions = removed;
</script>


<?php

include_once '../app/views/partials/footer.view.php';

?>