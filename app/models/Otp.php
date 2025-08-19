<?php

class Otp
{
    use Model;

    protected $table = 'otp_codes';

    public function find($code, $username)
    {
        $results = $this->first(['code'=>$code, 'username'=>$username]);

        if (!$results) {
            return 0;
        }

        $now = new DateTime();
        $expires_at = new DateTime($results->expires_at);

        if ($expires_at < $now)
        {
            return -1;
        }

        return 1;
    }

}