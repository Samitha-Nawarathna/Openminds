import { ROOT } from '../../core/config.js';

function close_popup() {

    let btns = document.querySelectorAll('.btn-dismiss'); 

    btns.forEach(element => {
        element.addEventListener('click', function() {
            element.closest('.popup').style.display='none';
            console.log('Popup closed');
        });
    });
}

close_popup();

let btn_change_role = document.querySelector('.btn-change-role');
let change_role_popup = document.querySelector('.roles');

btn_change_role.addEventListener('click', function() {
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
btn_ban.addEventListener('click', function(e) {
    e.preventDefault();
    confirmation_popup.querySelector('.message').innerHTML = 'Are you sure you want to ban this account?';
    confirmation_popup.style.display = 'block';
    let form = confirmation_popup.querySelector('.content form');
    form.setAttribute('action', ROOT + 'profileadmin/ban/');
});

btn_unban.addEventListener('click', function(e) {
    e.preventDefault();
    confirmation_popup.querySelector('.message').innerHTML = 'Are you sure you want to unban this account?';
    confirmation_popup.style.display = 'block';
    let form = confirmation_popup.querySelector('.content form');
    form.setAttribute('action', ROOT + 'profileadmin/unban/');
});


// ----------------------------------------------------
// NEW / MODIFIED Change Role Logic
// ----------------------------------------------------

// Step 1: Handle initial role change submission attempt
btn_change_role_submit.addEventListener('click', function(e) {
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
subject_form.addEventListener('submit', function(e) {
    e.preventDefault();
    
    // Capture the subject input
    let subject_input = subject_popup.querySelector('#subject_input').value;

    if (subject_input.trim() === '') {
        alert('Please enter a subject.');
        return;
    }

    // Add the subject input as a hidden field to the main role form
    let hidden_subject_field = document.createElement('input');
    hidden_subject_field.type = 'hidden';
    hidden_subject_field.name = 'subject';
    hidden_subject_field.value = subject_input;
    role_form.appendChild(hidden_subject_field);

    console.log('Subject added to form:', subject_input);
    
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
    old_listener.addEventListener('click', function(e) {
        e.preventDefault();
        console.log('Final confirmation clicked. Submitting role form.');
        role_form.submit(); // Submit the form with role and subject (if Expert)
    });
}

// Ensure dismiss buttons also close the subject popup if needed
subject_popup.querySelectorAll('.btn-dismiss').forEach(btn => {
    btn.addEventListener('click', function() {
        // If the user dismisses the subject popup, re-show the roles popup
        change_role_popup.style.display = 'block'; 
        subject_popup.style.display = 'none';
    });
});