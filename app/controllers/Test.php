<?php

class Test extends Controller
{
    public function index()
    {
        $user = new User;
        $id = 31;

        $results = $user->first(['id'=>$id]);
        $image_url = ROOT.$results->profile_picture;

        $this->view("profileupdate", ['display_name'=>$results->display_name, 'image_url'=>$image_url]);
    }

    public function test()
    {
        $user = new User;

        echo $user->update("alice", ["display_name"=>"alice123"], "username");
    }
}