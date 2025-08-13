<?php

class confirmdeletion extends Controller
{
    public function index()
    {
        $this->guard_verified_access();

        $this->view("profiledelete");
    }

    public function guard_verified_access()
    {
        if (!isset($_SESSION['verified_deletion']) || !$_SESSION['verified_deletion'])
        {
            header("Location: ".ROOT."home");
        }
    }

    public function delete()
    {
        $this->guard_verified_access();

        $user_id = $_SESSION['user_id'];

        $profile_services = new ProfileServices;
        $profile_services->delete($user_id);
        
        header("Location: ".ROOT."logout");
    }

}