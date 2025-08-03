<?php

class RegisterServices
{
    public function validate($data)
    {
        $errors = [];

        foreach (array_keys($_POST) as $field) {
            if (empty($data[$field])) {
                $errors[] = "fill all the fields!";
                return $errors;
            }
        }


        if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $errors[] = "Invalid email format.";

        }elseif (!$this->isEmailUnique($data['email'])) {
            $errors[] = "Email is already taken.";

        }elseif (!$this->isUsernameUnique($data['username'])) {
            $errors[] = "Username is already taken.";

        }elseif (!$this->isStrongPassword($data['password'])) {
            $errors[] = "Password must be 8+ chars, with upper, lower, number, special char.";

        }elseif ($data['password'] !== $data['confirm_password']) {
            $errors[] = "Passwords do not match.";

        }

        return $errors;
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

    protected function isStrongPassword($password): bool
    {
        $pattern = '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&]).{8,}$/';
        return preg_match($pattern, $password);
    }


    public function create_user($data)
    {
        $user = new User();
        $user->insert([
            'username' => $data['username'],
            'email' => $data['email'],
            'password' => password_hash($data['password'], PASSWORD_DEFAULT)
        ]);
    }

    public function unset_user_data()
    {
        unset($_SESSION['user_data']);
        return 1;
    }

}