<?php

class TopicController extends Controller
{

    public function create()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Handle form submission if needed
            exit;
        }

        $this->view('notes/create_title');
    }

    //----------------------------------------------------------//
    //-----------------------AJAX METHODS-----------------------//
    //----------------------------------------------------------//

    /**
     * API to create a new topic.
     */
    public function api_create()
    {
        $data = $this->json_request();

        // In a real application, you would initialize the Topics model here.
        // $topics_model = new Topics();

        $name = trim($data['name'] ?? '');
        $creator_id = $_SESSION['user_id'] ?? 'MOCK_USER_1'; // Use mock user ID if not in session

        if (empty($name)) {
            $this->json_respond(['success' => false, 'message' => 'Topic name cannot be empty.']);
            return;
        }
        
        // MOCK: Check for availability to prevent creating a duplicate
        // In a real app: $is_available = $topics_model->is_name_available($name);
        $is_available = ($name !== 'Mock Topic' && $name !== 'Existing Topic');

        if (!$is_available) {
            $this->json_respond(['success' => false, 'message' => 'Topic name already taken.']);
            return;
        }

        // MOCK: Simulate insertion and generation of a new ID
        $new_topic_id = rand(100, 999); 
        // In a real app: $new_topic_id = $topics_model->insert(['name' => $name, 'creator_id' => $creator_id]);

        if ($new_topic_id) {
            // Return the new topic_id so the frontend can use it for moving notes
            $this->json_respond(['success' => true, 'topic_id' => $new_topic_id, 'name' => $name]);
        } else {
            $this->json_respond(['success' => false, 'message' => 'Failed to create topic.']);
        }
    }

    /**
     * API to check if a topic name is available.
     * MOCK: Hardcodes availability based on a few test names.
     */
    public function api_is_name_available($name)
    {
        // $data = $this->json_request();
        $name = trim($name ?? '');

        if (empty($name)) {
            $this->json_respond(['available' => false, 'message' => 'Name cannot be empty.']);
            return;
        }

        // MOCK: Simulate database check. 'Existing Topic' is the only reserved name.
        $is_available = (strtolower($name) !== 'existing topic');
        
        // Corrected logic: 
        if ($is_available) {
            $this->json_respond(['available' => true, 'message' => 'Topic name is available.']);
            return;
        } else {
            $this->json_respond(['available' => false, 'message' => 'Topic name already exists.']);
            return;
        }
    }


    /**
     * NEW API: Search and filter available notes to assign to a topic.
     * MOCK: Returns a fixed list of notes.
     */
    public function api_search_notes()
    {
        $data = $this->json_request();
        $tags = $data->tags ?? [];
        $query = trim($data->query ?? '');
        $limit = $data->limit ?? 10;
        $offset = $data->offset ?? 0;
        
        // MOCK DATA STRUCTURE
        $all_notes = [
            ['id' => 101, 'title' => 'Quantum Theory of Light', 'tags' => ['physics', 'quantum', 'theory'], 'is_moved' => false],
            ['id' => 102, 'title' => 'Calculus: The Chain Rule', 'tags' => ['math', 'calculus'], 'is_moved' => false],
            ['id' => 103, 'title' => 'The French Revolution', 'tags' => ['history', 'europe', '18th century'], 'is_moved' => false],
            ['id' => 104, 'title' => 'React Hooks Deep Dive', 'tags' => ['programming', 'react', 'javascript'], 'is_moved' => false],
            ['id' => 105, 'title' => 'Stoichiometry Basics', 'tags' => ['chemistry', 'science'], 'is_moved' => false],
            ['id' => 106, 'title' => 'Literature Review Guide', 'tags' => ['academic', 'writing'], 'is_moved' => false],
            ['id' => 107, 'title' => 'Database Normal Forms', 'tags' => ['programming', 'sql', 'database'], 'is_moved' => false],
            ['id' => 108, 'title' => 'Classical Mechanics I', 'tags' => ['physics'], 'is_moved' => false],
            ['id' => 109, 'title' => 'Monet Painting Style', 'tags' => ['art', 'impressionism'], 'is_moved' => false],
            ['id' => 110, 'title' => 'Supply and Demand Curve', 'tags' => ['economics'], 'is_moved' => false],
            ['id' => 111, 'title' => 'Introduction to Algorithms', 'tags' => ['programming'], 'is_moved' => false],
            ['id' => 112, 'title' => 'Evolutionary Biology', 'tags' => ['biology', 'science'], 'is_moved' => false],
        ];

        // MOCK: Simple filtering logic (Real filtering would happen in the model/database)
        $filtered_notes = array_filter($all_notes, function($note) use ($query, $tags) {
            // Check query match (case-insensitive title match)
            $query_match = empty($query) || (stripos($note['title'], $query) !== false);

            // Check tag match (all required tags must be present)
            $tag_match = true;
            if (!empty($tags)) {
                foreach ($tags as $tag) {
                    if (!in_array($tag, $note['tags'])) {
                        $tag_match = false;
                        break;
                    }
                }
            }
            return $query_match && $tag_match;
        });
        
        $filtered_notes = array_values($filtered_notes); // Re-index array

        // MOCK: Apply limit and offset for pagination
        $total_results = count($filtered_notes);
        $notes_slice = array_slice($filtered_notes, $offset, $limit);

        $results_returned = count($notes_slice);
        $next_offset = $offset + $results_returned;
        $has_more = $next_offset < $total_results;

        $this->json_respond([
            "success" => true,
            "results_returned" => $results_returned,
            "next_offset" => $next_offset,
            "has_more" => $has_more,
            "notes" => $notes_slice
        ]);
    }

    /**
     * NEW API: Handles moving selected notes to the newly created topic.
     * MOCK: Simply confirms the action without database interaction.
     */
    public function api_move_notes_to_topic()
    {
        $data = $this->json_request();
        $new_topic_id = $data->new_topic_id ?? null;
        $note_ids = $data->note_ids ?? [];
        $count = count($note_ids);

        if (!$new_topic_id || $count === 0) {
            // Allow this to succeed if the user continues without moving notes
            $this->json_respond([
                "success" => true,
                "message" => "Topic $new_topic_id created. No notes were selected to move.",
                "notes_moved_count" => 0
            ]);
            return;
        }

        // MOCK: Simulate updating note records in the database.
        // In a real app: $notes_model->update_topic_id($note_ids, $new_topic_id);

        $this->json_respond([
            "success" => true,
            "message" => "$count notes successfully moved to topic ID $new_topic_id.",
            "notes_moved_count" => $count
        ]);
    }

    /**
     * NEW API: Helper method for loading more notes in the modal.
     * Note: This currently calls api_search_notes internally, but is left
     * separate to map to the 'Load More Notes' button event.
     */
    public function api_load_more_notes() {
        // Since the logic for load_more is the same as search with an offset change,
        // we can simply call api_search_notes() here in a real application, 
        // or provide specific mock data if needed for testing.
        $this->api_search_notes();
    }
    
    // --- Existing methods below ---

    public function api_all()
    {
        // Placeholder implementation for existing api_all
        $results = [
            // mock data
        ];
        // ... (existing implementation)
    }

    public function api_filter() 
    {
        // Placeholder implementation for existing api_filter
        $data = $this->json_request();
        $query = $data->query ?? '';
        $results = [];

        if (strtolower($query) === 'math') {
            $results[] = ['id' => 1, 'name' => 'Calculus Basics', 'note_count' => 15];
        } else {
             $results[] = ['id' => 2, 'name' => 'History of Rome', 'note_count' => 8];
        }

        if (count($results) > 0) {
            $this->json_respond(['success' => true, 'results' => $results]);
        } else {
            $this->json_respond(['success' => false, 'message' => 'No topics found.']);
        }
    }

    public function api_pin_topic($id) {
        $this->json_respond([
            "success" => true,
            "message" => "Topic 'Theoretical Physics' successfully pinned.",
            "data" => [
                "topic_id" => (int)$id,
                "is_pinned" => true,
                "updated_at" => "2025-11-26 18:36:45"
            ]
        ]);
    }

    public function api_unpin_topic($id) {
        $this->json_respond([
            "success" => true,
            "message" => "Topic 'Theoretical Physics' successfully unpinned.",
            "data" => [
                "topic_id" => (int)$id,
                "is_pinned" => false,
                "updated_at" => "2025-11-26 18:36:45"
            ]
        ]);
    }

    public function api_load_more() {
        $this->json_respond([
            "success" => true,
            "results_returned" => 3,
            "next_offset" => 13,
            "has_more" => false,
            "topics" => [
                ['id' => 11, 'name' => 'Advanced Thermodynamics'],
                ['id' => 12, 'name' => 'Renaissance Art'],
                ['id' => 13, 'name' => 'Data Structures in Python']
            ]
        ]);
    }
}