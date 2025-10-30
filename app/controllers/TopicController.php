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


}