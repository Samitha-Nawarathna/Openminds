<?php

class Test extends Controller
{
    public function index()
    {
        $this->view("test");
    }

    public function test()
    {
        $user = new User;

        echo $user->update("alice", ["display_name"=>"alice123"], "username");
    }
}