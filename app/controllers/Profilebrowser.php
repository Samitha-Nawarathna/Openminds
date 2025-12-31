<?php

class Profilebrowser extends Controller
{
    public function index()
    {
        $this->guard_admin();

        $this->view("profilebrowser");
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