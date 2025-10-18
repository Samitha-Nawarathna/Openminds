import {colors} from '../../core/config.js';

document.addEventListener('DOMContentLoaded', () => {
    // --- State Variables ---
    const allQuestions = ALL_QUESTIONS_DATA;
    let currentQuestionIndex = 0;
    
    // --- DOM Elements ---
    const questionNumberEl = document.getElementById('question-number');
    const questionTextEl = document.getElementById('question-text-p');
    const optionsListEl = document.getElementById('options-list');
    const btnBack = document.getElementById('btn-back');
    const btnNext = document.getElementById('btn-next');

    // --- Utility Functions ---

    /**
     * Renders a question and its options. Radio buttons are disabled.
     * @param {number} index - The index of the question in the allQuestions array.
     */
    function displayQuestion(index) {
        const question = allQuestions[index];
        
        // Update header and question text
        questionNumberEl.textContent = `Question ${index + 1} of ${allQuestions.length}`;
        questionTextEl.textContent = question.question_text;
        
        // Clear previous options
        optionsListEl.innerHTML = '';

        // Create and append new options
        console.log(question.options);

        question.options.forEach((option, optionIndex) => {
            const li = document.createElement('li');
            
            const input = document.createElement('input');
            input.type = 'radio';
            input.name = `question_${question.id}`;
            input.id = `q${question.id}_opt${optionIndex}`;
            input.value = optionIndex;
            // CRITICAL: Disable the input fields for review mode
            input.disabled = true;
            
            const label = document.createElement('label');
            label.htmlFor = input.id;
            label.textContent = option;
            
            // Optional: Visually indicate the correct answer (based on mock data)
            if (question.selected_index === optionIndex) {
                 // You would style this with CSS to show a green highlight
                 label.classList.add('is-correct'); 
                 input.checked = true; // Mark the correct one
            }

            if (question.correct_index === optionIndex)
            {
                label.style.color = colors.green;
            }



            label.prepend(input);
            li.appendChild(label);
            optionsListEl.appendChild(li);
        });

        // Update navigation button states
        btnBack.disabled = (index === 0);
        btnNext.disabled = (index === allQuestions.length - 1);
    }

    // --- Event Listeners (Navigation) ---

    // Note: We are using the navigation buttons to cycle questions in review mode.
    // We remove the `recordAnswer()` function call as answers aren't being tracked.

    btnNext.addEventListener('click', () => {
        if (currentQuestionIndex < allQuestions.length - 1) {
            currentQuestionIndex++;
            displayQuestion(currentQuestionIndex);
        }
    });

    btnBack.addEventListener('click', () => {
        if (currentQuestionIndex > 0) {
            currentQuestionIndex--;
            displayQuestion(currentQuestionIndex);
        }
    });

    // --- Initialization ---
    displayQuestion(0);
});