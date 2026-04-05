<script type="module" src="<?=ROOT?>/assets/js/views/notes/view.view.js"></script>
<link rel="stylesheet" href="<?=ROOT?>/assets/css/notes/view.view.css">


<div id="fixed-timer-container">
    
    <button id="focus-button-trigger" class="btn-primary">
        <span class="timer-icon">🕒</span> Focus Timer
    </button>
    
    <div id="running-timer-state" class="timer-display-running" style="display: none;">
        <span id="countdown-display-fixed">00:00</span>
        <button id="cancel-timer-btn" class="btn-cancel-fixed">&times;</button>
    </div>
</div>
            
<div id="timer-modal" class="modal">
    <div class="modal-content">
        <span class="close-btn">&times;</span>
        <h2>Set Focus Period</h2>
        <div class="focus-timer-container">
            <div class="timer-controls">
                <input type="number" id="timer-minutes-input" value="30" min="1" max="180">
                <span class="unit-label">min</span>
                <button id="timer-start-modal-btn" class="btn-start">Start</button>
            </div>
        </div>
    </div>
</div>

<div id="running-timer-state" class="timer-display-running" style="display: none;">
    <div class="progress-container">
        <div id="progress-bar-fill"></div>
    </div>
    <span id="countdown-display-fixed">00:00</span>
    <button id="cancel-timer-btn" class="btn-cancel-fixed">&times;</button>
</div>
