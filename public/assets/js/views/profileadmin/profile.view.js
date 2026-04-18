import { ROOT } from '../../core/config.js';

function close_popup() {

    let btns = document.querySelectorAll('.btn-dismiss');

    btns.forEach(element => {
        element.addEventListener('click', function () {
            element.closest('.popup').style.display = 'none';
            console.log('Popup closed');
        });
    });
}

close_popup();

let btn_change_role = document.querySelector('.btn-change-role');
let change_role_popup = document.querySelector('.roles');

btn_change_role.addEventListener('click', function () {
    change_role_popup.style.display = 'block';
});

let btn_change_role_submit = document.querySelector('.btn-changerole');
let btn_ban = document.querySelector('.btn-ban');
let btn_unban = document.querySelector('.btn-unban');

let confirmation_popup = document.querySelector('.confirmation');
let subject_popup = document.querySelector('.subject');
let role_form = document.querySelector('.role-form');
let subject_form = subject_popup.querySelector('.confirmation-btn'); // Form inside the subject popup

// ----------------------------------------------------
// Existing Ban/Unban Logic
// ----------------------------------------------------

let btn_next_ban = document.querySelector('.btn-next-ban');
let ban_reason_popup = document.querySelector('.popup.ban-reason');

btn_ban.addEventListener('click', function (e) {
    e.preventDefault();
    ban_reason_popup.style.display = 'block';
});

btn_next_ban.addEventListener('click', function (e) {
    e.preventDefault();
    let reason = document.getElementById('ban_reason_input').value;

    if (reason.trim() === '') {
        alert('Please provide a reason for the ban.');
        return;
    }

    ban_reason_popup.style.display = 'none';
    confirmation_popup.style.display = 'block';
    confirmation_popup.querySelector('.message').innerHTML = 'Are you sure you want to ban this account?';

    let form = confirmation_popup.querySelector('.content form');
    form.setAttribute('action', ROOT + 'profileadmin/ban/');

    // Add reason as hidden input
    let hiddenReason = form.querySelector('input[name="reason_for_ban"]');
    if (!hiddenReason) {
        hiddenReason = document.createElement('input');
        hiddenReason.type = 'hidden';
        hiddenReason.name = 'reason_for_ban';
        form.appendChild(hiddenReason);
    }
    hiddenReason.value = reason;
});

btn_unban.addEventListener('click', function (e) {
    e.preventDefault();
    confirmation_popup.querySelector('.message').innerHTML = 'Are you sure you want to unban this account?';
    confirmation_popup.style.display = 'block';
    let form = confirmation_popup.querySelector('.content form');
    form.setAttribute('action', ROOT + 'profileadmin/unban/');
});


// ----------------------------------------------------
// NEW / MODIFIED Change Role Logic
// ----------------------------------------------------

let selectedSubjects = [];
let subjectInput = document.getElementById('subject_input');
let tagsContainer = document.getElementById('subject_tags');
let btnConfirmSubjects = document.querySelector('.btn-confirm-subjects');

// Handle adding tags via Enter key
subjectInput.addEventListener('keydown', function (e) {
    if (e.key === 'Enter') {
        e.preventDefault();
        let value = this.value.trim();
        if (value && !selectedSubjects.includes(value)) {
            selectedSubjects.push(value);
            renderTags();
            this.value = '';
        }
    }
});

function renderTags() {
    tagsContainer.innerHTML = '';
    selectedSubjects.forEach((subject, index) => {
        let tag = document.createElement('div');
        tag.className = 'tag-pill';
        tag.innerHTML = `${subject} <span class="remove-tag" data-index="${index}">&times;</span>`;
        tagsContainer.appendChild(tag);
    });

    // Add event listeners for removal
    document.querySelectorAll('.remove-tag').forEach(span => {
        span.addEventListener('click', function () {
            let index = this.getAttribute('data-index');
            selectedSubjects.splice(index, 1);
            renderTags();
        });
    });
}

// Step 1: Handle initial role change submission attempt
btn_change_role_submit.addEventListener('click', function (e) {
    e.preventDefault();

    let role_input = document.getElementById('role').value;
    console.log('Selected Role:', role_input);

    if (role_input == 3) {
        // Path A: Expert Role Selected (Value 3)
        // Show subject popup instead of immediate confirmation
        subject_popup.style.display = 'block';
        console.log('Subject popup displayed for Expert role.');
        // Hide the initial role change popup
        change_role_popup.style.display = 'none';
    } else {
        // Path B: Non-Expert Role Selected (1, 2, or 4)
        // Go straight to final confirmation
        show_final_confirmation();
    }
});

// Step 2: Handle subject confirmation (for Expert role)
btnConfirmSubjects.addEventListener('click', function (e) {
    e.preventDefault();

    if (selectedSubjects.length === 0) {
        alert('Please add at least one subject.');
        return;
    }

    // Remove any previous subject inputs
    role_form.querySelectorAll('input[name="subjects[]"]').forEach(input => input.remove());

    // Add selected subjects as hidden fields to the main role form
    selectedSubjects.forEach(subject => {
        let hiddenField = document.createElement('input');
        hiddenField.type = 'hidden';
        hiddenField.name = 'subjects[]';
        hiddenField.value = subject;
        role_form.appendChild(hiddenField);
    });

    console.log('Subjects added to form:', selectedSubjects);

    // Hide the subject popup
    subject_popup.style.display = 'none';

    // Show the final confirmation popup
    show_final_confirmation();
});

// Step 3: Handle the final confirmation
function show_final_confirmation() {
    confirmation_popup.querySelector('.message').innerHTML = 'Are you sure you want to change role?';
    confirmation_popup.style.display = 'block';

    // Remove any previous confirmation listeners to prevent multiple submissions
    let old_listener = confirmation_popup.querySelector('.confirmation-btn').cloneNode(true);
    confirmation_popup.querySelector('.confirmation-btn').replaceWith(old_listener);

    // Set new listener to submit the main role form upon final confirmation
    old_listener.addEventListener('click', function (e) {
        e.preventDefault();
        console.log('Final confirmation clicked. Submitting role form.');
        role_form.submit(); // Submit the form with role and subjects
    });
}

// Ensure dismiss buttons also close the subject popup if needed
subject_popup.querySelectorAll('.btn-dismiss').forEach(btn => {
    btn.addEventListener('click', function () {
        // If the user dismisses the subject popup, re-show the roles popup
        change_role_popup.style.display = 'block';
        subject_popup.style.display = 'none';
        // Reset subjects when going back
        selectedSubjects = [];
        renderTags();
    });
});