<?php

class Ajax extends Controller
{
    public function index()
    {
        header("Location :".ROOT."home");
    }

    public function test()
    {
        $this->view('test');
        $data = file_get_contents("php://input");
        // $data = json_decode($data);

        echo $data;
    }

    public function set_profile_data()
    {
        header('Content-Type: application/json');

        if (!isset($_FILES['image'])) {
            echo json_encode(['success' => false, 'error' => 'No file uploaded']);
            exit;
        }
        
        $file = $_FILES['image'];
        
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
        $max_size = 2 * 1024 * 1024; // 2MB
        
        if ($file['error'] !== UPLOAD_ERR_OK) {
            echo json_encode(['success' => false, 'error' => 'Upload error']);
            exit;
        }
        
        if (!in_array($file['type'], $allowed_types)) {
            echo json_encode(['success' => false, 'error' => 'Unsupported file type']);
            exit;
        }
        
        if ($file['size'] > $max_size) {
            echo json_encode(['success' => false, 'error' => 'File too large']);
            exit;
        }
        
        // Validate it's really an image
        if (!getimagesize($file['tmp_name'])) {
            echo json_encode(['success' => false, 'error' => 'Invalid image']);
            exit;
        }
        
        // Move to uploads directory
        $user = new User;
        $results = $user->first(['username'=>$_SESSION['user_data']['username']]);

        $user_id = $results->id;

        $filename = 'profile.' . pathinfo($file['name'], PATHINFO_EXTENSION);
        $upload_path = './uploads/' .$user_id."/" ;

        if (!is_dir($upload_path)) {
            mkdir($upload_path, 0777, true);
        }

        $upload_path .= $filename;
        
        if (!move_uploaded_file($file['tmp_name'], $upload_path)) {
            echo json_encode(['success' => false, 'error' => 'Could not save file']);
            exit;
        }
        
        header("Location :".ROOT."dashboard");
        // $this->view("dashboard");
        exit;
    }

    public function retrive_user_profiles()
    {
        $sent_data = json_decode(file_get_contents("php://input"));

        // show($sent_data);

        $data = (array) $sent_data->data;
        $offset = $sent_data->offset;
        $limit = $sent_data->limit;

        $user = new User;

        $results = $user->where($data, $offset, $limit, ["id", "username", "profile_picture", "role"]);


        echo json_encode($results);

    }
}