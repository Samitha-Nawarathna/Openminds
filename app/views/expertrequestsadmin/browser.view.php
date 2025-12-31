<?php
    //setting page variables
    $title = 'All requests';
    $filename = 'expertrequestsadmin/browser';

    //put header
    include_once('../app/views/partials/header.view.php');

?>
<div class="requestbrowser-container main-content-container">
<header class="exercise-browser-header">
            <h3 class="main-title">All Requests</h3>
        </header>
        
        <div class="filter-bar">
            <input type="text" id="exercise-filter-input" placeholder="enter a username name">
            <button class="btn-filter" id="filter-btn">filter</button>
        </div>
        <div class="tab-btns">
            <div class="tab-button active" data-index="0">
                active
            </div>
            <div class="tab-button" data-index="1">
                banned
            </div>        
        </div>
        <div class="content-tabs">
            <div class="content-tab container active"></div>

            <div class="content-tab container"></div>
            <div class="content-tab container"></div>

</div>

<?php 
    include_once('../app/views/partials/footer.view.php');
?>