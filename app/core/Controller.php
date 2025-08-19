<?php

class Controller
{
    public function view($name, $data = [''])
    {
        $filename = "../app/views/".$name.".view.php";
        
        if (file_exists($filename)) {
            require $filename;
    
        }else{
            require "../app/views/404.view.php"; 
        }
    }

    public function login_guard()
    {
        if (!isset($_SESSION["user_id"])) {
            header("Location: ".ROOT."login");
        }
    }
    
    public function post_guard()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: ".ROOT."home");
        }
    }

    public function is_get()
    {
        return $_SERVER['REQUEST_METHOD'] === 'GET';
    }

    public function is_post()
    {
        return $_SERVER['REQUEST_METHOD'] === 'POST';
    }
}