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
function generate_mock_questions($type, $offset, $limit) {
    $all_questions = [
        ['id' => 2, 'title' => 'what is lagrangian method?', 'tag' => 'Physics', 'creator_id' => 'user_1', 'answered_by_user' => false],
        ['id' => 2, 'title' => 'how Jacobian related to gradient?', 'tag' => 'Maths', 'creator_id' => 'user_2', 'answered_by_user' => false],
        ['id' => 2, 'title' => 'solve in Hamiltonian mechanics?', 'tag' => 'Physics', 'creator_id' => 'user_3', 'answered_by_user' => true],
        ['id' => 2, 'title' => 'what does this operator do?', 'tag' => 'Quantum Computing', 'creator_id' => 'user_2', 'answered_by_user' => false],
        ['id' => 2, 'title' => 'how shadow work described by jung?', 'tag' => 'Psychology', 'creator_id' => 'user_4', 'answered_by_user' => true],
        ['id' => 2, 'title' => 'how to solve this in linear algebra?', 'tag' => 'Maths', 'creator_id' => 'user_5', 'answered_by_user' => true],
        ['id' => 2, 'title' => 'Explain Feynman diagrams', 'tag' => 'Physics', 'creator_id' => 'user_1', 'answered_by_user' => false],
        ['id' => 2, 'title' => 'Derive the Navier-Stokes equations', 'tag' => 'Fluid Dynamics', 'creator_id' => 'user_2', 'answered_by_user' => false],
        ['id' => 2, 'title' => 'What is the role of the amygdala?', 'tag' => 'Biology', 'creator_id' => 'user_6', 'answered_by_user' => true],
        ['id' => 2, 'title' => 'Why is P=NP a problem?', 'tag' => 'Computer Science', 'creator_id' => 'user_7', 'answered_by_user' => false],
        ['id' => 2, 'title' => 'The eleventh question for load more', 'tag' => 'Test', 'creator_id' => 'user_1', 'answered_by_user' => false],
        // Add more mock questions here for 'Load More' to work
    ];

    $filtered_questions = $all_questions;
    $current_user_id = 'user_2';

    if ($type === 'your') {
        $filtered_questions = array_filter($all_questions, fn($q) => $q['creator_id'] === $current_user_id);
    } elseif ($type === 'answered') {
        $filtered_questions = array_filter($all_questions, fn($q) => $q['answered_by_user'] === true);
    }

    // Apply offset and limit for pagination
    $questions_to_return = array_slice($filtered_questions, $offset, $limit);
    $has_more = count($filtered_questions) > ($offset + $limit);

    return [
        'questions' => array_values($questions_to_return),
        'has_more' => $has_more
    ];
}

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
    let mock_questions = <?=json_encode(generate_mock_questions($data['initial_tab'], 0, 10))?>;
</script>


<?php

include_once '../app/views/partials/footer.view.php';

?>