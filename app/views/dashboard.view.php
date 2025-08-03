<?php

if (!isset($_SESSION['user_id']))
{
    header('Location: '.ROOT.'login');
    exit;
}

?>

<h1>Dashboard</h1>