

document.addEventListener('DOMContentLoaded', () => {
    const note_card = document.querySelector('.note-card');
    // --- Timer DOM Elements (inside the modal) ---
    const timeInput = document.getElementById('timer-minutes-input');
    const timerStartModalBtn = document.getElementById('timer-start-modal-btn'); // Renamed
    const unitLabel = document.querySelector('.unit-label');

    const setTime = document.getElementById('timer-minutes-input').value; //save the set focut time
    
    // --- New Fixed Timer Elements ---
    const focusButtonTrigger = document.getElementById('focus-button-trigger');
    const runningTimerState = document.getElementById('running-timer-state');
    const countdownDisplayFixed = document.getElementById('countdown-display-fixed');
    const cancelTimerBtn = document.getElementById('cancel-timer-btn');

    // --- Modal Elements ---
    const timerModal = document.getElementById('timer-modal');
    const closeModalBtn = timerModal.querySelector('.close-btn');

    // --- Collapsible Tags Elements ---
    const tagsToggleBtn = document.querySelector('.tags-toggle-btn');
    const tagsContent = document.getElementById('tags-content');

    // --- State Variables ---
    let timerInterval = null;
    let isRunning = false;
    let totalSeconds = 0;

        // auto resume if timer exists
    if(localStorage.getItem("targetTime")){
    formatTime();
}

    // --- Utility and Core Timer Functions ---
    function open_note(id) {
        note_card.style.transform = 'translateX(-100%)';
        window.abstractNoteModalManager.openNote(id);
    }

    window.open_note = open_note;

    function close_note() {
        note_card.style.transform = 'translateX(-50%)';
    }

    window.close_note = close_note;


    function formatTime(seconds) {
        const mins = Math.floor(seconds / 60);
        const secs = seconds % 60;
        const displayMins = localStorage.getItem("targetTime") ? (parseInt(localStorage.getItem("targetTime")) - Date.now()) / 60000 : 0;
        return displayMins;
    }

    function updateCountdown() {
        const targetTime = localStorage.getItem("targetTime");
        
        if (!targetTime) {
            stopTimer();
            return;
        }

        const currentTime = Date.now();
        const remainingMs = targetTime - currentTime;

        if (remainingMs <= 0) {
            stopTimer(true);
            return;
        }

        // Convert ms to MM:SS
        const totalSeconds = Math.floor(remainingMs / 1000);
        const mins = Math.floor(totalSeconds / 60);
        const secs = totalSeconds % 60;
        
        countdownDisplayFixed.textContent = 
            `${mins.toString().padStart(2, '0')}:${secs.toString().padStart(2, '0')}`;
    }

    function startTimer() {
        const minutes = parseInt(timeInput.value);

        if (isNaN(minutes) || minutes <= 0) {
            alert("Please enter a valid focus time (1-180 minutes).");
            return;
        }

        // Calculate and save the end timestamp
        const targetTimestamp = Date.now() + (minutes * 60 * 1000);
        localStorage.setItem("targetTime", targetTimestamp);

        // Update UI
        timerModal.style.display = 'none';
        focusButtonTrigger.style.display = 'none';
        runningTimerState.style.display = 'flex';

        // Start Interval
        if (timerInterval) clearInterval(timerInterval);
        updateCountdown(); // Run once immediately
        timerInterval = setInterval(updateCountdown, 1000);
    }

    function stopTimer(completed = false) {
        clearInterval(timerInterval);
        localStorage.removeItem("targetTime");
        
        runningTimerState.style.display = 'none';
        focusButtonTrigger.style.display = 'block';

        if (completed) {
            alert("Focus period complete! Great work.");
        }
    }

// --- Persistence Logic (The "Auto-Resume") ---
    function checkExistingTimer() {
        const targetTime = localStorage.getItem("targetTime");
        if (targetTime) {
            const remaining = targetTime - Date.now();
            if (remaining > 0) {
                // Timer is still valid, resume UI state
                focusButtonTrigger.style.display = 'none';
                runningTimerState.style.display = 'flex';
                updateCountdown();
                timerInterval = setInterval(updateCountdown, 1000);
            } else {
                // Timer expired while page was closed
                localStorage.removeItem("targetTime");
            }
        }
    }

    // --- Initialization ---
    checkExistingTimer();

    // --- Event Listeners ---
    if (timerStartModalBtn) {
        timerStartModalBtn.addEventListener('click', startTimer);
    }

    if (cancelTimerBtn) {
        cancelTimerBtn.addEventListener('click', () => {
            if (confirm("Are you sure you want to cancel the focus period?")) {
                stopTimer();
            }
        });
    }

    // Modal Controls
    focusButtonTrigger.addEventListener('click', () => timerModal.style.display = 'block');
    closeModalBtn.addEventListener('click', () => timerModal.style.display = 'none');
    window.addEventListener('click', (e) => { if (e.target === timerModal) timerModal.style.display = 'none'; });
});
  

    // --- Delete Button Confirmation ---
    const deleteBtn = document.querySelector('.btn-delete');
    if (deleteBtn) {
        deleteBtn.addEventListener('click', (e) => {
            e.preventDefault();
            if (confirm("Are you sure you want to delete this note?")) {
                const deleteForm = document.getElementById('delete-note-form');
                if (deleteForm) {
                    deleteForm.submit();
                } else {
                    console.error("Delete form not found");
                }
            }
        });
    }
;