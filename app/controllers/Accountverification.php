<?php

class Accountverification extends Controller
{
    public function index()
    {
        // $this->guard_verification();

        $this->view('accountverification');
    }

    public function guard_verification()
    {
        if(!isset($_SESSION['verification_for']))
        {
            header("Location: ".ROOT."home");
            exit;
        }
    }

    public function verify()
    {
        $user_data = $_POST;
        $user_data['type'] = 'accountverification';
        $_SESSION['user_data'] = $user_data;

        $login_services = new LoginServices;
        $errors = $login_services->validate($user_data);

        if(!empty($errors))
        {
            $errors = implode("\n", $errors);
            unset($_SESSION['user_data']);
            
            $this->view("accountverification", [
                "message" => $errors,
                "username" => $_POST["username"],
                "password" => $_POST["password"],
            ]);

            return;
        }

        $user_data = $login_services->update_session_user_data();
        
        $otp_service = new OtpServices;
        $is_generated = $otp_service->send_otp($_SESSION['user_data']);

        if (!$is_generated)
        {
            $this->view("accountverification", [
                    "message" => "Error in OTP generation. Please try again!.",
                    "username" => $_POST["username"],
                    "password" => $_POST["password"],
                ]
            );

            return;
        }

        header("Location: ".ROOT."otppage"); // pass responsibility to otp controller
        // $this->view("otp", ['email' => $user_data['email']]); 
    }
}