const window_height = window.innerHeight;

function hex_to_rgb(hex) {
    hex = hex.replace(/^#/, '');
    if (hex.length === 3) {
      hex = hex.split('').map(c => c + c).join('');
    }
    const num = parseInt(hex, 16);
    return [num >> 16, (num >> 8) & 255, num & 255];
  }
  
  function rgb_to_hex([r, g, b]) {
    return "#" + [r, g, b]
      .map(x => x.toString(16).padStart(2, '0'))
      .join('');
  }
  
  function interpolate_three_colors(f1, f2, f3) {
    const total = f1 + f2 + f3;
    f1 /= total; f2 /= total; f3 /= total;
  
    const c1 = hex_to_rgb("#FF7375");
    const c2 = hex_to_rgb("#6375F1");
    const c3 = hex_to_rgb("#08BC8C");
  
    const r = Math.round(c1[0]*f1 + c2[0]*f2 + c3[0]*f3);
    const g = Math.round(c1[1]*f1 + c2[1]*f2 + c3[1]*f3);
    const b = Math.round(c1[2]*f1 + c2[2]*f2 + c3[2]*f3);
  
    return rgb_to_hex([r, g, b]);
  }

  
let hero_icon = document.querySelector('.hero-section .logo');
let hero_section = document.querySelector('.hero-section');

const page_width = window.innerWidth;
const page_height = window.innerHeight;

document.addEventListener('mousemove', (e) => {
    let cursor_x = e.pageX;
    let cursor_y = e.pageY;
    let r = 0;
    let g = 0;
    let p = 1;

    if (cursor_x < page_width*0.25) {
        r = 1;
        g = 0;
        p = 0;
    }

    if (cursor_x > page_width*0.75) {
        r = 0;
        g = 1;
        p = 0;
    } 

    if (cursor_x >= page_width*0.25 && cursor_x <= page_width*0.75) {
        r = (page_width*0.5 - cursor_x)/ (page_width*0.25);
        g = (cursor_x - page_width*0.5)/ (page_width*0.25);

        if (r < 0) {
            p = 1 - g;
            r = 0;
        }else
        {
            p = 1 - r;
            g = 0;
        }
    }
    
    
    let color = interpolate_three_colors(r, p, g);

    hero_icon.style.backgroundColor = color;
    
    console.log(cursor_x/page_width);
})
//   console.log(interpolate_three_colors(0.2, 0.3, 0.5));
  

let feature_section = document.querySelector('.feature-section');
const feature_section_start = feature_section.offsetTop;
const feature_section_height = feature_section.offsetHeight;

let feature_square = document.querySelector('.feature-section .image-section .square');
let feature_leaves = document.querySelectorAll('.feature-section .image-section .icon > *');
let num_movings = 3;

document.addEventListener('scroll', () => {
    let scroll_position = window.scrollY;

    if (scroll_position < feature_section_start + feature_section_height*0.25)
    {
            feature_square.style.transform = `translateY(0) translateX(0)`;
            feature_square.style.backgroundColor = `var(--color-secondary-background)`;
            for (let i = 0; i < num_movings; i++) {
                feature_leaves[i].style.transform = `translateY(${300 - i*100}%) translateX(0)`;
            }
    }

    if (scroll_position > feature_section_start + feature_section_height*0.25 && scroll_position < feature_section_start + feature_section_height*0.5)
    {
        feature_square.style.transform = `rotate(45deg)`;
        feature_square.style.backgroundColor = `var(--color-green)`;
        for (let i = 0; i < num_movings - 1; i++) {
            feature_leaves[i].style.transform = `translateY(${200 - i*100}%) translateX(0)`;
        }
    }

    if (scroll_position > feature_section_start + feature_section_height*0.5 && scroll_position < feature_section_start + feature_section_height*0.75)
    {
        feature_square.style.transform = `rotate(90deg)`;
        feature_square.style.backgroundColor = `var(--color-secondary-background)`;
        for (let i = 0; i < num_movings - 2; i++) {
            feature_leaves[i].style.transform = `translateY(${100 - i*100}%) translateX(0)`;
        }
    }

    if (scroll_position > feature_section_start + feature_section_height*0.75 && scroll_position < feature_section_start + feature_section_height*1)
    {
        feature_square.style.transform = `rotate(135deg)`;
        feature_square.style.backgroundColor = `var(--color-blue)`;
    }
        
    
});



let philosophy_section = document.querySelector('.philosophy-section');

const philosophy_start = philosophy_section.offsetTop - window_height;  
const philosophy_end = philosophy_start + philosophy_section.offsetHeight + window_height;

let philosophy_icon = document.querySelector('.philosophy-section .image .icon');

document.addEventListener('scroll', () => {
    let scrollPosition = window.scrollY;
    let speed = 0.5;
    // console.log(`Scroll Position: ${scrollPosition}, Start: ${start}, End: ${end}, Offset: ${scrollPosition - start}`);
    if (scrollPosition > philosophy_start && scrollPosition < philosophy_end) {
        let offset = (scrollPosition - philosophy_start) * speed - (window_height / 2);
        philosophy_icon.style.transform = `translateX(50%) translateY(${-offset}px)`;
        
    }

});



let questions = document.querySelectorAll('.question');

questions.forEach(element => {
    element.addEventListener('click', function() {
        let answer = element.querySelector('.answer');
        if (answer.classList.contains('active')) {
            answer.classList.remove('active');
            answer.classList.add('inactive');
        }else{
            answer.classList.remove('inactive');
            answer.classList.add('active');    
        }
    }
    );
});
