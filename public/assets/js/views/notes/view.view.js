document.addEventListener('DOMContentLoaded', () => {
    // --- DOM Elements ---
    const timeInput = document.getElementById('timer-minutes-input');
    const timerStartModalBtn = document.getElementById('timer-start-modal-btn');
    const focusButtonTrigger = document.getElementById('focus-button-trigger');
    const runningTimerState = document.getElementById('running-timer-state');
    const countdownDisplayFixed = document.getElementById('countdown-display-fixed');
    const cancelTimerBtn = document.getElementById('cancel-timer-btn');
    const timerModal = document.getElementById('timer-modal');
    const closeModalBtn = timerModal.querySelector('.close-btn');

    // --- State Variables ---
    let timerInterval = null;

    // --- Core Functions ---

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