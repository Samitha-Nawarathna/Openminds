document.addEventListener('DOMContentLoaded', () => {
    // --- DOM Elements ---
    const timeInput = document.getElementById('timer-minutes-input');
    const timerButton = document.getElementById('timer-btn');
    const countdownDisplay = document.getElementById('countdown-display');
    const unitLabel = document.querySelector('.unit-label');

    // --- State Variables ---
    let timerInterval = null; // Stores the interval ID for stopping the timer
    let isRunning = false;
    let totalSeconds = 0;

    // --- Utility Functions ---

    /** Formats seconds into MM:SS string. */
    function formatTime(seconds) {
        const mins = Math.floor(seconds / 60);
        const secs = seconds % 60;
        return `${String(mins).padStart(2, '0')}:${String(secs).padStart(2, '0')}`;
    }

    /** Updates the countdown display every second. */
    function updateCountdown() {
        if (totalSeconds <= 0) {
            stopTimer(true); // Stop and trigger completion
            return;
        }

        totalSeconds--;
        countdownDisplay.textContent = formatTime(totalSeconds);
    }

    // --- Core Timer Functions ---

    /** Starts the timer and updates the UI state. */
    function startTimer() {
        const minutes = parseInt(timeInput.value);

        if (isNaN(minutes) || minutes <= 0) {
            alert("Please enter a valid focus time (at least 1 minute).");
            return;
        }

        totalSeconds = minutes * 60;
        isRunning = true;

        // UI Changes for Start
        timeInput.style.display = 'none';
        unitLabel.style.display = 'none';
        
        timerButton.textContent = 'Cancel';
        timerButton.classList.remove('btn-start');
        timerButton.classList.add('btn-cancel');

        countdownDisplay.textContent = formatTime(totalSeconds);
        countdownDisplay.style.display = 'block';

        // Start the interval
        timerInterval = setInterval(updateCountdown, 1000);
    }

    /** Stops the timer and resets the UI state. */
    function stopTimer(completed = false) {
        clearInterval(timerInterval);
        isRunning = false;

        // UI Changes for Stop/Reset
        timeInput.style.display = 'inline-block';
        unitLabel.style.display = 'inline-block';
        
        timerButton.textContent = 'Start';
        timerButton.classList.remove('btn-cancel');
        timerButton.classList.add('btn-start');

        countdownDisplay.style.display = 'none';
        
        if (completed) {
            alert("Focus period complete! Great work.");
            // Reset input value to a default state (e.g., 30)
            timeInput.value = 30;
        }
    }

    // --- Event Listener ---

    timerButton.addEventListener('click', () => {
        if (isRunning) {
            if (confirm("Are you sure you want to cancel the focus period?")) {
                stopTimer();
            }
        } else {
            startTimer();
        }
    });
    

    document.querySelector('.btn-delete').addEventListener('click', () => {
        if (confirm("Are you sure you want to delete this note? (Mock Action)")) {
            alert("Note deleted.");
        }
    });

    // Initial check to ensure the countdown display is hidden on load
    stopTimer();
});