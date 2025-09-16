<?php  
    $title = "View Expert Request | Openminds";
    $filename = "expertrequestsadmin/view";

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
        <input type="button" class="button btn-none btn-approve" value="Approve">
        <input type="button" class="button btn-error btn-reject" value="Reject">  
        </div>

        <div class="status">
            <div class="approved display-group" style="display:<?=$approved_show?>">request has been approved!</div>
            <div class="rejected display-group" style="display:<?=$rejected_show?>">request has been reject. here is why</div>
            <div class="pending display-group" style="display:<?=$pending_show?>">Pending for approval.</div>

        </div>

        <div class="display-group" style="display: <?=$rejected_show?>">
            <?= htmlspecialchars($data['feedback'] ?? '') ?>
        </div>

        <input type="submit" class="button btn-undo" value="Undo Rejection" style="display: <?=$rejected_show?>">

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

<div class="popup feedback" style='display:none'>
    <div class="content container">
            <p class='message'></p>
            

                <form class='confirmation-btn' action='' method='post'>
                    <div class="input-group">
                        <textarea name="feedback"  id="" cols="30" rows="10"></textarea>
                    </div>
                    <input type="hidden" name="id" value="<?=$data['id']?>">
                    <div class="btns">
                        <button type= 'button' class='button btn-none btn-dismiss' onclick=''>Back</button>
                        <input type='submit' class='button btn-error' value='Send feedback'>
                    </div>
                </form>

        
    </div>
    <div class="background"></div>
</div>

<?php


    include_once "../app/views/partials/footer.view.php";

?>