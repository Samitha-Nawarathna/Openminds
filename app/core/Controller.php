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
}