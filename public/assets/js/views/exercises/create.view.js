// Dynamic form logic for Create Exercise
let questionCount = 1;
let questions = [];

function createQuestionCard(index, data = {}) {
    return `<div class="question-card" data-index="${index}">
        <div class="question-header">
            <span class="question-number">${index + 1}. Question ${index + 1}</span>
            <button type="button" class="delete-question-btn" title="Delete Question">&#128465;</button>
        </div>
        <div class="question-body">
            <label class="required">Question Text</label>
            <textarea name="questionText" required placeholder="Enter your question here...">${data.questionText || ''}</textarea>
            <label class="required">Answer Options</label>
            <div class="answer-options">
                <div>
                    <span class="option-label a">&#x1F170; Option A</span>
                    <input type="text" name="optionA" required placeholder="Enter option A" value="${data.optionA || ''}">
                </div>
                <div>
                    <span class="option-label b">&#x1F171; Option B</span>
                    <input type="text" name="optionB" required placeholder="Enter option B" value="${data.optionB || ''}">
                </div>
                <div>
                    <span class="option-label c">&#x1F172; Option C</span>
                    <input type="text" name="optionC" required placeholder="Enter option C" value="${data.optionC || ''}">
                </div>
                <div>
                    <span class="option-label d">&#x1F173; Option D</span>
                    <input type="text" name="optionD" required placeholder="Enter option D" value="${data.optionD || ''}">
                </div>
            </div>
            <label>Correct Answer(s) <span style="font-size:0.9em;color:#64748b;">(Select one or more)</span></label>
            <div class="correct-answers">
                <label class="option-label a"><input type="checkbox" class="correct-checkbox a" name="correctA"> Option A</label>
                <label class="option-label b"><input type="checkbox" class="correct-checkbox b" name="correctB"> Option B</label>
                <label class="option-label c"><input type="checkbox" class="correct-checkbox c" name="correctC"> Option C</label>
                <label class="option-label d"><input type="checkbox" class="correct-checkbox d" name="correctD"> Option D</label>
            </div>
            <label>Explanation (Optional)</label>
            <textarea name="explanation" placeholder="Explain why this answer is correct...">${data.explanation || ''}</textarea>
            <label class="required">Difficulty Level</label>
            <select name="difficulty" required>
                <option value="">Select difficulty level</option>
                <option value="easy" ${data.difficulty==='easy'?'selected':''}>Easy</option>
                <option value="medium" ${data.difficulty==='medium'?'selected':''}>Medium</option>
                <option value="hard" ${data.difficulty==='hard'?'selected':''}>Hard</option>
            </select>
        </div>
    </div>`;
}

function renderQuestions() {
    const container = document.getElementById('questionsContainer');
    container.innerHTML = '';
    questions.forEach((q, i) => {
        container.innerHTML += createQuestionCard(i, q);
    });
    attachDeleteHandlers();
}

function attachDeleteHandlers() {
    document.querySelectorAll('.delete-question-btn').forEach((btn, idx) => {
        btn.onclick = function() {
            questions.splice(idx, 1);
            renderQuestions();
        };
    });
}

document.addEventListener('DOMContentLoaded', function() {
    questions = [{}]; // Start with one empty question
    renderQuestions();

    document.getElementById('addQuestionBtn').onclick = function() {
        // Only add if previous question is filled
        const lastCard = document.querySelector('.question-card:last-child');
        if (lastCard) {
            const body = lastCard.querySelector('.question-body');
            const requiredFields = [
                body.querySelector('textarea[name="questionText"]'),
                body.querySelector('input[name="optionA"]'),
                body.querySelector('input[name="optionB"]'),
                body.querySelector('input[name="optionC"]'),
                body.querySelector('input[name="optionD"]'),
                body.querySelector('select[name="difficulty"]')
            ];
            let allFilled = requiredFields.every(f => f && f.value.trim() !== '');
            if (!allFilled) {
                alert('Please fill all required fields in the previous question before adding another.');
                return;
            }
        }
        questions.push({});
        renderQuestions();
    };

    document.getElementById('exerciseForm').onsubmit = function(e) {
        e.preventDefault();
        // Collect all questions
        questions = Array.from(document.querySelectorAll('.question-card')).map(card => {
            const body = card.querySelector('.question-body');
            return {
                questionText: body.querySelector('textarea[name="questionText"]').value,
                optionA: body.querySelector('input[name="optionA"]').value,
                optionB: body.querySelector('input[name="optionB"]').value,
                optionC: body.querySelector('input[name="optionC"]').value,
                optionD: body.querySelector('input[name="optionD"]').value,
                correctA: body.querySelector('input[name="correctA"]').checked,
                correctB: body.querySelector('input[name="correctB"]').checked,
                correctC: body.querySelector('input[name="correctC"]').checked,
                correctD: body.querySelector('input[name="correctD"]').checked,
                explanation: body.querySelector('textarea[name="explanation"]').value,
                difficulty: body.querySelector('select[name="difficulty"]').value
            };
        });
        // No final questions-list box displayed
        document.getElementById('expertApproval').style.display = 'block';
    };

    document.getElementById('saveDraftBtn').onclick = function() {
        alert('Draft saved!');
    };
    document.querySelector('.approve-btn').onclick = function() {
        alert('Exercise approved by expert!');
    };
});
