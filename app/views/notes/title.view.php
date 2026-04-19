<?php

$title = 'Topic browser';
$filename = 'notes/title';


include_once '../app/views/partials/header.view.php';
include_once '../app/views/partials/Rnavbar.view.php';
include "../app/views/partials/focus_timer.php";
?>
<div class="main-content-container">
    <header class="page-header">
        <img src="<?=ROOT?>assets/images/title.png" alt="Topics" class="title-icon">
        <div class="header-text">
            <h1 class="main-title">Topics</h1>
            <p class="page-description">notes are organized under title.</p>
        </div>
    </header>

    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>

    
        <div class="filter-bar">
            <input type="text" id="topic-filter-input" placeholder="type an topic name and click filter">
            <button class="btn-filter" id="filter-btn">Filter</button> 
            <a href="<?=ROOT?>/topics/create" class="btn-create">+ Create</a>
        </div>

        <?php if (!empty($data['recent_topics'])): ?>
        <div class="recent-topic-container">
            <div class="title-bar">
                <div class="title"><h3>Pinned Subjects</h3></div>
                <!-- <div class="toggle"><p class="caption">Hide</p></div> -->
            </div>
            <div class="card-swapper-container">
                <div class="cards-wrapper" id="cardsWrapper">

                    <?php 

                    $colors = ['--color-green-100', '--color-yellow-50', '--color-blue-100',  '--color-blue-200'];
                    
                    foreach ($data['recent_topics'] as $key => $topic) {
                        $rand_no = rand(0, sizeof($colors) - 1);

                        echo '
                        <a href="'.ROOT.'/notes/title/'.$data['recent_topic_ids'][$key].'" class="no-style-link">
                        <div class="card-container" data-id="'.$data['recent_topic_ids'][$key].'">
                            <div class="card">
                                <div class="icon-placeholder" style="background-color:var('.$colors[$rand_no].');">
                                    📁
                                    <span class="unpin-icon" data-id="'.$data['recent_topic_ids'][$key].'">unpin</span>
                                
                                </div>
                                
                            </div>                      
                            <p>'.$topic.'</p>
                        </div></a>
                        ';
                    }
                    ?>

                </div>
                <button class="nav-button right" onclick="scrollCards(1)">
                    <span class="arrow">›</span>
                </button>
            </div>
            
        </div>
        <?php endif; ?>

        <div class="list-container" id="subjects-list">
            <h3>Available Topics</h3>
            <?php 
            // Initial render of topics using PHP
            foreach ($data["initial_load"]['topics'] as $topic) {
                // Added data-id for JS event listener
                $note_count = $topic['note_count'] ?? 0;
                $note_text = $note_count . ' ' . ($note_count === 1 ? 'Note' : 'Notes');
                
                echo '
                <div class="topic-item" data-id="' . htmlspecialchars($topic['id']) . '">
                    <a href="'.ROOT.'/notes/list/'.htmlspecialchars($topic['id']).'" class="topic-info">
                        <span class="topic-name">' . htmlspecialchars($topic['name']) . '</span>
                        <span class="note-count">' . $note_text . '</span>
                    </a>
                    <div class="topic-actions">
                        <button class="action-btn rename-btn" title="Rename Topic" data-id="' . htmlspecialchars($topic['id']) . '" data-name="' . htmlspecialchars($topic['name']) . '" style="color: #666; stroke: #666;">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="pointer-events: none;"><path d="M12 20h9"></path><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"></path></svg>
                        </button>
                        <button class="action-btn delete-btn" title="Delete Topic" data-id="' . htmlspecialchars($topic['id']) . '" style="color: red; stroke: red;">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="pointer-events: none;"><path d="M3 6h18"></path><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path><line x1="10" y1="11" x2="10" y2="17"></line><line x1="14" y1="11" x2="14" y2="17"></line></svg>
                        </button>
                        <button class="action-btn pin-btn" title="Pin Topic" data-id="' . htmlspecialchars($topic['id']) . '">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="pointer-events: none;"><line x1="12" y1="17" x2="12" y2="22"></line><path d="M5 17h14v-1.76a2 2 0 0 0-1.11-1.79l-1.78-.9A2 2 0 0 1 15 10.76V6h1a2 2 0 0 0 0-4H8a2 2 0 0 0 0 4h1v4.76a2 2 0 0 1-1.11 1.79l-1.78.9A2 2 0 0 0 5 15.24Z"></path></svg>
                        </button>
                    </div>
                </div>';
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