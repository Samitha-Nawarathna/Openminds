<?php
    $title = "Account Banned";
    $filename = "banned";
    $no_navbar = true;
    $add_back = true;

    include_once(HEADER_PATH);
?>

<div class="body-container">
    <div class="banned-container container">
        <h1 class="heading">Access Restricted</h1>

        <div class="banned-card">
            <p>Your access to Openminds has been suspended by an administrator.</p>
            
            <div class="reason-box">
                <h3>Reason for ban</h3>
                <p class="reason-text"><?= htmlspecialchars($data['reason']) ?></p>
            </div>

            <!-- <p style="font-size: var(--font-size-sm); opacity: 0.8;">If you believe this is a mistake, please contact our support team.</p> -->
        </div>

        <div style="margin-top: 20px;">
            <a href="<?= ROOT ?>login" class="link">Back to Login</a>
        </div>
    </div>
</div>

<?php
    include_once(FOOTER_PATH);
?>
