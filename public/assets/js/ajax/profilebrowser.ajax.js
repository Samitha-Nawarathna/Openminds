import { ROOT } from '../core/config.js';

export async function get_content(type, limit, offset)
{
    let send_data = {};
    let data = {};
    let content = ``;

    if (type === "banned")
    {
        data["banned"] = 1;
    }else
    {
        data["banned"] = 0;
    }

    send_data["data"] = data;
    send_data["limit"] = limit;
    send_data["offset"] = offset;

    let res = await fetch(ROOT + 'ajax/retrive_user_profiles', {  
        method: 'POST',
        headers: {
          'Content-Type': 'application/json' 
        },
        body: JSON.stringify({
          ...send_data                     
        })
      });

    res = await res.json();


    for (let i = 0; i < res.length; i++) {
        const item = res[i];
        content +=
        `<div class="card">
                    <div class="left-align">
                        <img src="`+ROOT+item["profile_picture"]+`" alt="profile-picture" class="profile-picture-xs">
                        <div class="username">`+item["username"]+`</div>
                    </div>
                    <div class="right-align">
                        <div class="tile role">`+item["role"]+`</div>
                    </div>
                </div>`;

    }
      
    return content;

}

