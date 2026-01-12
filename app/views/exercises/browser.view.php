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
            <input type="text" id="exercise-filter-input" placeholder="Search title or author...">
            
            <!-- NEW: Toggle Button -->
            <button class="btn-filter" id="toggle-advanced-btn">Advanced</button>
            
            <button class="btn-filter" id="filter-btn">Search</button>
            <a href="<?=ROOT?>/exercises/create" class="btn-create">+ Create</a>
        </div>

        <!-- NEW: Advanced Filter Panel -->
        <div class="advanced-filter-panel hidden" id="advanced-filter-panel">
            <div class="filter-group">
                <label for="subject-filter">Subject</label>
                <select id="subject-filter">
                    <option value="">All Subjects</option>
                    <option value="Physics">Physics</option>
                    <option value="Maths">Maths</option>
                    <option value="Psychology">Psychology</option>
                    <option value="Quantum Computing">Quantum Computing</option>
                    <option value="Chemistry">Chemistry</option>
                </select>
            </div>
            
            <div class="filter-group">
                <label for="sort-filter">Sort By</label>
                <select id="sort-filter">
                    <option value="id-DESC">Newest First</option>
                    <option value="id-ASC">Oldest First</option>
                    <option value="title-ASC">Title (A-Z)</option>
                    <option value="title-DESC">Title (Z-A)</option>
                </select>
            </div>
        </div>

        <div class="tabs-container" id="tabs-container">
            <button class="tab-button <?= $data['initial_tab'] == 'all' ? 'active' : '' ?>" data-tab="all" id="all-tab">All</button>
            <button class="tab-button <?= $data['initial_tab'] == 'created' ? 'active' : '' ?>" data-tab="created" id="created-tab">created by you</button>
            <button class="tab-button <?= $data['initial_tab'] == 'attempted' ? 'active' : '' ?>" data-tab="attempted" id="attempted-tab">attempt by you</button>
            <?php

            //for testing
            if (isset($_SESSION['role']) && ($_SESSION['role'] === 'expert' || $_SESSION['role'] === 'admin')) {
                echo '<button class="tab-button ' . ($data['initial_tab'] == 'pending' ? 'active' : '') . '" data-tab="pending" id="pending-tab">Pending</button>';
            }
            ?>
        </div>

        <div class="list-container" id="exercises-list">
            <?php 
            if (!empty($data['initial_exercises'])) {
                foreach ($data['initial_exercises'] as $exercise) {
                    // Logic to determine link based on tab/role could go here or in Controller, keeping simple for view
                    echo '<a class="no-style-link" href="' . ROOT . '/exercises/attempt?id=' . htmlspecialchars($exercise['id']) . '">';
                    echo '<div class="exercise-item" data-id="' . htmlspecialchars($exercise['id']) . '">';                
                    echo '  <span class="exercise-title-list">' . htmlspecialchars($exercise['title']) . '</span>';
                    echo '  <span class="subject-pill" data-subject="' . htmlspecialchars($exercise['subject']) . '">';
                    echo '      ' . htmlspecialchars($exercise['subject']) . '';
                    echo '  </span>';
                    echo '</div>';
                    echo '</a>';
                }
            } else {
                echo '<p class="no-data-msg">No exercises found.</p>';
            }
            ?>
        </div>

        <div class="load-more-container">
            <button id="load-more-btn" class="btn-load-more">
                <?= isset($data['initial_has_more']) && $data['initial_has_more'] ? 'Load More' : 'No More Exercises' ?>
            </button>
        </div>
    </div>

    <script>
        // Pass essential state data to JavaScript
        const INITIAL_OFFSET = <?= $data['initial_limit'] ?? 5 ?>; 
        const INITIAL_TAB = '<?= $data['initial_tab'] ?? 'all' ?>';
    </script>


<?php
    include_once "../app/views/partials/footer.view.php";
?>