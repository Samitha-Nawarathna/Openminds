<?php

// Assuming Controller and other necessary classes (like Database, etc.) are available globally or inherited.

class Announcements extends Controller {

    public function index()
    {
        $this->view('announcements/index');
    }

    public function create()
    {
        $this->view('announcements/create');
    }

    public function edit($id)
    {
        $this->view('announcements/edit', [
            'announcement' => (array) $this->get_announcement_model()->first(['id' => $id])
        ]);
    }

    public function show($id)
    {
        
        $this->view('announcements/view', [
          'announcement' => (array) $this->get_announcement_model()->first(['id' => $id])  
        ]);
    }
    // --- Helper Method ---

    /**
     * Instantiates the Announcements Model.
     * @return Announcements
     */
    private function get_announcement_model()
    {
        return new AnnouncementModel();
    }

    /**
     * Fetches JSON data from the request body.
     * Assuming this method is defined in the base Controller class.
     * @return array|null
     */
    protected function json_request()
    {
        $json = file_get_contents('php://input');
        return json_decode($json, true);
    }
    
    // --- Admin API Functions ---

    public function api_admin_create() {
        $announcement_model = $this->get_announcement_model();
        
        // Get data from JSON payload
        $post_data = $this->json_request(); 
        // Placeholder for getting the logged-in admin's ID (e.g., from session/auth)
        $creator_id = $_SESSION['user_id'] ?? null; 
        
        // Basic validation and preparation
        $data_to_insert = [
            'title' => $post_data['title'] ?? 'No Title',
            'content' => $post_data['content'] ?? 'No Content',
            'style' => $post_data['style'] ?? 'primary-accent',
            'creator_id' => $creator_id,
            'is_active' => 1 // Default to active/visible on creation
        ];

        $new_id = $announcement_model->insert($data_to_insert);
        
        if ($new_id) {
            $data_to_insert['id'] = $new_id;
            $this->json_respond([
                "success" => true,
                "message" => "Announcement '{$data_to_insert['title']}' successfully created.",
                "data" => $data_to_insert
            ]);
        } else {
            $this->json_respond([
                "success" => false,
                "message" => "Failed to create announcement.",
                "data" => []
            ], 500);
        }
    }

    public function api_admin_edit($id) {
        $announcement_model = $this->get_announcement_model();
        
        // Get data from JSON payload
        $post_data = $this->json_request();
        
        // The 'updated_at' column is handled automatically by the database on UPDATE.
        $updated_rows = $announcement_model->update($id, $post_data, 'id');
        
        if ($updated_rows !== false) {
            // Fetch the updated announcement to return the latest data
            $updated_announcement = $announcement_model->first(['id' => $id]); 
            
            $this->json_respond([
                "success" => true,
                "message" => "Announcement ID {$id} successfully edited.",
                "data" => $updated_announcement
            ]);
        } else {
            $this->json_respond([
                "success" => false,
                "message" => "Failed to edit announcement ID {$id} or no changes were made.",
                "data" => []
            ], 400);
        }
    }

    public function api_admin_delete($id) {
        $announcement_model = $this->get_announcement_model();
        
        // Delete the record
        $announcement_model->delete($id, 'id'); 
        
        $this->json_respond([
            "success" => true,
            "message" => "Announcement ID {$id} successfully deleted.",
            "data" => [
                "announcement_id" => $id
            ]
        ]);
    }

    public function api_admin_load_all() {
        // Sanitize and define parameters
        $offset = (int)($_GET['offset'] ?? 0);
        $limit = (int)($_GET['limit'] ?? 5);
        $tab = $_GET['tab'] ?? 'all';
        $searchTerm = $_GET['search'] ?? '';

        $announcement_model = $this->get_announcement_model();
        
        $where_clauses = [];
        $data_params = [];
        
        // 1. Tab Filtering (Status)
        if ($tab === "active") {
            $where_clauses[] = "is_active = 1";
        } elseif ($tab === "hidden") {
            $where_clauses[] = "is_active = 0";
        }

        // 2. Search Filtering (String Matching)
        if (!empty($searchTerm)) {
            // Use LIKE for searching in title OR content
            $where_clauses[] = "(title LIKE :search_term OR content LIKE :search_term)";
            // Bind the search term with wildcards
            $data_params['search_term'] = "%" . $searchTerm . "%";
        }

        // Combine all WHERE clauses
        $where_sql = '';
        if (!empty($where_clauses)) {
            $where_sql = ' WHERE ' . implode(' AND ', $where_clauses);
        }

        // Construct the full SQL query
        $query_sql = "SELECT * FROM announcements" . $where_sql . " ORDER BY id DESC";

        // Execute the query using the custom query method which handles LIMIT/OFFSET
        $announcements = $announcement_model->query($query_sql, $data_params, $limit, $offset);
        
        if ($announcements !== false) {
            $this->json_respond([
                "success" => true,
                "data" => $announcements
            ]);
        } else {
            $this->json_respond([
                "success" => false,
                "message" => "Database query failed."
            ]);
        }
    }

    public function api_admin_hide($id) {
        $announcement_model = $this->get_announcement_model();
        
        $data_to_update = ['is_active' => 0];
        
        $updated = $announcement_model->update($id, $data_to_update, 'id');
        
        if ($updated > 0) {
            $this->json_respond([
                "success" => true,
                "message" => "Announcement ID {$id} has been hidden."
            ]);
        } else {
            $this->json_respond([
                "success" => false,
                "message" => "Failed to hide announcement ID {$id} (or already hidden)."
            ], 400);
        }
    }

    public function api_admin_unhide($id)
    {
        $announcement_model = $this->get_announcement_model();
        
        $data_to_update = ['is_active' => 1];
        
        $updated = $announcement_model->update($id, $data_to_update, 'id');
        
        if ($updated > 0) {
            $this->json_respond([
                "success" => true,
                "message" => "Announcement ID {$id} is now visible."
            ]);
        } else {
            $this->json_respond([
                "success" => false,
                "message" => "Failed to unhide announcement ID {$id} (or already visible)."
            ], 400);
        }
    }
    
    // --- User API Function ---

    public function api_user_load_latest() {
        $announcement_model = $this->get_announcement_model();
        
        $where_data = ['is_active' => 1];
        $limit = 5; 
        $offset = 0;
        
        // Fetches only active announcements. Note: The provided Model->where() lacks ORDER BY.
        // The implementation assumes 'where' returns data that can be reversed for 'latest'.
        $announcements = $announcement_model->where($where_data, $offset, $limit); 
        
        if ($announcements !== false) {
            // Reverse the array to get the "latest" items first, assuming ascending DB order.
            $latest_announcements = array_reverse($announcements);
            
            $this->json_respond([
                "success" => true,
                "latest_count" => count($latest_announcements),
                "data" => $latest_announcements
            ]);
        } else {
            $this->json_respond([
                "success" => true,
                "latest_count" => 0,
                "data" => []
            ]);
        }
    }
}