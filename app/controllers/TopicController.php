<?php

class TopicController extends Controller
{

    public function create()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Handle form submission if needed
            exit;
        }

        $this->view('notes/create_title');
    }

    //----------------------------------------------------------//
    //-----------------------AJAX METHODS-----------------------//
    //----------------------------------------------------------//

    public function api_create()
    {
        $data = $this->json_request();

        $topics_model = new Topics();

        $name = $data->name;
        $creator_id = $_SESSION['user_id'];

        $is_available = $topics_model->is_name_available($name);

        if ($is_available) {
            $this->json_response(['success' => false, 'message' => 'Topic name already taken.']);
            return;
        }

        $new_topic_id = $topics_model->insert([
            'name' => $name,
            'creator_id' => $creator_id
        ]);

        if ($new_topic_id) {
            $this->json_response(['success' => true, 'topic_id' => $new_topic_id]);
        } else {
            $this->json_response(['success' => false, 'message' => 'Failed to create topic.']);
        }
    }

    public function api_is_name_available()
    {
        $data = $this->json_request();

        $topics_model = new Topics();
        $is_available = $topics_model->is_name_available($name);

        if ($is_available) {
            $this->json_response(['available' => false]);
        } else {
            $this->json_response(['available' => false]);
        }
    } 

    public function api_filter()
    {
        $data = $this->json_request();
        $topics_model = new Topics();

        $results = $topics_model->filter_by_name($data['query'], 'name');



        if ($results) {
            

            $this->json_response(['success' => true, 'results' => $results]);
        } else {
            $this->json_response(['success' => false, 'message' => 'No topics found.']);
        }
    }

    public function api_pin_topic($id) {
        $this->json_respond([
            "success" => true,
            "message" => "Topic 'Theoretical Physics' successfully pinned.",
            "data" => [
                "topic_id" => (int)$id,
                "is_pinned" => true,
                "updated_at" => "2025-11-26 18:36:45"
            ]
        ]);
    }

    public function api_unpin_topic($id) {
        $this->json_respond([
            "success" => true,
            "message" => "Topic 'Theoretical Physics' successfully unpinned.",
            "data" => [
                "topic_id" => (int)$id,
                "is_pinned" => false,
                "updated_at" => "2025-11-26 18:36:45"
            ]
        ]);
    }

    public function api_load_more() {
        $this->json_respond([
            "success" => true,
            "results_returned" => 3,
            "next_offset" => 13,
            "available_more" => true,
            "data" => [
                ["id" => 11, "name" => "Organic Chemistry", "note_count" => 8],
                ["id" => 12, "name" => "Differential Equations", "note_count" => 25],
                ["id" => 13, "name" => "Microeconomics", "note_count" => 5]
            ]
        ]);
    }    


}