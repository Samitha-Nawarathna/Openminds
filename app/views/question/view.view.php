<?php

$title = 'Question creator';
$filename = 'question/view';

include_once '../app/views/partials/header.view.php';

?>

<?php
// --- MOCK API DATA SETUP (Mimics server-side data retrieval) ---
$CURRENT_USER_ID = 1; 

$mockQuestionDetails = [
    'id' => 1,
    'title' => "What is the Big O Notation for an unbalanced Binary Search Tree?",
    'description' => "I am studying data structures, and I understand that a balanced BST has an average search time of O(log n). However, what happens when it becomes completely unbalanced? What is the worst-case scenario for operations like search, insertion, and deletion?",
    'author_id' => 1,
    'author_name' => "Alice",
    'author_role' => "student",
    'time_posted' => "2025-10-30 10:00",
    'tags' => ["data-structures", "algorithms", "big-o"],
    'vote_count' => 5,
    'user_voted' => true,
    'user_vote_type' => 'up'
];

$mockInitialAnswers = [
    ['id' => 102, 'content' => "Since an unbalanced BST effectively becomes a linked list in the worst case (e.g., sequentially inserted data), the Big O notation for search, insertion, and deletion becomes O(n).", 'author_id' => 2, 'author_name' => "You", 'author_role' => "student", 'time_posted' => "2025-10-31 10:15", 'vote_count' => 10, 'user_voted' => false, 'is_accepted' => true],
    ['id' => 103, 'content' => "Correct. To mitigate this risk, modern systems often rely on self-balancing trees like AVL or Red-Black trees, which guarantee O(log n) worst-case performance by performing rotations.", 'author_id' => 3, 'author_name' => "Dr. Smith", 'author_role' => "admin", 'time_posted' => "2025-10-31 10:30", 'vote_count' => 25, 'user_voted' => true, 'user_vote_type' => 'up', 'is_accepted' => false],
    ['id' => 104, 'content' => "It's important to remember that 'unbalanced' means skewed, but the average case for a randomly built BST remains O(log n). The O(n) is strictly the worst-case scenario.", 'author_id' => 4, 'author_name' => "Bob Expert", 'author_role' => "expert", 'time_posted' => "2025-10-31 10:45", 'vote_count' => 5, 'user_voted' => false, 'is_accepted' => false]
];

// Generate 9 more mock answers
for ($i = 0; $i < 9; $i++) {
    $mockInitialAnswers[] = [
        'id' => 105 + $i,
        'content' => "A student answer number " . ($i + 1) . ". The worst-case for an unbalanced BST is O(n), which is terrible for performance.",
        'author_id' => 10 + $i,
        'author_name' => "Student " . ($i + 1),
        'author_role' => ($i % 3 === 0 ? "mentor" : "student"),
        'time_posted' => "2025-10-31 11:00",
        'vote_count' => 1,
        'user_voted' => false,
        'is_accepted' => false
    ];
}

$totalAnswerCount = count($mockInitialAnswers);
$answersToDisplay = 10;

// Sort answers (PHP mimic of initial JS sort)
usort($mockInitialAnswers, function($a, $b) use ($CURRENT_USER_ID) {
    if ($a['author_id'] === $CURRENT_USER_ID) return -1;
    if ($b['author_id'] === $CURRENT_USER_ID) return 1;
    if ($a['is_accepted'] && !$b['is_accepted']) return -1;
    if (!$a['is_accepted'] && $b['is_accepted']) return 1;
    $roleOrder = ['admin' => 4, 'expert' => 3, 'mentor' => 2, 'student' => 1];
    return $roleOrder[strtolower($b['author_role'])] - $roleOrder[strtolower($a['author_role'])];
});

$initialAnswersList = array_slice($mockInitialAnswers, 0, $answersToDisplay);
$hasMoreAnswers = $totalAnswerCount > $answersToDisplay;

$initialData = [
    'question' => $mockQuestionDetails,
    'answers' => [
        'list' => $initialAnswersList,
        'has_more' => $hasMoreAnswers
    ],
    'totalAnswerCount' => $totalAnswerCount
];

$initialDataJson = json_encode($initialData);
?>


<div id="question-page-container">
    <div id="question-panel">
        <div id="question-content-container">
            </div>

        <div id="question-footer" class="question-footer">
            <button id="answer-cta-btn" class="btn-blue" style="width: auto;" onclick="openModal('create-answer-modal')">Post Your Answer</button>
            <div id="question-action-controls" class="question-action-controls">
                </div>
        </div>
        <div id="split-trigger-point"></div>
    </div>
    
    <div id="void-filler"></div> 

    <div id="answers-panel">
        <div id="answers-panel-inner-content">
            <div id="answer-count-header" class="answer-count-header">
                </div>
            <div id="answers-list">
                </div>
            <button id="load-more-btn" class="btn-none" onclick="loadMoreAnswers()" style="width: 100%; display: none;">Load More Answers (0/0)</button>
        </div>
    </div>
</div>

<div id="create-answer-modal" class="modal">
    <div class="modal-content">
        <span class="close-btn" onclick="closeModal('create-answer-modal')">&times;</span>
        <h2>Post Your Answer</h2>
        <form id="create-answer-form" onsubmit="handleCreateAnswer(event)">
            <label for="new-answer-content">Answer Content:</label>
            <!-- <textarea id="new-answer-content" rows="10" required></textarea> -->
            <quill-editor 
                    id="new-answer-content"
                    name="content"
                    placeholder="Enter form content..."
                    storage-key="demo-editor-2"
                    height="250px">
                </quill-editor>
            <button type="submit" class="btn-blue">Submit Answer</button>
        </form>
    </div>
</div>

<div id="edit-question-modal" class="modal">
    <div class="modal-content">
        <span class="close-btn" onclick="closeModal('edit-question-modal')">&times;</span>
        <h2>Edit Question</h2>
        <form id="edit-question-form" onsubmit="handleEditQuestion(event)">
            <label for="edit-question-title">Title:</label>
            <input type="text" id="edit-question-title" required>
            <label for="edit-question-description">Description:</label>
            <quill-editor 
                id="edit-question-description"
                name="content"
                placeholder="Enter form content..."
                storage-key="demo-editor-2"
                height="250px">
            </quill-editor>            
            <!-- <textarea id="edit-question-description" rows="12" required></textarea> -->
            <label for="edit-question-tags">Tags (comma separated):</label>
            <input type="text" id="edit-question-tags">
            <button type="submit" class="btn-blue">Save Changes</button>
        </form>
    </div>
</div>

<div id="edit-answer-modal" class="modal">
    <div class="modal-content">
        <span class="close-btn" onclick="closeModal('edit-answer-modal')">&times;</span>
        <h2>Edit Answer</h2>
        <form id="edit-answer-form" onsubmit="handleEditAnswer(event)">
            <input type="hidden" id="edit-answer-id">
            <label for="edit-answer-content">Answer Content:</label>
            <quill-editor 
                id="edit-answer-content"
                name="content"
                placeholder="Enter form content..."
                storage-key="demo-editor-2"
                height="250px">
            </quill-editor>               
            <!-- <textarea id="edit-answer-content" rows="10" required></textarea> -->
            <button type="submit" class="btn-blue">Save Changes</button>
        </form>
    </div>
</div>

<div id="error-popup"></div>

<script>
    // Inject initial data into a global JS variable
    const INITIAL_DATA = <?php echo $initialDataJson; ?>;
</script>

<?php

include_once '../app/views/partials/footer.view.php';

?>
