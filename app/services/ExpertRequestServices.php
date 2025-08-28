<?php

class ExpertRequestsServices
{
    function validate(array $data): ServiceResult
    {
        $subject = $data['subject'] ?? null;
        $description = $data['description'] ?? null;

        if (empty($subject) || empty($description)) {
            return ServiceResult::failure(['Subject and description are required.']);
        }

        return ServiceResult::success();
    }

    function validate_file(array $file): ServiceResult
    {
        if ($file['error'] !== UPLOAD_ERR_OK) {
            return ServiceResult::failure(['File upload error.']);
        }

        $allowed_types = ['application/pdf'];
        if (!in_array($file['type'], $allowed_types)) {
            return ServiceResult::failure(['Only PDF files are allowed.']);
        }

        if ($file['size'] > 5 * 1024 * 1024) { // 5MB limit
            return ServiceResult::failure(['File size exceeds the limit of 5MB.']);
        }

        return ServiceResult::success();
    }

    function insert(array $data): ServiceResult
    {
        $requests = new ExpertRequests;
        $insert_result = $requests->insert($data);

        if($insert_result === false)
        {
            return ServiceResult::failure(['Failed to insert expert request into the database.']);
        }

        return ServiceResult::success(['id'=> $insert_result]);
    }

    function retrive_request(int $request_id): ServiceResult
    {
        $requests = new ExpertRequests;
        $request = $requests->first(['id' => $request_id]);

        $user = new User();
        $user_info = $user->first(['id' => $request->user_id]);

    

        if (!$request) {
            return ServiceResult::failure(['Request not found.']);
        }

        return ServiceResult::success(["id"=>$request->id, "subject"=>$request->subject, "description"=>$request->description, "proof_link"=>$request->proof_link, "image_url"=>$user_info->profile_picture, "display_name" => $user_info->display_name, "review"=>$request->review, "feedback"=>$request->feedback]);
    }

    public function download($filePath)
    {
        if (!file_exists($filePath)) {
            return ServiceResult::failure(['File not found.']);
        }

        // Get file info
        $fileName = basename($filePath);
        $fileSize = filesize($filePath);

        // Set headers
        header("Content-Description: File Transfer");
        header("Content-Type: application/octet-stream");
        header("Content-Disposition: attachment; filename=\"$fileName\"");
        header("Content-Length: " . $fileSize);
        header("Pragma: public");
        header("Cache-Control: must-revalidate");

        // Clear output buffer
        ob_clean();
        flush();

        // Read file
        readfile($filePath);

        return ServiceResult::success();
    }

    function find_by_id($id)
    {
        $requests = new ExpertRequests;
        $request = $requests->first(['id' => $id]);

        if ($request === false) {
            return ServiceResult::failure(['Request not found.']);
        }

        return ServiceResult::success([
            'id' => $request->id,
            'user_id' => $request->user_id,
            'subject' => $request->subject,
            'description' => $request->description,
            'proof_link' => $request->proof_link
        ]);
    }

    function update(array $data): ServiceResult
    {
        $requests = new ExpertRequests;
        $update_result = $requests->update($data['id'], $data);

        if ($update_result === false) {
            return ServiceResult::failure(['Failed to update expert request in the database.']);
        }

        return ServiceResult::success();
    }

    function approve_request($request_id)
    {
        $request_result = $this->find_by_id($request_id);
        if (!$request_result->is_success()) {
            return ServiceResult::failure(['Request not found.']);
        }

        $request_data = $request_result->get_data();

        $user = new User();
        $user_data = $user->first(['id' => $request_data['user_id']]);
        if ($user_data === false) {
            return ServiceResult::failure(['User not found.']);
        }

        // Update user role to expert
        $update_user_result = $user->update_to_expert($user_data->id, $request_data['subject']);

        if ($update_user_result === false) {
            return ServiceResult::failure(['Failed to update user role.']);
        }

        // Update request review status to approved
        $update_request_result = $this->update(['id' => $request_id, 'review' => 'approved']);
        if (!$update_request_result->is_success()) {
            return ServiceResult::failure(['Failed to update request status.']);
        }

        return ServiceResult::success();

        //send notification for the user
    }

    function reject_request($request_id, $feedback)
    {
        $request_result = $this->find_by_id($request_id);
        if (!$request_result->is_success()) {
            return ServiceResult::failure(['Request not found.']);
        }

        // Update request review status to rejected with feedback
        $update_request_result = $this->update(['id' => $request_id, 'review' => 'rejected', 'feedback' => $feedback]);
        if (!$update_request_result->is_success()) {
            return ServiceResult::failure(['Failed to update request status.']);
        }

        return ServiceResult::success();

        //send notification for the user
    }
}