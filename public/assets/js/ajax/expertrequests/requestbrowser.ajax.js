import { ROOT } from '../../core/config.js';

/**
 * Fetches a batch of requests based on state
 * @param {string} review - status (pending/approved/rejected)
 * @param {string} subject - text search term
 * @param {number} limit 
 * @param {number} offset 
 */
export async function get_content(review, subject, limit, offset) {
    let send_data = {
        data: {
            review: review,
            subject: subject
        },
        limit: limit,
        offset: offset
    };

    try {
        let res = await fetch(ROOT + '/expertrequest/retrive_user_expertrequests', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(send_data)
        });

        if (!res.ok) throw new Error("Network response was not ok");

        const data = await res.json();
        return generateHTML(data);
    } catch (error) {
        console.error("Fetch error:", error);
        return "";
    }
}

function generateHTML(items) {
    if (!items || items.length === 0) {
        return `
        <div class="empty-state-container" style="text-align: center; padding: 40px 20px;">
            <div class="empty-state-icon" style="margin-bottom: 16px;">
                <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="var(--color-gray-300)" stroke-width="1" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M13 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V9z"></path>
                    <polyline points="13 2 13 9 20 9"></polyline>
                </svg>
            </div>
            <p class="empty-state-text" style="color: var(--color-gray-500); margin-bottom: 24px;">No requests found in this category.</p>
            <a href="${ROOT}/expertrequest/create" class="btn-create" style="display: inline-flex; align-items: center; justify-content: center; padding: 8px 24px; font-weight: 600;">+ Create New Request</a>
        </div>`;
    }

    let content = "";
    items.forEach(item => {
        // Clean up description length for the UI
        let desc = item.description || "";
        if (desc.length > 50) desc = desc.substring(0, 47) + "...";

        content += `
        <a href="${ROOT}/expertrequest/show?id=${item.id}" class="profile-item no-style-link">
            <div class="left-align">
                <div class="request-info">
                    <span class="subject-text" style="font-weight: 700; color: var(--color-gray-900); font-size: 1.05rem;">${item.subject}</span>
                    <span class="desc-text" style="color: var(--color-gray-500); font-size: 0.9rem;">${desc}</span>
                </div>
            </div>
            <div class="right-align">
                <div class="role-pill status-${item.review}">${item.review}</div>
            </div>
        </a>`;
    });
    return content;
}