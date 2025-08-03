<?php

class OtpService
{
    private $code;
    private $created_at;
    private $otp;
    private $expire_from = 300;
    private $expires_at;

    public function generate($user_data)
    {
        $this->otp = new Otp;

        $this->code = random_int(100000, 999999);
        $this->expires_at = date("Y-m-d H:i:s", time() + $this->expire_from);
        
        $is_sent = $this->send($user_data['email']);

        if (!$is_sent)
        {
            return 0;
        }

        $this->insert_to_db($user_data);

        return 1;
    }

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

    public function verifiy()
    {

    }
}