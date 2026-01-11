<?php

class Notes extends Controller
{
    public function index()
    {
        $topics = new Topics();
        $params = [
            'order_by' => 'id',
            'order_dir' => 'DESC',
            'limit' => 10
        ];
        
        $data = [
            'create_url' => 'topics/create/',
            'initial_load' => [
                'topics' => json_decode(json_encode($topics->filter_and_search($params)), true) ?: [],
                'has_more' => true // logic to check count? For now assume true or check count
            ],
            'recent_topics' => ["science", "art", "maths"], // Placeholder or fetch real
            'recent_topic_ids' => [1, 2, 3]
        ];
        
        $this->view('notes/title', $data);
    }

    public function view_notes($topic_id)
    {
        $note_model = new NoteModel();
        
        $params = [
            'where' => ['topic_id' => $topic_id],
            'limit' => 10,
            'offset' => 0
        ];

        // Fetch notes
        $notes = json_decode(json_encode($note_model->filter_and_search($params)), true) ?: [];
        
        $data = [
            'current_user_id' => $_SESSION['user_id'] ?? 0,
            'browsing_topic_title' => (new Topics())->first(['id' => $topic_id])->name ?? 'Unknown Topic',
            'initial_tab' => 'created', 
            'create_url' => '/notes/create',
            'initial_load' => [
                'notes' => $notes,
                'has_more' => count($notes) >= 10
            ],
            'pinned_notes' => [], // Implement pinned logic query if needed
            'pinned_note_ids' => []
        ];

        $this->view('notes/note', $data);
    }

    public function show($note_id)
    {
        // $note_id = $_GET['id'] ?? null;
        //check if note is by current user or shared with current user

        $notes = new NoteModel;
        $note_shares = new NoteShares;
        $note_tags = new NoteTags;
        $topics = new Topics;
        $tags = new Tags;

        $note_data = $notes->first(['id' => $note_id]);
        // show($note_id);

        $topic_id = $note_data ? $note_data->topic_id : null;
        $topic_name = $topics->first(['id' => $topic_id])->name ?? 'Unknown Topic';

        $note_tags = $note_tags->where(['note_id' => $note_id]);

        $tag_names = [];

        foreach ($note_tags as $tag) {
            $tag_names[] = $tags->first(['id' => $tag->tag_id])->name;
        }

        $data = [
            'note' => [
                'id' => $note_id,
                'title' => $note_data->title ?? 'Unknown Note',
                'content' => $note_data->content ?? 'No content available.',
                'tags' => $tag_names,
            ]
        ];

        $this->view('notes/view', $data);
    }

    public function create()
    {

        $current_user_id = $_SESSION['user_id'] ?? null;
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Handle form submission to create a new note
            // show($_POST);
            $title = $_POST['title'] ?? '';
            $content = $_POST['content'] ?? '';
            $tag_list = explode(",", $_POST['tags']) ?? [];
            $topic = $_POST['topic'] ?? '';

            //check validity of inputs
            if (empty($title) || empty($content)) {
                // Handle error - missing required fields
                exit('Title and content are required.');
            }

            //import required models
            $notes = new NoteModel;
            $note_tags = new NoteTags;
            $topics = new Topics;
            $tags = new Tags;

            // Check if topic exists, if not create it
            $topic_data = $topics->first(['name' => $topic]);

            if (!$topic_data) {
                $topic_id = $topics->insert(['name' => $topic, 'creator_id' => $current_user_id]);
            } else {
                $topic_id = $topic_data->id;
            }

            // Create the new note

            $note_id = $notes->insert([
                'title' => $title,
                'content' => $content,
                'owner_id' => $current_user_id,
                'topic_id' => $topic_id
            ]);

            // Handle tags

            foreach ($tag_list as $tag_name) {
                $tag_name = trim($tag_name);
                if (empty($tag_name)) continue;

                // Check if tag exists, if not create it
                $tag_data = $tags->first(['name' => $tag_name]);
                if (!$tag_data) {
                    $tag_id = $tags->insert(['name' => $tag_name]);
                } else {
                    $tag_id = $tag_data->id;
                }

                // Associate tag with note
                $note_tags->insert([
                    'note_id' => $note_id,
                    'tag_id' => $tag_id
                ]);
            }

            //redirect to note view page
            header("Location: ".ROOT."/notes/view/" . $note_id);




            exit();
        }

