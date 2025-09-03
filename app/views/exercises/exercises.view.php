
<!-- Include header partial -->
<?php  
    $title = "Exercises | Openminds";
    $filename = "exercises/exercies";

    include_once "../app/views/partials/header.view.php";
?>


<!-- Link CSS at the top for proper styling -->
<link rel="stylesheet" href="/Openminds/public/assets/css/exercises/exercises.view.css">

<!-- Exercises UI with Filtering Options -->
<div class="exercises-container">
    <aside class="filters-panel" id="filtersPanel">
        <h3>Filters <span id="closeFilters" class="close-filters">&times;</span></h3>
        <div class="filter-group">
            <label for="subjectSelect">Subject</label>
            <select id="subjectSelect" multiple>
                <option>Mathematics</option>
                <option>Physics</option>
                <option>Computer Science</option>
                <option>Chemistry</option>
                <option>Biology</option>
            </select>
        </div>
        <div class="filter-group">
            <label>Status</label>
            <div><input type="checkbox" id="attempted"> <label for="attempted">Attempted</label></div>
            <div><input type="checkbox" id="notAttempted"> <label for="notAttempted">Not Attempted</label></div>
            <div><input type="checkbox" id="createdByYou"> <label for="createdByYou">Created by you</label></div>
        </div>
        <button id="applyFilters">Apply Filters</button>
    </aside>
    <main class="exercises-main">
        <div class="exercises-header">
            <button id="openFilters" class="filters-btn"><span>&#x1F50D;</span> Filters</button>
            <h2>Browse Exercises</h2>
            <input type="text" id="searchInput" placeholder="Search by title">
            <button id="createExerciseBtn">Create Exercise</button>
        </div>
        <div class="exercises-list" id="exercisesList">
            <!-- Example Exercise Cards -->
            <div class="exercise-card" data-subject="Mathematics" data-status="notAttempted">
                <span class="tag math">Mathematics</span>
                <h4>What is the derivative of x^2 + 3x - 5 with respect to x?</h4>
                <div class="meta">By: Expert1 | <span class="stars">★ 12</span> | Attempted: 150 times</div>
                <button class="start-btn">Start Exercise</button>
            </div>
            <div class="exercise-card" data-subject="Physics" data-status="attempted">
                <span class="tag physics">Physics</span>
                <h4>Explain Newton's Second Law of Motion and provide a real-world example.</h4>
                <div class="meta">By: PhysicsPro | <span class="stars">★ 25</span> | Attempted: 210 times</div>
                <button class="retry-btn">Retry</button>
            </div>
            <div class="exercise-card" data-subject="Computer Science" data-status="notAttempted">
                <span class="tag cs">Computer Science</span>
                <h4>What is the time complexity of a binary search algorithm?</h4>
                <div class="meta">By: CodeMaster | <span class="stars">★ 18</span> | Attempted: 95 times</div>
                <button class="start-btn">Start Exercise</button>
            </div>
            <div class="exercise-card" data-subject="Chemistry" data-status="notAttempted">
                <span class="tag chem">Chemistry</span>
                <h4>Balance the chemical equation: H2 + O2 → H2O.</h4>
                <div class="meta">By: ChemWiz | <span class="stars">★ 9</span> | Attempted: 50 times</div>
                <button class="start-btn">Start Exercise</button>
            </div>
            <div class="exercise-card" data-subject="Biology" data-status="attempted">
                <span class="tag bio">Biology</span>
                <h4>Describe the process of photosynthesis in plants.</h4>
                <div class="meta">By: BioNerd | <span class="stars">★ 23</span> | Attempted: 300 times</div>
                <button class="retry-btn">Retry</button>
            </div>
            <div class="exercise-card" data-subject="Mathematics" data-status="notAttempted">
                <span class="tag math">Mathematics</span>
                <h4>Solve the quadratic equation: x^2 - 5x + 6 = 0</h4>
                <div class="meta">By: MathGuru | <span class="stars">★ 21</span> | Attempted: 180 times</div>
                <button class="start-btn">Start Exercise</button>
            </div>
        </div>
        <button id="loadMoreBtn">Load More</button>
    </main>

    <div class="filters-overlay" id="filtersOverlay"></div>
</div>


<!-- Include footer partial -->


<!-- Link JS at the bottom for proper functionality -->
<script src="/Openminds/public/assets/js/views/exercises/exercises.view.js"></script>

<?php

    include_once "../app/views/partials/footer.view.php";

?>
