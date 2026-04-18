
<?php

class Expertrequest extends Controller
{
    public function index()
    {
        $this->login_guard();
        $this->view('expertrequests/requestbrowser');
    }


    public function create()
    {
        // Logic to create a new expert request
        // Validate input, save to database, etc.
        $this->login_guard();

        if ($this->is_post()) {
            // echo "create post...";
            // validate input
            $expert_requests_service = new ExpertRequestsServices();

            $sent_data = $_POST;
            $validation_result = $expert_requests_service->validate($sent_data);

            if (!$validation_result->is_success())
            {
                echo "validation failed";
                $this->view('expertrequests/create', ['message' => $validation_result->get_errors_string()]);
                return;
            }
            //get user id
            $user_id = $_SESSION['user_id'];
            $sent_data['user_id'] = $user_id;

            // 1. Handle Mandatory CV
            if (!isset($_FILES['cv']) || empty($_FILES['cv']['name'])) {
                $this->view('expertrequests/create', ['message' => 'CV is mandatory.']);
                return;
            }

            // Check CV errors
            if ($_FILES['cv']['error'] !== UPLOAD_ERR_OK) {
                $this->view('expertrequests/create', ['message' => 'CV upload failed. Error code: ' . $_FILES['cv']['error']]);
                return;
            }

            // Validate CV content
            $cv_validation = $expert_requests_service->validate_file($_FILES['cv']);
            if (!$cv_validation->is_success()) {
                $this->view('expertrequests/create', ['message' => $cv_validation->get_errors_string()]);
                return;
            }

            // Create Unique Directory for this Request
            $unique_id = uniqid('req_');
            $save_dir = __DIR__ . "/../../private/uploads/requests/" . $user_id . "/" . $unique_id . "/";
            
            if (!file_exists($save_dir)) {
                mkdir($save_dir, 0777, true);
            }

            // Save CV
            $cv_path = $save_dir . "CV.pdf";
            if (!move_uploaded_file($_FILES['cv']['tmp_name'], $cv_path)) {
                $this->view('expertrequests/create', ['message' => "Failed to save CV."]);
                return; 
            }
            $sent_data['proof_link'] = $cv_path;

            // 2. Handle Optional Supporting Documents
            if (isset($_FILES['supporting_docs']) && !empty($_FILES['supporting_docs']['name'][0])) {
                $files = $_FILES['supporting_docs'];
                $count = count($files['name']);

                for ($i = 0; $i < $count; $i++) {
                    if ($files['error'][$i] === UPLOAD_ERR_OK) {
                        // Simple validation for supporting docs (PDF/Images < 5MB)
                        // Note: Production should define strict types.
                        $tmp_name = $files['tmp_name'][$i];
                        $name = basename($files['name'][$i]);
                        $size = $files['size'][$i];
                        
                        if ($size < 5 * 1024 * 1024) {
                             $target = $save_dir . $name;
                             move_uploaded_file($tmp_name, $target);
                        }
                    }
                }
            }
            //update database

            $insert_result = $expert_requests_service->insert($sent_data);
            if (!$insert_result->is_success())
            {
                $this->view('expertrequests/create', ['message' => $insert_result->get_errors_string()]);
                return;
            }
            //retrive request view
            // $this->view('expertrequests/view', ['message' => "Request created successfully."]);
            
            
            $request_id = $insert_result->get_data()['id'] ?? null;
            header("Location: " . ROOT . "expertrequest/show?id=" . $request_id);
            
            
        }

        if ($this->is_get()) {
            // Render the form for creating a new expert request
            $this->view('expertrequests/create');
        }


    }

    public function show()
    {
        $this->login_guard();

        $request_id = $_GET['id'] ?? null;
        
        if ($request_id === false) {
            header("Location: ".ROOT."_404");
        }

        $expert_requests_service = new ExpertRequestsServices();
        $retrive_result = $expert_requests_service->retrive_request($request_id);

        if (!$retrive_result->is_success()) {
            header("Location: ".ROOT."expertrequest?message=".$retrive_result->get_message());
            exit;
        }

        $data = $retrive_result->get_data();

        // Scan for files in the request directory
        // proof_link contains full path to CV. Directory is the parent.
        if (!empty($data['proof_link'])) {
            $folder = dirname($data['proof_link']);
            $supporting_docs = [];

            if (is_dir($folder)) {
                $files = scandir($folder);
                foreach ($files as $file) {
                    if ($file !== '.' && $file !== '..') {
                        // Separate CV from supporting docs
                        if ($file !== 'CV.pdf' && basename($data['proof_link']) !== $file) {
                            $supporting_docs[] = $file;
                        }
                    }
                }
            }
            $data['supporting_docs'] = $supporting_docs;
        } else {
            $data['supporting_docs'] = [];
        }

        $this->view('expertrequests/view', $data);
    }

