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

        if (!$approve_result->is_success()) {
            header("Location: ".ROOT."expertrequestsadmin/show?id=".$request_id."&message=".$approve_result->get_message());
            exit;
        }

        header("Location: ".ROOT."expertrequestadmin/show?id=".$request_id);
    }

    public function reject()
    {
        $request_id = $_POST['id'] ?? null;
        $feedback = $_POST['feedback'] ?? null;
        
        if ($request_id === false) {
            header("Location: ".ROOT."_404");
        }

        $expert_requests_service = new ExpertRequestsServices();
        $reject_result = $expert_requests_service->reject_request($request_id, $feedback);

        if (!$reject_result->is_success()) {
            header("Location: ".ROOT."expertrequestsadmin/show?id=".$request_id."&message=".$reject_result->get_message());
            exit;
        }

        header("Location: ".ROOT."expertrequestadmin/show?id=".$request_id);

    }

    public function undo_rejection()
    {
        $request_id = $_POST['request_id'] ?? null;
        
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

}
