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
        
        // Fetch pinned topics
        $pinned_topics_rows = $topics->where(['pinned' => 1]);
        $pinned_topic_names = [];
        $pinned_topic_ids = [];

        if ($pinned_topics_rows) {
             foreach ($pinned_topics_rows as $row) {
                 $pinned_topic_names[] = $row->name;
                 $pinned_topic_ids[] = $row->id;
             }
        }

        $data = [
            'create_url' => 'topics/create/',
            'initial_load' => [
                'topics' => json_decode(json_encode($topics->filter_and_search($params)), true) ?: [],
                'has_more' => true // logic to check count? For now assume true or check count
            ],
            'recent_topics' => $pinned_topic_names,
            'recent_topic_ids' => $pinned_topic_ids
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
        
        // Fetch pinned notes for this topic
        $pinned_notes_rows = $note_model->where(['topic_id' => $topic_id, 'pinned' => 1]);
        $pinned_note_titles = [];
        $pinned_note_ids = [];

        if ($pinned_notes_rows) {
            foreach ($pinned_notes_rows as $row) {
                $pinned_note_titles[] = $row->title;
                $pinned_note_ids[] = $row->id;
            }
        }
        
        $data = [
            'current_user_id' => $_SESSION['user_id'] ?? 0,
            'browsing_topic_title' => (new Topics())->first(['id' => $topic_id])->name ?? 'Unknown Topic',
            'initial_tab' => 'created', 
            'create_url' => '/notes/create',
            'initial_load' => [
                'notes' => $notes,
                'has_more' => count($notes) >= 10
            ],
            'pinned_notes' => $pinned_note_titles,
            'pinned_note_ids' => $pinned_note_ids
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

        $current_user_id = $_SESSION['user_id'] ?? 1; // Default to 1 to avoid DB null constraint error if session missing
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

            $topic = trim($topic);
            if (empty($topic)) {
                $topic = 'General';
            }

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
            // Log Event
            $event = new Event;
            $event->log($current_user_id, 'note_created', 'Note', $note_id, ['title' => $title, 'subject_id' => 1]); // Default subject ID or fetch from topic

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
            $tags_input = $_POST['tags'] ?? ''; 

            //load required models
            $note_model = new NoteModel;
            $note_tags_model = new NoteTags;
            $tags_model = new Tags;
                
            //retrieve existing note
            $note = $note_model->first(['id' => $note_id]);

            if (!$note) {
                 // Handle 404
                 header("Location: ".ROOT."/notes?error=Note+not+found");
                 exit();
            }

            //validate ownership if not own by current user redirect to note page with errror message
            if ($note->owner_id != $_SESSION['user_id']) {
                 header("Location: ".ROOT."/notes?error=Access+Denied");
                 exit();
            }

            // Update note details
            $note_model->update($note_id, [
                'title' => $title,
                'content' => $content,
                'updated_at' => date("Y-m-d H:i:s") // Assuming your DB uses this format or default Timestamp handles it
            ]);

            // Handle Tags
            
            // 1. Remove existing tags for this note
            // NoteTags model likely extends Model, so we can use delete
            // But Model's delete uses id/id_column. Since note_tags table (note_id, tag_id) typically
            // doesn't have a single 'id' column, but we want to delete all by note_id.
            // Using delete($note_id, 'note_id') should work to delete ALL rows matching note_id.
            $note_tags_model->delete($note_id, 'note_id');
            
            // 2. Re-insert tags
            $tag_list = is_array($tags_input) ? $tags_input : explode(",", $tags_input);

            foreach ($tag_list as $tag_name) {
                $tag_name = trim($tag_name);
                if (empty($tag_name)) continue;

                // Check if tag exists, if not create it
                $tag_data = $tags_model->first(['name' => $tag_name]);
                if (!$tag_data) {
                    $tag_id = $tags_model->insert(['name' => $tag_name]);
                } else {
                    $tag_id = $tag_data->id;
                }

                // Associate tag with note
                $note_tags_model->insert([
                    'note_id' => $note_id,
                    'tag_id' => $tag_id
                ]);
            }

            // Redirect to the note view page after updating
            // Log Event
            $event = new Event;
            $event->log($_SESSION['user_id'], 'note_updated', 'Note', $note_id, ['subject_id' => 1]);

            header("Location: ".ROOT."/notes/view/" . $note_id . "?message=Note+updated+successfully");

            exit();
        }
        // $note_id = $_GET['id'] ?? null;

        $note_model = new NoteModel;
        $note_tags_model = new NoteTags;
        $topics_model = new Topics;
        $tags_model = new Tags;

        $note = $note_model->first(['id' => $note_id]);

        if (!$note) {
             // Handle 404 or redirect
             header("Location: ".ROOT."/notes");
             exit();
        }

        if ($note->owner_id != $_SESSION['user_id']) {
            // Authorized check
             header("Location: ".ROOT."/notes");
             exit();
        }

        // Get Topic Name (if passed to view)
        $topic = $topics_model->first(['id' => $note->topic_id]);
        $topic_name = $topic ? $topic->name : 'Unknown';

        // Get Tags
        $note_tags = $note_tags_model->where(['note_id' => $note_id]);
        $tag_names = [];
        if($note_tags) {
             foreach($note_tags as $nt) {
                 $t = $tags_model->first(['id' => $nt->tag_id]);
                 if($t) $tag_names[] = $t->name;
             }
        }
        
        $note_data = [
            'note' => [
                'id' => $note->id,
                'title' => $note->title,
                'content' => $note->content,
                'tags' => $tag_names,
                'topic' => $topic_name,
                'owner_id' => $note->owner_id
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
        if ($_SERVER['REQUEST_METHOD'] === 'POST' || $_SERVER['REQUEST_METHOD'] === 'GET') {
             $note_id = $_POST['note_id'] ?? $_GET['id'] ?? $_GET['note_id'] ?? '';
            
            $note_model = new NoteModel;
            $note = $note_model->first(['id' => $note_id]);

            if ($note && $note->owner_id == $_SESSION['user_id']) {
                $note_model->delete($note_id);

                // Log Event
                $event = new Event;
                $event->log($_SESSION['user_id'], 'note_deleted', 'Note', $note_id, []);

                // Redirect to topics list or dashboard
                header("Location: ".ROOT."/notes/list/".$note->topic_id."?message=Note+deleted");
                exit();
            } else {
                 header("Location: ".ROOT."/notes?error=Access+Denied");
                 exit();
            }
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

        // Validated ownership and fetch logic
        $note_model = new NoteModel;
        $note = $note_model->first(['id' => $note_id]);
        
        if(!$note || $note->owner_id != $_SESSION['user_id']){
             header("Location: ".ROOT."/notes"); 
             exit();
        }

        $note_shares = new NoteShares;
        $user_model = new User;
        
        $shares = $note_shares->where(['note_id' => $note_id]);
        $shared_users = [];
        
        if($shares){
            foreach($shares as $share){
                $u = $user_model->first(['id' => $share->user_id]);
                if($u) {
                    $shared_users[] = [
                        'id' => $u->id,
                        'name' => $u->username, 
                        'avatar' => $u->image ?? 'assets/images/placeholder.jpg'
                    ];
                }
            }
        }

        $data = [
            'note_id' => $note_id,
            'note_title' => $note->title,
            'initial_shared_users' => $shared_users,
            'form_action_url' => ROOT.'/notes/api/share'
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
        $user_id = $_SESSION['user_id'] ?? 0; // Or handle if not logged in
        if ($user_id) {
            $event = new Event;
            $event->log($user_id, 'note_referred', 'Note', $note_id, ['duration_seconds' => 30]); // Duration is mock for now unless sent from frontend
        }
        
        $this->json_respond([
            "success" => true,
            "message" => "Note referral event successfully stored."
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
    
            $data = $this->json_request();
            $input = !empty($data) ? $data : $_POST;

            $search_params = [
                'offset'    => $input['offset'] ?? 0,
                'limit'     => $input['limit'] ?? 10,
                'where'     => $input['where'] ?? [],
                'where_not' => $input['where_not'] ?? [],
                'like'      => $input['like'] ?? [],
                'order_by'  => $input['order_by'] ?? 'created_at',
                'order_dir' => $input['order_dir'] ?? 'DESC',
                'unique'    => true
            ];
            
            // Allow filtering by owner if not specified or ensure security
            if (!isset($search_params['where']['owner_id'])) {
                 $search_params['where']['owner_id'] = $_SESSION['user_id'];
            }
    
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