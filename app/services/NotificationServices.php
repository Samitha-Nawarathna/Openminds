<?php

class NotificationServices
{
    public function send_notification($sender_id, $content, $receiver_id): ServiceResult
    {
        $notifications = new Notifications();
        $data = [
            'sender_id' => $sender_id,
            'receiver_id' => $receiver_id,
            'content' => $content,
        ];

        $insert_result = $notifications->insert($data);

        if ($insert_result === false) {
            return ServiceResult::failure(['Failed to send notification.']);
        }

        return ServiceResult::success();
    }

    public function retrive($receiver_id, $is_read): ServiceResult
    {
        $notifications = new Notifications();
        $notifications_data = $notifications->where(['receiver_id' => $receiver_id, 'is_read' => $is_read]);

        if ($notifications_data === false) {
            return ServiceResult::failure(['Failed to retrieve notifications.']);
        }

        return ServiceResult::success($notifications_data);
    }
}