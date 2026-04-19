<?php

/**
 * Event Model
 */
class Event
{
    use Model;
    public $errors = [];
    protected $table = 'events';

    protected $allowedColumns = [
        'user_id',
        'event_time',
        'event_type',
        'entity_type',
        'entity_id',
        'data'
    ];

    public function validate($data)
    {
        $this->errors = [];

        if (empty($data['user_id'])) {
            $this->errors['user_id'] = "User ID is required";
        }

        if (empty($data['event_type'])) {
            $this->errors['event_type'] = "Event type is required";
        }

        if (empty($data['entity_type'])) {
            $this->errors['entity_type'] = "Entity type is required";
        }

        return empty($this->errors);
    }

    /**
     * Helper to log an event easily
     */
    public function log($user_id, $event_type, $entity_type, $entity_id = null, $data = [])
    {
        $eventData = [
            'user_id'     => $user_id,
            'event_type'  => $event_type,
            'entity_type' => $entity_type,
            'entity_id'   => $entity_id,
            'data'        => !empty($data) ? json_encode($data) : null,
            'event_time'  => date("Y-m-d H:i:s")
        ];

        $insertResult = $this->insert($eventData);

        if ($insertResult) {
            // Check user points and role to see if a promotion to mentor is needed
            $userData = $this->query("SELECT id, points, role FROM user WHERE id = :id", [':id' => $user_id]);

            if ($userData && !empty($userData)) {
                $user = $userData[0];
                if ($user->role == 1 && $user->points >= 50) {
                    // Promote from student (1) to mentor (2)
                    $this->query("UPDATE user SET role = 2 WHERE id = :id", [':id' => $user_id]);

                    // Update session if the promoted user is currently logged in
                    if (isset($_SESSION['user_id']) && $_SESSION['user_id'] == $user_id) {
                        $_SESSION['role'] = 'mentor';
                        $_SESSION['promotion_notification'] = "Congratulations! You have been promoted to Mentor based on your contributions!";
                    }
                }
            }
        }

        return $insertResult;
    }
}
