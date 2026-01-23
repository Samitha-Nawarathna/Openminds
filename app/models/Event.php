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

        return $this->insert($eventData);
    }
}
