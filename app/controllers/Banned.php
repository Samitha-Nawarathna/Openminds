<?php

class Banned extends Controller
{
    public function index()
    {
        $reason = $_GET['reason'] ?? 'No reason provided';
        
        $this->view("banned", [
            'reason' => urldecode($reason)
        ]);
    }
}
