

document.addEventListener('DOMContentLoaded', () => {
    const note_card = document.querySelector('.note-card');
    // --- Timer DOM Elements (inside the modal) ---
    const timeInput = document.getElementById('timer-minutes-input');
    const timerStartModalBtn = document.getElementById('timer-start-modal-btn'); // Renamed
    const unitLabel = document.querySelector('.unit-label');

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
        return `${String(mins).padStart(2, '0')}:${String(secs).padStart(2, '0')}`;
    }

    function updateCountdown() {
        if (totalSeconds <= 0) {
            stopTimer(true); // Stop and trigger completion
            return;
        }

        totalSeconds--;
        // Always update the fixed display
        countdownDisplayFixed.textContent = formatTime(totalSeconds);
    }

    function startTimer() {
        const minutes = parseInt(timeInput.value);

        if (isNaN(minutes) || minutes <= 0) {
            alert("Please enter a valid focus time (1-180 minutes).");
            return;
        }

        totalSeconds = minutes * 60;
        isRunning = true;
        timerModal.style.display = 'none'; // Close the modal upon starting

        // --- UI Changes for Start (Fixed Display) ---
        focusButtonTrigger.style.display = 'none';
        runningTimerState.style.display = 'flex';

        // Initial display update
        countdownDisplayFixed.textContent = formatTime(totalSeconds);

        timerInterval = setInterval(updateCountdown, 1000);
    }

    function stopTimer(completed = false) {
        clearInterval(timerInterval);
        isRunning = false;

        // --- UI Changes for Stop/Reset (Fixed Display) ---
        runningTimerState.style.display = 'none';
        focusButtonTrigger.style.display = 'block';

        if (completed) {
            alert("Focus period complete! Great work.");
            timeInput.value = 30; // Reset input
        }
    }




    // --- Collapsible Tags Logic ---
    // if (tagsToggleBtn && tagsContent) {
    //     tagsToggleBtn.addEventListener('click', () => {
    //         const isExpanded = tagsToggleBtn.getAttribute('aria-expanded') === 'true' || false;
    //         tagsToggleBtn.setAttribute('aria-expanded', !isExpanded);
    //         tagsContent.classList.toggle('show');

    //         const icon = tagsToggleBtn.querySelector('.toggle-icon');
    //         if (icon) {
    //             icon.textContent = isExpanded ? '▼' : '▲';
    //         }
    //     });
    // }

    // --- Modal Logic ---
    if (focusButtonTrigger && timerModal && closeModalBtn) {
        // Open Modal
        focusButtonTrigger.addEventListener('click', () => {
            timerModal.style.display = 'block';
        });

        // Close Modal on 'x' click
        closeModalBtn.addEventListener('click', () => {
            timerModal.style.display = 'none';
        });

        // Close Modal on outside click
        window.addEventListener('click', (event) => {
            if (event.target === timerModal) {
                timerModal.style.display = 'none';
            }
        });
    }


    // --- Timer Event Listeners ---

    // 1. Start timer from modal
    if (timerStartModalBtn) {
        timerStartModalBtn.addEventListener('click', () => {
            if (!isRunning) {
                startTimer();
            }
        });
    }

    // 2. Cancel timer from fixed display
    if (cancelTimerBtn) {
        cancelTimerBtn.addEventListener('click', () => {
            if (isRunning) {
                if (confirm("Are you sure you want to cancel the focus period?")) {
                    stopTimer();
                }
            }
        });
    }

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
});