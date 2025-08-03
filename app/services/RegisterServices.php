<?php

class RegisterServices
{

    public function create_user($data)
    {
        $user = new User();
        $user->insert([
            'username' => $data['username'],
            'email' => $data['email'],
            'password' => password_hash($data['password'], PASSWORD_DEFAULT)
        ]);
    }

}