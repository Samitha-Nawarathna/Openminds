<?php

    $title = $data["note"]["title"]." | Openminds";
    $filename = "notes/view";

    include_once "../app/views/partials/header.view.php";

    $data['note']['content'] = "
    In mathematics and physics, a vector space (also called a linear space) is a set whose elements, often called vectors, can be added together and multiplied ('scaled') by numbers called scalars. The operations of vector addition and scalar multiplication must satisfy certain requirements, called vector axioms. Real vector spaces and complex vector spaces are kinds of vector spaces based on different kinds of scalars: real numbers and complex numbers. Scalars can also be, more generally, elements of any field.
Vector spaces generalize Euclidean vectors, which allow modeling of physical quantities (such as forces and velocity) that have not only a magnitude, but also a direction. The concept of vector spaces is fundamental for linear algebra, together with the concept of matrices, which allows computing in vector spaces. This provides a concise and synthetic way for manipulating and studying systems of linear equations.
Vector spaces are characterized by their dimension, which, roughly speaking, specifies the number of independent directions in the space. This means that, for two vector spaces over a given field and with the same dimension, the properties that depend only on the vector-space structure are exactly the same (technically the vector spaces are isomorphic). A vector space is finite-dimensional if its dimension is a natural number. Otherwise, it is infinite-dimensional, and its dimension is an infinite cardinal. Finite-dimensional vector spaces occur naturally in geometry and related areas. Infinite-dimensional vector spaces occur in many areas of mathematics. For example, polynomial rings are countably infinite-dimensional vector spaces, and many function spaces have the cardinality of the continuum as a dimension.
Many vector spaces that are considered in mathematics are also endowed with other structures. This is the case of algebras, which include field extensions, polynomial rings, associative algebras and Lie algebras. This is also the case of topological vector spaces, which include function spaces, inner product spaces, normed spaces, Hilbert spaces and Banach spaces.
    ";

?>

<div class="note-viewer-wrapper">
    <div class="note-card">
        
        <div class="main-content-area">

            <div class="note-title-input"><?= htmlspecialchars($data['note']['title']) ?></div>
            
            <div class="tags-collapsible-container">
                <div id="tags-content" class="tags-collapsible-content.show">
                    <div class="tags-display">
                    <?php foreach ($data['note']['tags'] as $tag): ?>
                            <?php 
                                // NEW: Get the consistent colors for the current tag
                                $colors = generateTagColor($tag);
                            ?>
                            <span 
                                class="tag-pill" 
                                style="background-color: <?= $colors['bg'] ?>; color: <?= $colors['text'] ?>;">
                                <?= htmlspecialchars($tag) ?>
                            </span>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <div class="note-content-textarea"><?= htmlspecialchars($data['note']['content']) ?></div>
            <div id="fixed-timer-container">
    
    <button id="focus-button-trigger" class="btn-primary">
        <span class="timer-icon">🕒</span> Focus Timer
    </button>
    
    <div id="running-timer-state" class="timer-display-running" style="display: none;">
        <span id="countdown-display-fixed">00:00</span>
        <button id="cancel-timer-btn" class="btn-cancel-fixed">&times;</button>
    </div>
</div>
            
            <div class="action-buttons-bottom">
                <button class="btn-share button btn-primary"><a href="<?=ROOT?>/notes/share?id=<?=$data['note']['id']?>" class="no-style-link">Share</a></button>
                <button class="btn-edit button btn-none"><a href="<?=ROOT?>/notes/edit/<?=$data['note']['id']?>" class="no-style-link">Edit</a></button>
                <button class="btn-delete button btn-error"><a href="<?=ROOT?>/notes/delete/<?=$data['note']['id']?>" class="no-style-link">Delete</a></button>
            </div>
        </div>
        
    </div>
</div>

<div style="position: fixed; top: 10px; right: 55vw; z-index: 999;">
    <button onclick="window.open_note(1)">Open Note 1</button>
    <button onclick="window.open_note(2)">Open Note 2</button>
</div>



<div id="timer-modal" class="modal">
    <div class="modal-content">
        <span class="close-btn">&times;</span>
        <h2>Set Focus Period</h2>
        <div class="focus-timer-container">
            <div class="timer-controls">
                <input type="number" id="timer-minutes-input" value="30" min="1" max="180">
                <span class="unit-label">min</span>
                <button id="timer-start-modal-btn" class="btn-start">Start</button>
            </div>
        </div>
    </div>
</div>

<?php
    include_once "../app/views/partials/footer.view.php";
?>