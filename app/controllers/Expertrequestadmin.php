<?php

final class Expertrequestadmin extends Controller
{
    public function index()
    {
        $this->view("expertrequestsadmin/browser");
    }

    public function show()
    {
        $request_id = $_GET['id'] ?? null;
        
        if ($request_id === false) {
            header("Location: ".ROOT."expertrequestsadmin?message=Invalid Request ID");
        }

        $expert_requests_service = new ExpertRequestsServices();
        $retrive_result = $expert_requests_service->retrive_request($request_id);

        if (!$retrive_result->is_success()) {
            header("Location: ".ROOT."_404");
            exit;
        }

        $this->view('expertrequestsadmin/view', $retrive_result->get_data());

    }

    public function approve()
    {
        $request_id = $_POST['id'] ?? null;
        if ($request_id === false) {

            header("Location: ".ROOT."_404");
        }

        $expert_requests_service = new ExpertRequestsServices();
        $approve_result = $expert_requests_service->approve_request($request_id);
        
        $request_model = new ExpertRequests();
        $reciver_id = $request_model->first(['id' => $request_id])->user_id;

        $sender_id = $_SESSION['user_id'];


        if (!$approve_result->is_success()) {
            header("Location: ".ROOT."expertrequestsadmin/show?id=".$request_id."&message=".$approve_result->get_message());
            exit;
        }

        $notification_service = new NotificationServices();
        $notification_service->send_notification($sender_id, "Your expert request has been approved. You can now access expert features on our platform.", $reciver_id);
        

        header("Location: ".ROOT."expertrequestadmin/show?id=".$request_id);
    }

    public function reject()
    {
        $request_id = $_POST['id'] ?? null;
        $feedback = $_POST['feedback'] ?? null;
        
        if ($request_id === false) {
            header("Location: ".ROOT."expertrequestadmin/show?id=".$request_id."&message=invalid request id");
            exit;
        }
        
        if ($feedback === null || trim($feedback) === '') {
            header("Location: ".ROOT."expertrequestadmin/show?id=".$request_id."&message=feedback cannot be empty!");
            exit;
        }        

        $expert_requests_service = new ExpertRequestsServices();
        $reject_result = $expert_requests_service->reject_request($request_id, $feedback);

        if (!$reject_result->is_success()) {
            header("Location: ".ROOT."expertrequestadmin/show?id=".$request_id."&message=".$reject_result->get_message());
            exit;
        }

        header("Location: ".ROOT."expertrequestadmin/show?id=".$request_id);

    }

    public function undo_rejection()
    {
        $request_id = $_POST['id'] ?? null;
        
        if ($request_id === false) {
            header("Location: ".ROOT."_404");
        }

        $expert_requests_service = new ExpertRequestsServices();
        $update_result = $expert_requests_service->update(['id' => $request_id, 'review' => 'pending', 'feedback' => null]);

        if (!$update_result->is_success()) {
            header("Location: ".ROOT."expertrequestsadmin/show?id=".$request_id."&message=".$update_result->get_message());
            exit;
        }

        header("Location: ".ROOT."expertrequestadmin/show?id=".$request_id);

    }

        /**
     * AJAX Endpoint: Translates UI state to Database Filter Params
     */
    public function filter_requests()
    {
        // Decode JSON body from fetch request
        $json = file_get_contents('php://input');
        $data = json_decode($json, true);

        $limit = (int)($data['limit'] ?? 10);
        $offset = (int)($data['offset'] ?? 0);

        // Build the params array for the abstract database method
        $params = [
            'select'    => ['*'],
            'limit'     => $limit,
            'offset'    => $offset,
            'order_by'  => $data['sort'] ?? 'request_id',
            'order_dir' => $data['dir'] ?? 'DESC'
        ];

        // 1. Status Filter (from Tabs)
        if (!empty($data['review'])) {
            $params['where']['review'] = $data['review'];
        }

        // 2. Keyword Search (using LIKE)
        if (!empty($data['search'])) {
            $params['like']['description'] = $data['search'];
        }

        // 3. Subject Filter (from Advanced Modal)
        if (!empty($data['subject'])) {
            $params['where']['subject'] = $data['subject'];
        }

        $request_model = new UserRequestsModel();
        // This leverages your existing abstract database method
        $results = $request_model->filter_and_search($params);

        header('Content-Type: application/json');
        echo json_encode([
            'results' => $results,
            'has_more' => count($results) === $limit
        ]);
    }

}
