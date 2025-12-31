<!--create request logic,
view request logic,
edit request logic,
delete request logic,

-->
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

            //is file available
            if (isset($_FILES['file']) && $_FILES['file']['error'] === UPLOAD_ERR_OK)
            {
                $file_validation_result = $expert_requests_service->validate_file($_FILES['file']);
                if (!$file_validation_result->is_success())
                {
                    $this->view('expertrequests/create', ['message' => $file_validation_result->get_errors_string()]);
                    return;
                }
                //create save path
                $save_dir = __DIR__ . "/../../private/uploads/requests/" . $user_id . "/";
                $save_path = $save_dir."request.pdf";
                // create save folder if needed
                if (!file_exists($save_dir)) {
                    mkdir($save_dir, 0777, true);
                }
                //move file to save path
                $file_tmp_path = $_FILES['file']['tmp_name'];
                if(!move_uploaded_file($file_tmp_path, $save_path))
                {
                    $this->view('expertrequests/create', ['message' => "Failed to move uploaded file."]);
                    return;
                }

                $sent_data['proof_link'] = $save_path;
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
        // Logic to view an existing expert request by ID
        // Fetch from database and return the data
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

        $this->view('expertrequests/view', $retrive_result->get_data());
    }

    public function edit()
    {
        // Logic to edit an existing expert request by ID
        // Validate input, update in database, etc.
        $this->login_guard();
        $data['id'] = $_GET['id'] ?? null;

        $expert_requests = new ExpertRequests();
        $results = $expert_requests->first(['id' => $data['id']]);

        if ($results === false) {
            header("Location: " . ROOT . "_404");
            exit;
        }

        $this->view('expertrequests/edit', [
            'id' => $data['id'],
            'subject' => $results->subject ?? '',
            'description' => $results->description ?? '',
            'proof_link' => $results->proof_link ?? ''
        ]);        
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

        $request_id = $_POST['request_id'] ?? null;

        $expert_requests = new ExpertRequests();
        $existing_request = $expert_requests->first(['id' => $request_id]);

        if (!$existing_request) {
            header("Location: " . ROOT . "expertrequest?message=Request not found.");
            exit;
        }

        $role = $_SESSION['role'];
        
        if ($role !== 'admin') {
            $user_id = $_SESSION['user_id'];
            if ($existing_request->user_id !== $user_id) {
                header("Location: " . ROOT . "expertrequest?message=You are not authorized to download this file.");
                exit;
            }
        }

        $file_path = $existing_request->proof_link;


        $expert_requests_service = new ExpertRequestsServices();
        $expert_requests_service->download($file_path);

        
        
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
    
        // file upload handling (optional overwrite)
        if (isset($_FILES['file']) && $_FILES['file']['error'] === UPLOAD_ERR_OK) {
            $file_validation_result = $expert_requests_service->validate_file($_FILES['file']);
            if (!$file_validation_result->is_success()) {
                $this->view('expertrequests/edit', ['message' => $file_validation_result->get_errors_string()]);
                return;
            }
    
            $save_dir = __DIR__ . "/../../private/uploads/requests/" . $user_id ."/".$request_id."/";
            $save_path = $save_dir . "request.pdf";
    
            if (!file_exists($save_dir)) {
                mkdir($save_dir, 0777, true);
            }
    
            $file_tmp_path = $_FILES['file']['tmp_name'];
            if (!move_uploaded_file($file_tmp_path, $save_path)) {
                $this->view('expertrequests/edit', ['message' => "Failed to move uploaded file."]);
                return;
            }
    
            $sent_data['proof_link'] = $save_path;
        }
    
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

}