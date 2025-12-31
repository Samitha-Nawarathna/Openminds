import { ROOT } from '../../core/config.js';

function close_popup() {

    let btns = document.querySelectorAll('.btn-dismiss'); 

    btns.forEach(element => {
        element.addEventListener('click', function() {
            element.closest('.popup').style.display='none';
            console.log('Popup closed');
        });
    });
}// console.log('Expert Requests Admin View JS loaded');

close_popup();

let btn_approve = document.querySelector('.btn-approve');
let btn_reject = document.querySelector('.btn-reject');
let btn_undo = document.querySelector('.btn-undo');

let confirmation_popup = document.querySelector('.confirmation');

btn_approve.addEventListener('click', function() {
    confirmation_popup.querySelector('.message').innerHTML = 'Are you sure you want to approve this request?';
    confirmation_popup.style.display = 'block';
    let form = confirmation_popup.querySelector('.content form');

    form.setAttribute('action', ROOT + '/expertrequestadmin/approve');

});

btn_undo.addEventListener('click', function() {
    confirmation_popup.querySelector('.message').innerHTML = 'Are you sure you want to undo this request?';
    confirmation_popup.style.display = 'block';
    let form = confirmation_popup.querySelector('.content form');

    form.setAttribute('action', ROOT + '/expertrequestadmin/undo_rejection');
});

let feedback_popup = document.querySelector('.feedback');

btn_reject.addEventListener('click', function() {
    // confirmation_popup.querySelector('.message').innerHTML = 'Are you sure you want to approve this request?';
    feedback_popup.style.display = 'block';
    let form = feedback_popup.querySelector('.content form');
    form.querySelector('.input-group textarea').setAttribute('placeholder', 'Please provide a reason for rejecting this request.');
    // console.log(form);

    form.setAttribute('action', ROOT + '/expertrequestadmin/reject');

});