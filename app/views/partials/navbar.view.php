<div class="nav-trigger">
        ☰
</div>

<nav class="nav-hidden">
    <div class="left-side"><div class="name">Openminds</div></div>

    <div class="middle-side">
        <div class="nav-links">
            <ul>
                <li>Notes</li>
                <li>Q & A</li>
                <li class="dropdown">
                    Exercises
                    <ul class="dropdown-menu">
                        <li>All</li>
                        <li>By You</li>
                        <li>For approval</li>
                    </ul>
                </li>
                <li>Analysis</li>
                <li>Expert Requests</li>
            </ul>
        </div>
    </div>
    <div class="right-side">
    <div class="hamburger">
        ☰
        </div>
        <div class="nav-links">
            <ul>
                <li><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-bell-icon lucide-bell"><path d="M10.268 21a2 2 0 0 0 3.464 0"/><path d="M3.262 15.326A1 1 0 0 0 4 17h16a1 1 0 0 0 .74-1.673C19.41 13.956 18 12.499 18 8A6 6 0 0 0 6 8c0 4.499-1.411 5.956-2.738 7.326"/></svg></li>
                <li class="dropdown">
                    <img class='profile-pic' src="<?=ROOT?>\uploads\0\profile.avif" alt="">
                    <ul class="dropdown-menu">
                        <li>Profile</li>
                        <li>Log out</li>
                    </ul>
                </li>
            </ul>
        </div>
    </div>
</nav>
<!-- <div class="nav-placeholder"></div> -->

<script>
  const hamburger = document.querySelector(".hamburger");
  const navLinks = document.querySelector(".nav-links");

  hamburger.addEventListener("click", () => {
    navLinks.classList.toggle("active");
  });

    const nav_trigger = document.querySelector(".nav-trigger");
    const nav = document.querySelector("nav");

    nav_trigger.addEventListener("mouseover", () => {
        document.querySelector("nav").classList.toggle("nav-hidden");
        nav_trigger.classList.add("nav-trigger-hidden");
        nav_trigger.style.transform = "translateY(-100%)";
    });

    nav.addEventListener("mouseleave", () => {
        setTimeout(() => {
            document.querySelector("nav").classList.add("nav-hidden");
            nav_trigger.classList.remove("nav-trigger-hidden");
            nav_trigger.style.transform = "translateY(-10%)";
        }, 2000);

    });
</script>
