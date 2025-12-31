<?php
    $filename = 'announcements/index';
    $title = 'Announcements | Openminds';
    include_once "../app/views/partials/header.view.php";
?>

<div class="main-content-container">
        
    <header class="exercise-browser-header">
        <div class="main-title">Announcements</div>
    </header>
    
    <!-- Filter Bar -->
    <div class="filter-bar">
        <!-- Input field for search -->
        <input type="text" id="announcement-search" placeholder="Search by title or content...">
        <!-- Link to the dedicated Create Page -->
        <a href="<?=ROOT?>/announcements/create" class="btn-create">+ Create</a>
    </div>

    <!-- Tabs for Filtering Status -->
    <div class="tabs-container">
        <button class="tab-button active" data-tab="all" onclick="switchTab('all')">All</button>
        <button class="tab-button" data-tab="active" onclick="switchTab('active')">Active</button>
        <button class="tab-button" data-tab="hidden" onclick="switchTab('hidden')">Hidden</button>
    </div>

    <!-- List Container -->
    <div class="list-container" id="announcements-list">
        <div class="intro">Loading announcements...</div>
    </div>

    <!-- Load More Button -->
    <div class="load-more-container" style="display: none;" id="load-more-container">
        <button id="load-more-btn" class="btn-load-more">Load More</button>
    </div>
</div>

<!-- Simple Confirmation Modal for Delete -->
<div id="delete-modal" class="modal-overlay" style="display: none;">
    <div class="container modal-content" style="max-width: 400px; text-align: center;">
        <h2>Delete Announcement?</h2>
        <p style="color: var(--color-text-muted); margin-bottom: var(--space-md);">This action cannot be undone.</p>
        <div class="btns" style="width: 100%; justify-content: center; display: flex; gap: 10px;">
            <button class="button-secondary" onclick="closeModal()">Cancel</button>
            <button class="button btn-error" id="confirm-delete-btn">Delete</button>
        </div>
    </div>
</div>

