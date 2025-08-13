<?php
    // if (!isset($_SESSION['user_id'])) {
    //     header("Location: ".ROOT."login");
    //     exit;
    // }

    $title = "Profile";
    $filename = "profile";

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
        <form action="<?=ROOT?>expert_request" method="post" class="button-wrapper btn1">
            <input class="button btn-primary" type="submit" value = "Become an Expert">
        </form>
        <form action="<?=ROOT?>profileupdate" method="post" class="button-wrapper btn2">
            <input class="button btn-none" type="submit" value = "Edit">
        </form>
        <form action="<?=ROOT?>profile/delete" method="post" class="button-wrapper btn3">
            <input class="button btn-error" type="submit" value = "Delete">
        </form>                
    </div>
</div>


<?php
    include_once(FOOTER_PATH);
?>