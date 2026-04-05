<!-- <div class="nav-trigger">
        ☰
</div>

<nav class="nav-hidden">
    <div class="left-side"><div class="name">Openminds</div></div>

    <div class="middle-side">
        <div class="nav-links">
            <ul>
                <li><a href="<?=ROOT?>/notes" class="no-style-link">Notes</a></li>
                <li><a href="<?=ROOT?>/question" class="no-style-link">Q & A</a></li>
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
               
               <?php 
               
               $nav_role = $_SESSION['role'] ?? null;

               if ($nav_role && $nav_role === "admin"){
                    echo '<li class="dropdown">
                    Admin previlages
                    <ul class="dropdown-menu">
                        <li><a href="'.ROOT.'/expertrequestadmin" class="no-style-link">Expert Requests</li>
                        <li><a href="'.ROOT.'/profileadmin" class="no-style-link">User Profiles</li>
                    </ul>
                </li>';

                    }?>

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
<div class="nav-placeholder"></div> -->

<script type="module">
    const ROOT = "<?=ROOT?>";

//     const session_data = <?=json_encode($data['session'])?>;
//     console.log(session_data, ROOT);

//     let data = {
//         receiver_id: session_data.user_id,
//     };

//     let send_data = {
//         "data": data,
//         "offset": 0,
//         "limit": 10
//     }

//     let res = await fetch(ROOT + 'ajax/retrive_user_notifications', {  
//         method: 'POST',
//         headers: {
//           'Content-Type': 'application/json' 
//         },
//         body: JSON.stringify({
//           ...send_data                     
//         })
//         });
    
//     res = await res.json();
//     console.log(res);

//     let notifications_container = document.querySelector(".notifications-container");
//     let content = "";

//     if (!notifications_container){
//         console.error("Notifications container not found");
        
//     }

//     if (res.success===false){
//         content = "<li class='notification-item'>Error loading notifications</li>";
//     }
//      else
//      if (res.length === 0) {
//         content = "<li class='notification-item'>No new notifications</li>";
//     } else {
//         res.forEach(notification => {
//             content += `<li class='notification-item'>${notification.content}</li>`;
//         });
//     }
//     notifications_container.innerHTML = content;

//     const nav_trigger = document.querySelector(".nav-trigger");
//     const nav = document.querySelector("nav");

//     let hideTimer = null;

//     nav_trigger.addEventListener("mouseover", () => {
//         nav.classList.toggle("nav-hidden");
//         nav_trigger.classList.add("nav-trigger-hidden");
//         nav_trigger.style.transform = "translateY(-100%)";
//     });

//     nav.addEventListener("mouseleave", () => {
//         // start 2s timer to hide nav
//         hideTimer = setTimeout(() => {
//             nav.classList.add("nav-hidden");
//             nav_trigger.classList.remove("nav-trigger-hidden");
//             nav_trigger.style.transform = "translateY(-10%)";
//             hideTimer = null; // clear reference
//         }, 2000);
//     });

// nav.addEventListener("mouseenter", () => {
//     // cancel hiding if user comes back quickly
//     if (hideTimer) {
//         clearTimeout(hideTimer);
//         hideTimer = null;
//     }
// });

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

