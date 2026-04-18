<?php

    $title = "Exercises | Openminds";
    $filename = "exercises/browser";

    include_once "../app/views/partials/header.view.php";

?>

<div class="main-content-container" data-user-role="<?= htmlspecialchars($data['role'] ?? 'student') ?>">
        
        <header class="exercise-browser-header">
            <h1 class="main-title">Exercises</h1>
        </header>
        
        <div class="filter-bar">
            <input type="text" id="exercise-filter-input" placeholder="Search title or author...">
            
            <!-- NEW: Toggle Button -->
            <button class="btn-filter" id="toggle-advanced-btn">Advanced</button>
            
            <button class="btn-filter" id="filter-btn">Search</button>
            <a <?= !empty($data['can_create']) ? 'href="' . ROOT . '/exercises/create"' : 'data-href="' . ROOT . '/exercises/create"' ?> class="btn-create<?= !empty($data['can_create']) ? '' : ' is-disabled' ?>" <?= !empty($data['can_create']) ? '' : 'aria-disabled="true" tabindex="-1"' ?>>+ Create</a>
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
                    <option value="Chemistry">Electronics</option>

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

        

        <?php $role = strtolower(trim($data['role'] ?? 'student')); ?>

<div class="tabs-container" id="tabs-container">
    <button class="tab-button <?= ($data['initial_tab'] ?? 'all') == 'all' ? 'active' : '' ?>" data-tab="all" id="all-tab">All</button>
    <button class="tab-button <?= ($data['initial_tab'] ?? 'all') == 'created' ? 'active' : '' ?>" data-tab="created" id="created-tab">Created by you</button>
    <button class="tab-button <?= ($data['initial_tab'] ?? 'all') == 'attempted' ? 'active' : '' ?>" data-tab="attempted" id="attempted-tab">Attempt by you</button>
    
    <?php if (in_array($role, ['expert', 'admin'], true)): ?>
        <button class="tab-button <?= ($data['initial_tab'] ?? 'all') == 'pending' ? 'active' : '' ?>" data-tab="pending" id="pending-tab">Pending</button>
    <?php endif; ?>
</div>

<div class="created-subtabs is-hidden-by-role" id="created-subtabs">
    <button class="created-subtab-btn active" data-created-subtab="created_published" id="created-published-tab">Published</button>
    <button class="created-subtab-btn" data-created-subtab="created_draft" id="created-draft-tab">Draft and pending</button>
</div>

        <div class="list-container" id="exercises-list">
            <?php 
            if (!empty($data['initial_exercises'])) {
                foreach ($data['initial_exercises'] as $exercise) {
                    $exerciseId = (int)($exercise['id'] ?? 0);
                    $exerciseTitle = htmlspecialchars((string)($exercise['title'] ?? 'Untitled Exercise'));
                    $subjectName = htmlspecialchars((string)($exercise['subject'] ?? 'General'));
                    $creatorName = htmlspecialchars((string)($exercise['creator_name'] ?? 'Unknown'));
                    $createdAt = htmlspecialchars((string)($exercise['created_at'] ?? 'just now'));
                    $statusRaw = strtolower(trim((string)($exercise['status'] ?? 'draft')));
                    $statusClass = in_array($statusRaw, ['draft', 'pending', 'reject', 'published', 'approved'], true)
                        ? $statusRaw
                        : 'draft';
                    $statusLabel = $statusRaw === 'approved' ? 'published' : $statusRaw;
                    $voteCount = isset($exercise['vote_count'])
                        ? (int)$exercise['vote_count']
                        : ((int)($exercise['upvotes'] ?? 0) - (int)($exercise['downvotes'] ?? 0));

                    echo '<a class="no-style-link" href="' . ROOT . '/exercises/attempt?id=' . $exerciseId . '">';
                    echo '  <div class="exercise-item" data-id="' . $exerciseId . '">';
                    echo '      <div class="exercise-vote-column" aria-hidden="true">';
                    echo '          <span class="vote-icon">&#128077;&#65038;</span>';
                    echo '          <span class="vote-count">' . $voteCount . '</span>';
                    echo '          <span class="vote-label">Votes</span>';
                    echo '      </div>';
                    echo '      <div class="exercise-main-column">';
                    echo '          <h3 class="exercise-title-list">' . $exerciseTitle . '</h3>';
                    echo '          <div class="exercise-meta-row">';
                    echo '              <span class="meta-item">Created by <strong>' . $creatorName . '</strong></span>';
                    echo '              <span class="meta-separator" aria-hidden="true">&bull;</span>';
                    echo '              <span class="meta-item">' . $createdAt . '</span>';
                    echo '          </div>';
                    echo '      </div>';
                    echo '      <div class="exercise-side-column">';
                    echo '          <span class="status-pill status-' . $statusClass . '"><span class="status-icon" aria-hidden="true">○</span><span>' . htmlspecialchars($statusLabel) . '</span></span>';
                    echo '          <span class="subject-pill" data-subject="' . $subjectName . '">' . $subjectName . '</span>';
                    echo '          <span class="menu-dots" aria-hidden="true">&bull;&bull;&bull;</span>';
                    echo '      </div>';
                    echo '  </div>';
                    echo '</a>';
                }
            } else {
                echo '<div class="empty-state">';
                echo '  <div class="empty-state-icon" aria-hidden="true">&#128218;</div>';
                echo '  <p class="empty-state-message">No exercises found in this section</p>';
                if (!empty($data['can_create'])) {
                    echo '  <a class="empty-state-link" href="' . ROOT . '/exercises/create">Create your first exercise</a>';
                }
                echo '</div>';
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
        const INITIAL_OFFSET = <?= $data['initial_offset'] ?? 0 ?>;
        const INITIAL_LIMIT = <?= $data['initial_limit'] ?? 5 ?>;
        const INITIAL_TAB = '<?= $data['initial_tab'] ?? 'all' ?>';
        const USER_ROLE = '<?= htmlspecialchars($data['role'] ?? 'student') ?>';
    </script>


<?php
    include_once "../app/views/partials/footer.view.php";
?>