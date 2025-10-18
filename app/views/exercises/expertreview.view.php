<?php
$title = "Review Exercise | Openminds";
include_once "../app/views/partials/header.view.php";
?>
<link rel="stylesheet" href="/Openminds/public/assets/css/exercises/expertreview.view.css">
<div class="review-container">
    <div class="review-header">
        <a href="/Openminds/expert/approvals" class="back-link">&larr; Back to Pending Approvals</a>
        <h1 class="review-title">Review Exercise</h1>
    </div>
    <div class="context-bar">
        <span class="status pending">Pending Approval</span>
        <span class="meta">Subject: <strong>Geography</strong></span>
        <span class="meta">Title: <strong>European Capitals and Geography Quiz</strong></span>
        <span class="meta">Created by: <strong>mentor_user123</strong> on 2023-10-27</span>
        <span class="meta">Questions: <strong>3</strong></span>
    </div>
    <div class="exercise-preview-card">
        <div class="preview-title">Exercise Preview</div>
        <div class="question-block">
            <div class="question-header">
                <span class="question-number">Question 1:</span>
                <span class="difficulty easy">Easy</span>
            </div>
            <div class="question-text">What is the capital of France?</div>
            <div class="answer-list">
                <div class="answer correct">A) Paris <span class="answer-icon">&#10003;</span></div>
                <div class="answer">B) London</div>
                <div class="answer">C) Berlin</div>
                <div class="answer">D) Madrid</div>
            </div>
            <div class="correct-answer">Correct Answer: <strong>A) Paris</strong></div>
            <div class="explanation-block">
                <span class="explanation-label">Explanation:</span>
                <div class="explanation-text">Paris is the capital and most populous city of France.</div>
            </div>
        </div>
        <div class="question-block">
            <div class="question-header">
                <span class="question-number">Question 2:</span>
                <span class="difficulty medium">Medium</span>
            </div>
            <div class="question-text">Which of the following are European capitals? (Select all that apply)</div>
            <div class="answer-list">
                <div class="answer">A) Berlin</div>
                <div class="answer">B) Tokyo</div>
                <div class="answer correct">C) Rome <span class="answer-icon">&#10003;</span></div>
                <div class="answer">D) Sydney</div>
            </div>
            <div class="correct-answer">Correct Answer: <strong>C) Rome</strong></div>
            <div class="explanation-block">
                <span class="explanation-label">Explanation:</span>
                <div class="explanation-text">Rome is the largest country in Europe by area, covering about 40% of the continent.</div>
            </div>
        </div>
    </div>
    <form id="decisionForm">
        <div class="decision-card">
            <div class="decision-title">Expert Decision</div>
            <div class="decision-options">
                <label class="option-label">
                    <input type="radio" name="decision" value="approve" id="approveOption">
                    <span class="option-text">Approve Exercise - This exercise is correct and ready to publish.</span>
                </label>
                <label class="option-label">
                    <input type="radio" name="decision" value="reject" id="rejectOption">
                    <span class="option-text">Reject Exercise - This exercise has an issue.</span>
                </label>
            </div>
            <div class="feedback-block" id="feedbackBlock" style="display:none;">
                <label for="feedback" class="feedback-label">Feedback for Creator <span class="required">*</span></label>
                <textarea id="feedback" name="feedback" placeholder="Please provide feedback to the creator..." required></textarea>
                <div class="feedback-hint">e.g. "Question 2 needs clearer wording. Consider specifying that multiple answers are required."</div>
            </div>
            <button type="submit" class="submit-btn">Submit Decision</button>
        </div>
    </form>
</div>
<script src="/Openminds/public/assets/js/views/exercises/expertreview.view.js"></script>
<?php include_once "../app/views/partials/footer.view.php"; ?>
