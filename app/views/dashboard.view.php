<?php

if (!isset($_SESSION['user_id']))
{
    header('Location: '.ROOT.'login');
    exit;
}

include_once("../app/views/partials/header.view.php");

?>

<h1>Dashboard</h1>

<?php

include_once("../app/views/partials/footer.view.php");

?>