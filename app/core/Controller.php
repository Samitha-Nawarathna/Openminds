<?php

class Controller
{
    public function view($name, $data = [''])
    {
        if ($_SESSION) {
            $data['session'] = $_SESSION;
        }

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

    public function admin_guard()
    {
        if (!isset($_SESSION["user_id"]) || $_SESSION['role'] !== 'admin') {
            header("Location: ".ROOT."home");
        }
    }

    public function expert_guard()
    {
        if (!isset($_SESSION["user_id"]) || $_SESSION['role'] !== 'expert') {
            header("Location: ".ROOT."home");
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

    protected function json_request()
    {
        $json_string = file_get_contents('php://input');
        $data = json_decode($json_string, true);
    
        if (json_last_error() !== JSON_ERROR_NONE) {
            error_log('JSON Decode Error: ' . json_last_error_msg());
            

            return []; 
        }
        
        if ($data === null) {
            return [];
        }
    
        return $data;
    }
    protected function json_respond($data)
    {
        if (ob_get_level() > 0) {
            ob_clean(); 
        }

        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }
}