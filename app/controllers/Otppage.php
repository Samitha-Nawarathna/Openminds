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

        $otp_services = new OtpServices;
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

        $otp_services = new OtpServices;
        $result = $otp_services->verify($user_data);

        if ($result === 1) {
            $view = $otp_services->process_forward($user_data);
            
            $this->view($view);
            show($_SESSION);
            exit;

        }

        

        $message;


        if ($result === 0){
            $message = 'invalid OTP. please try again.';
        } elseif ($result === -1){
            $message = 'OTP expired. please try again.';
        }

        $this->view('otp', [
            'message'=> $message
        ]);
        return 0;

    }
}