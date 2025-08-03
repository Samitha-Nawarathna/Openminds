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
        $_SESSION['user_data'] = $user_data;

        $login_services = new LoginServices;
        $errors = $login_services->validate($user_data);

        if(!empty($errors))
        {
            $errors = implode("\n", $errors);

            $this->view("login", [
                "message" => $errors,
                "username" => $_POST["username"],
                "password" => $_POST["password"],
            ]);

            return;
        }

        $otp_service = new OtpServices;
        $is_generated = $otp_service->generate($_SESSION['user_data']);

        if (!$is_generated)
        {
            $this->view("login", [
                    "message" => "Error in OTP generation. Please try again!.",
                    "username" => $_POST["username"],
                    "password" => $_POST["password"],
                ]
            );

            return;
        }

        $this->view("otp"); 

    }
}