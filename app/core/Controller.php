<?php

class Controller
{
    public function view($name)
    {
        // $model = new Model;
        // show($model->first(["password" => "1234"], ["username" => "amara"]));

        $filename = "../app/views/".$name.".view.php";
        
        if (file_exists($filename)) {
            require $filename;
    
        }else{
            require "../app/views/404.view.php"; 
        }
    }
}