        $data = [
            'top_tags' => [
                ['name' => 'Physics', 'count' => 12],
                ['name' => 'Psychology', 'count' => 9],
                ['name' => 'Maths', 'count' => 15],
                ['name' => 'design', 'count' => 3],
                ['name' => 'Quantum Computing', 'count' => 7],
            ],
            'form_action_url' => '/your-backend-controller/save-note'
        ];

        $this->view('notes/create', $data);
    }

    public function edit($note_id)
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {

            // Handle form submission to update the note
            $note_id = $_POST['note_id'] ?? '';
            $title = $_POST['title'] ?? '';
            $content = $_POST['content'] ?? '';
            $tags = $_POST['tags'] ?? [];

            //load required models
                
            //retrieve existing note

            //validate ownership if not own by current user redirect to note page with errror message

            // Update note details

            // Redirect to the note view page after updating
            header("Location: ".ROOT."/notes/view/" . $note_id);

            exit();
        }
        // $note_id = $_GET['id'] ?? null;

       // --- MOCK DATA SETUP ---
       $notes = new NoteModel;
       $note_shares = new NoteShares;
       $note_tags = new NoteTags;
       $topics = new Topics;
       $tags = new Tags;

       $note_data = $notes->first(['id' => $note_id]);
       // show($note_id);

       $topic_id = $note_data ? $note_data->topic_id : null;
       $topic_name = $topics->first(['id' => $topic_id])->name ?? 'Unknown Topic';

       $note_tags = $note_tags->where(['note_id' => $note_id]);

       $tag_names = [];

       foreach ($note_tags as $tag) {
           $tag_names[] = $tags->first(['id' => $tag->tag_id])->name;
       }

       $note_data = [
           'note' => [
               'id' => $note_id,
               'title' => $note_data->title ?? 'Unknown Note',
               'content' => $note_data->content ?? 'No content available.',
               'tags' => $tag_names,
               'topic' => $topic_name
           ]
       ];

        $data = [
            'top_tags' => [
                ['name' => 'Physics', 'count' => 12],
                ['name' => 'Psychology', 'count' => 9],
                ['name' => 'Maths', 'count' => 15],
                ['name' => 'design', 'count' => 3],
                ['name' => 'Quantum Computing', 'count' => 7],
            ],
            // 'form_action_url' => '/your-backend-controller/update-note'
        ];

        $this->view('notes/edit', array_merge($data, ['note' => $note_data['note']]));
    }

    public function delete()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            $note_id = $_POST['note_id'] ?? '';
            // Load required models

            // Validate ownership if not own by current user redirect to note page with error message

            // Perform deletion logic here (e.g., remove from database)

            // Redirect to notes list after deletion
            header("Location: ".ROOT."/notes/notes?message=Note+".$note_id."+deleted+successfully");
            exit();
        }
    }

    public function share()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $note_id = $_POST['note_id'] ?? '';
            $share_with_user_ids = $_POST['share_with_user_ids'] ?? [];

            exit();
        }

        $note_id = $_GET['note_id'] ?? null;

        // --- MOCK DATA SETUP ---
        $data = [
            'note_id' => 'note_42_share',
            'note_title' => 'Color theory',
            // Users currently shared with
            'initial_shared_users' => [

            ],
            'form_action_url' => '/your-backend-controller/share-note'
        ];

        $this->view('notes/share', $data);
    }


    //----------------------------------------------------------//
    //-----------------------AJAX METHODS-----------------------//
    //----------------------------------------------------------//

    public function api_search_notes_by_tags()
    {
        //read tags from post
        $data = $this->json_request();

        //import note model
        $note_model = new NoteModel;

        //get notes
        $tags = $data['tags'] ?? [];
        $notes = $note_model->search_by_tags($tags, $user_id ?? 1);

        $note_data = [];

        foreach ($notes as $row) {
            //prepare each note data
            $note_data[] = [
                'id' => $row->id,
                'title' => $row->title,
            ];
        }

        //prepare json respond
        $this->json_respond([
            'status' => 'success',
            'notes' => $note_data
        ]);
    }

    public function api_get_note_by_id($note_id)
    {

        if (!$note_id) {
            $this->json_respond([
                'status' => 'error',
                'message' => 'Note ID is required'
            ]);
            return;
        }

        
        //import note model
        $note_model = new NoteModel;
        $note = $note_model->first(['id' => $note_id]);

        //compare owner id and session id
        $user_id = $_SESSION['user_id'];

        if(!$user_id)
        {
            $this->json_respond([
                'status' => 'error',
                'message' => 'User not logged in'
            ]);
            return;
        }

        if($note->owner_id != $user_id)
        {
            $this->json_respond([
                'status' => 'error',
                'message' => 'Note not found or access denied'
            ]);
            return;
        }

        //get note topic name
        $topics = new Topics;
        $topic = $topics->first(['id' => $note->topic_id]);
        $note->topic_name = $topic ? $topic->name : 'Unknown Topic';

        //get all the tags
        $note_tags_model = new NoteTags;
        $note_tags = $note_tags_model->where(['note_id' => $note_id]);

        $tags_model = new Tags;
        $tag_names = [];

        foreach ($note_tags as $tag) {
            $tag_data = $tags_model->first(['id' => $tag->tag_id]);
            if ($tag_data) {
                $tag_names[] = $tag_data->name;
            }
        }

        if ($note) {
            $note_data = [
                'id' => $note->id,
                'title' => $note->title,
                'content' => $note->content,
                'topic' => [
                    'id' => $note->topic_id,
                    'name' => $note->topic_name
                ],
                'tags' => $tag_names
            ];


            $this->json_respond([
                'status' => 'success',
                'note' => $note_data
            ]);
        } else {
            $this->json_respond([
                'status' => 'error',
                'message' => 'Note not found'
            ]);
        }
    }

    public function api_share()
    {
        $data = $this->json_request();

        $note_id = $data['note_id'] ?? null;
        $share_with_user_ids = $data['share_with_user_ids'] ?? [];

        if (!$note_id || empty($share_with_user_ids)) {
            $this->json_respond([
                'status' => 'error',
                'message' => 'Note ID and user IDs are required'
            ]);
            return;
        }

        //import note shares model
        $note_shares = new NoteShares;

        //share note
        foreach ($share_with_user_ids as $user_id) {
            $note_shares->insert([
                'note_id' => $note_id,
                'user_id' => $user_id
            ]);
        }

        $this->json_respond([
            'status' => 'success',
            'message' => 'Note shared successfully'
        ]);
    }

    public function api_pin_note($id) {
        $note_model = new NoteModel();
        if ($note_model->pin($id)) {
            $this->json_respond([
                "success" => true,
                "message" => "Note successfully pinned.",
                "data" => ["note_id" => (int)$id, "is_pinned" => true]
            ]);
        } else {
             $this->json_respond(["success" => false, "message" => "Failed to pin note."]);
        }
    }

    public function api_unpin_note($id) {
        $note_model = new NoteModel();
        if ($note_model->unpin($id)) {
            $this->json_respond([
                "success" => true,
                "message" => "Note successfully unpinned.",
                "data" => ["note_id" => (int)$id, "is_pinned" => false]
            ]);
        } else {
             $this->json_respond(["success" => false, "message" => "Failed to unpin note."]);
        }
    }

    public function api_filter() {
        $data = $this->json_request();
        // Map frontend filter params to backend model params
        $params = [
            'where' => [],
            'like' => []
        ];

        if (!empty($data['topic_id'])) {
            $params['where']['topic_id'] = $data['topic_id'];
        }
         // Add other filters as needed

        $note_model = new NoteModel();
        $results = $note_model->filter_and_search($params);
        
        $this->json_respond([
            "success" => true,
            "data" => $results
        ]);
    }

    public function api_store_refer_event($note_id) {
        // Assume logic to log note referral event for $note_id
        $this->json_respond([
            "success" => true,
            "message" => "Note referral event successfully stored.",
            "data" => [
                "note_id" => (int)$note_id,
                "user_id" => 42,
                "event_type" => "note_referred",
                "referral_count" => 5
            ]
        ]);
    }

    public function api_load_more() {
        $data = $this->json_request();
        $offset = $data['offset'] ?? 0;
        $limit = $data['limit'] ?? 5;
        $topic_id = $data['topic_id'] ?? null;

        $params = [
            'limit' => $limit,
            'offset' => $offset,
            'where' => []
        ];

        if ($topic_id) {
             $params['where']['topic_id'] = $topic_id;
        }

        $note_model = new NoteModel();
        $notes = $note_model->filter_and_search($params) ?: [];
        
        $this->json_respond([
            "success" => true,
            "results_returned" => count($notes),
            "next_offset" => $offset + count($notes),
            "available_more" => count($notes) >= $limit,
            "data" => $notes
        ]);
    }

        // --- New API Endpoint for Search and Filtering ---
        public function api_search_notes()
        {
            // 1. Instantiate the Model
            $note_model = new NoteModel();
    
            // --- 2. Define MOCK SEARCH PARAMETERS for Testing ---
            // Modify these parameters to test different filtering scenarios.
            $search_params = [
                // PAGINATION
                'offset'    => 0, // Start at the first record (page 1)
                'limit'     => 10, // Fetch 10 records per "page"
    
                // EQUALITY FILTERS (WHERE column IN (values))
                'where'     => [
                    // Example: Only show notes from owner IDs 94 and 101
                    // 'owner_id'      => [94, 101], 
                    // Example: Only show pinned notes (pinned = 1)
                    // 'pinned'        => [1], 
                ],
    
                // INEQUALITY FILTERS (WHERE column NOT IN (values))
                'where_not' => [
                    // Example: Exclude notes associated with topic ID 6
                    'topic_id'      => [6],
                ],
    
                // STRING MATCHING (WHERE column LIKE '%term%')
                'like'      => [
                    // Example: Search for the word 'science' in the title OR content
                    'title'     => 'science',
                    // 'content'   => 'science', 
                ],
                
                // NUMERICAL/DATE RANGE FILTERS (WHERE column BETWEEN min AND max)
                // 'range'     => [
                //     // Example: Only show notes created in the last 6 weeks (adjust date as needed)
                //     'created_at' => [
                //         '2025-10-01 00:00:00', // Start Date
                //         '2025-12-31 23:59:59', // End Date
                //     ],
                // ],
    
                // ORDERING
                'order_by'  => 'created_at',
                'order_dir' => 'DESC', // Newest first
                'unique' => true
            ];
            
            // --- To test the next page of results, change the offset: ---
            // $search_params['offset'] = 10; 
    
            // --- To test a different search term: ---
            // $search_params['like']['title'] = 'algebra';
            // $search_params['like']['content'] = 'algebra';
            // $search_params['where']['pinned'] = [0]; // Unpinned notes
    
            // 3. Execute the search
            $results = $note_model->filter_and_search($search_params);
    
            // 4. Return the results as JSON
            if ($results !== false) {
                $this->json_respond([
                    "success" => true,
                    "total_results" => count($results), // Note: This is only the count of the LMITED results
                    "next_offset" => $search_params['offset'] + $search_params['limit'],
                    "data" => $results
                ]);
            } else {
                $this->json_respond([
                    "success" => false,
                    "message" => "Failed to execute search query."
                ], 500); // 500 Internal Server Error
            }
        }
        


}