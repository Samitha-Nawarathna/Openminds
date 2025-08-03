<?php

class Otppage extends Controller
{
    public function index()
    {
        $this->view("otp");
    }

    public function generate()
    {
        $user_data = $_SESSION['user_data'];

        $otp_service = new OtpService;
        $successful = $otp_service->generate($user_data);
        show($successful);

        if ($successful) {
            $this->view('otp', [
                'message'=> 'OTP sent successfully.'
            ]);
            
        }else{
            $this->view('otp', [
                'message'=> 'Error in OTP generation. please try again.'
            ]);
        }
    }

    public function verify()
    {
        $user_data = $_SESSION['user_data'];

        $code = htmlspecialchars($_POST['otp']);
        $otp = new Otp;

        $result = $otp->find($code, $user_data['username']);

        $message;

        if ($result === 1) {
            $message = 'OTP found!';
            
            $register_services = new RegisterServices;
            $register_services->create_user($user_data);

            $this->view('login');
            exit;

        } elseif ($result === 0){
            $message = 'invalid OTP. please try again.';
        } elseif ($result === -1){
            $message = 'OTP expired. please try again.';
        }

        $this->view('otp', [
            'message'=> $message
        ]);
    }
}