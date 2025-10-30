<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

function show($thing)
{
    echo "<pre>";
    print_r($thing);
    echo "</pre>";
    exit;
}

function regenerate_session_id()
{
    if (!isset($_SESSION["regenerated_time"])) {
        session_regenerate_id();
        $_SESSION["regenerated_time"] = time();
    }else{
        $regenerate_time_period = 60*30;

        if (time() - $_SESSION["regenerated_time"] > $regenerate_time_period) {
            session_regenerate_id();
            $_SESSION["regenerated_time"] = time();                
        }
    }
}

function set_message($content, $type)
{
    $_SESSION['message'] = [
        'content' => $content,
        'type' => $type
    ];
}

function generateTagColor($tagName) {
    // 1. Generate a consistent hash from the tag name string
    $hash = crc32($tagName);
    
    // 2. Map the hash to a Hue value (0-359 degrees) for a repeatable color
    $hue = $hash % 360; 
    
    // 3. Define Saturation and Lightness for a light background and dark text
    $saturation = '60%'; // Consistent saturation level
    $lightness_bg = '90%'; // Very light background
    $lightness_text = '25%'; // Dark text for contrast
    
    // Return the colors in HSL format for the 'style' attribute
    return [
        'bg' => "hsl($hue, $saturation, $lightness_bg)",
        'text' => "hsl($hue, $saturation, $lightness_text)"
    ];
}