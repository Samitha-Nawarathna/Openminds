<?php

class Otppage extends Controller
{
    public function index()
    {
        if (!isset($_SESSION['user_data']['type'])) {
            header("Location: ".ROOT."home");
        }
        
        $email = $_SESSION['user_data']['email'];
        $this->view("otp", ['email' => $email]);
    }

    public function generate()
    {
        $user_data = $_SESSION['user_data'];

        $otp_services = new OtpServices;
        $successful = $otp_services->send_otp($user_data);
        // show($successful);

        if ($successful) {
            $this->view('otp', [
                'message'=> 'OTP sent successfully.',
                'email'=> $user_data['email']
            ]);// suggestion: use header
            
        }else{
            $this->view('otp', [
                'message'=> 'Error in OTP generation. please try again.',
                'email'=> $user_data['email']
            ]);// suggestion: use header
        }
    }// compose generate, update table and send here

    public function verify()
    {
        $user_data = $_SESSION['user_data'];//sould be guarded!!!!!!!!!

        $otp_services = new OtpServices;
        $result = $otp_services->verify($user_data);

        if ($result === 1) {
            $view = $otp_services->process_forward($user_data);
            
            $this->view($view);
            show($_SESSION);
            exit;

        }// better to redirect to propper view

        

        $message;


        if ($result === 0){
            $message = 'invalid OTP. please try again.';
        } elseif ($result === -1){
            $message = 'OTP expired. please try again.';
        }

        $this->view('otp', [
            'message'=> $message,
            'email'=> $user_data['email']
        ]);//error hand;ing
        return 0;

    }
}