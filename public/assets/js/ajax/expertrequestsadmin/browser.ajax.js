import { ROOT } from '../../core/config.js';

let max_chars = 60;

/**
 * Communicates with the backend to retrieve filtered requests.
 * Returns an object containing the HTML and a boolean for pagination.
 */
export async function get_content(state)
{
    try {
        let res = await fetch(ROOT + 'expertrequestadmin/filter_requests', {  
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(state)
        });

        if (!res.ok) throw new Error("Network response was not ok");
        
        const response = await res.json();
        const data = response.results;
        let html = '';

        if (!data || data.length === 0) {
            return { html: state.offset === 0 ? `<div class="no-results">No records found.</div>` : '', has_more: false };
        }

        for (let item of data) {
            // Mapping: assuming your DB result has request_id, user_name, description, review, subject
            html += `
                <a href="${ROOT}expertrequestadmin/show?id=${item['request_id']}" class="profile-item no-style-link animate-in">
                    <div class="left-align">
                        <img src="${ROOT}${item["profile_picture_url"] || 'assets/images/profiles/default.png'}" class="profile-picture-xs">
                        <div class="description-container">
                            <span class="user-name-label">
                                ${item["user_name"]} 
                                <span class="subject-tag">${item['subject']}</span>
                            </span>
                            <div class="description">${item["description"].substring(0, max_chars)}...</div>
                        </div>
                    </div>
                    <div class="role-pill ${item['review']}">
                        ${item['review']}
                    </div>
                </a>`;
        }

        return { html, has_more: response.has_more };

    } catch (error) {
        console.error("Fetch error:", error);
        return { html: '<div class="error-msg">Error loading content.</div>', has_more: false };
    }
}