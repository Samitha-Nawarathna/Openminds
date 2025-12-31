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

let btn_delete = document.querySelector('.btn-delete');
console.log(btn_delete);
let confirmation_popup = document.querySelector('.confirmation');

btn_delete.addEventListener('click', function() {
    console.log('Delete button clicked');
    confirmation_popup.querySelector('.message').innerHTML = 'Are you sure you want to delete this request?';
    confirmation_popup.style.display = 'block';
    let form = confirmation_popup.querySelector('.content form');

    form.setAttribute('action', ROOT + '/expertrequest/delete');

});