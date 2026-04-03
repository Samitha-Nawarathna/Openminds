import { ROOT } from "../../core/config.js";

document.addEventListener('DOMContentLoaded', () => {
    const shell = document.querySelector('.mentor-shell');
    const editBtn = document.getElementById('edit-btn');
    const hideBtn = document.getElementById('hide-btn');

    if (!shell) {
        return;
    }

    const editUrl = shell.dataset.editUrl || window.EXERCISE_MENTOR_EDIT_URL || `${ROOT}/exercises/edit`;
    const hideUrl = shell.dataset.hideUrl || window.EXERCISE_MENTOR_HIDE_URL || `${ROOT}/exercises/hide`;

    if (editBtn) {
        editBtn.addEventListener('click', () => {
            window.location.href = editUrl;
        });
    }

    if (hideBtn) {
        hideBtn.addEventListener('click', async () => {
            hideBtn.disabled = true;
            hideBtn.textContent = 'Hiding...';

            try {
                const response = await fetch(hideUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({ exercise_id: shell.dataset.exerciseId })
                });

                const data = await response.json();
                if (!response.ok || data.success === false) {
                    throw new Error(data.message || 'Unable to hide exercise');
                }

                hideBtn.textContent = 'Hidden for 5 minutes';
                shell.classList.add('is-hidden-now');
                setTimeout(() => {
                    window.location.reload();
                }, 1200);
            } catch (error) {
                console.error('Hide error:', error);
                hideBtn.disabled = false;
                hideBtn.textContent = 'Hide from Users';
                alert(error.message || 'Failed to hide exercise');
            }
        });
    }
});