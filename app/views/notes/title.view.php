<?php

$title = 'Topic browser';
$filename = 'notes/title';

include_once '../app/views/partials/header.view.php';


?>

<div class="main-content-container">
    <header>
        <h1 class="main-title">Topics</h1>
    </header>
    
        <div class="filter-bar">
            <input type="text" id="topic-filter-input" placeholder="enter a username/name">
            <button class="btn-filter" id="filter-btn">Filter</button> 
            <a href="<?=ROOT?>/topics/create" class="btn-create">+ Create</a>
        </div>

        <div class="recent-topic-container">
            <div class="title-bar">
                <div class="title"><h3>Pinned Topics</h3></div>
                <div class="toggle"><p class="caption">Hide</p></div>
            </div>
            <div class="card-swapper-container">
                <div class="cards-wrapper" id="cardsWrapper">

                    <?php 

                    $colors = ['--color-green-100', '--color-yellow-50', '--color-blue-100',  '--color-blue-200'];
                    
                    foreach ($data['recent_topics'] as $key => $topic) {
                        $rand_no = rand(0, sizeof($colors) - 1);

                        echo '
                        <a href="'.ROOT.'/notes/title/'.$data['recent_topic_ids'][$key].'" class="no-style-link">
                        <div class="card-container">
                            <div class="card">
                                <div class="icon-placeholder" style="background-color:var('.$colors[$rand_no].');">
                                    📁
                                    <span class="unpin-icon">unpin</span>
                                
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

        <div class="list-container" id="topics-list">
            <h3>Available Topics</h3>
            <?php 
            // Initial render of topics using PHP
            foreach ($data["initial_load"]['topics'] as $topic) {
                // Added data-id for JS event listener
                echo '<div class="topic-item" data-id="' . htmlspecialchars($topic['id']) . '"><a href="'.ROOT.'/notes/list/'.htmlspecialchars($topic['id']).'" class="no-style-link">' . htmlspecialchars($topic['name']) . '<span class="pin-icon">pin</span></a></div>';
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