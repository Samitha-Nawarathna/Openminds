import { ROOT } from '../core/config.js';

// Function now takes the full parameter object and an append flag
export async function get_content(send_data, appendMode)
{
    // limit and offset are already included in send_data by profilebrowser.view.js
    
    let res = await fetch(ROOT + 'profilebrowser/api/search_and_filter', {  
        method: 'POST',
        headers: {
          'Content-Type': 'application/json' 
        },
        body: JSON.stringify({
          ...send_data                     
        })
      });

    res = await res.json();
    // return res;
    console.log("API Response:", res);

    let content = "";
    
    if (res.length === 0 && !appendMode) {
        // Display a message if no profiles are found on initial load/search
        content = `<div class="no-results">No profiles found matching your criteria.</div>`;
    }

    for (let i = 0; i < res.length; i++) {
        const item = res[i];
        
        // Determine profile status for styling (e.g., if 'banned', add an opacity class)
        const isBanned = item.status === 'banned';
        const opacityClass = isBanned ? ' opacity-50' : '';
        const rolePillClass = item.role_name === 'expert' ? 'role-pill--expert' : 'role-pill--other'; 

        content +=
        `
            <a href="${ROOT}profileadmin/profile?id=${item.profile_id}" class="profile-item no-style-link${opacityClass}">
                <div class="left-align">
                    <img src="${ROOT}${item.profile_picture}" alt="profile-picture" class="profile-picture-xs">
                    <div class="username-info">
                        <div class="username">${item.username}</div>
                        <div class="subject-info">${item.subject_name || 'No Subject'}</div>
                    </div>
                </div>
                <div class="right-align">
                    <div class="role-pill ${rolePillClass}">${item.role_name}</div>
                    ${isBanned ? '<div class="status-badge status-badge--banned">BANNED</div>' : ''}
                </div>
            </a>    
        `;
    }

    // You would typically handle hiding/showing the load more button here based on res.length < send_data.limit
    
    return content;
}