<style>
    /* ---------------------------------- */
    /* Root Variables and General Styling */
    /* ---------------------------------- */
    ::-webkit-scrollbar { width: 6px; }
    ::-webkit-scrollbar-track { background: transparent; }
    ::-webkit-scrollbar-thumb { background: var(--color-gray-200); border-radius: 3px; }
    ::-webkit-scrollbar-thumb:hover { background: var(--color-gray-300); }

    :root {
        --color-primary-accent: #2563eb;
        --color-text-dark: #1f2937;
        --color-text-light: #6b7280;
        --color-gray-100: #f3f4f6;
        --color-gray-600: #4b5563;
        --color-blue-50: #eff6ff;
        --color-border: #e5e7eb;
        --radius-md: 0.5rem;
        --sidebar-width: 260px;
        --sidebar-collapsed-width: 70px;
        --space-xl: 2rem;
    }

    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
    }

    body {
        font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
        background: #f9fafb;
    }

    /* ---------------------------------- */
    /* Sidebar Layout and Collapsing */
    /* ---------------------------------- */
    .sidebar-container {
        position: fixed;
        left: 0;
        top: 0;
        height: 100vh;
        width: var(--sidebar-width);
        background: white;
        border-right: 1px solid var(--color-border);
        transition: width 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        overflow: hidden;
        z-index: 1000;
    }

    .sidebar-container.collapsed {
        width: var(--sidebar-collapsed-width);
    }

    .sidebar-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 1.25rem 1rem;
        border-bottom: 1px solid var(--color-border);
        height: 65px;
    }

    .collapsed .sidebar-header {
        justify-content: center;
        padding: 1.25rem 0.5rem;
    }

    .sidebar-logo {
        font-size: 1.25rem;
        font-weight: 700;
        color: var(--color-primary-accent);
        white-space: nowrap;
        opacity: 1;
        transition: opacity 0.2s;
    }

    .collapsed .sidebar-logo {
        opacity: 0;
        width: 0;
        overflow: hidden;
    }

    .toggle-btn {
        background: none;
        border: none;
        cursor: pointer;
        padding: 0.5rem;
        border-radius: var(--radius-md);
        display: flex;
        align-items: center;
        justify-content: center;
        transition: background 0.2s;
        min-width: 32px;
    }

    .toggle-btn:hover {
        background: var(--color-gray-100);
    }

    .toggle-btn svg {
        width: 20px;
        height: 20px;
        stroke: var(--color-text-dark);
        fill: none;
        stroke-width: 2;
        transition: transform 0.3s;
    }

    .collapsed .toggle-btn svg {
        transform: rotate(180deg);
    }

    .sidebar-content {
        padding: 1.5rem 1rem;
        overflow-y: auto;
        height: calc(100vh - 65px - 70px);
    }

    .nav-section {
        margin-bottom: 2.5rem;
    }

    .nav-header {
        font-size: 0.7rem;
        text-transform: uppercase;
        color: var(--color-text-light);
        letter-spacing: 0.12em;
        margin-bottom: 0.75rem;
        font-weight: 700;
        padding-left: 0.75rem;
        opacity: 0.8;
        white-space: nowrap;
        transition: opacity 0.2s;
    }

    .collapsed .nav-header {
        opacity: 0;
        height: 0;
        margin: 0;
        padding: 0;
    }

    /* ---------------------------------- */
    /* Navigation Links */
    /* ---------------------------------- */
    .nav-link {
        display: flex;
        align-items: center;
        gap: 0.85rem;
        padding: 0.75rem 1rem;
        color: var(--color-text-dark);
        text-decoration: none;
        border-radius: var(--radius-md);
        transition: all 0.2s;
        font-size: 0.95rem;
        font-weight: 500;
        margin-bottom: 0.25rem;
        position: relative;
        white-space: nowrap;
        width: 100%; /* Important for submenus */
    }

    .nav-link:hover {
        background-color: var(--color-gray-100);
        color: var(--color-primary-accent);
    }

    .nav-link.active {
        background-color: var(--color-blue-50);
        color: var(--color-primary-accent);
        font-weight: 600;
    }

    .nav-link svg {
        min-width: 20px;
        width: 20px;
        height: 20px;
        stroke: currentColor;
        fill: none;
        stroke-width: 2;
        stroke-linecap: round;
        stroke-linejoin: round;
    }

    .nav-link-text {
        opacity: 1;
        transition: opacity 0.2s;
    }

    .collapsed .nav-link {
        justify-content: center;
        padding: 0.75rem;
    }

    .collapsed .nav-link-text {
        opacity: 0;
        position: absolute;
        pointer-events: none;
    }

    /* Tooltip for collapsed state */
    .collapsed .nav-link::after,
    .collapsed .sidebar-footer::after {
        content: attr(data-tooltip);
        position: absolute;
        left: 100%;
        margin-left: 0.75rem;
        padding: 0.5rem 0.75rem;
        background: var(--color-text-dark);
        color: white;
        border-radius: 0.375rem;
        font-size: 0.875rem;
        white-space: nowrap;
        opacity: 0;
        pointer-events: none;
        transition: opacity 0.2s;
        z-index: 1001;
    }

    .collapsed .nav-link:hover::after,
    .collapsed .sidebar-footer:hover::after {
        opacity: 1;
    }

    /* ---------------------------------- */
    /* Submenu Specific Styling (Analysis Dropdown) */
    /* ---------------------------------- */

    .submenu {
        list-style: none;
        padding: 0;
        margin: 0;
        overflow: hidden;
        /* Use max-height for smooth transition */
        max-height: 0; 
        transition: max-height 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }

    .submenu.open {
        /* Set a value larger than the max height of all sub-links combined */
        max-height: 500px; 
    }

    .submenu .nav-link {
        /* Indent the sub-links */
        padding-left: 3rem; 
        font-size: 0.9rem;
        font-weight: 400;
        color: var(--color-gray-600);
    }
    
    .submenu .nav-link:hover {
        color: var(--color-text-dark);
    }

    /* Toggled link style (Analysis) */
    .dropdown-toggle {
        justify-content: flex-start; /* Keep text/icon aligned left in expanded state */
    }

    .dropdown-icon {
        width: 16px;
        height: 16px;
        margin-left: auto; /* Push icon to the right */
        transform: rotate(0deg);
        transition: transform 0.3s;
    }

    .dropdown-toggle.open .dropdown-icon {
        transform: rotate(-180deg);
    }
    
    /* Adjustments for Collapsed State */
    .collapsed .dropdown-toggle .dropdown-icon {
        display: none; /* Hide arrow in collapsed state */
    }
    
    .collapsed .submenu {
        display: none; /* Submenu should always be hidden in collapsed main state */
    }
    
    /* ---------------------------------- */
    /* Admin Privileges */
    /* ---------------------------------- */
    .admin-divider {
        border-top: 1px solid var(--color-border);
        margin: 1.5rem 0.5rem;
        padding-top: 1rem;
        transition: margin 0.3s;
    }

    .collapsed .admin-divider {
        margin: 0.5rem 0.25rem;
        padding-top: 0.5rem;
    }

    /* ---------------------------------- */
    /* Notifications */
    /* ---------------------------------- */
    .notification-wrapper {
        position: relative;
    }

    .notification-dropdown {
        position: fixed; /* Fixed to viewport to escape sidebar overflow */
        /* Top and Left will be set by JS */
        width: 300px;
        background: white;
        border: 1px solid var(--color-border);
        border-radius: var(--radius-md);
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
        z-index: 1100; /* Higher than sidebar (1000) */
        opacity: 0;
        pointer-events: none;
        transform: translateX(-10px);
        transition: opacity 0.2s, transform 0.2s;
        display: block; /* Always block, visibility controlled by opacity/pointer-events */
    }

    .notification-dropdown.visible {
        opacity: 1;
        pointer-events: auto;
        transform: translateX(0);
    }
    
    /* Remove hover selector since we use JS class now */
    /* .notification-wrapper:hover .notification-dropdown { ... } */

    .notification-header {
        padding: 0.75rem 1rem;
        font-weight: 600;
        border-bottom: 1px solid var(--color-border);
        background: var(--color-gray-100);
        border-radius: var(--radius-md) var(--radius-md) 0 0;
        font-size: 0.9rem;
    }

    .notification-list {
        list-style: none;
        padding: 0;
        margin: 0;
        max-height: 350px;
        overflow-y: auto;
    }

    .notification-item {
        padding: 0.75rem 1rem;
        border-bottom: 1px solid var(--color-border);
        font-size: 0.85rem;
        color: var(--color-text-dark);
    }
    
    .notification-item:last-child {
        border-bottom: none;
    }

    .notification-item.empty {
        text-align: center;
        color: var(--color-text-light);
        padding: 1.5rem;
    }
    
    .notif-date {
        font-size: 0.75rem;
        color: var(--color-text-light);
        margin-top: 0.25rem;
    }

    .notification-footer {
        padding: 0.75rem;
        text-align: center;
        border-top: 1px solid var(--color-border);
        background: var(--color-gray-100);
        border-radius: 0 0 var(--radius-md) var(--radius-md);
    }

    .view-all-btn {
        display: inline-block;
        font-size: 0.85rem;
        color: var(--color-primary-accent);
        text-decoration: none;
        font-weight: 600;
    }
    
    .view-all-btn:hover {
        text-decoration: underline;
    }

    /* Adjust for collapsed sidebar */
    .collapsed .notification-dropdown {
        left: 100%; /* Keep it pushed out */
    }

    /* ---------------------------------- */
    /* Sidebar Footer (Profile Section)   */
    /* ---------------------------------- */
    .sidebar-footer {
        padding: 1rem;
        border-top: 1px solid var(--color-border);
        display: flex;
        align-items: center;
        gap: 0.75rem;
        text-decoration: none;
        color: var(--color-text-dark);
        transition: all 0.2s;
        position: absolute;
        bottom: 0;
        width: 100%;
        background: white;
        height: 70px;
    }
    
    .sidebar-footer:hover {
        background-color: var(--color-gray-100);
    }
    
    .profile-avatar {
        min-width: 36px;
        width: 36px;
        height: 36px;
        border-radius: 50%;
        object-fit: cover;
    }
    
    .profile-info {
        display: flex;
        flex-direction: column;
        overflow: hidden; /* For truncation */
        white-space: nowrap;
        opacity: 1;
        transition: opacity 0.2s;
    }
    
    .profile-name {
        font-size: 0.9rem;
        font-weight: 600;
    }
    
    .profile-role {
        font-size: 0.75rem;
        color: var(--color-text-light);
        text-transform: capitalize;
    }
    
    .collapsed .profile-info {
        opacity: 0;
        width: 0;
        pointer-events: none;
    }
    
    .collapsed .sidebar-footer {
        justify-content: center;
        padding: 1rem 0;
    }
