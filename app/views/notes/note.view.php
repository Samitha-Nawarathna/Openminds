<?php

    $title = "Notes | Openminds";
    $filename = "notes/note";

    include_once "../app/views/partials/header.view.php";
    include "../app/views/partials/focus_timer.php";
    include_once '../app/views/partials/Rnavbar.view.php';

?>

<div class="main-content-container">
    <header class="page-header">
        <img src="<?=ROOT?>assets/images/note.png" alt="Notes" class="title-icon">
        <div class="header-text">
            <h1 class="main-title">Notes <span class="title-suffix">under <?= htmlspecialchars($data['browsing_topic_title']) ?></span></h1>
            <p class="page-description">Manage and organize your notes</p>
        </div>
    </header>    


        <div class="filter-bar">
            <input type="text" id="note-filter-input" placeholder="write tag name and press enter">
            <button class="btn-filter" id="filter-btn">Filter</button> 
            <a href="<?=ROOT?>/notes/create?title=<?php echo $data['browsing_topic_title']; ?>" class="btn-create">+ Create</a>
        </div>

        <?php if (!empty($data['pinned_notes'])): ?>
        <div class="recent-topic-container">
            <div class="title-bar">
                <div class="title"><h3>Pinned Notes</h3></div>
                <div class="toggle"><p class="caption">Hide</p></div>
            </div>
            <div class="card-swapper-container">
                <div class="cards-wrapper" id="cardsWrapper">

                    <?php 

                    $colors = ['--color-green-100', '--color-yellow-50', '--color-blue-100',  '--color-blue-200'];
                    
                    foreach ($data['pinned_notes'] as $key => $note) {
                        $rand_no = rand(0, sizeof($colors) - 1);

                        echo '
                        <a href="'.ROOT.'/notes/view/'.$data['pinned_note_ids'][$key].'" class="no-style-link">
                        <div class="card-container" data-id="'.$data['pinned_note_ids'][$key].'">
                            <div class="card">
                                <div class="icon-placeholder" style="background-color:var('.$colors[$rand_no].');">
                                    📁
                                    <span class="unpin-icon" data-id="'.$data['pinned_note_ids'][$key].'">unpin</span>
                                
                                </div>
                                
                            </div>                      
                            <p>'.$note.'</p>
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

    
</div>

    <div class="note-browser-container">
        <div class="tabs-container" id="tabs-container">
            <!-- <button class="tab-button active" data-tab="created" id="created-tab">Created</button> -->
            <!-- <button class="tab-button" data-tab="shared" id="shared-tab">Shared</button> -->
        </div>

        <div class="list-container" id="notes-list">
            <h3>Available Notes</h3>
            <?php 
            // Initial render of topics using PHP
            foreach ($data["initial_load"]['notes'] as $note) {
                // Added data-id for JS event listener
                echo '<a class="no-style-link" href="'.ROOT.'/notes/view/'.$note['id'].'"><div class="note-item" data-id="' . htmlspecialchars($note['id']) . '">' . htmlspecialchars($note['title']) . '<span class="pin-icon">pin</span></div></a>';
            }
            ?>
        </div>


        <div class="load-more-container">
            <button id="load-more-btn" class="btn-load-more">
                <?= $data["initial_load"]['has_more'] ? 'Load More' : 'No More Notes' ?>
            </button>
        </div>
    </div>

<?php
include_once "../app/views/partials/footer.view.php";
?>