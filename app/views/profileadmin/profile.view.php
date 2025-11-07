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

<div class="body-container">
    <div class="profile-section">
        <div class="common-details">
            <img src="<?=$profile_picture_path?>" alt="" class="profile-picture">
            <div class="profile-details container">
                <div class="display-name"><?=$display_name?></div>
                <div class="role tile"><?=$role?></div>
                <div class="created-at"><?=$created_at?></div>
            </div>
            <!-- <div class="expert-in container">
                <p class="">Experts in:</p>
                <div class="subjects">
                    <div class="tile">Physics</div>
                    <div class="tile">Physics</div>
                    <div class="tile">Physics</div>
                    <div class="tile">Physics</div>
                </div>
            </div> -->
        </div>
        <div class="expert-details">

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
                    <?=$total_upvotes?> pts
                </div>                
            </div>                                                
        </div>
    </div>
    <div class="button-section">
        <div class="button-wrapper btn1">
            <button class="btn-change-role button btn-primary">Change Role</button>
        </div>
        <!-- <form action="#" method="post" class="button-wrapper btn2">
            <input class="button btn-primary" type="submit" value = "Change Role">
        </form> -->
        <form action="<?=ROOT?>profileadmin/ban?id=<?=$profile_id?>" method="post" class="button-wrapper btn2" style="display:<?=$display_ban?>">
            <input class="button btn-none btn-ban" type="submit" value = "Ban">
        </form>
        <form action="<?=ROOT?>profileadmin/unban?id=<?=$profile_id?>" method="post" class="button-wrapper btn3" style="display:<?=$display_unban?>">
            <input class="button btn-error btn-unban" type="submit" value = "Unban">
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


<?php
    include_once(FOOTER_PATH);
?>