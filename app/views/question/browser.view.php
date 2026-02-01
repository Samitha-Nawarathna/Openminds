<?php

$title = 'Question browser';
$filename = 'question/browser';

include_once '../app/views/partials/header.view.php';

?>

<?php

$user_stats = $data['user_stats'] ?? null;
$initial_tab = $data['initial_tab'] ?? 'all';

?>


<div class="main-content-container">
    <header>
        <div class="title-panel">
            <div>
                <h1 class="main-title">Questions</h1>
                <p class="page-description">Browse and search through the community questions</p>
            </div>
            <div class="title-icon">
                <!-- Using a simple SVG icon or an image if available. Using an SVG for now -->
                <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="10"></circle>
                    <path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"></path>
                    <line x1="12" y1="17" x2="12.01" y2="17"></line>
                </svg>
            </div>
        </div>
    </header>

    <div class="filter-bar">
        <div class="search-wrapper">
            <input type="text" id="tag-filter-input" placeholder="type a question to search">
            <button class="btn-filter" id="filter-btn">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
            </button>
        </div>
        <button class="btn-advanced-filter" id="advanced-filter-btn">
            Advanced
            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"></polyline></svg>
        </button>
        <a href="<?= ROOT ?>/question/create" class="btn-create">+ Create</a>
    </div>

    <div class="advanced-filter-dropdown" id="advanced-filter-dropdown" style="display: none;">
        <!-- Placeholder for advanced options, can be expanded later -->
        <div class="filter-group">
            <label>Sort by:</label>
            <select id="sort-filter">
                <option value="newest">Newest</option>
                <option value="votes">Most Votes</option>
            </select>
        </div>
        <div class="filter-group">
            <label>Status:</label>
            <select id="status-filter">
                <option value="all">All</option>
                <option value="solved">Solved</option>
                <option value="unsolved">Unsolved</option>
            </select>
        </div>
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

    <?php if (!empty($user_stats)): ?>
    <div class="user-stats-card-fixed">
        <h3 class="stats-card-title">Your Progress</h3>
        <div class="stats-grid">
            <div class="stat-item">
                <span class="stat-num"><?= $user_stats['questions'] ?></span>
                <span class="stat-label">Questions</span>
            </div>
            <div class="stat-item">
                <span class="stat-num"><?= $user_stats['answers'] ?></span>
                <span class="stat-label">Answers</span>
            </div>
            <div class="stat-item">
                <span class="stat-num"><?= $user_stats['q_votes'] ?></span>
                <span class="stat-label">Q-Votes</span>
            </div>
            <div class="stat-item">
                <span class="stat-num"><?= $user_stats['a_votes'] ?></span>
                <span class="stat-label">A-Votes</span>
            </div>
        </div>
        <div class="stats-footer">
            <a href="<?= ROOT ?>/analysis" class="stats-link">View Full Analysis →</a>
        </div>
    </div>
    <?php endif; ?>

</div>

<script>
    let initial_tab = '<?=$initial_tab?>';
    // let mock_questions = removed;
</script>


<?php

include_once '../app/views/partials/footer.view.php';

?>