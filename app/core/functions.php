<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

function show($thing)
{
    echo "<pre>";
    print_r($thing);
    echo "</pre>";
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

// function send_mail($email, $subject, $message)
// {
//     $mail = new PHPMailer(true);
//     try {
//         $mail->isSMTP();
//         $mail->Host       = 'smtp.gmail.com';        // SMTP server
//         $mail->SMTPAuth   = true;
//         $mail->Username   = 'samithanawarathna322@gmail.com';  // Gmail address
//         $mail->Password   = 'yqyw rzsi iklf boyd';     // Use App Password!
//         $mail->SMTPSecure = 'tls';
//         $mail->Port       = 587;

//         $mail->setFrom('samithanawarathna322@gmail.com', 'OpenMinds');
//         $mail->addAddress($email);
//         $mail->Subject = $subject;
//         $mail->Body    = $message;

//         $mail->send();
//     } catch (Exception $e) {
//         echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
//         return 0;
//     }

//     return 1;

// }