    public function edit()
    {
        $this->login_guard();
        $data['id'] = $_GET['id'] ?? null;

        $expert_requests = new ExpertRequests();
        $results = $expert_requests->first(['id' => $data['id']]);

        if ($results === false) {
            header("Location: " . ROOT . "_404");
            exit;
        }

        $edit_data = [
            'id' => $data['id'],
            'subject' => $results->subject ?? '',
            'description' => $results->description ?? '',
            'proof_link' => $results->proof_link ?? ''
        ];

        // Scan for supporting documents
        if (!empty($edit_data['proof_link'])) {
            $folder = dirname($edit_data['proof_link']);
            $supporting_docs = [];

            if (is_dir($folder)) {
                $files = scandir($folder);
                foreach ($files as $file) {
                    if ($file !== '.' && $file !== '..') {
                        if ($file !== 'CV.pdf' && basename($edit_data['proof_link']) !== $file) {
                            $supporting_docs[] = $file;
                        }
                    }
                }
            }
            $edit_data['supporting_docs'] = $supporting_docs;
        } else {
            $edit_data['supporting_docs'] = [];
        }

        $this->view('expertrequests/edit', $edit_data);
    }

    public function delete()
    {
        // Logic to delete an existing expert request by ID
        // Remove from database and handle any necessary cleanup
        // $this->login_guard();
        // $this->post_guard();

        $request_id = $_POST['id'] ?? null;

        if ($request_id === null) {
            // header("Location: " . ROOT . "_404");
            $this->view('expertrequests/show?id='.$request_id, ['message' => 'Invalid request ID.']);
            exit;
        }

        $expert_requests = new ExpertRequests();

        $existing_request = $expert_requests->first(['id' => $request_id]);
        if (!$existing_request) {
            $this->view('expertrequests/show?id='.$request_id, ['message' => 'request not found in DB.']);
            exit;
        }

        // verify ownership
        $user_id = $_SESSION['user_id'];
        if ($existing_request->user_id !== $user_id) {
            $this->view('expertrequests/show?id='.$request_id, ['message' => 'unautherized action.']);
            exit;
        }

        $delete_result = $expert_requests->delete($request_id);
        header("Location: " . ROOT . "expertrequest"); 
    }

    public function download()
    {
        $this->login_guard();

        $request_id = $_GET['request_id'] ?? $_POST['request_id'] ?? null;
        $file_name = $_GET['file'] ?? $_POST['file'] ?? null;

        if (!$request_id) {
            // Try extracting ID from proof_link query if legacy, but ideally rely on ID.
            header("Location: " . ROOT . "expertrequest?message=Invalid download request.");
            exit;
        }

        $expert_requests = new ExpertRequests();
        $existing_request = $expert_requests->first(['id' => $request_id]);

        if (!$existing_request) {
            header("Location: " . ROOT . "expertrequest?message=Request not found.");
            exit;
        }

        // Authorization Guard
        $role = $_SESSION['role'];
        if ($role !== 'admin') {
            $user_id = $_SESSION['user_id'];
            if ($existing_request->user_id !== $user_id) {
                header("Location: " . ROOT . "expertrequest?message=You are not authorized to download this file.");
                exit;
            }
        }

        // Path Resolution
        if ($file_name) {
            // Prevent directory traversal
            $file_name = basename($file_name); 
            $request_dir = dirname($existing_request->proof_link);
            $file_path = $request_dir . '/' . $file_name;
        } else {
            // Default to the main proof link (CV)
            $file_path = $existing_request->proof_link;
        }

        // Serve File
        if (file_exists($file_path)) {
            $expert_requests_service = new ExpertRequestsServices();
            $expert_requests_service->download($file_path);
        } else {
            header("Location: " . ROOT . "expertrequest?message=File not found on server.");
            exit;
        }
    }

