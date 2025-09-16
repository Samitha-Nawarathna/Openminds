<?php
    //setting page variables
    $title = 'All requests';
    $filename = 'expertrequests/requestbrowser';

    //put header
    include_once('../app/views/partials/header.view.php');

?>
<div class="requestbrowser-container background-gradient">
    <div class="requestbrowser-wrapper">
        <div class="title">
            Your Requests
        </div>
        <div class="option-container">
            <div class="search-container">
                <input type="text" placeholder="Search your requests" class="search-input">
                <button class="search-btn">
                    filter
                </button>
            </div>
            <form action="<?=ROOT?>expertrequest/create" method="get">
                <button class="button create-btn">
                    + Create
                </button>
            </form>
        </div>
        <div class="tab-btns">
            <div class="button" data-index="0">
                pending
            </div>
            <div class="btn-none button" data-index="1">
                approved
            </div>
            <div class="btn-none button" data-index="2">
                rejected
            </div>                    
        </div>
        <div class="content-tabs">
            <div class="content-tab container active"></div>

            <div class="content-tab container"></div>
            <div class="content-tab container"></div>
        </div>

    </div>

</div>

<?php 
    include_once('../app/views/partials/footer.view.php');
?>