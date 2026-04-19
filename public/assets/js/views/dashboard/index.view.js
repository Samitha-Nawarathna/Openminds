import { ROOT } from "../../core/config.js"

// =========================================================
// 1. API CONFIGURATION & CORE FETCH FUNCTION
// =========================================================

/**
 * Maps the high-level dashboard component name (used for state/tabs)
 * to the actual backend endpoint URL.
 */
var ENDPOINT_MAP = {
    // Static Endpoints
    'user-summary': ROOT + '/dashboard/api_user_summary',
    'impact-metrics': ROOT + '/dashboard/api_impact_metrics',
    'community-banner': ROOT + '/dashboard/api_community_banner', // NOTE: This will no longer be used for the list
    'notifications': ROOT + '/dashboard/api_notifications',

    // Tab Endpoints (data-tab IDs mapped to API routes)
    'pinned-notes': { 'endpoint': ROOT + '/dashboard/api_pinned_notes', 'viewer_endpoint': ROOT + 'notes/view/' },
    'asked-questions': { 'endpoint': ROOT + '/dashboard/api_asked_questions', 'viewer_endpoint': ROOT + 'question/show/' },
    'created-exercises': { 'endpoint': ROOT + '/dashboard/api_created_exercises', 'viewer_endpoint': ROOT + 'exercises/show/' },
    'answered-exercises': { 'endpoint': ROOT + '/dashboard/api_answered_exercises', 'viewer_endpoint': ROOT + 'exercises/show/' },
    'attempt-exercises': { 'endpoint': ROOT + '/dashboard/api_attempt_exercises', 'viewer_endpoint': ROOT + 'exercises/show/' },
    'expert-requests': { 'endpoint': ROOT + '/dashboard/api_expert_requests', 'viewer_endpoint': ROOT + 'exercises/attempt/' },
    'announcements': { 'endpoint': ROOT + '/dashboard/api_announcements', viewer_endpoint: ROOT + 'announcements/view/' }
};

// ENDPOINT_MAP = Object.fromEntries(
//     Object.entries(ENDPOINT_MAP).map(([k, v]) => [k, ROOT + v])
// );

/**
 * Simulates a real API call using the standard Fetch API.
 * This is the function to replace with any custom client wrapper (e.g., Axios) later.
 * * @param {string} endpoint The full URL path (e.g., /api/dashboard/user-summary?limit=10&offset=0)
 * @returns {Promise<Object>} The parsed JSON response data.
 */
async function fetchApiData(endpoint) {
    try {
        const response = await fetch(endpoint);

        if (!response.ok) {
            // Throw an error for 4xx or 5xx status codes
            throw new Error(`API call failed: ${response.status} ${response.statusText}`);
        }

        return await response.json();
    } catch (error) {
        console.error("Fetch Error:", error);
        // Re-throw the error to be caught by the calling functions (e.g., App.loadUserSummary)
        throw error;
    }
}



function createBadge(badgeData) {
    if (!badgeData) return '';
    return `<span class="badge badge-${badgeData.style}">${badgeData.text}</span>`;
}

function createTags(tags) {
    if (!tags || tags.length === 0) return '';
    return tags.map(tag => `<span class="tag tag-${tag.style}">${tag.text}</span>`).join('');
}

function createMeta(metaList) {
    if (!metaList) return '';
    return metaList.map((m, index) => {
        const br = index < metaList.length - 1 ? '<br>' : '';
        return `<strong>${m.key}:</strong> ${m.value} ${br} `;
    }).join('');
}

function createContentCard(item, url = ROOT + "notes/view") {
    const badgeHtml = createBadge(item.status_badge);
    const tagsHtml = createTags(item.tags);
    const metaHtml = createMeta(item.meta_details);
    console.log("item", item);

    let secondaryHtml = '';
    if (item.secondary_info) {
        secondaryHtml = `<span style="font-size: 0.8rem; font-weight: 600; color: var(--color-text-dark); margin-top: 5px;">${item.secondary_info}</span>`;
    }

    // Special check: Announcements have different layout (block display)
    if (typeof item.id === "string" && item.id.startsWith('an-')) { // Check for 'an-' prefix
        return `
            <div class="content-card" style="display: block;">
            
                <div class="content-title" style="color: var(--color-text-dark);">${item.title}</div>
                <div class="content-meta">${metaHtml}</div>
            </div>
        `;
    }

    // Fix the routing format. Certain modules use path variables while others rely on query parameters.
    let finalHref = `${url}/?id=${item.id}`;
    if (url.includes('announcements/view/') || url.includes('notes/view/')) {
        finalHref = `${url}${item.id}`;
    }

    return `
        <div class="" data-id=${item.id}>
        <a href="${finalHref}" class="content-card no-style-link">
            <div class="content-details">
                <div class="content-title">${item.title}</div>
                <div class="content-meta">${metaHtml}</div>
            </div>
            <div class="content-status-right">
                <div style="margin-bottom: 5px;">${tagsHtml}</div>
                ${badgeHtml}
                ${secondaryHtml}
            </div>
        </a>    
        </div>
    `;
}

