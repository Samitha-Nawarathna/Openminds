import { ROOT } from '../core/config.js';

export async function get_content(send_data, appendMode)
{
    try {
        let res = await fetch(ROOT + 'profilebrowser/api/search_and_filter', {  
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ ...send_data })
        });

        const data = await res.json();
        
        if (data.length === 0) {
            if (appendMode) return "";
            return `
                <div class="no-results">
                    <i class="fa fa-search"></i>
                    <p>No profiles found matching your criteria.</p>
                </div>`;
        }

        let content = "";
        data.forEach(item => {
            const isBanned = item.status === 'banned';
            const opacityClass = isBanned ? ' opacity-50' : '';
            const roleClass = item.role_name === 'expert' ? 'role-pill--expert' : 'role-pill--other';

            content += `
                <a href="${ROOT}profileadmin/profile?id=${item.profile_id}" class="profile-item no-style-link${opacityClass}">
                    <div class="left-align flex items-center gap-3">
                        <img src="${ROOT}${item.profile_picture || "/uploads/0/profile.avif"}" class="profile-picture-xs rounded-full w-10 h-10 object-cover">
                        <div class="username-info">
                            <div class="username font-bold">${item.username}</div>
                            <div class="text-xs text-gray-500">${item.subject_name || 'General'}</div>
                        </div>
                    </div>
                    <div class="right-align flex items-center gap-2">
                        <div class="role-pill ${roleClass}">${item.role_name}</div>
                        ${isBanned ? '<span class="status-badge status-badge--banned text-red-500 text-xs font-bold">BANNED</span>' : ''}
                    </div>
                </a>`;
        });

        return content;
    } catch (error) {
        console.error("Fetch Error:", error);
        return `<div class="no-results text-red-500">Error loading profiles. Please try again.</div>`;
    }
}