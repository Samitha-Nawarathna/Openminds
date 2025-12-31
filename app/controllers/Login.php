<?php

class Login extends Controller
{
    public function index()
    {
        $this->view("login");
    }

    public function login()
    {
        $user_data = $_POST;
        $user_data['type'] = 'login';
        $_SESSION['user_data'] = $user_data;// currently use session for keep user data temporary. better idea: use temporary table for that.

        $login_services = new LoginServices;
        $errors = $login_services->validate($user_data);

        if(!empty($errors))
        {
            $errors = implode("\n", $errors);
            unset($_SESSION['user_data']);
            
            $this->view("login", [
                "message" => $errors,
                "username" => $_POST["username"],
                "password" => $_POST["password"],
            ]);

            return;
        }

    
        $login_services = new LoginServices;
        $login_services->set_session($user_data);

        // $notification_services = new NotificationServices;
        // $notification_services->send_notification(0, 'You have successfully logged in.', $_SESSION['user_id']);

        $login_services->unset_user_data();
        
        // $otp_service = new OtpServices;
        // $is_generated = $otp_service->send_otp($_SESSION['user_data']);

        // if (!$is_generated)
        // {
        //     $this->view("login", [
        //             "message" => "Error in OTP generation. Please try again!.",
        //             "username" => $_POST["username"],
        //             "password" => $_POST["password"],
        //         ]
        //     );

        //     return;
        // }

        header("Location: ".ROOT."profile"); // pass responsibility to otp controller
        // $this->view("otp", ['email' => $user_data['email']]); 

    }
}