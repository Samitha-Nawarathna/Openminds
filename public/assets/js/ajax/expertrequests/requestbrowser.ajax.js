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
    if (!items || items.length === 0) return "";

    let content = "";
    items.forEach(item => {
        // Clean up description length for the UI
        let desc = item.description || "";
        if (desc.length > 50) desc = desc.substring(0, 47) + "...";

        content += `
        <a href="${ROOT}/expertrequest/show?id=${item.id}" class="profile-item no-style-link">
            <div class="left-align">
                <div class="request-info">
                    <span class="subject-text">${item.subject}</span>
                    <span class="desc-text">${desc}</span>
                </div>
            </div>
            <div class="right-align">
                <div class="role-pill status-${item.review}">${item.review}</div>
                <div class="arrow-icon">→</div>
            </div>
        </a>`;
    });
    return content;
}