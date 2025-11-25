<?php

    $title = "Create Topic | Openminds";
    $filename = "notes/create_title";
    $add_back = true;

    include_once "../app/views/partials/header.view.php";

?>

<div class="main-content-container"> 
        <section id="create-topic-section">
            <h2 class="title">Create Topic</h2>
            <label for="topic-name-input">Enter the topic name:</label>
            <input type="text" id="topic-name-input" oninput="window.validateTopicName(this.value)" placeholder="e.g., Quantum Physics Basics">
            <p id="validation-result" class="validation-message"></p>
            <button id="create-topic-btn" class="button button-primary" onclick="window.createTopic()" disabled>Create Topic</button>
        </section>

        </div>

    <div id="notes-modal" class="modal">
        <div class="modal-content">
            <button class="close-btn" onclick="window.closeNotesModal()">&times;</button>
            <section id="move-notes-section" class="move-notes-section">
                <h2>📂 Search Notes & Assign</h2>
                <div class="panels-container">
                    <div class="panel left-panel">
                        <h3>Available Notes</h3>
                        <div class="search-bar-container">
                            <input type="text" id="tag-input" placeholder="Type tag and press Enter" onkeydown="window.addTagOnEnter(event)">
                            <button class="btn-filter" onclick="window.searchNotes(true)">Search</button>
                        </div>

                        <div id="current-tags"></div>

                        <h4>Search Results:</h4>
                        <ul id="available-notes-list" class="notes-list">
                        </ul>
                        <div id="load-more-container">
                            <button id="load-more-btn" class="button-secondary" onclick="window.mockLoadMoreData()" style="width: 100%; margin-top: var(--space-sm); display: none;">
                                Load More Notes
                            </button>
                        </div>
                        
                    </div>

                    <div class="panel right-panel">
                        <h3>Notes to Move</h3>
                        <ul id="added-notes-list" class="notes-list">
                            </ul>
                    </div>
                </div>
                
                <div id="move-notes-btn-container">
                    <button id="move-notes-btn" class="button" onclick="window.moveNotesToNewTopic()">Continue without Moving Notes</button>
                </div>
                
            </section>
        </div>
    </div>

<?php
    include_once "../app/views/partials/footer.view.php";
?>