function createNotificationItem(notif) {
    return `
        <div class="notification-item">
            <div class="notif-icon" style="background: ${notif.icon_color}"></div>
            <div class="notif-content">
                ${notif.content_html}
                <span class="notif-time">${notif.time_ago}</span>
            </div>
        </div>
    `;
}

// =========================================================
// 3. MAIN APPLICATION LOGIC 
// =========================================================

const App = {
    state: {
        tabs: {}, // Stores pagination state per main tab
        notifications: { offset: 0, limit: 5, endOfResults: false, loading: false },
        communityFeed: { offset: 0, limit: 5, endOfResults: false, loading: false } // Dedicated state for announcements feed
    },

    init() {
        this.loadUserSummary();
        this.loadImpactMetrics();
        this.loadCommunityAnnouncements(true); // Load the paginated list of announcements
        this.loadNotifications(true);
        this.setupTabs();
    },

    // --- Static Data Loaders ---
    async loadUserSummary() {
        try {
            // Updated endpoint
            const data = await fetchApiData(ENDPOINT_MAP['user-summary']);

            // Update Avatar
            document.getElementById('user-avatar').style.backgroundImage = `url('${data.avatar_url}')`;

            // Toggle Start Work Button
            if (data.can_start_work) {
                document.getElementById('btn-start-work').style.display = 'flex';
            }

            // Toggle Expert/Admin Sections
            const expertSection = document.getElementById('expert-section');
            const adminSection = document.getElementById('admin-section');
            const expertTab = document.getElementById('tab-expert-requests');

            if (data.is_expert || data.is_admin) {
                expertSection.style.display = 'block';
                // Only show the tab if it exists in the HTML
                if (expertTab) expertTab.style.display = 'block';
            }

            if (data.is_admin) {
                adminSection.style.display = 'block';
            }

            // Update Welcome Message
            document.getElementById('welcome-message').textContent = `Welcome back, ${data.name}`;

        } catch (e) { console.error("User Summary Error", e); }
    },

    async loadImpactMetrics() {
        try {
            // Updated endpoint
            const data = await fetchApiData(ENDPOINT_MAP['impact-metrics']);

            document.getElementById('stat-answers').textContent = data.answers_shared;
            document.getElementById('stat-answers-change').textContent = data.answers_change;
            document.getElementById('stat-marks').textContent = data.avg_exercise_mark;
            document.getElementById('stat-marks-sub').textContent = data.avg_mark_subtext;

            // Build Heatmap
            const heatmapContainer = document.getElementById('heatmap-container');
            heatmapContainer.innerHTML = '';
            data.consistency_days.forEach(level => {
                const dayDiv = document.createElement('div');
                dayDiv.className = `day-block level-${level}`;
                heatmapContainer.appendChild(dayDiv);
            });

        } catch (e) { console.error("Impact Metrics Error", e); }
    },

    // Refactored to load a paginated list of announcements instead of a single banner
    async loadCommunityAnnouncements(isInitialLoad = false) {
        const state = this.state.communityFeed;
        const endpoint = ENDPOINT_MAP['community-banner']; // Use the paginated endpoint
        const container = document.getElementById('community-banner'); // Use the existing banner container

        // 1. Prepare UI containers inside the banner box
        let listContainer = container.querySelector('.list-container');
        let loader = container.querySelector('.loader');

        if (isInitialLoad) {
            // Clear the existing banner content (title, message) on initial load
            container.innerHTML = '<div class="banner-list-title" style="font-weight: 700; margin-bottom: 10px;">Community Announcements</div>';
            listContainer = document.createElement('div');
            listContainer.className = 'list-container';
            container.appendChild(listContainer);

            loader = document.createElement('div');
            loader.className = 'loader';
            loader.style.cssText = 'text-align: center; padding: 10px; display: none;';
            loader.innerHTML = 'Loading...';
            container.appendChild(loader);

            state.offset = 0;
            state.endOfResults = false;
        }

        if (state.loading || (state.endOfResults && !isInitialLoad)) return;
        state.loading = true;

        // 2. Manage Loaders and Buttons
        loader.style.display = 'block';

        const existingBtn = container.querySelector('.btn-load-more');
        const endOfListMsg = container.querySelector('.end-of-list-msg');

        if (existingBtn) existingBtn.remove();
        if (endOfListMsg) endOfListMsg.remove();


        try {
            const url = `${endpoint}?limit=${state.limit}&offset=${state.offset}`;
            const data = await fetchApiData(url);

            // 3. Render Items
            data.items.forEach(item => {
                // Announcements use createContentCard, not createNotificationItem
                listContainer.insertAdjacentHTML('beforeend', createContentCard(item));
            });

            // 4. Update State
            state.offset += data.items.length;
            state.endOfResults = data.metadata.end_of_results;

            // 5. Render Load More Button if applicable
            if (!state.endOfResults) {
                const btn = document.createElement('button');
                btn.className = 'btn-load-more';
                btn.textContent = 'Load More';
                btn.onclick = () => this.loadCommunityAnnouncements(false);
                container.appendChild(btn);
            } else if (state.offset > 0) {
                const msg = document.createElement('p');
                msg.className = 'end-of-list-msg';
                msg.style.cssText = 'text-align: center; margin-top: 15px; color: var(--color-text-light);';
                msg.textContent = '-- End of Announcements --';
                container.appendChild(msg);
            } else if (state.offset === 0) {
                listContainer.innerHTML = '<div style="padding: 10px; text-align: center; color: var(--color-text-light);">No community announcements available.</div>';
            }

            container.style.display = 'block'; // Ensure the container is visible

        } catch (error) {
            console.error(`Error loading community announcements`, error);
            if (isInitialLoad) {
                container.innerHTML = '<div style="padding:10px; color:red">Failed to load announcements feed.</div>';
            } else {
                container.insertAdjacentHTML('beforeend', `<div style="padding:1rem; color:red">Error loading more announcements.</div>`);
            }
        } finally {
            loader.style.display = 'none';
            state.loading = false;
        }
    },


    // --- Paginated Data Loaders ---

    async loadNotifications(isInitialLoad = false) {
        const state = this.state.notifications;
        const endpoint = ENDPOINT_MAP['notifications'];
        const listContainer = document.getElementById('notifications-list');
        // Assuming notifications-list is inside a container that also holds the loader and button
        const notificationsContainer = listContainer.parentElement;

        if (state.loading || (state.endOfResults && !isInitialLoad)) return;
        state.loading = true;

        // 1. Manage Loaders and Buttons
        let loader = document.getElementById('notifications-loader');
        loader.style.display = 'block';

        const existingBtn = notificationsContainer.querySelector('.btn-load-more');
        const endOfListMsg = notificationsContainer.querySelector('.end-of-list-msg');

        if (existingBtn) existingBtn.remove();
        if (endOfListMsg) endOfListMsg.remove();

        // 2. Clear list and reset state on initial load
        if (isInitialLoad) {
            listContainer.innerHTML = '';
            state.offset = 0;
            state.endOfResults = false;
        }

        try {
            const url = `${endpoint}?limit=${state.limit}&offset=${state.offset}`;
            // Use the real fetch wrapper
            const data = await fetchApiData(url);

            // 3. Render Items
            data.items.forEach(item => {
                listContainer.insertAdjacentHTML('beforeend', createNotificationItem(item));
            });

            // 4. Update State
            state.offset += data.items.length;
            state.endOfResults = data.metadata.end_of_results;

            // 5. Render Load More Button if applicable
            if (!state.endOfResults) {
                const btn = document.createElement('button');
                btn.className = 'btn-load-more';
                btn.textContent = 'Load More';
                // Note: Arrow function binds 'this' correctly for the click handler
                btn.onclick = () => this.loadNotifications(false);
                notificationsContainer.appendChild(btn);
            } else if (state.offset > 0) {
                const msg = document.createElement('p');
                msg.className = 'end-of-list-msg';
                msg.style.cssText = 'text-align: center; margin-top: 15px; color: var(--color-text-light);';
                msg.textContent = '-- End of Notifications --';
                notificationsContainer.appendChild(msg);
            } else if (state.offset === 0) {
                listContainer.innerHTML = '<div style="padding: 10px; text-align: center; color: var(--color-text-light);">No new notifications.</div>';
            }


        } catch (error) {
            console.error(`Error loading notifications`, error);
            if (isInitialLoad) {
                listContainer.innerHTML = '<div style="padding:10px; color:red">Failed to load notifications.</div>';
            } else {
                notificationsContainer.insertAdjacentHTML('beforeend', `<div style="padding:1rem; color:red">Error loading more notifications.</div>`);
            }
        } finally {
            loader.style.display = 'none';
            state.loading = false;
        }
    },

    setupTabs() {
        const tabs = document.querySelectorAll('.tab-controls .tab');
        const contents = document.querySelectorAll('.tab-content');

        tabs.forEach(tab => {
            const tabId = tab.dataset.tab;
            // Initialize State for each tab
            this.state.tabs[tabId] = { offset: 0, limit: 10, endOfResults: false, loading: false };

            tab.addEventListener('click', () => {
                // UI Toggle
                tabs.forEach(t => t.classList.remove('active'));
                contents.forEach(c => c.classList.remove('active'));

                tab.classList.add('active');
                const contentDiv = document.getElementById(tabId);
                contentDiv.classList.add('active');

                // Load Data if it's the first time clicking the tab
                const listContainer = contentDiv.querySelector('.list-container');
                if (this.state.tabs[tabId].offset === 0) { // Check if no data has been loaded yet
                    this.loadTabContent(tabId, true); // true for initial load
                }
            });
        });

        // Trigger first active tab to load its content
        const activeTab = document.querySelector('.tab-controls .tab.active');
        if (activeTab) activeTab.click();
    },

    async loadTabContent(tabId, isInitialLoad = false) {
        const endpoint = ENDPOINT_MAP[tabId]['endpoint'];
        const viewer_endpoint = ENDPOINT_MAP[tabId]['viewer_endpoint'];
        if (!endpoint) {
            console.error(`No API endpoint defined for tab: ${tabId}`);
            return;
        }

        const state = this.state.tabs[tabId];
        const contentDiv = document.getElementById(tabId);
        const listContainer = contentDiv.querySelector('.list-container');

        if (state.loading || (state.endOfResults && !isInitialLoad)) return;
        state.loading = true;

        // Manage Loaders and Buttons
        let loader = contentDiv.querySelector('.loader');
        if (!loader) {
            loader = document.createElement('div');
            loader.className = 'loader';
            loader.innerHTML = 'Loading...';
            contentDiv.appendChild(loader);
        }
        loader.style.display = 'block';

        const existingBtn = contentDiv.querySelector('.btn-load-more');
        if (existingBtn) existingBtn.remove();

        const endOfListMsg = contentDiv.querySelector('.end-of-list-msg-tab');
        if (endOfListMsg) endOfListMsg.remove();

        // Clear list on initial load
        if (isInitialLoad) {
            listContainer.innerHTML = '';
            state.offset = 0; // Reset offset on initial load/re-load
            state.endOfResults = false;
        }

        try {
            const url = `${endpoint}?limit=${state.limit}&offset=${state.offset}`;
            // Use the real fetch wrapper
            const data = await fetchApiData(url,);

            // Render Items
            data.items.forEach(item => {
                listContainer.insertAdjacentHTML('beforeend', createContentCard(item, viewer_endpoint));
            });

            // Update State
            state.offset += data.items.length;
            state.endOfResults = data.metadata.end_of_results;

            // Render Load More Button if applicable
            if (!state.endOfResults) {
                const btn = document.createElement('button');
                btn.className = 'btn-load-more';
                btn.textContent = 'Load More';
                // Note: Arrow function binds 'this' correctly for the click handler
                btn.onclick = () => this.loadTabContent(tabId, false);
                contentDiv.appendChild(btn);
            } else if (state.offset > 0) {
                const msg = document.createElement('p');
                msg.className = 'end-of-list-msg-tab';
                msg.style.cssText = 'text-align: center; margin-top: 15px; color: var(--color-text-light);';
                msg.textContent = '-- End of List --';
                contentDiv.appendChild(msg);
            } else if (state.offset === 0) {
                listContainer.innerHTML = `
                    <div class="null-state">
                        <div class="null-state-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
                            </svg>
                        </div>
                        <div class="null-state-text">No items to display</div>
                    </div>
                `;
            }


        } catch (error) {
            console.error(`Error loading ${tabId}`, error);
            listContainer.insertAdjacentHTML('beforeend', `<div style="padding:1rem; color:red">Error loading content.</div>`);
        } finally {
            loader.style.display = 'none';
            state.loading = false;
        }
    }
};

document.addEventListener('DOMContentLoaded', () => {
    App.init();
});