<?php

class LoginServices
{
    public function validate($user_data)
    {
        $errors = [];

        foreach (array_keys($_POST) as $field) {
            if (empty($user_data[$field])) {
                $errors[] = "fill all the fields!";
                return $errors;
            }
        }

        $_SESSION['user_data']['is_email'] = 1;

        if (!filter_var($user_data['username'], FILTER_VALIDATE_EMAIL)) {
            if ($this->isUsernameUnique($user_data['username'])) {
                $errors[] = "Invalid credentials.";   
            }else
            {
                if(!$this->is_password_match_username($user_data['username'], $user_data['password'], 0))
                {
                    $errors[] = "Invalid credentials.";
                }
            }

            $_SESSION['user_data']['is_email'] = 0;

        }elseif ($this->isEmailUnique($user_data['username'])) {
            $errors[] = "Invalid credentials.";


        }elseif (!$this->is_password_match_email($user_data['username'], $user_data['password'], 1))
        {
            $errors[] = "Invalid credentials.";
        }

        return $errors;
    }

    public function update_session_user_data()
    {
        $user = new User;

        if($_SESSION['user_data']['is_email'])
        {
            $_SESSION['user_data']['email'] = $_SESSION['user_data']['username'];
            $result = $user->first(['email' => $_SESSION['user_data']['email']]);
            $_SESSION['user_data']['username'] = $result->username;
        }else
        {
            $result = $user->first(['username' => $_SESSION['user_data']['username']]);
            $_SESSION['user_data']['email'] = $result->email;
        }

        return $_SESSION['user_data'];

    }

    protected function isEmailUnique($email): bool
    {
        $user = new User();
        return !$user->first(['email' => $email]);
    }

    protected function isUsernameUnique($username): bool
    {
        $user = new User();
        return !$user->first(['username' => $username]);
    }

    protected function is_password_match_username($username, $password)
    {
        $user = new User;
        $results = $user->first(['username' => $username]);
        
        $hashed_password = $results->password;

        return password_verify($password, $hashed_password);
    }

    protected function is_password_match_email($email, $password)
    {
        $user = new User;
        $results = $user->first(['email' => $email]);
        
        $hashed_password = $results->password;

        return password_verify($password, $hashed_password);
    }    

    public function set_session($user_data)
    {
        $user = new User;


        $results = $user->first(['username' => $user_data['username']]);

        $_SESSION['user_id'] = $results->id;
        $_SESSION['password'] = $results->password;
        $_SESSION['role'] = $results->role;

        return 1;
    }

    public function unset_user_data()
    {
        unset($_SESSION['user_data']);
        return 1;
    }
}