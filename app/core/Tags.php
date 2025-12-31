<?php

class TagController extends Controller
{
    public function api_search_tags_by_name()
    {
        $data = $this->json_request();
        $tags_model = new Tags();

        $query = $data['query'];
        $results = $tags_model->filter_by_name($query, 'name');

        $this->json_response(['results' => $results]);
    }
}