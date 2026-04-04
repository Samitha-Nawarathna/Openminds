            
               <?php 
               
               $nav_role = $_SESSION['role'] ?? null;

            //    include_once '../app/views/partials/header.view.php';


            //    include "C:\xampp\htdocs\Openminds\public\assets\css\analysis\systemview.view.css";

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
    <!-- <div class="right-side">
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
    </div> -->
</nav>
<!-- <div class="nav-placeholder"></div> -->

<script type="module">
    const ROOT = "<?=ROOT?>";

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
        --rsidebar-width: 350px;
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

    .rsidebar-container {
        position: fixed;
        right: 0;
        top: 0;
        height: 100vh;
        width: var(--rsidebar-width);
        background: white;
        border-left: 1px solid var(--color-border);
        transition: width 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        overflow: hidden;
        z-index: 1000;
    }

    .rsidebar-container.collapsed {
        width: var(--sidebar-collapsed-width);
    }

    .rsidebar-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 1.25rem 1rem;
        border-bottom: 1px solid var(--color-border);
        height: 65px;
    }

    .collapsed .rsidebar-header {
        justify-content: center;
        padding: 1.25rem 0.5rem;
    }

    .rsidebar-logo {
        font-size: 1.25rem;
        font-weight: 700;
        color: var(--color-primary-accent);
        white-space: nowrap;
        opacity: 1;
        transition: opacity 0.2s;
    }

    .collapsed .rsidebar-logo {
        opacity: 0;
        width: 0;
        overflow: hidden;
    }

    .rsidebar-toggle-btn {
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

    .rsidebar-toggle-btn:hover {
        background: var(--color-gray-100);
    }

    .rsidebar-toggle-btn svg {
        width: 20px;
        height: 20px;
        stroke: var(--color-text-dark);
        fill: none;
        stroke-width: 2;
        transition: transform 0.3s;
    }

    .collapsed .rsidebar-toggle-btn svg {
        transform: rotate(180deg);
    }

    .rsidebar-content {
        padding: 1.5rem 1rem;
        overflow-y: auto;
        height: calc(100vh - 65px);
    }

    .rnav-section {
        margin-bottom: 2.5rem;
    }

    .rnav-header {
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

    .collapsed .rnav-header {
        opacity: 0;
        height: 0;
        margin: 0;
        padding: 0;
    }

    /* ---------------------------------- */
    /* Navigation Links */
    /* ---------------------------------- */
    .rnav-link {
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

    .rnav-link:hover {
        background-color: var(--color-gray-100);
        color: var(--color-primary-accent);
    }

    .rnav-link.active {
        background-color: var(--color-blue-50);
        color: var(--color-primary-accent);
        font-weight: 600;
    }

    .rnav-link svg {
        min-width: 20px;
        width: 20px;
        height: 20px;
        stroke: currentColor;
        fill: none;
        stroke-width: 2;
        stroke-linecap: round;
        stroke-linejoin: round;
    }

    .rnav-link-text {
        opacity: 1;
        transition: opacity 0.2s;
    }

    .collapsed .rnav-link {
        justify-content: center;
        padding: 0.75rem;
    }

    .collapsed .rnav-link-text {
        opacity: 0;
        position: absolute;
        pointer-events: none;
    }

    /* Tooltip for collapsed state */
    .collapsed .rnav-link::after {
        content: attr(data-tooltip);
        position: absolute;
        right: 100%;
        margin-right: 0.75rem;
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

    .collapsed .rnav-link:hover::after {
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

    .submenu .rnav-link {
        /* Indent the sub-links */
        padding-left: 3rem; 
        font-size: 0.9rem;
        font-weight: 400;
        color: var(--color-gray-600);
    }
    
    .submenu .rnav-link:hover {
        color: var(--color-text-dark);
    }

    /* Toggled link style (Analysis) */
    .rdropdown-toggle {
        justify-content: flex-start; /* Keep text/icon aligned left in expanded state */
    }

    .rdropdown-icon {
        width: 16px;
        height: 16px;
        margin-left: auto; /* Push icon to the right */
        transform: rotate(0deg);
        transition: transform 0.3s;
    }

    .rdropdown-toggle.open .rdropdown-icon {
        transform: rotate(-180deg);
    }
    
    /* Adjustments for Collapsed State */
    .collapsed .rdropdown-toggle .rdropdown-icon {
        display: none; /* Hide arrow in collapsed state */
    }
    
    .collapsed .submenu {
        display: none; /* Submenu should always be hidden in collapsed main state */
    }
    
    /* ---------------------------------- */
    /* Admin Privileges */
    /* ---------------------------------- */
    .radmin-divider {
        border-top: 1px solid var(--color-border);
        margin: 1.5rem 0.5rem;
        padding-top: 1rem;
        transition: margin 0.3s;
    }

    .collapsed .radmin-divider {
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

    .kpi-card {
        background-color: var(--color-blue-50);
        padding: 1rem;
        margin-bottom: 1rem;
        border: 1px solid var(--color-blue-100);
        border-radius: var(--radius-md);
        transition: all 0.3s ease;
    }
    .kpi-title {
        font-size: 0.75rem;
        color: var(--color-text-light);
        margin-bottom: 0.25rem;
    }
    .kpi-value {
        font-size: 1.5rem;
        font-weight: 700;
        color: var(--color-primary-accent);
        transition: font-size 0.3s ease;
    }
    .kpi-change {
        font-size: 0.75rem;
    }

    .overview-grid {
        padding: 1rem;
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        /* 4 columns for 4 KPIs */
        grid-template-rows: repeat(2, 1fr);
        gap: 1rem;
        transition: all 0.3s ease;
    }

    /* Collapsed state for KPI cards */
    .collapsed .overview-grid {
        grid-template-columns: 1fr;
        padding: 0.5rem;
        gap: 0.5rem;
    }

    .collapsed .kpi-card {
        padding: 0.5rem;
        margin-bottom: 0;
        text-align: center;
    }

    .collapsed .kpi-title,
    .collapsed .kpi-change {
        display: none;
    }

    .collapsed .kpi-value {
        font-size: 1.1rem;
    }

    /* Focus Timer Squares UI */
    .focus-timer-squares {
        display: flex;
        align-items: flex-end;
        gap: 1rem;
        margin-bottom: 1.5rem;
        padding: 0 0.5rem;
        transition: all 0.3s ease;
    }

    .focus-square {
        background-color: var(--color-blue-50);
        border: 1px solid var(--color-blue-100);
        border-radius: var(--radius-md);
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;
        text-align: center;
        padding: 1rem;
        box-shadow: 0 2px 4px rgba(0,0,0,0.02);
        transition: all 0.3s ease;
    }

    /* Big square for Today */
    .focus-square.today {
        width: 120px;
        height: 120px;
        background-color: var(--color-primary-accent);
        color: white;
        border: none;
        box-shadow: 0 4px 6px rgba(37, 99, 235, 0.2);
    }
    
    .focus-square.today .focus-title {
        font-size: 0.8rem;
        color: rgba(255, 255, 255, 0.9);
        font-weight: 500;
        margin-bottom: 0.5rem;
    }

    .focus-square.today .focus-value {
        font-size: 2rem;
        font-weight: 800;
        line-height: 1;
        transition: font-size 0.3s ease;
    }
    
    .focus-square.today .focus-unit {
        font-size: 0.8rem;
        opacity: 0.8;
        margin-top: 0.25rem;
    }

    /* Smaller square for Yesterday */
    .focus-square.yesterday {
        width: 90px;
        height: 90px;
    }
    .focus-square.yesterday .focus-title {
        font-size: 0.7rem;
        color: var(--color-text-light);
        margin-bottom: 0.25rem;
    }

    .focus-square.yesterday .focus-value {
        font-size: 1.25rem;
        font-weight: 700;
        color: var(--color-text-dark);
        transition: font-size 0.3s ease;
    }
    
    .focus-square.yesterday .focus-unit {
        font-size: 0.7rem;
        color: var(--color-text-light);
    }

    /* Smaller square for target */
    .focus-square.target {
        width: 90px;
        height: 90px;
    }
    .focus-square.target .focus-title {
        font-size: 0.7rem;
        color: var(--color-text-light);
        margin-bottom: 0.25rem;
    }

    .focus-square.target .focus-value {
        font-size: 1.25rem;
        font-weight: 700;
        color: var(--color-text-dark);
        transition: font-size 0.3s ease;
    }
    
    .focus-square.target .focus-unit {
        font-size: 0.7rem;
        color: var(--color-text-light);
    }

    /* Collapsed state for Focus Timer Squares */
    .collapsed .focus-timer-squares {
        flex-direction: column;
        align-items: center;
        gap: 0.5rem;
        padding: 0;
    }

    .collapsed .focus-square,
    .collapsed .focus-square.today,
    .collapsed .focus-square.yesterday,
    .collapsed .focus-square.target {
        width: 50px;
        height: 50px;
        padding: 0.5rem;
    }

    .collapsed .focus-square .focus-title,
    .collapsed .focus-square .focus-unit {
        display: none;
    }

    .collapsed .focus-square .focus-value,
    .collapsed .focus-square.today .focus-value,
    .collapsed .focus-square.yesterday .focus-value,
    .collapsed .focus-square.target .focus-value {
        font-size: 1.1rem;
    }
</style>


<aside class="rsidebar-container" id="rsidebar">
    <div class="rsidebar-header">
        <div class="rsidebar-logo">Openminds</div>
        <button class="rsidebar-toggle-btn" id="rtoggleBtn" aria-label="Toggle sidebar">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
                <path d="M15 18l-6-6 6-6"/>
            </svg>
        </button>
    </div>   
            
            <div class="rnav-section">
            <div class="rnav-header"><br>YOUR PROGRESS</div>

            <!-- kpi-cards -->
                    <section class="overview-grid" id="overview-panel">
                <div class="kpi-card">
                    <div class="kpi-title">Total Notes</div>
                    <div class="kpi-value" id="stat-notes">0</div>
                    <div class="kpi-change" id="change-notes"></div>
                </div>
                <div class="kpi-card">
                    <div class="kpi-title">Questions Asked</div>
                    <div class="kpi-value" id="stat-questions">0</div>
                    <div class="kpi-change" id="change-questions"></div>
                </div>
                <div class="kpi-card">
                    <div class="kpi-title">Exercises Attempted</div>
                    <div class="kpi-value" id="stat-exercises">0</div>
                    <div class="kpi-change" id="change-exercises"></div>
                </div>
                <div class="kpi-card">
                    <div class="kpi-title">Consistency</div>
                    <div class="kpi-value" id="stat-consistency">0/0</div>
                    <div class="kpi-change" id="change-consistency"></div>
                </div>
            </div>
            </section>
            
            <div class="rnav-header" style="margin-top: 2rem;">FOCUS TIMER</div>
            <div class="focus-timer-squares">
                <!-- Smaller square for Yesterday -->
                <div class="focus-square yesterday">
                    <div class="focus-title">Yesterday</div>
                    <div class="focus-value" id="stat-focus-yesterday">0</div>
                    <div class="focus-unit">min</div>
                </div>

                <!-- Big square for Today -->
                <div class="focus-square today">
                    <div class="focus-title">Today</div>
                    <div class="focus-value" id="stat-focus-today">0</div>
                    <div class="focus-unit">min</div>
                </div>
                
                
                <!-- Smaller square for target -->
                <div class="focus-square target" onclick="setFocusTarget()" style="cursor: pointer;">
                    <div class="focus-title">Target</div>
                    <div class="focus-value" id="stat-focus-target">0</div>
                    <div class="focus-unit">min</div>
                </div>

                <script>
                function setFocusTarget() {
                    const current = document.getElementById('stat-focus-target').innerText;
                    const val = prompt("Set your daily focus target (minutes):", current);
                    if (val !== null && val.trim() !== "" && !isNaN(val)) {
                        document.getElementById('stat-focus-target').innerText = val;
                        localStorage.setItem('focus_target_minutes', val);
                    }
                }
                document.addEventListener('DOMContentLoaded', () => {
                    const saved = localStorage.getItem('focus_target_minutes');
                    if (saved) document.getElementById('stat-focus-target').innerText = saved;
                });
                </script>
            </div>
            
            
            <?php if ($nav_role === "expert"): ?>
                <div class="radmin-divider">
                    <div class="rnav-header">Admin Privileges</div>
                    <a href="<?=ROOT?>/expertrequestadmin" class="rnav-link" data-tooltip="Expert Requests (Admin)">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
                            <rect width="8" height="4" x="8" y="2" rx="1" ry="1"/>
                            <path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"/>
                            <path d="M12 11h4"/><path d="M12 16h4"/><path d="M8 11h.01"/><path d="M8 16h.01"/></svg>
                        <span class="rnav-link-text">Expert Requests (Admin)</span>
                    </a>
                    <a href="<?=ROOT?>/profileadmin" class="rnav-link" data-tooltip="User Profiles">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
                            <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/>
                            <path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/>
                        </svg>
                        <span class="rnav-link-text">User Profiles</span>
                    </a>
                    <a href="<?=ROOT?>/announcements" class="rnav-link" data-tooltip="Announcements">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path d="M6 8a6 6 0 0 1 12 0c0 7 3 9 3 9H3s3-2 3-9"/><path d="M10.3 21a1.94 1.94 0 0 0 3.4 0"/></svg>
                        <span class="rnav-link-text">Announcements</span>
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</aside>


<script>
    window.updateFocusTimeUI = function() {
        const todayEl = document.getElementById('stat-focus-today');
        const yesterdayEl = document.getElementById('stat-focus-yesterday');
        if(todayEl) todayEl.textContent = (parseInt(localStorage.getItem('focusTime_today')) || 0);
        if(yesterdayEl) yesterdayEl.textContent = (parseInt(localStorage.getItem('focusTime_yesterday')) || 0);
    };
    
    // const targetEl = document.getElementById('stat-focus-target');
    



    document.addEventListener('DOMContentLoaded', () => {
        // Initialize focus timer UI
        window.updateFocusTimeUI();
        window.addEventListener('storage', function(e) {
            if(e.key === 'focusTime_today' || e.key === 'focusTime_yesterday') window.updateFocusTimeUI();
        });

        const rsidebar = document.getElementById('rsidebar');
        const rtoggleBtn = document.getElementById('rtoggleBtn');
        const rdropdownToggles = document.querySelectorAll('.rdropdown-toggle');
        const rnavLinks = document.querySelectorAll('.rnav-link');
        const currentPath = window.location.pathname;
        
        // --- Sidebar Collapse/Expand Logic ---
        
        // Restore saved state on page load
        const savedState = localStorage.getItem('sidebarCollapsed');
        if (savedState === 'true') {
            rsidebar.classList.add('collapsed');
        }

        rtoggleBtn.addEventListener('click', () => {
            rsidebar.classList.toggle('collapsed');
            
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