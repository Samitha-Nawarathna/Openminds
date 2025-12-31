<?php

class Notifications extends Controller {
    // ... existing methods ...

    public function api_load_more() {
        $this->json_respond([
            "success" => true,
            "results_returned" => 3,
            "next_offset" => 3,
            "available_more" => true,
            "data" => [
                [
                    "id" => 301,
                    "type" => "note_shared",
                    "message" => "**Alice** shared the note 'Q4 Planning' with you.",
                    "read" => false,
                    "timestamp" => "2025-11-26 18:00:00"
                ],
                [
                    "id" => 302,
                    "type" => "exercise_approved",
                    "message" => "Your submission for **Dijkstra's Algorithm** was approved.",
                    "read" => true,
                    "timestamp" => "2025-11-25 10:30:00"
                ],
                [
                    "id" => 303,
                    "type" => "system_maintenance",
                    "message" => "Scheduled maintenance tonight at 11 PM UTC.",
                    "read" => false,
                    "timestamp" => "2025-11-25 09:00:00"
                ]
            ]
        ]);
    }
}