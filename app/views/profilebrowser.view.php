<?php
    //setting page variables
    $title = 'All Profiles';
    $filename = 'profilebrowser';

    //variables
    $profile_picture_url = ROOT."/uploads/0/profile.avif";

    //put header
    include_once('../app/views/partials/header.view.php');

?>
<div class="profilebrowser-container main-content-container">
    <header class="exercise-browser-header">
            <h3 class="main-title">Profiles</h3>
        </header>
        
        <div class="filter-bar">
            <!-- ID used by profilebrowser.view.js for search -->
            <input type="text" id="exercise-filter-input" placeholder="enter a username name">
            <button class="btn-filter" id="filter-btn">filter</button>
        </div>
        <div class="tab-btns">
            <!-- These data-index attributes trigger the state change in JS -->
            <div class="tab-button btn-primary" data-index="0">
                active
            </div>
            <div class="tab-button btn-none" data-index="1">
                banned
            </div>        
        </div>
        <div class="content-tabs">
            <!-- Content will be rendered here -->
            <div class="content-tab container active"></div>

            <div class="content-tab container"></div>

        </div>
        
        <!-- Load More Button -->
        <div class="load-more-container">
            <button class="btn-load-more" id="load-more-btn">Load More</button>
        </div>

    </div>

</div>

<?php 
    include_once('../app/views/partials/footer.view.php');
?>