</style>


<aside class="sidebar-container" id="sidebar">
    <div class="sidebar-header">
        <div class="sidebar-logo">Openminds</div>
        <button class="toggle-btn" id="toggleBtn" aria-label="Toggle sidebar">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
                <path d="M15 18l-6-6 6-6"/>
            </svg>
        </button>
    </div>

    <div class="sidebar-content">
        <div class="nav-section">
            <div class="nav-header">Main Menu</div>
            
            <a href="<?=ROOT?>/dashboard" class="nav-link" data-tooltip="Dashboard">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
                <span class="nav-link-text">Dashboard</span>
            </a>
            
            <a href="<?=ROOT?>/notes" class="nav-link" data-tooltip="Notes">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path d="M14.5 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7.5L14.5 2z"/><polyline points="14 2 14 8 20 8"/></svg>
                <span class="nav-link-text">Notes</span>
            </a>
            
            <a href="<?=ROOT?>/question" class="nav-link" data-tooltip="Q & A">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"/><path d="M12 17h.01"/></svg>
                <span class="nav-link-text">Q & A</span>
            </a>
            
            <a href="#" class="nav-link dropdown-toggle" data-tooltip="Analytics" data-dropdown-target="analysis-submenu">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
                    <path d="M22 12h-4l-3 9L9 3l-3 9H2"/>
                </svg>
                <span class="nav-link-text">Analytics</span>
                <svg class="dropdown-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
                    <path d="M6 9l6 6 6-6"/>
                </svg>
            </a>
            <div class="submenu" id="analysis-submenu">
                <a href="<?=ROOT?>/analysis" class="nav-link sub-link" data-tooltip="Overview">
                    <span class="nav-link-text">Overview</span>
                </a>
                <a href="<?=ROOT?>/analysis/reflection" class="nav-link sub-link" data-tooltip="Reflection">
                    <span class="nav-link-text">Reflection</span>
                </a>
                <a href="<?=ROOT?>/analysis/influence" class="nav-link sub-link" data-tooltip="Influence">
                    <span class="nav-link-text">Influence</span>
                </a>
                <a href="<?=ROOT?>/analysis/insights" class="nav-link sub-link" data-tooltip="Insights & Recommendations">
                    <span class="nav-link-text">Insights & Recommendations</span>
                </a>
                <a href="<?=ROOT?>/analysis/systemview" class="nav-link sub-link" data-tooltip="System Overview">
                    <span class="nav-link-text">System Overview</span>
                </a>
            </div>
            <a href="<?=ROOT?>/exercises" class="nav-link" data-tooltip="Exercise">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
                    <path d="M17.596 12.768a2 2 0 1 0 2.829-2.829l-1.768-1.767a2 2 0 0 0 2.828-2.829l-2.828-2.828a2 2 0 0 0-2.829 2.828l-1.767-1.768a2 2 0 1 0-2.829 2.829z"/>
                    <path d="m2.5 21.5 1.4-1.4"/>
                    <path d="m20.1 3.9 1.4-1.4"/>
                    <path d="M5.343 21.485a2 2 0 1 0 2.829-2.828l1.767 1.768a2 2 0 1 0 2.829-2.829l-6.364-6.364a2 2 0 1 0-2.829 2.829l1.768 1.767a2 2 0 0 0-2.828 2.829z"/>
                    <path d="m9.6 14.4 4.8-4.8"/>
                </svg>
                <span class="nav-link-text">Exercise</span>
            </a>
        </div>

        <div class="nav-section">
            <div class="nav-header">User Management</div>
            <a href="<?=ROOT?>/profile" class="nav-link" data-tooltip="Profile">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                <span class="nav-link-text">Profile</span>
            </a>
            
            <div class="nav-item-wrapper notification-wrapper">
                <a href="#" class="nav-link notification-trigger" data-tooltip="Notifications">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path d="M6 8a6 6 0 0 1 12 0c0 7 3 9 3 9H3s3-2 3-9"/><path d="M10.3 21a1.94 1.94 0 0 0 3.4 0"/></svg>
                    <span class="nav-link-text">Notifications</span>
                </a>
                <div class="notification-dropdown">
                    <div class="notification-header">Recent Notifications</div>
                    <ul class="notification-list"></ul>
                    <div class="notification-footer">
                        <a href="<?=ROOT?>/dashboard" class="view-all-btn">View all</a>
                    </div>
                </div>
            </div>

            <a href="<?=ROOT?>/expertrequest" class="nav-link" data-tooltip="Expert Requests (My)">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><circle cx="10" cy="8" r="5"/><path d="M2 21a8 8 0 0 1 14 0"/><path d="m18.5 17.5 4.5 4.5"/></svg>
                <span class="nav-link-text">Expert Requests (My)</span>
            </a>

            <a href="<?=ROOT?>/logout" class="nav-link" data-tooltip="Log out">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" x2="9" y1="12" y2="12"/></svg>
                <span class="nav-link-text">Log out</span>
            </a>

            <?php if ($nav_role === "expert"): ?>
                <div class="admin-divider">
                    <div class="nav-header">Admin Privileges</div>
                    <a href="<?=ROOT?>/expertrequestadmin" class="nav-link" data-tooltip="Expert Requests (Admin)">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
                            <rect width="8" height="4" x="8" y="2" rx="1" ry="1"/>
                            <path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"/>
                            <path d="M12 11h4"/><path d="M12 16h4"/><path d="M8 11h.01"/><path d="M8 16h.01"/></svg>
                        <span class="nav-link-text">Expert Requests (Admin)</span>
                    </a>
                    <a href="<?=ROOT?>/profileadmin" class="nav-link" data-tooltip="User Profiles">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
                            <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/>
                            <path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/>
                        </svg>
                        <span class="nav-link-text">User Profiles</span>
                    </a>
                    <a href="<?=ROOT?>/announcements" class="nav-link" data-tooltip="Announcements">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path d="M6 8a6 6 0 0 1 12 0c0 7 3 9 3 9H3s3-2 3-9"/><path d="M10.3 21a1.94 1.94 0 0 0 3.4 0"/></svg>
                        <span class="nav-link-text">Announcements</span>
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <a href="<?=ROOT?>/profile" class="sidebar-footer" data-tooltip="Profile">
        <?php 
        $nav_user_id = $_SESSION['user_id'] ?? 0;
        $nav_role_display = $_SESSION['role'] ?? 'User';
        $nav_username = $_SESSION['user_data']['username'] ?? $_SESSION['user_name'] ?? 'My Profile'; 
        
        // Try .avif first, fallback to generic avatar
        $profile_img_path = ROOT . "/uploads/" . $nav_user_id . "/profile.avif";
        ?>
        <img src="<?=$profile_img_path?>" 
             onerror="this.onerror=null; this.src='https://ui-avatars.com/api/?name=' + encodeURIComponent('<?= htmlspecialchars($nav_username) ?>') + '&background=random';" 
             alt="Profile" class="profile-avatar">
        <div class="profile-info">
            <span class="profile-name"><?= htmlspecialchars($nav_username) ?></span>
            <span class="profile-role"><?= htmlspecialchars($nav_role_display) ?></span>
        </div>
    </a>
