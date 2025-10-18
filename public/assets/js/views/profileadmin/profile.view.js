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

let btn_change_role = document.querySelector('.btn-change-role');
let change_role_popup = document.querySelector('.roles');

btn_change_role.addEventListener('click', function() {
    change_role_popup.style.display = 'block';
});