<script>
    const API_ROOT = '<?=ROOT?>/announcements/api';
    const LIMIT = 5; // Batch size matches your backend expectations
    
    let allAnnouncements = [];
    let currentTab = 'all';
    let currentOffset = 0;
    let isLoading = false;
    let deleteTargetId = null;

    // 1. Load Data on Init
    document.addEventListener('DOMContentLoaded', () => {
        fetchAnnouncements(true); // true = reset/initial load
    });

    // 2. Fetch Data (Server-Side Pagination & Search)
    async function fetchAnnouncements(reset = false) {
        if (isLoading) return;
        isLoading = true;

        const listContainer = document.getElementById('announcements-list');
        const loadMoreContainer = document.getElementById('load-more-container');
        const loadMoreBtn = document.getElementById('load-more-btn');
        const searchTerm = document.getElementById('announcement-search').value.trim();

        if (reset) {
            currentOffset = 0;
            allAnnouncements = [];
            listContainer.innerHTML = '<div class="intro">Loading...</div>';
            loadMoreContainer.style.display = 'none';
        } else {
            loadMoreBtn.textContent = 'Loading...';
        }

        try {
            // Construct URL with Server-Side Pagination, Tab, and Search params
            let url = `${API_ROOT}/admin/load_all?tab=${currentTab}&limit=${LIMIT}&offset=${currentOffset}`;
            if (searchTerm) {
                // Encode the search term for safe URL transport
                url += `&search=${encodeURIComponent(searchTerm)}`;
            }
            
            const response = await fetch(url);
            const json = await response.json();
            
            if (json.success) {
                if (reset) {
                    allAnnouncements = json.data;
                } else {
                    // Append new data to existing array
                    allAnnouncements = [...allAnnouncements, ...json.data];
                }

                // Render the updated list
                renderList();

                // Logic to show/hide "Load More"
                // If we received fewer items than LIMIT, we've reached the end.
                if (json.data.length < LIMIT) {
                    loadMoreContainer.style.display = 'none';
                } else {
                    loadMoreContainer.style.display = 'block';
                    loadMoreBtn.textContent = 'Load More';
                }

                // Increment offset for next batch
                currentOffset += LIMIT;

            } else {
                if(reset) listContainer.innerHTML = `<div class="error">Failed to load data.</div>`;
            }
        } catch (error) {
            console.error(error);
            if(reset) listContainer.innerHTML = `<div class="error">Network error.</div>`;
        } finally {
            isLoading = false;
        }
    }

    // 3. Render List (Simplified: no client-side search)
    function renderList() {
        const listContainer = document.getElementById('announcements-list');
        
        // The list is already filtered by the server based on the current search term and tab.
        
        if (allAnnouncements.length === 0) {
            listContainer.innerHTML = '<div class="intro">No announcements found.</div>';
            return;
        }

        // Build HTML
        listContainer.innerHTML = allAnnouncements.map(item => `
            <a class="no-style-link" href="<?=ROOT?>/announcements/view/${item.id}">
            <div class="announcement-item ${item.is_active == 0 ? 'opacity-50' : ''}" id="row-${item.id}">
                    <div class="info-group">
                        <span class="announcement-title-list">${escapeHtml(item.title)}</span>
                        <span class="style-pill" data-style="${item.style}">
                            ${item.style.replace('-', ' ')}
                        </span>
                        ${item.is_active == 0 ? '<span class="status-badge">Hidden</span>' : ''}
                    </div>
                    
                    <div class="announcement-actions">
                        <a href="<?=ROOT?>/announcements/edit/${item.id}" class="btn-none no-style-link" style="padding: 5px 10px; font-size: 0.8rem;">Edit</a>
                        
                        <button class="btn-none toggle-status-btn" style="padding: 5px 10px; font-size: 0.8rem;" onclick="toggleStatus(${item.id}, ${item.is_active})">
                            ${item.is_active == 1 ? 'Hide' : 'Unhide'}
                        </button>
                        
                        <button class="btn-error" style="padding: 5px 10px; font-size: 0.8rem; border-radius: var(--radius-sm); background-color: var(--color-red-100); border:1px solid var(--color-error); color:var(--color-error);" onclick="openDeleteModal(${item.id})">
                            Delete
                        </button>
                    </div>
                
            </div>
            </a>
        `).join('');
    }

    // 4. Tab Handling
    function switchTab(tab) {
        if (currentTab === tab) return; // No change
        
        currentTab = tab;
        
        // Update visual tab state
        document.querySelectorAll('.tab-button').forEach(btn => {
            btn.classList.toggle('active', btn.dataset.tab === tab);
        });

        // Reset and Fetch new batch
        fetchAnnouncements(true);
    }

    // 5. Event Listeners (Search input now triggers a full reset/fetch)
    document.getElementById('announcement-search').addEventListener('input', debounce(() => {
        fetchAnnouncements(true);
    }, 300));

    document.getElementById('load-more-btn').addEventListener('click', () => {
        fetchAnnouncements(false); // false = append
    });

    // Debounce utility to prevent rapid API calls while typing
    function debounce(func, delay) {
        let timeout;
        return function(...args) {
            clearTimeout(timeout);
            timeout = setTimeout(() => func.apply(this, args), delay);
        };
    }

    // 6. Action Handlers (Hide/Delete)
    async function toggleStatus(id, currentStatus) {
        const endpoint = currentStatus == 1 ? 'hide' : 'unhide';
        try {
            const res = await fetch(`${API_ROOT}/${endpoint}/${id}`, { method: 'POST' });
            const json = await res.json();
            if (json.success) {
                // Find and update status in local data
                const index = allAnnouncements.findIndex(a => a.id == id);
                if (index !== -1) {
                    allAnnouncements[index].is_active = currentStatus == 1 ? 0 : 1;
                }
                
                // Since the active/hidden filter is now server-side, 
                // the safest action is to refetch the current tab/search state from the server.
                // This ensures the list stays consistent with the server's current filter settings.
                fetchAnnouncements(true); 
            }
        } catch (e) {
            // Using console.error instead of alert
            console.error("Action failed during toggle status:", e);
        }
    }

    function openDeleteModal(id) {
        deleteTargetId = id;
        document.getElementById('delete-modal').style.display = 'flex';
    }

    function closeModal() {
        document.getElementById('delete-modal').style.display = 'none';
        deleteTargetId = null;
    }

    document.getElementById('confirm-delete-btn').addEventListener('click', async () => {
        if (!deleteTargetId) return;
        
        try {
            const res = await fetch(`${API_ROOT}/admin/delete/${deleteTargetId}`, { method: 'POST' });
            const json = await res.json();
            
            if (json.success) {
                // Remove from array and re-render
                allAnnouncements = allAnnouncements.filter(a => a.id != deleteTargetId);
                renderList();
                closeModal();
                // If we are showing 'All' and just deleted an item, the server might now return 
                // a new item to fill the slot. Trigger a refetch to maintain the limit integrity.
                if (allAnnouncements.length % LIMIT === 0) {
                     fetchAnnouncements(true); // Refetch the current state
                }

            }
        } catch (e) {
            // Using console.error instead of alert
            console.error("Delete failed:", e);
        }
    });

    // Utility
    function escapeHtml(text) {
        if (!text) return '';
        return text.replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;");
    }
</script>

<style>
    /* Inline styles for modal & Load More */
    .modal-overlay {
        position: fixed; top: 0; left: 0; width: 100%; height: 100%;
        background: rgba(0,0,0,0.5); z-index: 1000;
        display: flex; justify-content: center; align-items: center;
    }
    .opacity-50 { opacity: 0.6; }
    .info-group { display: flex; align-items: center; gap: 10px; }
    .status-badge { font-size: 0.7rem; background: #eee; padding: 2px 6px; border-radius: 4px; }
    
    /* Load More Styles (similar to browser.view.php) */
    .load-more-container {
        text-align: center;
        margin: 20px 0 50px 0;
    }

    .btn-load-more {
        padding: 10px 30px;
        border: 1px solid var(--color-primary);
        border-radius: 8px;
        background-color: white;
        color: var(--color-primary);
        cursor: pointer;
        font-size: 1em;
        transition: background-color 0.2s;
    }
    .btn-load-more:hover {
        background-color: var(--color-primary);
        color: white;
    }
</style>

<?php include_once "../app/views/partials/footer.view.php"; ?>