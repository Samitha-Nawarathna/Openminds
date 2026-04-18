<?php

class ForgotPassword extends Controller
{
    public function index()
    {
        $this->view("forgot_password");
    }

    public function verify_email()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email_or_username = $_POST['email_or_username'];
            $user_model = new User;

            // Try to find user by email or username
            $user = $user_model->first(['email' => $email_or_username]);
            if (!$user) {
                $user = $user_model->first(['username' => $email_or_username]);
            }

            if ($user) {
                $user_data = [
                    'user_id' => $user->id,
                    'username' => $user->username,
                    'email' => $user->email,
                    'type' => 'forgot_password'
                ];

                $_SESSION['user_data'] = $user_data;

                $otp_service = new OtpServices;
                $is_generated = $otp_service->send_otp($user_data);

                if ($is_generated) {
                    header("Location: " . ROOT . "otppage");
                    exit;
                } else {
                    $this->view("forgot_password", ['message' => 'Error in OTP generation. Please try again!.']);
                    return;
                }
            } else {
                $this->view("forgot_password", ['message' => 'User not found with that email or username.']);
                return;
            }
        }
        $this->index();
    }
}
