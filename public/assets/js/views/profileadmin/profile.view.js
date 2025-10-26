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

let btn_change_role_submit = document.querySelector('.btn-changerole');
let btn_ban = document.querySelector('.btn-ban');
let btn_unban = document.querySelector('.btn-unban');
// let btn_change_role = document.querySelector('.btn-changerole');

let confirmation_popup = document.querySelector('.confirmation');

btn_ban.addEventListener('click', function(e) {
    e.preventDefault();

    confirmation_popup.querySelector('.message').innerHTML = 'Are you sure you want to ban this account?';
    confirmation_popup.style.display = 'block';
    let form = confirmation_popup.querySelector('.content form');

    form.setAttribute('action', ROOT + 'profileadmin/ban/');
    // form.submit();

});

btn_unban.addEventListener('click', function(e) {
    e.preventDefault();

    confirmation_popup.querySelector('.message').innerHTML = 'Are you sure you want to unban this account?';
    confirmation_popup.style.display = 'block';
    let form = confirmation_popup.querySelector('.content form');

    form.setAttribute('action', ROOT + 'profileadmin/unban/');
    // form.submit();

});

let role_form = document.querySelector('.role-form');

btn_change_role_submit.addEventListener('click', function(e) {
    e.preventDefault();

    confirmation_popup.querySelector('.message').innerHTML = 'Are you sure you want to change role?';
    confirmation_popup.style.display = 'block';
    // let form = confirmation_popup.querySelector('.content form');

    // form.setAttribute('action', ROOT + 'profileadmin/changerole/');
    //submit only if confirmed
    confirmation_popup.querySelector('.confirmation-btn').addEventListener('click', function(e) {
        e.preventDefault();
        role_form.submit();
    });


    // form.submit();

}
);



// btn_change_role.addEventListener('click', function(e) {
//     e.preventDefault();

//     confirmation_popup.querySelector('.message').innerHTML = 'Are you sure you want to change role?';
//     confirmation_popup.style.display = 'block';
//     let form = confirmation_popup.querySelector('.content form');

//     form.setAttribute('action', ROOT + 'profileadmin/changerole/');
//     // form.submit();

// });
