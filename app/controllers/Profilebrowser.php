<?php

class requestbrowser extends Controller
{
    public function index()
    {
        $this->guard_admin();

        $this->view("requestbrowser");
    }

    public function guard_admin()
    {
        
    }
}