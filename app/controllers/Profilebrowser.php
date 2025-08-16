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
}