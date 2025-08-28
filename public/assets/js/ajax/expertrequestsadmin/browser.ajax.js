import { ROOT } from '../../core/config.js';

let max_chars = 40;

export async function get_content(review, limit, offset)
{
    let send_data = {};
    let data = {};
    let content = ``;

    data["review"] = review;

    send_data["data"] = data;
    send_data["limit"] = limit;
    send_data["offset"] = offset;

    let res = await fetch(ROOT + 'ajax/retrive_user_expertrequests_admin', {  
        method: 'POST',
        headers: {
          'Content-Type': 'application/json' 
        },
        body: JSON.stringify({
          ...send_data                     
        })
      });

    res = await res.json();
    console.log(res);


    for (let i = 0; i < res.length; i++) {
        const item = res[i];
      
        content +=
        `<div class="card"><a href="${ROOT}/expertrequestadmin/show?id=${item['id']}">
                    <div class="left-align">
                        <img src="`+ROOT+item["profile_picture_url"]+`" alt="profile-picture" class="profile-picture-xs">
                        <div class="desription">`+item["description"].substring(0, max_chars)+`...</div>
                    </div>
                    <div class="right-align">
                        <div class="tile role">`+item["subject"]+`</div>
                    </div>
                </a></div>`;

    }
      
    return content;

}

