<?php

if (count($_SESSION['history']) > 1) {
    array_pop($_SESSION['history']); // remove current
    $previous = end($_SESSION['history']);
    header("Location: " . $previous);
    exit;
}

header("Location: /"); // fallback
exit;
