<?php  
    $title = "View Expert Request | Openminds";
    $filename = "expertrequests/view";

    include_once "../app/views/partials/header.view.php";

    $review = $data['review'] ?? null;

    $pending_show = "none";
    $approved_show = "none";
    $rejected_show = "none";

    $document_show = "none";

    if ($data['proof_link'] != null)
    {
        $document_show = "flex";
    }

    if ($review === "pending")
    {
        $pending_show = "flex";
    }
    else if ($review === "approved")
    {
        $approved_show = "block";
    }
    else if ($review === "rejected")
    {
        $rejected_show = "block";
    }
?>

<div class="view-wrapper">
    <div class="container request-content">
        <h1>View Request</h1>
        <img class='preview' src="<?=ROOT.$data['image_url']?>" alt="profile picture" >
        <div class="name">by <a href="#"></a><?=$data['display_name']?></div>
        <div class="display-group">
            <?= htmlspecialchars($data['subject'] ?? '') ?>
        </div>
        <div class="display-group">
            <?= htmlspecialchars($data['description'] ?? '') ?>
        </div>

        <div class="upload-wrapper" style="display: <?=$document_show?>">
                    <!-- <div class="input-group"><?=$data['proof_link']?></div> -->
                    <form action="<?=ROOT?>/expertrequests/download?proof_link=<?=$data['proof_link']?>" method="post">
                        <input type="submit" class="button btn-none" value="download document">
                    </form>
                </div>

        <div class="user-actions" style="display: <?=$pending_show?>">
            <form action="<?=ROOT?>/expertrequest/edit" method="get">
                <input type="hidden" name="id" value="<?=$data['id']?>">
                <input type="submit" class="button btn-none btn-edit" value="Edit">
            </form>
        <input type="button" class="button btn-error btn-delete" value="Delete">  
        </div>

        <div class="status">
            <div class="approved display-group" style="display:<?=$approved_show?>">Congrad: Your request has been approved!</div>
            <div class="rejected display-group" style="display:<?=$rejected_show?>">Your request has been reject. here is why?</div>
            <div class="pending display-group" style="display:<?=$pending_show?>">Pending for approval.</div>

        </div>

        <div class="display-group" style="display: <?=$rejected_show?>">
            <?= htmlspecialchars($data['feedback'] ?? '') ?>
        </div>
    </div>
</div>

<div class="popup confirmation" style='display:none'>
    <div class="content">
        <div class="container">
            <p class='message'></p>
            <div class="btns">
            <button class='button btn-none btn-dismiss'>Back</button>

            <form class='confirmation-btn' action='' method='post'>
                <input type="hidden" name="id" value="<?=$data['id']?>">
                <input type='submit' class='button btn-error' value='Confirm'>
            </form>

            </div>
        </div>
    </div>
    <div class="background"></div>
</div>


<?php


    include_once "../app/views/partials/footer.view.php";

?>