<?php
    // if (!isset($_SESSION['user_id'])) {
    //     header("Location: ".ROOT."login");
    //     exit;
    // }

    $title = "Profile";
    $filename = "profileadmin/profile";

    $profile_id = $data["id"];
    $id  = $data["id"];

    $profile_picture_path = $data["profile_picture_path"];

    $display_name = $data["display_name"];

    $created_at = $data["created_at"]; 

    $role = $data["role"];

    $total_notes = $data["total_notes"];

    $total_exercises = $data["total_exercises"];

    $total_questions = $data["total_questions"];

    $total_answers = $data["total_answers"];

    $total_upvotes = $data["total_upvotes"];

    $total_points = $data["total_points"];

    $banned = $data["banned"];

    $display_ban = $banned ? "none" : "block";
    $display_unban = $banned ? "block" : "none";

    include_once(HEADER_PATH);

?>

<style>
    .lucide-icon {
        width: 20px;
        height: 20px;
        vertical-align: middle;
        margin-right: 8px;
        stroke: currentColor;
        stroke-width: 2;
        stroke-linecap: round;
        stroke-linejoin: round;
        fill: none;
    }
    .analysis-card .title {
        display: flex;
        align-items: center;
        gap: 5px;
    }
    .button-wrapper form, .button-wrapper button {
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }
    /* Specific adjustment for the role/date section */
    .profile-details div {
        display: flex;
        align-items: center;
        gap: 6px;
    }
</style>

<div class="body-container">
    <div class="profile-section">
        <div class="common-details">
            <img src="<?=$profile_picture_path?>" alt="" class="profile-picture">
            <div class="profile-details container">
                <div class="display-name"><?=$display_name?></div>
                <div class="role tile">
                    <svg xmlns="http://www.w3.org/2000/svg" class="lucide-icon" viewBox="0 0 24 24"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                    <?=$role?>
                </div>
                <div class="created-at">
                    <svg xmlns="http://www.w3.org/2000/svg" class="lucide-icon" viewBox="0 0 24 24"><rect width="18" height="18" x="3" y="4" rx="2" ry="2"/><line x1="16" x2="16" y1="2" y2="6"/><line x1="8" x2="8" y1="2" y2="6"/><line x1="3" x2="21" y1="10" y2="10"/></svg>
                    <?=$created_at?>
                </div>
            </div>
        </div>
        <div class="expert-details">

        </div>
    </div>
    <div class="analysis-section">
        <div class="analysis-wrapper">
            <div class="total-notes container analysis-card">
                <div class="title">
                    <svg xmlns="http://www.w3.org/2000/svg" class="lucide-icon" viewBox="0 0 24 24"><path d="M14.5 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7.5L14.5 2z"/><polyline points="14 2 14 8 20 8"/><line x1="16" x2="8" y1="13" y2="13"/><line x1="16" x2="8" y1="17" y2="17"/><line x1="10" x2="8" y1="9" y2="9"/></svg>
                    Total Notes
                </div>
                <div class="count">
                    <?=$total_notes?>
                </div>                
            </div>
            
            <div class="total-notes container analysis-card">
                <div class="title">
                    <svg xmlns="http://www.w3.org/2000/svg" class="lucide-icon" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"/><path d="M12 17h.01"/></svg>
                    Total Questions
                </div>
                <div class="count">
                <?=$total_questions?>
                </div>
            </div>

            <div class="total-exercises container analysis-card">
                <div class="title">
                    <svg xmlns="http://www.w3.org/2000/svg" class="lucide-icon" viewBox="0 0 24 24"><path d="m12 19-7-7 3-3 7 7Z"/><path d="m18 13-1.5-7.5L2 2l3.5 14.5L13 18l5-5Z"/><path d="m2 2 7.586 7.586"/><circle cx="11" cy="11" r="2"/></svg>
                    Total Exercises
                </div>
                <div class="count">
                    <?=$total_exercises?>
                </div>                
            </div>

            <div class="total-answers container analysis-card">
                <div class="title">
                    <svg xmlns="http://www.w3.org/2000/svg" class="lucide-icon" viewBox="0 0 24 24"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
                    Total Answers
                </div>
                <div class="count">
                    <?=$total_answers?>
                </div>                
            </div>

            <div class="total-upvotes container analysis-card">
                <div class="title">
                    <svg xmlns="http://www.w3.org/2000/svg" class="lucide-icon" viewBox="0 0 24 24"><path d="M7 10v12"/><path d="M15 5.88 14 10h5.83a2 2 0 0 1 1.92 2.56l-2.33 8A2 2 0 0 1 17.5 22H4a2 2 0 0 1-2-2v-8a2 2 0 0 1 2-2h2.76a2 2 0 0 0 1.79-1.11L12 2h0a3.13 3.13 0 0 1 3 3.88Z"/></svg>
                    Total Upvotes gained
                </div>
                <div class="count">
                    <?=$total_upvotes?>
                </div>                
            </div>

            <div class="total-points container analysis-card">
                <div class="title">
                    <svg xmlns="http://www.w3.org/2000/svg" class="lucide-icon" viewBox="0 0 24 24"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
                    Points
                </div>
                <div class="count">
                    <?=$total_upvotes?> pts
                </div>                
            </div>                                                                      
        </div>
    </div>
    <div class="button-section">
        <div class="button-wrapper btn1">
            <button class="btn-change-role button btn-primary">
                <svg xmlns="http://www.w3.org/2000/svg" class="lucide-icon" viewBox="0 0 24 24"><path d="M21 12a9 9 0 0 0-9-9 9.75 9.75 0 0 0-6.74 2.74L3 8"/><path d="M3 3v5h5"/><path d="M3 12a9 9 0 0 0 9 9 9.75 9.75 0 0 0 6.74-2.74L21 16"/><path d="M16 21h5v-5"/></svg>
                Change Role
            </button>
        </div>

        <form action="<?=ROOT?>profileadmin/ban?id=<?=$profile_id?>" method="post" class="button-wrapper btn2" style="display:<?=$display_ban?>">
            <button class="button btn-none btn-ban" type="submit">
                <svg xmlns="http://www.w3.org/2000/svg" class="lucide-icon" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><path d="m4.9 4.9 14.2 14.2"/></svg>
                Ban
            </button>
        </form>

        <form action="<?=ROOT?>profileadmin/unban?id=<?=$profile_id?>" method="post" class="button-wrapper btn3" style="display:<?=$display_unban?>">
             <button class="button btn-error btn-unban" type="submit">
                <svg xmlns="http://www.w3.org/2000/svg" class="lucide-icon" viewBox="0 0 24 24"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10"/><path d="m9 12 2 2 4-4"/></svg>
                Unban
            </button>
        </form>          
    </div>
