<?php

class Profilebrowser extends Controller
{
    public function index()
    {
        $this->guard_admin();

        $subjects_model = new Subjects();
        $data['subjects'] = $subjects_model->findAll();
        // show($data);

        $this->view("profilebrowser", $data);
    }

    public function guard_admin()
    {
        
    }

    public function api_search_and_filter()
    {
        $params = $this->json_request();


        $profile_summary_model = new ProfileSummaryModel;
        $this->json_respond($profile_summary_model->filter_and_search($params));

    }
}