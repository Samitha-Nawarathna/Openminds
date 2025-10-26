import { ROOT } from '../../core/config.js';

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
            if (question.correct_index === optionIndex) {
                 // You would style this with CSS to show a green highlight
                 label.classList.add('is-correct'); 
                 input.checked = true; // Mark the correct one
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

// --- Add these new DOM element selectors at the top ---
const btnReject = document.getElementById('btn-reject');
const rejectModal = document.getElementById('reject-modal');
const closeRejectModalBtn = document.getElementById('close-reject-modal');


// --- NEW: Event Listeners for Reject Modal ---

// 1. Reject Button Click (Opens Modal)
if (btnReject) {
    btnReject.addEventListener('click', () => {
        rejectModal.style.display = 'block';
    });
}

// 2. Close Modal Logic
if (closeRejectModalBtn) {
    closeRejectModalBtn.addEventListener('click', () => {
        rejectModal.style.display = 'none';
    });
}
window.addEventListener('click', (e) => {
    if (e.target === rejectModal) {
        rejectModal.style.display = 'none';
    }
});


function close_popup() {

    let btns = document.querySelectorAll('.btn-dismiss'); 

    btns.forEach(element => {
        element.addEventListener('click', function() {
            element.closest('.popup').style.display='none';
            console.log('Popup closed');
        });
    });
}// console.log('Expert Requests Admin View JS loaded');

close_popup();

let btn_approve = document.querySelector('.btn-approve');
let btn_reject = document.querySelector('.btn-reject');

let confirmation_popup = document.querySelector('.confirmation');

btn_approve.addEventListener('click', function(e) {
    e.preventDefault();

    confirmation_popup.querySelector('.message').innerHTML = 'Are you sure you want to approve this request?';
    confirmation_popup.style.display = 'block';
    let form = confirmation_popup.querySelector('.content form');

    form.setAttribute('action', ROOT + 'exercises/approve/');
    // form.submit();

});

// btn_undo.addEventListener('click', function() {
//     confirmation_popup.querySelector('.message').innerHTML = 'Are you sure you want to undo this request?';
//     confirmation_popup.style.display = 'block';
//     let form = confirmation_popup.querySelector('.content form');

//     form.setAttribute('action', ROOT + '/expertrequestadmin/undo_rejection');
// });

// let feedback_popup = document.querySelector('.feedback');

// btn_reject.addEventListener('click', function() {
//     // confirmation_popup.querySelector('.message').innerHTML = 'Are you sure you want to approve this request?';
//     feedback_popup.style.display = 'block';
//     let form = feedback_popup.querySelector('.content form');
//     form.querySelector('.input-group textarea').setAttribute('placeholder', 'Please provide a reason for rejecting this request.');
//     // console.log(form);

//     form.setAttribute('action', ROOT + '/expertrequestadmin/reject');

// });