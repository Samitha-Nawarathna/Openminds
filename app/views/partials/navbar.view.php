<div class="nav-trigger">
        ☰
</div>

<nav class="nav-hidden">
    <div class="left-side"><div class="name">Openminds</div></div>

    <div class="middle-side">
        <div class="nav-links">
            <ul>
                <li><a href="<?=ROOT?>/notes" class="no-style-link">Notes</li>
                <li><a href="<?=ROOT?>/question" class="no-style-link">Q & A</li>
                <li class="dropdown">
                    Exercises
                    <ul class="dropdown-menu">
                        <li><a href="<?=ROOT?>/exercises" class="no-style-link">All</li>
                        <li><a href="<?=ROOT?>/exercises" class="no-style-link">By You</li>
                        <li><a href="<?=ROOT?>/exercises" class="no-style-link">For approval</li>
                    </ul>
                </li>
                <li><a href="<?=ROOT?>/analysis" class="no-style-link">Analysis</li>
                <li><a href="<?=ROOT?>/expertrequest" class="no-style-link">Expert Requests</a></li>
            </ul>
        </div>
    </div>
    <div class="right-side">
    <div class="hamburger">
        ☰
        </div>
        <div class="nav-links">
            <ul>
                <li class="dropdown">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-bell-icon lucide-bell"><path d="M10.268 21a2 2 0 0 0 3.464 0"/><path d="M3.262 15.326A1 1 0 0 0 4 17h16a1 1 0 0 0 .74-1.673C19.41 13.956 18 12.499 18 8A6 6 0 0 0 6 8c0 4.499-1.411 5.956-2.738 7.326"/></svg>
                    <ul class="dropdown-menu" style="width:20vw">
                        <li class="notification-header">Notifications</li>
                        <div class="notifications-container">
                            <li class="notification-item">New comment on your post</li>
                            <li class="notification-item">Your exercise has been approved</li>
                            <li class="notification-item">New follower: JohnDoe</li>
                            <li class="notification-item">Your profile was viewed 10 times today</li>
                            <li class="notification-item">System maintenance scheduled for tonight</li>
                        </div>
                        <li class="notification-footer"><div class="button btn-none">See all notifications</div></li>
                    </ul>
                </li>

                <li class="dropdown">
                    <img class='profile-pic' src="<?=ROOT?>\uploads\0\profile.avif" alt="">
                    <ul class="dropdown-menu">
                        <li><a href="<?=ROOT?>/profile" class="no-style-link">Profile</a></li>
                        <li><a href="<?=ROOT?>/logout" class="no-style-link">Log out</a></li>
                    </ul>
                </li>
            </ul>
        </div>
    </div>
</nav>
<!-- <div class="nav-placeholder"></div> -->

<script type="module">
    const ROOT = "<?=ROOT?>";

    const session_data = <?=json_encode($data['session'])?>;
    console.log(session_data, ROOT);

    let data = {
        receiver_id: session_data.user_id,
    };

    let send_data = {
        "data": data,
        "offset": 0,
        "limit": 10
    }

    let res = await fetch(ROOT + 'ajax/retrive_user_notifications', {  
        method: 'POST',
        headers: {
          'Content-Type': 'application/json' 
        },
        body: JSON.stringify({
          ...send_data                     
        })
        });
    
    res = await res.json();
    console.log(res);

    let notifications_container = document.querySelector(".notifications-container");
    let content = "";

    if (res.length === 0) {
        content = "<li class='notification-item'>No new notifications</li>";
    } else {
        res.forEach(notification => {
            content += `<li class='notification-item'>${notification.content}</li>`;
        });
    }
    notifications_container.innerHTML = content;

    const nav_trigger = document.querySelector(".nav-trigger");
    const nav = document.querySelector("nav");

    let hideTimer = null;

    nav_trigger.addEventListener("mouseover", () => {
        nav.classList.toggle("nav-hidden");
        nav_trigger.classList.add("nav-trigger-hidden");
        nav_trigger.style.transform = "translateY(-100%)";
    });

    nav.addEventListener("mouseleave", () => {
        // start 2s timer to hide nav
        hideTimer = setTimeout(() => {
            nav.classList.add("nav-hidden");
            nav_trigger.classList.remove("nav-trigger-hidden");
            nav_trigger.style.transform = "translateY(-10%)";
            hideTimer = null; // clear reference
        }, 2000);
    });

nav.addEventListener("mouseenter", () => {
    // cancel hiding if user comes back quickly
    if (hideTimer) {
        clearTimeout(hideTimer);
        hideTimer = null;
    }
});

    // const hamburger = document.querySelector(".hamburger");
    // const navLinks = document.querySelector(".nav-links");

    // hamburger.addEventListener("click", () => {
    //     navLinks.classList.toggle("active");
    // });

    //     const nav_trigger = document.querySelector(".nav-trigger");
    //     const nav = document.querySelector("nav");

    //     nav_trigger.addEventListener("mouseover", () => {
    //         document.querySelector("nav").classList.toggle("nav-hidden");
    //         nav_trigger.classList.add("nav-trigger-hidden");
    //         nav_trigger.style.transform = "translateY(-100%)";
    //     });

    //     nav.addEventListener("mouseleave", () => {
    //         setTimeout(() => {
    //             document.querySelector("nav").classList.add("nav-hidden");
    //             nav_trigger.classList.remove("nav-trigger-hidden");
    //             nav_trigger.style.transform = "translateY(-10%)";
    //         }, 2000);

    //     });
</script>
