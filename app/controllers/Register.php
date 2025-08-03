<?php

class Register extends Controller
{
    public function index()
    {
        $this->view("register");
    }

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

    public function register()
    {

        $errors = $this->validate($_POST);

        if (!empty($errors)) {
            $errors = implode("\n", $errors);

            $this->view("register", [
                "message" => $errors,
                "username" => $_POST["username"],
                "password" => $_POST["password"],
                "email" => $_POST["email"]
            ]);

            return;
        }

        $_SESSION['user_data'] = $_POST;
        $_SESSION['user_data']['type'] = 'registration';

        $otp_service = new OtpService;
        $is_generated = $otp_service->generate($_SESSION['user_data']);

        if (!$is_generated)
        {
            $this->view("register", [
                    "message" => "Error in OTP generation. Please try again!.",
                    "username" => $_POST["username"],
                    "password" => $_POST["password"],
                    "email" => $_POST["email"]
                ]
            );

            return;
        }

        $this->view("otp");
        // $this->createUser($_POST);

    }
}
