<?php


class Analysis extends Controller
{
    private $analytics_model;

    public function __construct() {
        $this->analytics_model = new AnalyticsModel();
    }
    
    public function index()
    {
        // Load the view
        $this->view('analysis/index');
    }

    public function influence()
    {
        // View for the Influence dashboard
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
    
    /**
     * API endpoint to retrieve all core dashboard analytics data.
     */
    public function api_dashboard_data()
    {
        // Fetch the data from the model
        $data = $this->analytics_model->generateAllAnalyticsData();
        
        // Set the header to indicate JSON response
        header('Content-Type: application/json');
        
        // Encode and output the data
        echo json_encode($data);
        
        exit;
    }
    
    /**
     * NEW API endpoint to retrieve all influence and QA-related data.
     */
    public function api_influence_data()
    {
        // Fetch the influence data from the model
        $data = $this->analytics_model->generateInfluenceData();
        
        // Set the header to indicate JSON response
        header('Content-Type: application/json');
        
        // Encode and output the data
        echo json_encode($data);
        
        exit;
    }

    public function api_reflection_data()
    {
        // Fetch the reflection data from the model
        $user_id = $_SESSION['user_id'] ?? null;
        $data = $this->analytics_model->generateReflectionData($user_id);
        
        // Set the header to indicate JSON response
        header('Content-Type: application/json');
        
        // Encode and output the data
        echo json_encode($data);
        
        exit;
    }
}