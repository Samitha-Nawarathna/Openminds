<?php

class Analysis extends Controller
{
    public function index()
    {
        // Load the view
        $this->view('analysis/index');
    }

    public function influence()
    {
        $this->view('analysis/influence');
    }

    
    public function reflection()
    {
        $this->view('analysis/reflection');
    }

    
    public function suggestions()
    {
        $this->view('analysis/suggestions');
    }

    public function systemview()
    {
        // Load the system view analysis
        $this->view('analysis/systemview');
    }
}