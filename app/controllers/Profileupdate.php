<?php

class Profileupdate extends Controller
{
    public function index()
    {
        $this->login_guard();

        $user = new User;
        $id = $_SESSION['user_id'];

        $results = $user->first(['id'=>$id]);
        $image_url = ROOT.$results->profile_picture;

        $this->view("profileupdate", ['display_name'=>$results->display_name, 'image_url'=>$image_url]);
    }

    public function update()
    {
        // $default_image_url = ROOT."\uploads\\0\profile.avif";
        $id = $_SESSION['user_id'];
        // show($_SESSION);
        $user = new User;
        $results = $user->first(["id"=>$id]);

        $profile_picture = ROOT.$results->profile_picture;

        
        
        $results = $user->update($id, ["display_name"=>$_POST['display_name']], "id");

        if ($results === false)
        {
            $this->view('profileupdate', ['display_name'=>$display_name, 'image_url'=>$profile_picture, 'message'=>'Unable to update display_name']);
            exit;            
        }

        if (!isset($_FILES['image'])) {
            $this->view('profileupdate', ['display_name'=>$display_name, 'image_url'=>$profile_picture, 'message'=>'Could not save file, file not uploaded.']);
            exit;
        }
        
        $file = $_FILES['image'];

        $register_services = new RegisterServices;
        $register_services->unset_user_data();

        $user = new User;
        $results = $user->first(["id"=>$id]);

        $user_id = $results->id;

        $filename = 'profile.' . pathinfo($file['name'], PATHINFO_EXTENSION);
        $upload_path = './uploads/' .$user_id."/" ;

        if (!is_dir($upload_path)) {
            mkdir($upload_path, 0777, true);
        }

        $upload_path .= $filename;
        move_uploaded_file($file['tmp_name'], $upload_path);
        
        // if (!move_uploaded_file($file['tmp_name'], $upload_path)) {
        //     // $this->view('profileupdate', ['display_name'=>$display_name, 'image_url'=>$profile_picture, 'message'=>'Could not save file']);
        //     // echo json_encode(['success' => false, 'error' => 'Could not save file']);
        //     // exit;
        // }

        $results = $user->update($id, ["profile_picture"=>$upload_path]);

        if ($results === false)
        {
            $this->view('profileupdate', ['display_name'=>$display_name, 'image_url'=>$profile_picture, 'message'=>'Unable to save image 2']);
            exit;            
        }

        
        header("Location: ".ROOT."profile");
    }

}