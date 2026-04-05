 import { ROOT } from "../../core/config.js";

document.addEventListener('DOMContentLoaded', () => {
    const shell = document.querySelector('.mentor-shell');
    const editBtn = document.getElementById('edit-btn');
    const hideBtn = document.getElementById('hide-btn');

    if (!shell) {
        return;
    }

    const editUrl = shell.dataset.editUrl || window.EXERCISE_MENTOR_EDIT_URL || `${ROOT}/exercises/edit`;
    const toggleUrl = shell.dataset.toggleUrl || window.EXERCISE_MENTOR_TOGGLE_URL || shell.dataset.hideUrl || window.EXERCISE_MENTOR_HIDE_URL || `${ROOT}/exercises/toggleVisibility`;
    const exerciseId = shell.dataset.exerciseId;

    const setVisibilityState = (visibility) => {
        const nextVisibility = visibility === 'hidden' ? 'hidden' : 'visible';
        shell.dataset.visibility = nextVisibility;
        if (hideBtn) {
            hideBtn.dataset.visibility = nextVisibility;
            hideBtn.textContent = nextVisibility === 'hidden' ? 'Show to Users' : 'Hide from Users';
            hideBtn.setAttribute('aria-pressed', nextVisibility === 'hidden' ? 'true' : 'false');
            hideBtn.disabled = false;
        }
        shell.classList.toggle('is-hidden-now', nextVisibility === 'hidden');
    };

    if (editBtn) {
        editBtn.addEventListener('click', () => {
            window.location.href = editUrl;
        });
    }

    if (hideBtn) {
        setVisibilityState(shell.dataset.visibility || window.EXERCISE_MENTOR_VISIBILITY || hideBtn.dataset.visibility || 'visible');

        hideBtn.addEventListener('click', async () => {
            const currentVisibility = shell.dataset.visibility || hideBtn.dataset.visibility || 'visible';
            const nextVisibility = currentVisibility === 'hidden' ? 'visible' : 'hidden';
            hideBtn.disabled = true;
            hideBtn.textContent = currentVisibility === 'hidden' ? 'Showing...' : 'Hiding...';

            try {
                const response = await fetch(toggleUrl, {
                    method: 'POST',
                    credentials: 'same-origin',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        exercise_id: exerciseId,
                        id: exerciseId
                    })
                });

                const data = await response.json().catch(() => ({}));
                if (!response.ok || data.success === false) {
                    throw new Error(data.message || 'Unable to hide exercise');
                }

                setVisibilityState(data.visibility || nextVisibility);
            } catch (error) {
                console.error('Hide error:', error);
                setVisibilityState(shell.dataset.visibility || 'visible');
                alert(error.message || 'Failed to hide exercise');
            }
        });
    }
});