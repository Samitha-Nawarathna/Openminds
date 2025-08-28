import { get_content } from '../ajax/profilebrowser.ajax.js';

let btns = document.querySelectorAll('.tab-btns .button');
let cards = document.querySelectorAll('.content-tab');

let primary = 'btn-primary';
let none = 'btn-none';

btns.forEach(btn => {
  btn.addEventListener('click', () => {
    btns.forEach(el => {
      el.classList.remove(primary);
      el.classList.add(none);
      });
    btn.classList.add(primary);
    btn.classList.remove(none);
  });
});

let index2type = {0: "active", 1: "banned"};

let currentIndex = 0;
let type = index2type[currentIndex];

get_content(type, 0, 10).then(content => {
    cards[currentIndex].innerHTML = content;
});


btns.forEach(btn => {
  btn.addEventListener("click", () => {
    const targetIndex = parseInt(btn.dataset.index);
    if (targetIndex === currentIndex) return;

    const direction = targetIndex > currentIndex ? 1 : -1;

    const currentCard = cards[currentIndex];
    const nextCard = cards[targetIndex];

    nextCard.classList.add("incoming");
    type = index2type[targetIndex];

    get_content(type, 0, 10).then(content => {
        nextCard.innerHTML = content;
    });


    currentCard.classList.remove("active");
    currentCard.classList.add(direction === 1 ? "out-forward" : "out-backward");

    requestAnimationFrame(() => {
      nextCard.classList.remove("incoming");
      nextCard.classList.add("active");


      setTimeout(() => {
        currentCard.classList.remove("out-forward", "out-backward");
      }, 500);
    });

    currentIndex = targetIndex;
  });
});