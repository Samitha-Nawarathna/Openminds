<?php

class Logout extends Controller
{
    public function index()
    {
        $_SESSION = array();

        if (ini_get("session.use_cookies")) {
            setcookie(session_name(), '', time() - 42000, '/');
        }

        session_destroy();

        header("Location: ".ROOT."login");
        exit;
    }
}