<?php

class Profilesetup extends Controller
{
    public function index()
    {
        if (!isset($_SESSION['user_data']['type'])) {
            header("Location: ".ROOT."home");
        }

        if ( $_SESSION['user_data']['type'] !== 'registration') {
            header("Location: ".ROOT."home");
        }
        $username = $_SESSION['user_data']['username'] ?? "Anna";
        // $_SESSION['user_data'] = ["username"=>"alice"];
        $default_image_url = ROOT."\uploads\\0\profile.avif";

        $this->view("profilesetup", ['username'=>$username, 'default_image_url'=>$default_image_url]);
    }

    public function set()
    {
        $default_image_url = ROOT."\uploads\\0\profile.avif";
        $user_data = $_SESSION['user_data'];
        show($_POST);
        show($user_data);
        $username = $_SESSION['user_data']['username'];
        
        $user = new User;
        $results = $user->update($username, ["display_name"=>$_POST['display_name']], "username");

        if ($results === false)
        {
            $this->view('profilesetup', ['username'=>$username, 'default_image_url'=>$default_image_url, 'message'=>'Unable to update display_name']);
            exit;            
        }

        if (!isset($_FILES['image'])) {
            $this->view('profilesetup', ['username'=>$username, 'default_image_url'=>$default_image_url, 'message'=>'Could not save file, file not uploaded.']);
            exit;
        }
        
        $file = $_FILES['image'];

        $register_services = new RegisterServices;
        $register_services->unset_user_data();

        $user = new User;
        $results = $user->first(['username'=>$username]);

        $user_id = $results->id;

        $filename = 'profile.' . pathinfo($file['name'], PATHINFO_EXTENSION);
        $upload_path = './uploads/' .$user_id."/" ;

        if (!is_dir($upload_path)) {
            mkdir($upload_path, 0777, true);
        }

        $upload_path .= $filename;
        
        if (!move_uploaded_file($file['tmp_name'], $upload_path)) {
            $this->view('profilesetup', ['username'=>$username, 'default_image_url'=>$default_image_url, 'message'=>'Could not save file']);
            // echo json_encode(['success' => false, 'error' => 'Could not save file']);
            exit;
        }

        $results = $user->update($username, ["profile_picture"=>$upload_path], "username");

        if ($results === false)
        {
            $this->view('profilesetup', ['username'=>$username, 'default_image_url'=>$default_image_url, 'message'=>'Unable to save image 2']);
            exit;            
        }

        $loginservices = new LoginServices;

        $loginservices->set_session($user_data);
        $loginservices->unset_user_data($user_data);
        
        header("Location: ".ROOT."dashboard");
    }

}