<?php  
    $title = "Create Exercises | Openminds";
    $filename = "exercises/create";

    include_once "../app/views/partials/header.view.php";
?>
<link rel="stylesheet" href="/Openminds/public/assets/css/exercises/create.view.css">
<div class="create-exercise-container">
    <div class="create-header-box">
        <h1 class="create-title">Create Exercise</h1>
        <p class="create-desc">Create engaging multiple-choice questions for students.</p>
    </div>
    <div class="card">
        <form id="exerciseForm">
            <div class="exercise-section card">
                <div class="card-header left-align">📖 Exercise Details</div>
                <div class="card-body">
                    <label for="subject" class="required">Subject</label>
                    <select id="subject" name="subject" required>
                        <option value="">Select a subject</option>
                        <option>Mathematics</option>
                        <option>Physics</option>
                        <option>Computer Science</option>
                        <option>Chemistry</option>
                        <option>Biology</option>
                    </select>
                    <label for="exerciseTitle" class="required">Exercise Title</label>
                    <input type="text" id="exerciseTitle" name="exerciseTitle" placeholder="Enter exercise title (e.g., 'Calculus Derivatives Practice')" required>
                </div>
            </div>
            <div id="questionsContainer">
                <!-- Questions will be rendered here by JS -->
            </div>
            <div class="form-actions">
                <button type="button" id="addQuestionBtn" class="add-question-btn">+ Add Another Question</button>
                <button type="button" id="saveDraftBtn" class="draft-btn">Save Draft</button>
                <button type="submit" id="submitApprovalBtn" class="submit-btn">Submit for Approval</button>
            </div>
        </form>
        <div id="expertApproval" class="expert-approval" style="display:none;">
            <button class="approve-btn">Approve as Expert</button>
        </div>
    </div>
</div>
<script src="/Openminds/public/assets/js/views/exercises/create.view.js"></script>

<?php 
include_once "../app/views/partials/footer.view.php"; 
?>