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

    $subjects = $data["subjects_str"];

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

    .tags-container {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        margin-top: 10px;
        min-height: 35px;
        padding: 5px;
        border-radius: var(--radius-sm);
        border: 1px dashed var(--color-border);
    }

    /* .tag-pill {
        display: inline-flex;
        align-items: center;
        background: var(--color-primary);
        color: white;
        padding: 4px 12px;
        border-radius: 20px;
        font-size: var(--font-size-xs);
        gap: 8px;
    } */

    .tag-pill .remove-tag {
        cursor: pointer;
        font-weight: bold;
        opacity: 0.8;
    }

    .tag-pill .remove-tag:hover {
        opacity: 1;
    }
</style>

<div class="body-container">
    <div class="profile-section">
        <div class="common-details">
            <img src="<?=$profile_picture_path?>" alt="" class="profile-picture">
            <div class="profile-details container">
                <div class="display-name"><?=$display_name?></div>
                <div class="role tile">
                    <?=$role?>
                </div>
                <div class="created-at">
                    Since: <?=$created_at?>
                </div>
            </div>
        </div>
        <div class="expert-details">

            <?php
                if ($subjects && $role == 'Expert')
                {
                    echo '<div class="expert-in">
                    <p class="">Experts in:</p></div>
                    <div class="subjects" style="margin-left:var(--space-md);">';
                    
                    //iterate through subjects and display them as pills
                    foreach ($subjects as $subject) {
                        echo '<div class="tag-pill">'.$subject.'</div>';
                    }
                    echo '</div>';                
                }

            ?>
        </div>
    </div>
    <div class="analysis-section">
        <div class="analysis-wrapper">
            <div class="total-notes container analysis-card">
                <div class="title">
                    Total Notes
                </div>
                <div class="count">
                    <?=$total_notes?>
                </div>                
            </div>
            
            <div class="total-notes container analysis-card">
                <div class="title">
                    Total Questions
                </div>
                <div class="count">
                <?=$total_questions?>
                </div>
            </div>

            <div class="total-exercises container analysis-card">
                <div class="title">
                    Total Exercises
                </div>
                <div class="count">
                    <?=$total_exercises?>
                </div>                
            </div>

            <div class="total-answers container analysis-card">
                <div class="title">
                    Total Answers
                </div>
                <div class="count">
                    <?=$total_answers?>
                </div>                
            </div>

            <div class="total-upvotes container analysis-card">
                <div class="title">
                    Total Upvotes gained
                </div>
                <div class="count">
                    <?=$total_upvotes?>
                </div>                
            </div>

            <div class="total-points container analysis-card">
                <div class="title">
                    Points
                </div>
                <div class="count">
                    <?=$total_points?> pts
                </div>                
            </div>                                                                      
        </div>
    </div>
    <div class="button-section">
        <div class="button-wrapper btn1">
            <button class="btn-change-role button btn-primary">
                Change Role
            </button>
        </div>

        <form action="<?=ROOT?>profileadmin/ban?id=<?=$profile_id?>" method="post" class="button-wrapper btn2" style="display:<?=$display_ban?>">
            <button class="button btn-none btn-ban" type="submit">
                Ban
            </button>
        </form>

        <form action="<?=ROOT?>profileadmin/unban?id=<?=$profile_id?>" method="post" class="button-wrapper btn3" style="display:<?=$display_unban?>">
             <button class="button btn-error btn-unban" type="submit">
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

<div class="popup subject" style='display:none'>
    <div class="content">
        <div class="container">
            <p class='message'>Select Subjects</p>
            <div class="input-group">
                <label for="subject_input" style="color:var(--color-placeholder)">Type a subject and press Enter:</label>
                <input type="text" id="subject_input" placeholder="e.g. Mathematics" autocomplete="off">
                <div class="tags-container" id="subject_tags">
                    <!-- Tags will be injected here -->
                </div>
            </div>
            <div class="btns" style="margin-top: 20px;">
                <button class='button btn-none btn-dismiss'>Back</button>
                <button class='button btn-error btn-confirm-subjects'>Confirm Subjects</button>
            </div>

        </div>
    </div>
    <div class="background"></div>
</div>

<div class="popup ban-reason" style='display:none'>
    <div class="content">
        <div class="container">
            <p class='message'>Reason for Banning</p>
            <div class="input-group">
                <label for="ban_reason_input" style="color:var(--color-placeholder)">Please specify the reason for banning this user:</label>
                <textarea name="reason_for_ban" id="ban_reason_input" style="width: 100%; min-height: 100px; padding: 10px; border-radius: 5px; border: 1px solid var(--color-border); background: transparent; color: var(--color-text);"></textarea>
            </div>
            <div class="btns" style="display:flex; margin-top: 15px;">
                <button class='button btn-none btn-dismiss'>Back</button>
                <button class='button btn-error btn-next-ban'>Next</button>
            </div>
        </div>
    </div>
    <div class="background"></div>
</div>

<?php
    include_once(FOOTER_PATH);
?>