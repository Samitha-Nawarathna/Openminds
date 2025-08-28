<?php

// define('CONTROLLERS_DIR', "../controllers/");
// define('_404_FILE', "../controllers/_404.php");

class App
{
    private $CONTROLLERS_DIR = "..\app\controllers\\";
    private $_404_FILE = "..\app\controllers\_404.php";
    private $controller = "Home";
    private $method = "index";

    private function split_url()
    {
        $URL = $_GET["url"] ?? "home";
        $URL = explode("?", $URL)[0]; 
        $URL = explode("/", $URL); 
    
        return $URL;
    }
    
    public function load_controller()
    {
        $URL = $this->split_url();
    
        $filename = $URL[0];
        $filename = $this->CONTROLLERS_DIR.ucfirst($filename).".php";
        if (isset($URL[1]) && ($URL[1] != "" || $URL[1] != "index"))
        {
            $this->method = $URL[1];
        }
    
        if (file_exists($filename)) {
            require $filename;
            $this->controller = ucfirst($URL[0]);
    
        }else{
            require $this->_404_FILE; 
            $this->controller = "_404";
        }

        $controller = new $this->controller;
        if(!method_exists($controller, $this->method))
        {
            $this->method = "index";
        }
        call_user_func_array([$controller, $this->method], []);
    
    }
    
}
