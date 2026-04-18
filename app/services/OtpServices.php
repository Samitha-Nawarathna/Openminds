<?php

class OtpServices
{
    private $code;
    private $created_at;
    private $otp;
    private $expire_from = 300;
    private $expires_at;


    public function send_otp($user_data)
    {
        $this->generate($user_data);
        
        $is_sent = $this->send($user_data['email']);

        if (!$is_sent)
        {
            return 0;
        }

        $this->insert_to_db($user_data);

        return 1;
    }// use generate, update table,  and then send 

    public function generate($user_data)
    {
        $this->otp = new Otp;

        $this->code = random_int(100000, 999999);
        $this->expires_at = date("Y-m-d H:i:s", time() + $this->expire_from);
        
    }// keep only generation logic

    private function send($email)
    {
        $is_sent = true;

        $subject = "Openminds: Your OTP Code";
        $message = "Your code is: {$this->code}";
        $from = "samithanawarathna322@gmail.com";

        $headers  = 'MIME-Version: 1.0' . "\r\n";
        $headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";        
        $headers .= 'From: '.$from."\r\n".
        'Reply-To: '.$from."\r\n" .
        'X-Mailer: PHP/' . phpversion();

        $is_sent = mail($email, $subject, $message, $headers);

        return $is_sent;
    }

    private function insert_to_db($user_data)
    {
        $this->otp->insert([
            'username'=>$user_data['username'],
            'email'=>$user_data['email'],
            'code'=>$this->code,
            'type'=>$user_data['type'],
            'expires_at'=>$this->expires_at
        ]);
    }

    public function verify($user_data)
    {

        $code = htmlspecialchars($_POST['otp']);//sould be guardad!!!!!!!
        $otp = new Otp;

        $result = $otp->find($code, $user_data['username']);

        return $result;
    }

    public function process_forward($user_data) // rename as redirect_forward
    {
        $type = $user_data['type'];
        // show($user_data);

        switch ($type) {
            case 'login':
                #login
                $login_services = new LoginServices;
                $login_services->set_session($user_data);

                $notification_services = new NotificationServices;
                $notification_services->send_notification(0, 'You have successfully logged in.', $_SESSION['user_id']);

                $login_services->unset_user_data();

    
                header("Location: ".ROOT."dashboard");
                exit;

            case 'accountverification':
                #accountverification
                $verification_services = new AccountVerificationServices;
                $_SESSION['verified_deletion'] = 1;
                $verification_services->process_forward($_SESSION['verification_for']);

                $login_services = new LoginServices;
                $login_services->unset_user_data();
                exit;

            case 'change_password':
                $profile_service = new ProfileServices;

                $result = $profile_service->change_password($user_data);

                if ($result->has_errors()) {
                    $errors = $result->get_errors();
                    $message = implode(", ", $errors);
                    header("Location: ".ROOT."profileupdate/change_password?message=".$message);
                    exit;   
                }

                $notification_services = new NotificationServices;
                $notification_services->send_notification(0, 'You have successfully changed your password!', $user_data['user_id']);


    
                header("Location: ".ROOT."profileupdate?message=You have successfully changed your password!");               
                break;            
            case 'forgot_password':
                $_SESSION['user_id'] = $user_data['user_id'];
                header("Location: ".ROOT."profileupdate/reset_password");
                exit;
            default:
                # registration
                $register_services = new RegisterServices;
                $login_services = new LoginServices;
                
                $register_services->create_user($user_data);
                $login_services->set_session($user_data);
                // $register_services->unset_user_data();
                
                header("Location: ".ROOT."profile");
                exit;
        }
    }
}