</aside>


<script>
    document.addEventListener('DOMContentLoaded', () => {
        const sidebar = document.getElementById('sidebar');
        const toggleBtn = document.getElementById('toggleBtn');
        const dropdownToggles = document.querySelectorAll('.dropdown-toggle');
        const navLinks = document.querySelectorAll('.nav-link');
        const currentPath = window.location.pathname;
        
        // --- Sidebar Collapse/Expand Logic ---
        
        // Restore saved state on page load
        const savedState = localStorage.getItem('sidebarCollapsed');
        if (savedState === 'true') {
            sidebar.classList.add('collapsed');
        }

        toggleBtn.addEventListener('click', () => {
            sidebar.classList.toggle('collapsed');
            
            // Save state to localStorage
            const isCollapsed = sidebar.classList.contains('collapsed');
            localStorage.setItem('sidebarCollapsed', isCollapsed);
            
            // If collapsing, ensure all submenus are closed (UX clean up)
            if (isCollapsed) {
                 document.querySelectorAll('.submenu.open').forEach(submenu => {
                    submenu.classList.remove('open');
                });
                document.querySelectorAll('.dropdown-toggle.open').forEach(toggle => {
                    toggle.classList.remove('open');
                });
            }
        });
        
        // --- Submenu Collapse/Expand Logic ---
        
        dropdownToggles.forEach(toggle => {
            toggle.addEventListener('click', (e) => {
                // Ignore click if the sidebar is collapsed (let tooltip handle it)
                if (sidebar.classList.contains('collapsed')) return;
                
                e.preventDefault(); 
                
                const targetId = toggle.getAttribute('data-dropdown-target');
                const targetSubmenu = document.getElementById(targetId);
                
                if (targetSubmenu) {
                    const isOpening = !targetSubmenu.classList.contains('open');
                    
                    // Collapse any other open submenus
                    document.querySelectorAll('.submenu.open').forEach(submenu => {
                        if (submenu !== targetSubmenu) {
                            submenu.classList.remove('open');
                        }
                    });
                    
                    // Remove 'open' class from all other toggles
                    document.querySelectorAll('.dropdown-toggle.open').forEach(t => {
                        if (t !== toggle) {
                            t.classList.remove('open');
                        }
                    });

                    // Toggle the state of the clicked submenu
                    if (isOpening) {
                        targetSubmenu.classList.add('open');
                        toggle.classList.add('open');
                    } else {
                        targetSubmenu.classList.remove('open');
                        toggle.classList.remove('open');
                    }
                }
            });
        });
        
        // --- Active Link Logic ---
        
        navLinks.forEach(link => {
            const linkHref = link.href.split('?')[0]; // Remove query params for comparison
            const currentPathStripped = currentPath.replace(/\/$/, ''); // Remove trailing slash
            
            // Check for exact match (e.g., /dashboard)
            if (linkHref === currentPathStripped) {
                link.classList.add('active');
            }
        });
        
        
        // If a sub-link is active, ensure its parent dropdown is open on load
        const activeSubLink = document.querySelector('.submenu .nav-link.active');
        if (activeSubLink) {
            const parentSubmenu = activeSubLink.closest('.submenu');
            const parentToggle = document.querySelector(`.dropdown-toggle[data-dropdown-target="${parentSubmenu.id}"]`);
            
            if (parentSubmenu && parentToggle) {
                parentSubmenu.classList.add('open');
                parentToggle.classList.add('open');
            }
        }
        
        // --- Notification Logic ---
        const notificationTrigger = document.querySelector('.notification-trigger');
        const notificationDropdown = document.querySelector('.notification-dropdown');
        const notificationList = document.querySelector('.notification-list');
        let notificationsLoaded = false;
        let notifHideTimer = null;

        function showDropdown() {
            if (notifHideTimer) {
                clearTimeout(notifHideTimer);
                notifHideTimer = null;
            }

            const rect = notificationTrigger.getBoundingClientRect();
            notificationDropdown.style.top = `${rect.top}px`;
            // Position to the right of the sidebar (trigger width + padding/margin logic)
            // rect.right gives the right edge of the trigger
            notificationDropdown.style.left = `${rect.right + 10}px`; 
            notificationDropdown.classList.add('visible');
        }

        function hideDropdown() {
            notifHideTimer = setTimeout(() => {
                notificationDropdown.classList.remove('visible');
            }, 300); // Small delay to allow moving mouse to dropdown
        }

        if (notificationTrigger && notificationDropdown) {
            // Trigger events
            notificationTrigger.addEventListener('mouseenter', async () => {
                showDropdown();

                if (notificationsLoaded) return;
                
                // Show loading state
                notificationList.innerHTML = '<li class="notification-item empty">Loading...</li>';
                
                try {
                    const res = await fetch('<?=ROOT?>/ajax/retrive_user_notifications', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({
                            data: { receiver_id: <?= $_SESSION['user_id'] ?? 0 ?> },
                            offset: 0,
                            limit: 10
                        })
                    });
                    
                    const data = await res.json();
                    
                    if (Array.isArray(data) && data.length > 0) {
                        let content = '';
                        data.slice(0, 10).forEach(notif => {
                            const text = notif.content || notif.message || "New notification";
                            const date = notif.created_at ? new Date(notif.created_at).toLocaleDateString() : '';
                            content += `
                                <li class="notification-item">
                                    <div class="notif-text">${text}</div>
                                    <div class="notif-date">${date}</div>
                                </li>`;
                        });
                        notificationList.innerHTML = content;
                    } else {
                        notificationList.innerHTML = '<li class="notification-item empty">No new notifications</li>';
                    }
                    
                    notificationsLoaded = true; 
                    
                } catch (error) {
                    console.error("Failed to load notifications", error);
                    notificationList.innerHTML = '<li class="notification-item empty">Error loading notifications</li>';
                }
            });

            notificationTrigger.addEventListener('mouseleave', hideDropdown);

            // Dropdown events (to keep it open when hovered)
            notificationDropdown.addEventListener('mouseenter', () => {
                if (notifHideTimer) clearTimeout(notifHideTimer);
            });

            notificationDropdown.addEventListener('mouseleave', hideDropdown);
        }
    });
</script>