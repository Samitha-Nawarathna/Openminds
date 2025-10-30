<?php

class Notes extends Controller
{
    public function index()
    {
        $data = [
            'create_url' => '/your-backend-controller/create-topic-view' // URL for the Create button
        ];
        
        // Helper function to generate mock topic data
        function generate_mock_topics($offset, $limit, $filter_term) {
            $all_topics = [
                ['id' => 't1', 'name' => 'Science', 'creator_id' => 'user_1'],
                ['id' => 't2', 'name' => 'Maths', 'creator_id' => 'user_2'],
                ['id' => 't3', 'name' => 'Linear algebra', 'creator_id' => 'user_1'],
                ['id' => 't4', 'name' => 'Calculus', 'creator_id' => 'user_3'],
                ['id' => 't5', 'name' => 'Integration', 'creator_id' => 'user_4'],
                ['id' => 't6', 'name' => 'Psychology', 'creator_id' => 'user_5'],
                ['id' => 't7', 'name' => 'Biology', 'creator_id' => 'user_1'],
                ['id' => 't8', 'name' => 'Fluid Dynamics', 'creator_id' => 'user_6'],
                ['id' => 't9', 'name' => 'Computer Science', 'creator_id' => 'user_7'],
                ['id' => 't10', 'name' => 'Thermodynamics', 'creator_id' => 'user_8'],
                // ['id' => 't11', 'name' => 'Cosmology', 'creator_id' => 'user_1'], 
                // ['id' => 't12', 'name' => 'Topology', 'creator_id' => 'user_9'],
                // ['id' => 't12', 'name' => 'Topology', 'creator_id' => 'user_9'],
                ['id' => 't13', 'name' => 'Number Theory', 'creator_id' => 'user_10'],
                ['id' => 't14', 'name' => 'Philosophy', 'creator_id' => 'user_11'],
                ['id' => 't15', 'name' => 'Ethics', 'creator_id' => 'user_12'],
            ];
        
            // Simple text filter simulation
            if ($filter_term) {
                $filter_term = strtolower($filter_term);
                $all_topics = array_filter($all_topics, function($t) use ($filter_term) {
                    return str_contains(strtolower($t['name']), $filter_term);
                });
            }
        
            // Apply offset and limit for pagination
            $topics_to_return = array_slice($all_topics, $offset, $limit);
            $has_more = count($all_topics) > ($offset + $limit);
        
            return [
                'topics' => array_values($topics_to_return),
                'has_more' => $has_more,
            ];
        }
        
        // Initial data load for the PHP rendering (first 10 items)
        $data["initial_load"] = generate_mock_topics(0, 10, '');
        $data['recent_topics'] = ["science", "art", "maths", "physics", "chemistry", "history"];
        $data['recent_topic_ids'] = [1, 3, 2, 4, 8, 7];
        $this->view('notes/title', $data);
    }

    public function view_notes($topic_id)
    {
        // $topic_id = $_GET['topic_id'] ?? null;

        // --- MOCK DATA SETUP ---

        $data = [
            'current_user_id' => 'user_1',
            // The context of the note currently being browsed (used for the header pill)
            'browsing_topic_title' => 'Science', 
            'initial_tab' => 'created', 
            'create_url' => '/your-backend-controller/create-note-view'
        ];

        $data['pinned_notes'] = ["science", "art", "maths", "physics", "chemistry", "history"];
        $data['pinned_note_ids'] = [1, 3, 2, 4, 8, 7];

        // Helper function to generate mock notes data based on type
        function generate_mock_notes($type, $offset, $limit) {
            // Mock data for the 'Created' (by user_1) and 'Shared' tabs
            $created_notes = [
                ['id' => 1, 'title' => 'Calculus Basics', 'tag' => 'Maths', 'relation' => 'Created'],
                ['id' => 1, 'title' => 'Quantum Fields', 'tag' => 'Physics', 'relation' => 'Created'],
                ['id' => 1, 'title' => 'Set Theory Axioms', 'tag' => 'Maths', 'relation' => 'Created'],
                ['id' => 1, 'title' => 'A Note on Ethics', 'tag' => 'Philosophy', 'relation' => 'Created'],
                ['id' => 1, 'title' => 'Kinematics in 3D', 'tag' => 'Physics', 'relation' => 'Created'],
            ];

            $shared_notes = [
                ['id' => 1, 'title' => 'Shared: General Relativity', 'tag' => 'Physics', 'relation' => 'Shared'],
                ['id' => 1, 'title' => 'Shared: Python Tips', 'tag' => 'CS', 'relation' => 'Shared'],
                ['id' => 1, 'title' => 'Shared: Thermodynamics', 'tag' => 'Physics', 'relation' => 'Shared'],
                ['id' => 1, 'title' => 'Shared: Abstract Algebra', 'tag' => 'Maths', 'relation' => 'Shared'],
                ['id' => 1, 'title' => 'Shared: Psychology Stats', 'tag' => 'Psychology', 'relation' => 'Shared'],
            ];

            $data_source = ($type === 'created') ? $created_notes : $shared_notes;
            
            // Add extra items for 'Load More' to work
            if ($type === 'created') {
                array_push($data_source, ['id' => 1, 'title' => '6th Created Note', 'tag' => 'Test', 'relation' => 'Created']);
                array_push($data_source, ['id' => 1, 'title' => '7th Created Note', 'tag' => 'Test', 'relation' => 'Created']);
                array_push($data_source, ['id' => 1, 'title' => '8th Created Note', 'tag' => 'Test', 'relation' => 'Created']);
                array_push($data_source, ['id' => 1, 'title' => '9th Created Note', 'tag' => 'Test', 'relation' => 'Created']);
                array_push($data_source, ['id' => 1, 'title' => '10th Created Note', 'tag' => 'Test', 'relation' => 'Created']);
                array_push($data_source, ['id' => 1, 'title' => '11th Created Note (Load More)', 'tag' => 'Test', 'relation' => 'Created']);
            }

            // Apply offset and limit for pagination
            $notes_to_return = array_slice($data_source, $offset, $limit);
            $has_more = count($data_source) > ($offset + $limit);

            return [
                'notes' => array_values($notes_to_return),
                'has_more' => $has_more
            ];
        }

        $data["initial_load"] = generate_mock_notes($data['initial_tab'], 0, 10);

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
            // $topic_data = $topics->first(['name' => $topic]);

            // if (!$topic_data) {
            //     $topic_id = $topics->insert(['name' => $topic, 'creator_id' => $current_user_id]);
            // } else {
            //     $topic_id = $topic_data->id;
            // }

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
            header("Location: ".ROOT."/notes/show?id=" . $note_id);




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
            header("Location: ".ROOT."/notes/show?id=" . $note_id);

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



}