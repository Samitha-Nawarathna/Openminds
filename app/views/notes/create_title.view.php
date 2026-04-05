<?php

    $title = "Create Topic | Openminds";
    $filename = "notes/create_title";
    $add_back = true;

    include_once "../app/views/partials/header.view.php";
    include "../app/views/partials/focus_timer.php";
    include_once '../app/views/partials/Rnavbar.view.php';

?>

<!-- Split screen container -->
<div class="split-container">
    <!-- Left section with image and text -->
    <div class="left-section">
        <div class="left-content">
            <img src="<?=ROOT?>assets/images/topic_creator_1.png" alt="Create Topic" class="feature-image">
            <div class="feature-text">
                <h3>Create a Topic</h3>
                <p>Create topics to structure your notes and make them easy to find. Each topic becomes a dedicated space for related content.</p>
            </div>
        </div>
    </div>

    <!-- Right section with form -->
    <div class="main-content-container"> 
        <section id="create-topic-section">
            <h2 class="title">Create Topic</h2>
            <label for="topic-name-input" >Enter the topic name:</label>
            <input type="text" id="topic-name-input"  placeholder="e.g., Quantum Physics Basics">
            <p id="validation-result" class="validation-message"></p>
            <button id="create-subject-btn" class="button button-primary" onclick="window.createSubject()">Create Subject</button>

            <button onclick="history.back()" class= "button" style="text-align: center; background-color: var(--color-surface); color: black; border: none;">
                ← Back
            </button>
        </section>
    </div>
</div>

    <div id="notes-modal" class="modal">
        <div class="modal-content">
            <button class="close-btn" onclick="window.closeNotesModal()">&times;</button>
            <section id="move-notes-section" class="move-notes-section">
                <h2 class="modal-title">📂 Move notes to topic</h2>
                <p class="modal-subtitle">move available notes to new topic</p>
                <div class="panels-container">
                    <div class="panel left-panel">
                        <!-- <h3>Available Notes</h3> -->
                        <div class="search-bar-container">
                            <input type="text" id="tag-input" placeholder="Type tag and press Enter" onkeydown="window.addTagOnEnter(event)">
                            <button class="btn-filter" onclick="window.searchNotes(true)">Search</button>
                        </div>

                        <div id="current-tags"></div>

                        <!-- <h4>Search Results:</h4> -->
                        <ul id="available-notes-list" class="notes-list">
                            <li class="note-item empty-state">Search for notes to assign.</li>
                        </ul>
                        <div id="load-more-container" class=  "text-center" style="padding-top: var(--space-sm);">
                            <button id="load-more-btn" class="button-secondary" onclick="window.loadMoreNotes()" style="width: 100%; display: none;">
                                Load More Notes
                            </button>
                            <p id="search-message" class="validation-message"></p>
                        </div>
                        
                    </div>

                    <div class="panel right-panel">
                        <h3>Notes to Move</h3>
                        <ul id="added-notes-list" class="notes-list">
                             <li class="note-item empty-state empty-state-illustration">
                                <svg width="80" height="80" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" style="opacity: 0.3; margin-bottom: 8px;">
                                    <path d="M9 12h6M9 16h6M9 8h6" stroke-linecap="round"/>
                                    <path d="M4 6a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6Z"/>
                                </svg>
                                <span style="color: var(--color-gray-500); font-size: var(--font-size-sm);">No notes added yet</span>
                             </li>
                            </ul>
                    </div>
                </div>
                
                <div id="move-notes-btn-container">
                    <button id="move-notes-btn" class="button button-primary" onclick="window.moveNotesToNewTopic()">Continue without Moving Notes</button>
                </div>
                
            </section>
        </div>
    </div>
    
<script>
    window.ROOT = "<?=ROOT?>/";
</script>

<?php
    include_once "../app/views/partials/footer.view.php";
?>