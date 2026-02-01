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
        <div class="header-section">
            <img class='preview' src="<?=ROOT.$data['image_url']?>" alt="profile picture" >
            <div class="name">by <a href="#"></a><?=$data['display_name']?></div>
        </div>

        <div class="status">
            <div class="approved display-group" style="display:<?=$approved_show?>">Congrad: Your request has been approved!</div>
            <div class="rejected display-group" style="display:<?=$rejected_show?>">Your request has been reject. here is why?</div>
            <div class="pending display-group" style="display:<?=$pending_show?>">Pending for approval.</div>
        </div>

        <div class="display-group">
            <label class="field-label">Subject</label>
            <div class="subject-pill">
                <?= htmlspecialchars($data['subject'] ?? '') ?>
            </div>
        </div>

        <div class="display-group">
            <label class="field-label">Description</label>
            <div class="description-content">
                <?= htmlspecialchars($data['description'] ?? '') ?>
            </div>
        </div>

        <div class="upload-wrapper" style="display: <?=$document_show?>; flex-direction: column; align-items: flex-start; gap: 16px;">
            
            <!-- Main CV -->
            <div class="file-group">
                <label class="field-label" style="margin-bottom: 8px; display: block;">Curriculum Vitae</label>
                <form action="<?=ROOT?>/expertrequest/download" method="get">
                    <input type="hidden" name="request_id" value="<?=$data['id']?>">
                    <button type="submit" class="button btn-none" style=" padding-left: var(--space-sm); color: black; font-weight: 600;">
                        Download CV (PDF)
                    </button>
                </form>
            </div>

            <!-- Supporting Documents -->
            <?php if (!empty($data['supporting_docs'])): ?>
            <div class="file-group">
                <label class="field-label" style="margin-bottom: 8px; display: block;">Supporting Documents</label>
                <div class="file-list" style="display: flex; flex-direction: column; gap: 8px;">
                    <?php foreach($data['supporting_docs'] as $doc): ?>
                    <form action="<?=ROOT?>/expertrequest/download" method="get">
                        <input type="hidden" name="request_id" value="<?=$data['id']?>">
                        <input type="hidden" name="file" value="<?=htmlspecialchars($doc)?>">
                        <button type="submit" class="button btn-none" style="text-align: left; padding-left: var(--space-sm); color:black; font-size: 0.95rem;">
                            attachment: <?=htmlspecialchars($doc)?>
                        </button>
                    </form>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>

        </div>

        <div class="user-actions" style="display: <?=$pending_show?>">
            <form action="<?=ROOT?>/expertrequest/edit" method="get">
                <input type="hidden" name="id" value="<?=$data['id']?>">
                <input type="submit" class="button btn-none btn-edit" value="Edit">
            </form>
            <input type="button" class="button btn-delete-soft" value="Delete">  
        </div>

        <div class="display-group" style="display: <?=$rejected_show?>">
            <label class="field-label" style="color: var(--color-red);">Feedback</label>
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