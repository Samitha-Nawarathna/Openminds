<?php

class Register extends Controller
{
    public function index()
    {
        $this->view("register");
    }

    public function register()
    {
        $register_services = new RegisterServices;

        $errors = $register_services->validate($_POST);

        if (!empty($errors)) {
            $errors = implode("\n", $errors);

            $this->view("register", [
                "message" => $errors,
                "username" => $_POST["username"],
                "password" => $_POST["password"],
                "email" => $_POST["email"]
            ]);

            return;
        }

        $_SESSION['user_data'] = $_POST;
        $user_data = $_SESSION['user_data'];
        $_SESSION['user_data']['type'] = 'registration';

        $otp_service = new OtpServices;
        $is_generated = $otp_service->send_otp($_SESSION['user_data']);

        if (!$is_generated)
        {
            $this->view("register", [
                    "message" => "Error in OTP generation. Please try again!.",
                    "username" => $_POST["username"],
                    "password" => $_POST["password"],
                    "email" => $_POST["email"]
                ]
            );

            return;
        }

        header("Location: ".ROOT."otppage");

        // Temporary bypass OTP stage
        // $otp_service = new OtpServices;
        // $otp_service->process_forward($_SESSION['user_data']);

    }
}