</div>


<div class="popup roles" style="display:none">
    <div class="content container">
        <p class='message'>Change User Role</p>
        <input type="hidden" name="id" value="<?=$profile_id?>">

        <form action="<?=ROOT?>profileadmin/changerole" method="post" class="role-form">
            <input type="hidden" name="user_id" value="<?=$profile_id?>">

            <select name="new_role" id="role" class="role-select">
                <option value="1" default>Student</option>
                <option value="2">Mentor</option>
                <option value="3">Expert</option>
                <option value="4">Admin</option>                
            </select>
            <div class="btns">
                <button class='button btn-none btn-dismiss' type="button">Back</button>
                <input type='submit' class='button btn-primary btn-changerole' value='Change Role'>
            </div>
        </form>
    </div>

    <div class="background"></div>
</div>

<div class="popup confirmation" style='display:none'>
    <div class="content">
        <div class="container">
            <p class='message'></p>
            <div class="btns" style="display:flex;">
            <button class='button btn-none btn-dismiss'>Back</button>

            <form class='confirmation-btn' action='' method='post'>
                <input type='submit' class='button btn-error' value='Confirm'>
                <input type="hidden" name="user_id" value="<?=$profile_id?>">
            </form>

            </div>
        </div>
    </div>
    <div class="background"></div>
</div>

<div class="popup subject" style='display:block'>
    <div class="content">
        <div class="container">
            <p class='message'>Select a Subject</p>
            <div class="input-group">
            <label for="subject_input" style="color:var(--color-placeholder)">Enter the subject name:</label>

            <input type="text" name="subject" id="subject_input">
            </div>
            <div class="btns" >
                <button class='button btn-none btn-dismiss'>Back</button>

                <form class='confirmation-btn' action='' method='post'>
                    <input type='submit' class='button btn-error' value='Confirm'>
                    <input type="hidden" name="user_id" value="<?=$profile_id?>">
                </form>
            </div>

        </div>
    </div>
    <div class="background"></div>
</div>

<?php
    include_once(FOOTER_PATH);
?>