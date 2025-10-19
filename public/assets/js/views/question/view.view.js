import {ROOT} from "../../core/config.js";

// --- MOCK AJAX FUNCTION ---
function mockAjaxCall(endpoint, data) {
    console.log(`[MOCK AJAX] Calling endpoint: ${endpoint} with data:`, data);
    return new Promise(resolve => {
        setTimeout(() => {
            const mockResponse = {
                success: true,
                new_count: (data.current_count || 0) + (data.action === 'upvote' ? 1 : -1)
            };
            resolve(mockResponse);
        }, 300);
    });
}

// --- CLIENT-SIDE LOGIC ---

/**
 * Handles the voting action (upvote/downvote)
 */
function handleVote(element, action) {
    // ... (previous voting logic remains unchanged)
}

// --- NEW FUNCTIONS FOR EDIT AND DELETE ---

/**
 * Handles the click on an "Edit" button.
 * In a real app, this would open an editing form.
 * @param {string} itemType - 'question' or 'answer'.
 * @param {string} itemId - The ID of the item to edit.
 */
function handleEdit(itemType, itemId) {
    if (itemType === 'question')
    {
        window.location.href = ROOT + `question/edit?id=${itemId}`;
        return;    
    }
    window.location.href = ROOT + `question/edit_answer?id=${itemId}`;
    // Future logic: Show an inline editing form or redirect to an edit page.
}

/**
 * Handles the click on a "Delete" button.
 * In a real app, this would show a confirmation modal.
 * @param {string} itemType - 'question' or 'answer'.
 * @param {string} itemId - The ID of the item to delete.
 */
function handleDelete(itemType, itemId) {
    if (itemType === 'question')
        {
            window.location.href = ROOT + `question/delete?id=${itemId}`;
            return;    
        }
        window.location.href = ROOT + `question/delete_answer?id=${itemId}`;
}


// Event listener for the fixed 'Answer' button
document.addEventListener('DOMContentLoaded', () => {
    const answerBtn = document.getElementById('answer-btn');
    if (answerBtn) { // Check if the button exists before adding listener
        answerBtn.addEventListener('click', () => {
        });
    }
});

document.addEventListener("DOMContentLoaded", () => {

    // --- Voting (for both question & answers) ---
    document.querySelectorAll(".upvote-count, .downvote-count").forEach(el => {
      el.addEventListener("click", () => {
        const type = el.classList.contains("upvote-count") ? "upvote" : "downvote";
        handleVote(el, type);
      });
    });
  
    // --- Edit buttons (question + answers) ---
    document.querySelectorAll(".btn-edit").forEach(btn => {
      btn.addEventListener("click", () => {
        const type = btn.dataset.type;
        const id = btn.dataset.id;
        handleEdit(type, id);
      });
    });
  
    // --- Delete buttons (question + answers) ---
    document.querySelectorAll(".btn-delete").forEach(btn => {
      btn.addEventListener("click", () => {
        const type = btn.dataset.type;
        const id = btn.dataset.id;
        handleDelete(type, id);
      });
    });
  
    // --- Answer button ---
    const answerBtn = document.getElementById("answer-btn");
    if (answerBtn) {
      answerBtn.addEventListener("click", () => {
        // Example: navigate to answer form or show modal
        // handleAnswer();
        console.log("Answer button clicked");
      });
    }
  
  });