    public function update()
    {
        $expert_requests_service = new ExpertRequestsServices();
    
        $sent_data = $_POST;
        $request_id = $_POST['id'] ?? null;
    
        if (!$request_id) {
            $this->view('expertrequests/edit', ['message' => 'Invalid request ID.']);
            return;
        }
    
        // get user id
        $user_id = $_SESSION['user_id'];
    
        // fetch the existing request
        $existing_request_result = $expert_requests_service->find_by_id($request_id);
        if (!$existing_request_result->is_success()) {
            $this->view('expertrequests/edit', ['message' => 'Request not found.']);
            return;
        }

        $existing_request = $existing_request_result->get_data();
    
        // verify ownership
        if ($existing_request['user_id'] !== $user_id) {
            $this->view('expertrequests/edit', ['message' => 'You are not authorized to edit this request.']);
            return;
        }
    
        // validate new data
        $validation_result = $expert_requests_service->validate($sent_data);
        if (!$validation_result->is_success()) {
            $this->view('expertrequests/edit', ['message' => $validation_result->get_errors_string()]);
            return;
        }
    
        $sent_data['user_id'] = $user_id;
        $sent_data['id'] = $request_id;

        // Get existing proof_link to find the folder
        $existing_folder = !empty($existing_request['proof_link']) ? dirname($existing_request['proof_link']) : null;

        // Handle files marked for deletion
        if (!empty($_POST['files_to_delete'])) {
            $files_to_delete = json_decode($_POST['files_to_delete'], true);
            if ($existing_folder && is_array($files_to_delete)) {
                foreach ($files_to_delete as $filename) {
                    $file_path = $existing_folder . '/' . basename($filename);
                    if (file_exists($file_path)) {
                        unlink($file_path);
                    }
                }
            }
        }

        // Handle CV replacement (optional)
        if (isset($_FILES['cv']) && $_FILES['cv']['error'] === UPLOAD_ERR_OK) {
            $cv_validation = $expert_requests_service->validate_file($_FILES['cv']);
            if (!$cv_validation->is_success()) {
                $this->view('expertrequests/edit', ['message' => $cv_validation->get_errors_string()]);
                return;
            }

            // Ensure folder exists
            if (!$existing_folder) {
                // Create new folder if request didn't have one
                $unique_id = uniqid('req_');
                $existing_folder = __DIR__ . "/../../private/uploads/requests/" . $user_id . "/" . $unique_id . "/";
                if (!file_exists($existing_folder)) {
                    mkdir($existing_folder, 0777, true);
                }
            }

            // Replace CV
            $cv_path = $existing_folder . '/CV.pdf';
            if (!move_uploaded_file($_FILES['cv']['tmp_name'], $cv_path)) {
                $this->view('expertrequests/edit', ['message' => "Failed to save new CV."]);
                return;
            }
            $sent_data['proof_link'] = $cv_path;
        }

        // Handle new supporting documents (optional)
        if (isset($_FILES['supporting_docs']) && !empty($_FILES['supporting_docs']['name'][0])) {
            $files = $_FILES['supporting_docs'];
            $count = count($files['name']);

            // Ensure folder exists
            if (!$existing_folder) {
                $unique_id = uniqid('req_');
                $existing_folder = __DIR__ . "/../../private/uploads/requests/" . $user_id . "/" . $unique_id . "/";
                if (!file_exists($existing_folder)) {
                    mkdir($existing_folder, 0777, true);
                }
            }

            for ($i = 0; $i < $count; $i++) {
                if ($files['error'][$i] === UPLOAD_ERR_OK) {
                    $tmp_name = $files['tmp_name'][$i];
                    $name = basename($files['name'][$i]);
                    $size = $files['size'][$i];
                    
                    if ($size < 5 * 1024 * 1024) {
                         $target = $existing_folder . '/' . $name;
                         move_uploaded_file($tmp_name, $target);
                    }
                }
            }
        }
    
        // Remove non-database fields from sent_data
        unset($sent_data['files_to_delete']);
        
        // update database
        $update_result = $expert_requests_service->update($sent_data);
        if (!$update_result->is_success()) {
            $this->view('expertrequests/edit', ['message' => $update_result->get_errors_string()]);
            return;
        }
    
        // redirect to show updated request
        header("Location: " . ROOT . "expertrequest/show?id=" . $request_id);
        exit;
    }

    public function api_search_subjects()
    {
        $this->login_guard();

        header('Content-Type: application/json');

        $q = trim($_GET['q'] ?? '');

        if ($q === '') {
            echo json_encode([]);
            return;
        }

        $subjects_model = new Subjects();
        $results = $subjects_model->search_by_name($q, 'name');

        if (!$results) {
            echo json_encode([]);
            return;
        }

        // Return only id + name
        $output = array_map(function($row) {
            return ['id' => $row->id, 'name' => $row->name];
        }, $results);

        echo json_encode($output);
    }

    public function retrive_user_expertrequests()
    {
        $this->login_guard();
        
        // Read JSON input
        $json = file_get_contents('php://input');
        $data = json_decode($json, true);

        $review_status = $data['data']['review'] ?? 'pending';
        $subject_search = $data['data']['subject'] ?? '';
        $limit = (int)($data['limit'] ?? 10);
        $offset = (int)($data['offset'] ?? 0);

        $expert_requests_service = new ExpertRequests();
        
        // Build parameters for the filter_and_search method
        $params = [
            'where' => [
                'user_id' => $_SESSION['user_id'], // Enforce ownership
                'review'  => $review_status
            ],
            'like' => [],
            'limit' => $limit,
            'offset' => $offset,
            'order_by' => 'id',
            'order_dir' => 'DESC'
        ];

        // Add search filter if provided
        if (!empty($subject_search)) {
            $params['like']['subject'] = $subject_search;
        }

        // Use the robust method we discussed
        $results = $expert_requests_service->filter_and_search($params);

        header('Content-Type: application/json');
        echo json_encode($results);
    }

}