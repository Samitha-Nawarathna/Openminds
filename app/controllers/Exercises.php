<?php

class Exercises extends Controller
{
    public function index()
    {
        $role = strtolower(trim((string)($_SESSION['role'] ?? 'student')));
        $user_id = $_SESSION['user_id'] ?? 0;

        if (!in_array($role, ['student', 'mentor', 'expert', 'admin'], true)) {
        $role = 'student';
        }
        $can_create = ($role === 'mentor');
        $can_edit = $can_create;

        // Initial load parameters
        $initial_tab = 'all';
        $initial_offset = 0;
        $initial_limit = 5;

        $exerciseModel = new ExercisesModel();
        $expert_subject_ids = $this->get_expert_subject_ids($role, $user_id);
        $initial_load_result = $exerciseModel->get_browser_list([
            'role' => $role,
            'user_id' => $user_id,
            'tab' => $initial_tab,
            'offset' => $initial_offset,
            'limit' => $initial_limit,
            'expert_subject_ids' => $expert_subject_ids,
            
        ]);

        $initial_exercises = array_map(function ($row) {
            return (array)$row;
        }, $initial_load_result['rows'] ?? []);

        $data = [
            'current_user_id' => $user_id,
            'initial_tab' => $initial_tab, 
            'create_url' => '/your-backend-controller/create-exercise-view',
            // NEW: Initial exercises data is stored here
            'initial_exercises' => $initial_exercises,
            'initial_has_more' => ($initial_offset + $initial_limit) < ($initial_load_result['total'] ?? 0),
            'initial_limit' => $initial_limit,
            'initial_offset' => $initial_offset,
            'role' => $role,
            'can_create' => $can_create,
            'can_edit' => $can_edit,
        ];
        $this->view('exercises/browser', $data);
    }



    public function create(){

        // GET: show the editor page
        if ($this->is_get()) {
            $this->view('exercises/create');
            return;
        }

        // POST to /exercises/create (non-API form submit) - keep behavior minimal for now
        if ($this->is_post()) {
            // If called as a normal form post, redirect to the view (not used by SPA)
            header('Location: '.ROOT.'/exercises/create');
            exit;
        }
    }

    /**
     * API: GET /exercises/api/subjects
     * Returns subject list for exercise create form dropdown.
     */
    public function api_subjects()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Method Not Allowed']);
            return;
        }

        try {
            $subjectsModel = new Subjects();
            $rows = $subjectsModel->where([]) ?: [];

            $subjects = array_map(function ($row) {
                return [
                    'id' => (int)($row->id ?? 0),
                    'name' => trim((string)($row->name ?? '')),
                ];
            }, $rows);

            $subjects = array_values(array_filter($subjects, function ($subject) {
                return $subject['id'] > 0 && $subject['name'] !== '';
            }));

            usort($subjects, function ($a, $b) {
                return strcasecmp($a['name'], $b['name']);
            });

            $this->json_respond([
                'success' => true,
                'subjects' => $subjects,
            ]);
        } catch (Exception $e) {
            error_log('api_subjects error: ' . $e->getMessage());
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Failed to load subjects.']);
            return;
        }
    }

    /**
     * API: POST /api/exercises/create
     * Accepts JSON body and inserts exercise, questions, answers, and tags in a single transaction.
     */
    public function api_create()
    {
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Method Not Allowed']);
            return;
        }

        $data = $this->json_request();
        // $current_user = $_SESSION['user_id'] ?? null;
        $current_user = $_SESSION['user_id'] ?? 2;

        // Require authentication
        if (!$current_user) {
            http_response_code(401);
            echo json_encode(['success' => false, 'message' => 'Authentication required to create exercises.']);
            return;
        }

        $metadata = $data['metadata'] ?? null;
        $questions = $data['questions'] ?? null;

        if (empty($metadata) || empty($metadata['title']) || !isset($metadata['subject']) || !is_array($questions)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Invalid request: missing required metadata or questions.']);
            return;
        }

        $subject_id = (int)$metadata['subject'];
        if ($subject_id <= 0) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Invalid subject selection.']);
            return;
        }

        if (count($questions) < 3) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'An exercise must have at least 3 questions.']);
            return;
        }

        // Validate questions and options
        foreach ($questions as $qi => $q) {
            $q_text = trim($q['question_text'] ?? $q['prompt'] ?? '');
            $q_expl = trim($q['explanation'] ?? '');
            $weight = isset($q['weight']) ? intval($q['weight']) : 0;
            $opts = $q['options'] ?? [];

            if ($q_text === '' || $q_expl === '' || $weight < 1) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => "Invalid question at index $qi: ensure question_text, explanation and weight >= 1 are provided."]); 
                return;
            }

            if (!is_array($opts) || count($opts) < 2) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => "Question at index $qi must have at least two options."]); 
                return;
            }

            $hasCorrect = false;
            foreach ($opts as $opt) {
                if (!isset($opt['answer_text']) && !isset($opt['text'])) {
                    http_response_code(400);
                    echo json_encode(['success' => false, 'message' => "Each option must include answer_text/text for question index $qi."]); 
                    return;
                }
                if (!empty($opt['is_correct']) || !empty($opt['isCorrect'])) $hasCorrect = true;
            }

            if (!$hasCorrect) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => "Please mark at least one correct option for question index $qi."]); 
                return;
            }
        }

        // Good to go --- perform DB operations in a transaction
        try {
            // Create PDO and begin transaction
            $pdo = new PDO("mysql:host=".DBHOST.";dbname=".DBNAME.";charset=utf8mb4", DBUSER, DBPASS, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
            $pdo->beginTransaction();

            // Resolve selected subject from dropdown value
            $subjects = new Subjects();
            $subject_exists = $subjects->first(['id' => $subject_id]);
            if (!$subject_exists) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Selected subject does not exist.']);
                return;
            }

            // Insert exercise
            $stmt = $pdo->prepare("INSERT INTO exercises (subject_id, title, description, creator_id, status, created_at) VALUES (:subject_id, :title, :description, :creator_id, :status, :created_at)");
            $stmt->execute([
                ':subject_id' => $subject_id,
                ':title' => $metadata['title'],
                ':description' => $metadata['description'] ?? null,
                ':creator_id' => $current_user,
                ':status' => 'pending',
                ':created_at' => date('Y-m-d H:i:s')
            ]);

            $exercise_id = $pdo->lastInsertId();

            // Tags
            $tags_string = $metadata['tags'] ?? '';
            $tags_array = array_filter(array_map('trim', explode(',', $tags_string)));

            foreach ($tags_array as $tag_name) {
                // Find or create tag
                $tagStmt = $pdo->prepare("SELECT id FROM tags WHERE name = :name LIMIT 1");
                $tagStmt->execute([':name' => $tag_name]);
                $tagRow = $tagStmt->fetch(PDO::FETCH_ASSOC);

                if ($tagRow) {
                    $tag_id = $tagRow['id'];
                } else {
                    $insTag = $pdo->prepare("INSERT INTO tags (name) VALUES (:name)");
                    $insTag->execute([':name' => $tag_name]);
                    $tag_id = $pdo->lastInsertId();
                }

                // Insert relation (exercisetag)
                $insRel = $pdo->prepare("INSERT INTO exercisetag (exercise_id, tag_id) VALUES (:exercise_id, :tag_id)");
                $insRel->execute([':exercise_id' => $exercise_id, ':tag_id' => $tag_id]);
            }

            // Insert questions and options
            $qStmt = $pdo->prepare("INSERT INTO exercisequestion (question_text, explanation, weight, exercise_id, display_order) VALUES (:question_text, :explanation, :weight, :exercise_id, :display_order)");
            $optStmt = $pdo->prepare("INSERT INTO exerciseanswer (answer_text, is_correct, display_order, question_id) VALUES (:answer_text, :is_correct, :display_order, :question_id)");

            foreach ($questions as $qi => $q) {
                $question_text = trim($q['question_text'] ?? $q['prompt']);
                $explanation = trim($q['explanation']);
                $weight = intval($q['weight']);

                $qStmtParams = [
                    ':question_text' => $question_text,
                    ':explanation' => $explanation,
                    ':weight' => $weight,
                    ':exercise_id' => $exercise_id,
                    ':display_order' => $qi
                ];

                $qStmt->execute($qStmtParams);
                $question_id = $pdo->lastInsertId();

                $opts = $q['options'];
                foreach ($opts as $oi => $opt) {
                    $answer_text = trim($opt['answer_text'] ?? $opt['text']);
                    $is_correct = (!empty($opt['is_correct']) || !empty($opt['isCorrect'])) ? 1 : 0;

                    $optStmt->execute([
                        ':answer_text' => $answer_text,
                        ':is_correct' => $is_correct,
                        ':display_order' => $oi,
                        ':question_id' => $question_id
                    ]);
                }
            }

            $pdo->commit();

            http_response_code(201);
            echo json_encode(['success' => true, 'message' => 'Exercise created successfully', 'id' => $exercise_id]);
            return;

        } catch (Exception $e) {
            if (isset($pdo) && $pdo->inTransaction()) $pdo->rollBack();
            // Log detailed error for server logs
            error_log('Exercise creation error: ' . $e->getMessage() . "\n" . $e->getTraceAsString());
            // Return a useful message in development to aid debugging
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Database error while creating exercise', 'error' => $e->getMessage()]);
            return;
        }
    }


    public function attempt()
    {
        $exercise_id = isset($_GET['id']) ? (int)$_GET['id'] : null;

        if (!$exercise_id) {
            header('Location: '.ROOT.'/exercises?message=Exercise ID is required to attempt an exercise');
            return;
        }

        $exercises = new ExercisesModel;
        $exercise = $exercises->first(['id' => $exercise_id]);

        if (!$exercise || $exercise->status !== 'approved') {
            header('Location: '.ROOT.'/exercises?message=Exercise is not approved for attempts');
            return;
        }

        $data = [
            'exercise_id' => (int)$exercise_id,
            'exercise_title' => $exercise->title,
        ];

        $this->view('exercises/attempt', $data);
    }


    public function show()
    {
        //only accessible to creator
        $exercise_id = $_GET['id'] ?? 1;
        if (!$exercise_id) {
            // Handle missing exercise ID (e.g., redirect or show error)
            header('Location: '.ROOT.'/exercises?message=Exercise ID is required to attempt an exercise');
        }

        $exercises = new ExercisesModel;
        $exercise_data = $exercises->first(['id' => $exercise_id]);

        if(!$exercise_data->status === 'approved'){
            header('Location: '.ROOT.'/exercises?message=Exercise is not approved for attempts');
        }

        $user = new User;
        $subject = new Subjects;
        $exercise_tag = new ExerciseTag;
        $user_vote_exercise = new UserVoteExercise;
        $tags = new Tags;
        $exercisequestion = new Exercisequestion;
        $exerciseanswer = new Exerciseanswer;


        $creator = $user->first(['id' => $exercise_data->creator_id])->username ?? 'Unknown';
        $subject_name = $subject->first(['id' => $exercise_data->subject_id])->name ?? 'Unknown Subject';

        $tag_in_exercise = $exercise_tag->where(['exercise_id' => $exercise_id]);
        $tags_list = [];

        foreach ($tag_in_exercise as $key => $tag) {
             $tag_info = $subject->first(['id' => $tag->tag_id]);
            $tag_list[] = $tags->first(['id' => $tag->tag_id])->name ?? 'Unknown Tag';
        }

        $votes = $user_vote_exercise->where(['exercise_id' => $exercise_id]);
    
        $upvotes = 0;
        $downvotes = 0;
        $user_vote_status = 'none';

        foreach ($votes as $vote) {
            if ($vote->votetype === 'upvote') {
                $upvotes++;
            } elseif ($vote->votetype === 'downvote') {
                $downvotes++;
            }

            // if ($vote->user_id === $current_user->id) {
            //     $user_vote_status = $vote->vote_type;
            // }
        }



        if (!$exercise_data) {
            // Handle case where exercise is not found
            header('Location: '.ROOT.'/exercises?message=Exercise not found');
        }

        $questions = $exercisequestion->where(['exercise_id' => $exercise_id]);
        
        if (empty($questions)) {
            // Handle case where no questions are found for the exercise
            header('Location: '.ROOT.'/exercises/attempt?id='.$exercise_id.'&message=No questions found for this exercise');
        }

        $question_list = [];


        foreach ($questions as $question) {
            $answers = $exerciseanswer->where(['question_id' => $question->id]);
            $answer_options = [];

            foreach ($answers as $ans) {
                $answer_options[] = $ans->answer_text;
            }

            $question_list[] = [
                'id' => $question->id,
                'question_text' => $question->question_text,
                'options' => $answer_options
            ];
            
        };

        // remember to fetch review data too
        $review_data = [
            'average_score' => 0.8,
        ];


        // --- MOCK DATA SETUP ---
        $role = $_SESSION['role'] ?? 'student';
        $can_edit = in_array($role, ['expert', 'admin'], true);

        $data = [
            'exercise_details' => [
                'id' => $exercise_data->id,
                'title' => $exercise_data->title,
                'creator' => $creator,
                'role' => 'under '.$subject_name,
                'created_at' => $exercise_data->created_at,
                'tags' => $tag_list,
                'upvotes' => $upvotes,
                'downvotes' => $downvotes,
                'user_vote_status' => 'upvote', // possible values: 'upvoted', 'downvoted', 'none'
                
            ],
            'questions' => $question_list,
            'review_data' => $review_data,
            'can_edit' => $can_edit
        ];

        $this->view('exercises/view', $data);
        
    }

    public function edit()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // --- POST (Update Logic) ---
            
            // Load relevant models (ensure ExerciseQuestion and ExerciseAnswer models are used)
            $exercise_id = $_POST['exercise_id'] ?? 1;
    
            // Validation and Permission checks (omitted for brevity, but should be here)
    
            // **TODO: Implement the update logic for all exercise, question, and answer data**
    
            // After update redirect to show page
            header('Location: '.ROOT.'/exercises/show?id='.$exercise_id);
        } else {
            // --- GET (Load Edit View Logic) ---
            $this->view('exercises/edit', []);
            
            $exercise_id = $_GET['id'] ?? null; // Changed default to null for proper check
    
            if (!$exercise_id) {
                header('Location: '.ROOT.'/exercises?message=Exercise ID is required to edit an exercise');
                return; // Use return after header
            }
    
            // --- Load Models ---
            // Assuming your models are named as shown in the original code
            $exercises = new ExercisesModel;
            $exercisequestion = new Exercisequestion;
            $exerciseanswer = new Exerciseanswer;
            $tags = new Tags;
            $subject = new Subjects;
            $exercise_tag = new ExerciseTag;
            
            // --- 1. Fetch Exercise Metadata ---
            $exercise_data = $exercises->first(['id' => $exercise_id]);
    
            if (!$exercise_data) {
                header('Location: '.ROOT.'/exercises?message=Exercise not found');
                return;
            }
    
            // Note: The status check below seems intended to restrict editing of approved exercises. 
            // We'll keep the original logic but be mindful it might need adjustment (e.g., status should be 'draft').
            if($exercise_data->status === 'approved'){
                 header('Location: '.ROOT.'/exercises?message=Exercise is approved and cannot be edited');
                 return;
            }
    
            // --- 2. Fetch Questions and Answers ---
            // Fetch questions, ideally ordered by the new `display_order` column
            // Assuming the model supports a simple WHERE condition for now:
            $questions = $exercisequestion->where(['exercise_id' => $exercise_id]);
            
            $question_list = [];
    
            foreach ($questions as $question) {
                // Fetch answers for the current question, ideally ordered by the new `display_order` column
                $answers = $exerciseanswer->where(['question_id' => $question->id]);
                $answer_options = [];
    
                foreach ($answers as $ans) {
                    // Construct the front-end option object, including new fields
                    $answer_options[] = [
                        // 'id' => $ans->id, // Use if needed for internal JS tracking
                        'text' => $ans->answer_text,
                        'isCorrect' => (bool)$ans->is_correct, // Cast to boolean for JS
                        // 'displayOrder' => $ans->display_order // Use if fetched from model
                    ];
                }
    
                // Construct the front-end question object, including new fields
                $question_list[] = [
                    'id' => $question->id,
                    'prompt' => $question->question_text,
                    'explanation' => $question->explanation, // NEW FIELD
                    'weight' => $question->weight,           // NEW FIELD
                    'options' => $answer_options,
                    // 'displayOrder' => $question->display_order // Use if fetched from model
                ];
            }
            
            // --- 3. Format Metadata (for front-end) ---
            $tag_in_exercise = $exercise_tag->where(['exercise_id' => $exercise_id]);
            $tags_list = [];
    
            foreach ($tag_in_exercise as $tag) {
                $tags_list[] = $tags->first(['id' => $tag->tag_id])->name ?? 'Unknown Tag';
            }
    
            // --- 4. Package all data for the view ---
            // We must fetch and pass the subject name and tags list as a comma-separated string
            $subject_name = $subject->first(['id' => $exercise_data->subject_id])->name ?? 'Unknown Subject';
    
            $data = [
                // This is the core data used to initialize the JS state (EXERCISE_METADATA and EXERCISE_QUESTIONS)
                'initial_data' => [
                    'metadata' => [
                        'id' => $exercise_data->id,
                        'title' => $exercise_data->title,
                        'subjectId' => $exercise_data->subject_id, // Pass ID for potential future use
                        'subject' => $subject_name,
                        'description' => $exercise_data->description ?? '', // NEW FIELD
                        'tags' => implode(', ', $tags_list), // Format as a comma-separated string for the input field
                    ],
                    'questions' => $question_list,
                ],
                
                // Other data for the 'edit' view (like creator, votes, etc. from original code)
                'exercise_details' => [
                    'exercise_id' => $exercise_data->id,
                    'title' => $exercise_data->title,
                    'subject' => $subject_name,
                    // ... (other fields for exercise summary)
                ],
                
                // The view will need the formatted `initial_data` to inject into the front-end script.
                // The original logic for votes and creator is retained but not necessary for the builder.
                // ... (Votes and Review data)
            ];
    
            $this->view('exercises/edit', $data); // Assuming you are reusing the 'create' view for editing
        }
    }
    public function delete()
    {
        // Process exercise deletion
        $exercise_id = $_POST['id'] ?? null;

        if ($exercise_id) {
            //load relevant models

            //delete related data first due to foreign key constraints in database following order:
            //delete answers
            //delete questions

            //finally delete the exercise
            
            //show success message
            header('Location: '.ROOT.'/exercises?message=Exercise deleted successfully');
        } else {
            header('Location: '.ROOT.'/exercises?message=Invalid exercise ID');
        }
    }

    public function hide()
    {
        $exercise_id = $_GET['id'] ?? null;
        header('Location: '.ROOT.'/exercises/show?id='.$exercise_id.'&message=Hide exercise triggered');
    }

    public function expertreview()
    {
        // ============= EXPERT ROLE CHECK =============
        // Only expert users can access pending exercise reviews
        if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'expert') {
            header('Location: ' . ROOT . 'home?message=Only experts can review exercises');
            exit;
        }

        $current_expert_id = $_SESSION['user_id'];
        
        // If GET request with exercise ID, load specific exercise for review
        if ($this->is_get()) {
            $exercise_id = $_GET['id'] ?? null;

            if (!$exercise_id) {
                header('Location: ' . ROOT . 'exercises?message=Exercise ID is required');
                exit;
            }

            $exercise_id = (int)$exercise_id;

            try {
                $pdo = $this->db();
                
                // Fetch exercise with creator and subject info
                $exerciseStmt = $pdo->prepare("
                    SELECT e.id, e.title, e.status, e.subject_id, e.creator_id, e.created_at, 
                           s.name AS subject_name, u.username AS creator_name
                    FROM exercises e 
                    LEFT JOIN subjects s ON s.id = e.subject_id
                    LEFT JOIN user u ON u.id = e.creator_id
                    WHERE e.id = :id AND e.status = 'pending'
                    LIMIT 1
                ");
                $exerciseStmt->execute([':id' => $exercise_id]);
                $exercise = $exerciseStmt->fetch(PDO::FETCH_ASSOC);

                if (!$exercise) {
                    header('Location: ' . ROOT . 'exercises?message=Exercise not found or not pending');
                    exit;
                }

                // Check: Expert cannot review their own exercises
                if ($exercise['creator_id'] == $current_expert_id) {
                    header('Location: ' . ROOT . 'exercises?message=You cannot review your own exercises');
                    exit;
                }

                // Fetch bundle data (questions, options, etc)
                $bundle = $this->fetchExerciseBundle($pdo, $exercise_id);

                $data = [
                    'exercise_id' => $exercise_id,
                    'exercise_title' => $exercise['title'],
                    'creator_name' => $exercise['creator_name'],
                    'subject_name' => $exercise['subject_name'],
                    'created_at' => $exercise['created_at'],
                    'mode' => 'review',
                ];

                $this->view('exercises/attempt', $data);

            } catch (Exception $e) {
                error_log('expertreview error: ' . $e->getMessage());
                header('Location: ' . ROOT . 'exercises?message=Error loading exercise');
                exit;
            }
        }
        // If POST request, this is the actual review submission (handled by JS)
        else if ($this->is_post()) {
            // This is typically handled by api_approve_exercise() or api_reject_exercise()
            header('Location: ' . ROOT . 'exercises');
            exit;
        }
    }

    public function viewattempt($exercise_id, $attempt_id)
    {
        $exercise_id = (int)$exercise_id;
        $attempt_id = (int)$attempt_id;

        if ($exercise_id <= 0 || $attempt_id <= 0) {
            header('Location: '.ROOT.'/exercises?message=Invalid attempt reference');
            return;
        }

        $exercises = new ExercisesModel;
        $exercise = $exercises->first(['id' => $exercise_id]);

        if (!$exercise || $exercise->status !== 'approved') {
            header('Location: '.ROOT.'/exercises?message=Exercise not available');
            return;
        }

        $data = [
            'exercise_id' => $exercise_id,
            'attempt_id' => $attempt_id,
            'exercise_title' => $exercise->title,
        ];

        $this->view('exercises/viewattempt', $data);
    }

    /**
     * API: POST /exercises/api/approve
     * Approves a pending exercise.
     */
    public function api_approve_exercise()
    {
        // Only experts can approve
        if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'expert') {
            $this->json_error('Only experts can approve exercises', 403);
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->json_error('Method not allowed', 405);
        }

        $data = $this->json_request();
        $exercise_id = (int)($data['exercise_id'] ?? 0);
        $current_expert_id = $_SESSION['user_id'];

        if (!$exercise_id) {
            $this->json_error('Missing exercise ID', 400);
        }

        try {
            $pdo = $this->db();

            // Fetch the exercise
            $exerciseStmt = $pdo->prepare("SELECT id, status, creator_id FROM exercises WHERE id = :id LIMIT 1");
            $exerciseStmt->execute([':id' => $exercise_id]);
            $exercise = $exerciseStmt->fetch(PDO::FETCH_ASSOC);

            if (!$exercise) {
                $this->json_error('Exercise not found', 404);
            }

            if ($exercise['status'] !== 'pending') {
                $this->json_error('Exercise is not pending', 400);
            }

            // Check: Expert cannot review their own exercises
            if ($exercise['creator_id'] == $current_expert_id) {
                $this->json_error('You cannot approve your own exercises', 403);
            }

            // Update exercise status to 'approved'
            // IMPORTANT: Only update status, reviewed_by, and updated_at - DO NOT modify schema
            $updateStmt = $pdo->prepare("
                UPDATE exercises 
                SET status = 'approved', reviewed_by = :reviewed_by, updated_at = NOW()
                WHERE id = :id
            ");
            $updateStmt->execute([
                ':id' => $exercise_id,
                ':reviewed_by' => $current_expert_id
            ]);

            $this->json_respond([
                'success' => true,
                'message' => 'Exercise approved successfully',
                'exercise_id' => $exercise_id,
                'new_status' => 'approved'
            ]);

        } catch (Exception $e) {
            error_log('api_approve_exercise error: ' . $e->getMessage());
            $this->json_error('Database error while approving exercise', 500);
        }
    }

    /**
     * API: POST /exercises/api/reject
     * Rejects a pending exercise with optional feedback.
     */
    public function api_reject_exercise()
    {
        // Only experts can reject
        if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'expert') {
            $this->json_error('Only experts can reject exercises', 403);
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->json_error('Method not allowed', 405);
        }

        $data = $this->json_request();
        $exercise_id = (int)($data['exercise_id'] ?? 0);
        $feedback = trim($data['feedback'] ?? '');
        $current_expert_id = $_SESSION['user_id'];

        if (!$exercise_id) {
            $this->json_error('Missing exercise ID', 400);
        }

        try {
            $pdo = $this->db();

            // Fetch the exercise
            $exerciseStmt = $pdo->prepare("SELECT id, status, creator_id FROM exercises WHERE id = :id LIMIT 1");
            $exerciseStmt->execute([':id' => $exercise_id]);
            $exercise = $exerciseStmt->fetch(PDO::FETCH_ASSOC);

            if (!$exercise) {
                $this->json_error('Exercise not found', 404);
            }

            if ($exercise['status'] !== 'pending') {
                $this->json_error('Exercise is not pending', 400);
            }

            // Check: Expert cannot review their own exercises
            if ($exercise['creator_id'] == $current_expert_id) {
                $this->json_error('You cannot reject your own exercises', 403);
            }

            // Update exercise: keep status as 'pending', save feedback, and update timestamp
            // Feedback is stored but exercise remains pending for creator to revise
            $updateStmt = $pdo->prepare("
                UPDATE exercises 
                SET feedback = :feedback, reviewed_by = :reviewed_by, updated_at = NOW()
                WHERE id = :id
            ");
            $updateStmt->execute([
                ':id' => $exercise_id,
                ':feedback' => !empty($feedback) ? $feedback : null,
                ':reviewed_by' => $current_expert_id
            ]);

            // Optional: Send notification to creator about feedback
            // This can be implemented by creating a notification record
            if (!empty($feedback)) {
                $this->send_feedback_notification($pdo, $exercise['creator_id'], $exercise_id, $feedback);
            }

            $this->json_respond([
                'success' => true,
                'message' => 'Feedback sent to exercise creator',
                'exercise_id' => $exercise_id,
                'status' => 'pending',
                'feedback_sent' => !empty($feedback)
            ]);

        } catch (Exception $e) {
            error_log('api_reject_exercise error: ' . $e->getMessage());
            $this->json_error('Database error while sending feedback', 500);
        }
    }

    /**
     * Helper: Sends a notification to the creator with feedback
     */
    private function send_feedback_notification($pdo, $creator_id, $exercise_id, $feedback)
    {
        try {
            // Create notification entry for the creator
            $notificationStmt = $pdo->prepare("
                INSERT INTO notifications (sender_id, receiver_id, content, created_at)
                VALUES (:sender_id, :receiver_id, :content, NOW())
            ");
            $notificationStmt->execute([
                ':sender_id' => $_SESSION['user_id'],
                ':receiver_id' => $creator_id,
                ':content' => "Your exercise #$exercise_id received feedback: " . substr($feedback, 0, 100)
            ]);
        } catch (Exception $e) {
            error_log('send_feedback_notification error: ' . $e->getMessage());
            // Don't throw - this is auxiliary functionality
        }
    }

    /**
     * API: GET /exercises/api/pending
     * Fetches pending exercises for expert review.
     * Supports pagination and optional subject filtering.
     */
    public function api_get_pending_exercises()
    {
        // Only experts can view pending exercises for review
        if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'expert') {
            $this->json_error('Only experts can view pending exercises', 403);
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            $this->json_error('Method not allowed', 405);
        }

        $offset = (int)($_GET['offset'] ?? 0);
        $limit = (int)($_GET['limit'] ?? 10);
        $subject_id = (int)($_GET['subject_id'] ?? 0);

        // Validate pagination params
        if ($limit < 1 || $limit > 100) $limit = 10;
        if ($offset < 0) $offset = 0;

        try {
            $pdo = $this->db();

            // Build query to fetch pending exercises
            $query = "
                SELECT e.id, e.title, e.description, e.status, e.created_at, 
                       e.subject_id, s.name AS subject_name, 
                       e.creator_id, u.username AS creator_name,
                       (SELECT COUNT(*) FROM exercisequestion WHERE exercise_id = e.id) AS question_count
                FROM exercises e
                LEFT JOIN subjects s ON s.id = e.subject_id
                LEFT JOIN user u ON u.id = e.creator_id
                WHERE e.status = 'pending'
            ";

            $params = [];

            // // Optional: Filter by subject
            // if ($subject_id > 0) {
            //     $query .= " AND e.subject_id = :subject_id";
            //     $params[':subject_id'] = $subject_id;
            // }

            // Exclude creator's own exercises
            $query .= " AND e.creator_id != :current_user_id";
            $params[':current_user_id'] = $_SESSION['user_id'];

            // Order by creation date (newest first) and paginate
            $query .= " ORDER BY e.created_at DESC LIMIT :limit OFFSET :offset";

            $stmt = $pdo->prepare($query);
            $stmt->execute(array_merge($params, [
                ':limit' => $limit,
                ':offset' => $offset
            ]));
            $exercises = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Get total count for pagination info
            $countQuery = "
                SELECT COUNT(*) as total FROM exercises e
                WHERE e.status = 'pending' AND e.creator_id != :current_user_id
            ";
            if ($subject_id > 0) {
                $countQuery .= " AND e.subject_id = :subject_id";
            }

            $countStmt = $pdo->prepare($countQuery);
            $countStmt->execute(array_merge(
                [':current_user_id' => $_SESSION['user_id']],
                ($subject_id > 0) ? [':subject_id' => $subject_id] : []
            ));
            $total = $countStmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;

            $this->json_respond([
                'success' => true,
                'exercises' => array_map(function ($e) {
                    return [
                        'id' => (int)$e['id'],
                        'title' => $e['title'],
                        'description' => $e['description'],
                        'creator_name' => $e['creator_name'],
                        'subject_name' => $e['subject_name'],
                        'question_count' => (int)$e['question_count'],
                        'created_at' => $e['created_at']
                    ];
                }, $exercises),
                'total' => (int)$total,
                'offset' => $offset,
                'limit' => $limit,
                'has_more' => ($offset + $limit) < $total
            ]);

        } catch (Exception $e) {
            error_log('api_get_pending_exercises error: ' . $e->getMessage());
            $this->json_error('Failed to load pending exercises', 500);
        }
    }

    /**
     * API: GET /exercises/api/load_review_data/{exercise_id}
     * Loads exercise data for review (similar to attempt data but with read-only mode).
     */
    public function api_load_review_data($exercise_id = null)
    {
        // Only experts can load review data
        if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'expert') {
            $this->json_error('Only experts can review exercises', 403);
        }

        $exercise_id = (int)$exercise_id;
        if ($exercise_id <= 0) {
            $this->json_error('Missing exercise ID', 400);
        }

        try {
            $pdo = $this->db();
            
            // Fetch exercise
            $exerciseStmt = $pdo->prepare("
                SELECT e.id, e.title, e.status, e.subject_id, e.creator_id, e.created_at,
                       s.name AS subject_name, u.username AS creator_name
                FROM exercises e
                LEFT JOIN subjects s ON s.id = e.subject_id
                LEFT JOIN user u ON u.id = e.creator_id
                WHERE e.id = :id AND e.status = 'pending'
                LIMIT 1
            ");
            $exerciseStmt->execute([':id' => $exercise_id]);
            $exercise = $exerciseStmt->fetch(PDO::FETCH_ASSOC);

            if (!$exercise) {
                $this->json_error('Exercise not found or not pending', 404);
            }

            // Check: Expert cannot review their own exercises
            if ($exercise['creator_id'] == $_SESSION['user_id']) {
                $this->json_error('You cannot review your own exercises', 403);
            }

            // Fetch bundle (questions and options)
            $bundle = $this->fetchExerciseBundle($pdo, $exercise_id);

            $this->json_respond([
                'success' => true,
                'id' => (int)$bundle['exercise']['id'],
                'title' => $bundle['exercise']['title'],
                'subject' => $bundle['exercise']['subject_name'],
                'tags' => $bundle['exercise']['tags'] ?? [],
                'creator_name' => $exercise['creator_name'],
                'creator_role' => $bundle['exercise']['creator_role'] ?? 'Unknown',
                'created_at' => $bundle['exercise']['created_at'],
                'creator_id' => (int)$bundle['exercise']['creator_id'],
                'questions' => $bundle['questions']
            ]);

        } catch (Exception $e) {
            error_log('api_load_review_data error: ' . $e->getMessage());
            $this->json_error('Unable to load review data', 500);
        }
    }

    /**
     * API: GET /exercises/api/load_attempt_data/{exercise_id}
     * Loads the full exercise structure including answers and explanations for client-side use.
     * This replaces the security-conscious separation for self-assessment mode.
     */
    public function api_load_attempt_data($exercise_id = null)
    {
        // Allow requesting all approved exercises using a sentinel value
        if ($exercise_id === 'all' || $exercise_id === 'list') {
            try {
                $pdo = $this->db();
                $rows = $pdo->query("SELECT e.id, e.title, s.name AS subject, e.created_at FROM exercises e LEFT JOIN subjects s ON s.id = e.subject_id WHERE e.status = 'approved' ORDER BY e.created_at DESC")->fetchAll(PDO::FETCH_ASSOC);

                $this->json_respond([
                    'success' => true,
                    'exercises' => array_map(function ($row) {
                        return [
                            'id' => (int)$row['id'],
                            'title' => $row['title'],
                            'subject' => $row['subject'],
                            'created_at' => $row['created_at'],
                        ];
                    }, $rows)
                ]);
            } catch (Exception $e) {
                $this->json_error('Failed to load exercises list', 500);
            }
        }

        $exercise_id = (int)$exercise_id;
        if ($exercise_id <= 0) {
            $this->json_error('Missing exercise ID.', 400);
        }

        try {
            $pdo = $this->db();
            $bundle = $this->fetchExerciseBundle($pdo, $exercise_id);

            if (!$bundle) {
                $this->json_error('Exercise not found', 404);
            }

            if ($bundle['exercise']['status'] !== 'approved') {
                $this->json_error('Exercise is not approved for attempts', 403);
            }

            $this->json_respond([
                'success' => true,
                'id' => (int)$bundle['exercise']['id'],
                'title' => $bundle['exercise']['title'],
                'subject' => $bundle['exercise']['subject_name'],
                'tags' => $bundle['exercise']['tags'] ?? [],
                'created_at' => $bundle['exercise']['created_at'],
                'creator_name' => $bundle['exercise']['creator_name'] ?? 'Unknown',
                'creator_role' => $bundle['exercise']['creator_role'] ?? 'Unknown',
                'creator_id' => (int)$bundle['exercise']['creator_id'],
                'questions' => $bundle['questions'],
            ]);
        } catch (Exception $e) {
            error_log('api_load_attempt_data error: '.$e->getMessage());
            $this->json_error('Unable to load exercise data', 500);
        }
    }

/**
     * API: GET /exercises/api/published
     * Fetches exercises available for browsing, supporting filtering/pagination (R6).
     */
    public function api_get_published()
    {
        $offset = (int)($_GET['offset'] ?? 0);
        $limit = (int)($_GET['limit'] ?? 10);
        
        try {
            $exerciseModel = new ExercisesModel();
            $exercises = $exerciseModel->filter_and_search([
                'where' => ['status' => 'approved'],
                'order_by' => 'created_at',
                'order_dir' => 'DESC',
                'limit' => $limit,
                'offset' => $offset
            ]);

            $this->json_respond([
                'success' => true,
                'exercises' => $exercises
            ]);
        } catch (Exception $e) {
            error_log('api_get_published error: '.$e->getMessage());
            $this->json_error('Failed to load exercises', 500);
        }
    }

    /**
     * API: GET /exercises/api/pending_review
     * Fetches the list of exercises pending review for the current Subject Expert (R5).
     */
    public function api_get_pending_review()
    {
        try {
            $exerciseModel = new ExercisesModel();
            $exercises = $exerciseModel->filter_and_search([
                'where' => ['status' => 'pending'],
                'order_by' => 'created_at',
                'order_dir' => 'DESC'
            ]);

            $this->json_respond([
                'success' => true,
                'exercises' => $exercises
            ]);
        } catch (Exception $e) {
            error_log('api_get_pending_review error: '.$e->getMessage());
            $this->json_error('Failed to load pending exercises', 500);
        }
    }
    
    /**
     * API: POST /exercises/api/attempt/{exercise_id}
     * Submits a user's answers and returns the calculated results (R7).
     */
    public function api_submit_attempt($exercise_id = null)
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || empty($exercise_id)) {
             $this->json_error('Invalid request or missing exercise ID.', 400);
        }

        $exercise_id = (int)$exercise_id;
        if ($exercise_id <= 0) {
            $this->json_error('Invalid exercise ID.', 400);
        }

        $payload = $this->json_request();
        $answers = $payload['answers'] ?? [];
        if (!is_array($answers) || empty($answers)) {
            $this->json_error('No answers submitted.', 400);
        }

        // Get current user from session (fallback to test user 2 if not set)
        //$current_user = $_SESSION['user_id'] ?? null;
        
        $current_user = $_SESSION['user_id'] ?? null;
        if (!$current_user) {
            $this->json_error('Authentication required.', 401);
        }

        try {
            $pdo = $this->db();
            $bundle = $this->fetchExerciseBundle($pdo, $exercise_id);

            if (!$bundle) {
                $this->json_error('Exercise not found', 404);
            }

            if ($bundle['exercise']['status'] !== 'approved') {
                $this->json_error('Exercise is not approved for attempts', 403);
            }

            // Map questions for quick access
            $questionMap = [];
            $maxScore = 0;
            foreach ($bundle['questions'] as $q) {
                $questionMap[$q['question_id']] = $q;
                $maxScore += (float)$q['weight'];
            }

            // Normalize user answers into map
            $userSelections = [];
            foreach ($answers as $item) {
                $qid = isset($item['question_id']) ? (int)$item['question_id'] : 0;
                $selected = isset($item['selected_option_ids']) && is_array($item['selected_option_ids'])
                    ? array_map('intval', $item['selected_option_ids'])
                    : [];
                if ($qid > 0) {
                    $userSelections[$qid] = $selected;
                }
            }

            $details = [];
            $totalScore = 0.0;

            foreach ($questionMap as $qid => $qData) {
                $selected = $userSelections[$qid] ?? [];
                sort($selected);

                $correct = array_map('intval', array_column(array_filter($qData['options'], function ($opt) {
                    return $opt['is_correct'] === true;
                }), 'option_id'));
                sort($correct);

                $isCorrect = !empty($selected) && $selected === $correct;
                $scoreEarned = $isCorrect ? (float)$qData['weight'] : 0.0;
                $totalScore += $scoreEarned;

                $options = [];
                foreach ($qData['options'] as $opt) {
                    $options[] = [
                        'option_id' => (int)$opt['option_id'],
                        'text' => $opt['text'],
                        'is_correct' => (bool)$opt['is_correct'],
                        'was_selected' => in_array((int)$opt['option_id'], $selected, true),
                    ];
                }

                $details[] = [
                    'question_id' => (int)$qid,
                    'prompt' => $qData['prompt'],
                    'user_score' => $scoreEarned,
                    'max_weight' => (float)$qData['weight'],
                    'explanation' => $qData['explanation'],
                    'options' => $options,
                ];
            }

            // Persist attempt and answers in one transaction
            $pdo->beginTransaction();

            // Create question table entries for FK constraint (maps exercisequestion IDs to question table IDs)
            $questionIdMap = [];  // exercisequestion.id => question.id
            
            foreach ($bundle['questions'] as $q) {
                try {
                    $exerciseQId = (int)$q['question_id'];  // question_id from bundle (which is exercisequestion.id)
                    $qTitle = 'Exercise ' . $exercise_id . ' - Q' . $exerciseQId;  // Unique identifier
                    
                    // Check if this question mapping already exists
                    $stmtCheck = $pdo->prepare("SELECT id FROM question WHERE title = :title LIMIT 1");
                    $stmtCheck->execute([':title' => $qTitle]);
                    $existing = $stmtCheck->fetch(PDO::FETCH_ASSOC);
                    
                    if ($existing) {
                        $questionIdMap[$exerciseQId] = (int)$existing['id'];
                    } else {
                        // Create new question entry
                        $stmtInsert = $pdo->prepare("INSERT INTO question (title, content, creator_id) VALUES (:title, :content, :creator_id)");
                        $stmtInsert->execute([
                            ':title' => $qTitle,
                            ':content' => $q['prompt'],
                            ':creator_id' => $current_user,
                        ]);
                        $questionIdMap[$exerciseQId] = (int)$pdo->lastInsertId();
                    }
                } catch (Exception $qError) {
                    error_log('Error creating question mapping: ' . $qError->getMessage() . "\nQuestion data: " . json_encode($q));
                    throw $qError;
                }
            }

            // Mark previous attempts as not latest
            $markOld = $pdo->prepare("UPDATE exercise_attempt SET latest = 0 WHERE exe_id = :exe_id AND u_id = :user_id AND latest = 1");
            $markOld->execute([':exe_id' => $exercise_id, ':user_id' => $current_user]);

            $attemptStmt = $pdo->prepare("INSERT INTO exercise_attempt (exe_id, u_id, score, latest) VALUES (:exe_id, :user_id, :score, 1)");
            $attemptStmt->execute([
                ':exe_id' => $exercise_id,
                ':user_id' => $current_user,
                ':score' => $totalScore,
            ]);

            $attemptId = (int)$pdo->lastInsertId();

            $answerStmt = $pdo->prepare("INSERT INTO attempt_answer (attempt_id, question_id, user_response, is_correct, score_earned) VALUES (:attempt_id, :question_id, :user_response, :is_correct, :score_earned)");

            foreach ($details as $detail) {
                try {
                    $exerciseQId = (int)$detail['question_id'];  // exercisequestion.id
                    $actualQuestionId = $questionIdMap[$exerciseQId] ?? null;  // Get mapped question.id
                    
                    if (!$actualQuestionId) {
                        throw new Exception('No question mapping found for exercisequestion ID ' . $exerciseQId);
                    }
                    
                    $userResp = $userSelections[$exerciseQId] ?? [];
                    $answerStmt->execute([
                        ':attempt_id' => $attemptId,
                        ':question_id' => $actualQuestionId,  // Use mapped question.id
                        ':user_response' => json_encode($userResp),
                        ':is_correct' => $detail['user_score'] >= $detail['max_weight'] ? 1 : 0,
                        ':score_earned' => (float)$detail['user_score'],
                    ]);
                } catch (Exception $answerError) {
                    error_log('Answer insert error for exerciseQ' . $exerciseQId . ': ' . $answerError->getMessage());
                    throw $answerError;
                }
            }

            $pdo->commit();

            $this->json_respond([
                'success' => true,
                'attempt_id' => $attemptId,
                'exercise_id' => $exercise_id,
                'total_score' => $totalScore,
                'total_max_score' => $maxScore,
                'details' => $details,
            ]);

        } catch (Exception $e) {
            if (isset($pdo) && $pdo->inTransaction()) {
                $pdo->rollBack();
            }
            $errorMsg = $e->getMessage();
            error_log('api_submit_attempt error: ' . $errorMsg . "\n" . $e->getTraceAsString());
            // Always return detailed error for now (remove in production)
            $this->json_error('Database Error: ' . $errorMsg, 500);
        }
    }
    
    /**
     * API: GET /exercises/api/history
     * Fetches a list of the user's previously attempted exercises (R9).
     */
    public function api_get_attempt_history()
    {
        // Get current user from session (fallback to test user 2 if not set)
        $current_user = $_SESSION['user_id'] ?? 2;
        
        if (!$current_user) {
            $this->json_error('Authentication required', 401);
        }

        try {
            $attemptModel = new ExerciseAttempt();
            $attempts = $attemptModel->filter_and_search([
                'where' => ['u_id' => $current_user],
                'order_by' => 'date',
                'order_dir' => 'DESC'
            ]);

            $this->json_respond([
                'success' => true,
                'attempts' => $attempts
            ]);
        } catch (Exception $e) {
            error_log('api_get_attempt_history error: '.$e->getMessage());
            $this->json_error('Failed to load attempt history', 500);
        }
    }

    /**
     * API: GET /exercises/api/history/{attempt_id}
     * Fetches the detailed results and explanations for a past attempt (R9).
     */
    public function api_get_attempt_details($attempt_id = null)
    {
        $attempt_id = (int)$attempt_id;
        if ($attempt_id <= 0) {
            $this->json_error('Missing attempt ID.', 400);
        }

        try {
            $pdo = $this->db();

            $attemptStmt = $pdo->prepare("SELECT ea.id, ea.exe_id, ea.u_id, ea.score, ea.date, ex.title, ex.status, s.name AS subject_name FROM exercise_attempt ea JOIN exercises ex ON ex.id = ea.exe_id LEFT JOIN subjects s ON s.id = ex.subject_id WHERE ea.id = :attempt_id LIMIT 1");
            $attemptStmt->execute([':attempt_id' => $attempt_id]);
            $attempt = $attemptStmt->fetch(PDO::FETCH_ASSOC);

            if (!$attempt) {
                $this->json_error('Attempt not found', 404);
            }

            if ($attempt['status'] !== 'approved') {
                $this->json_error('Exercise is not approved', 403);
            }

            $questionStmt = $pdo->prepare("SELECT q.id, q.question_text, q.explanation, q.weight FROM exercisequestion q WHERE q.exercise_id = :exercise_id ORDER BY q.display_order ASC, q.id ASC");
            $questionStmt->execute([':exercise_id' => $attempt['exe_id']]);
            $questions = $questionStmt->fetchAll(PDO::FETCH_ASSOC);

            $answerStmt = $pdo->prepare("SELECT id, answer_text, is_correct, question_id FROM exerciseanswer WHERE question_id = :question_id ORDER BY display_order ASC, id ASC");
            $attemptAnswerStmt = $pdo->prepare("SELECT user_response, is_correct, score_earned FROM attempt_answer WHERE attempt_id = :attempt_id AND question_id = :question_id LIMIT 1");
            // Map exercisequestion.id to the related question.id created during submission
            $questionMapStmt = $pdo->prepare("SELECT id FROM question WHERE title = :title LIMIT 1");

            $details = [];
            $maxScore = 0;

            foreach ($questions as $q) {
                $maxScore += (float)$q['weight'];
                $answerStmt->execute([':question_id' => $q['id']]);
                $optionsRaw = $answerStmt->fetchAll(PDO::FETCH_ASSOC);

                // Attempt answers are stored against question.id (not exercisequestion.id)
                $mappedQuestionId = null;
                $mapTitle = 'Exercise ' . $attempt['exe_id'] . ' - Q' . $q['id'];
                $questionMapStmt->execute([':title' => $mapTitle]);
                $mappedRow = $questionMapStmt->fetch(PDO::FETCH_ASSOC);
                if ($mappedRow) {
                    $mappedQuestionId = (int)$mappedRow['id'];
                }

                $attemptAnswerStmt->execute([
                    ':attempt_id' => $attempt_id,
                    ':question_id' => $mappedQuestionId ?? 0
                ]);
                $attemptAnswer = $attemptAnswerStmt->fetch(PDO::FETCH_ASSOC);

                $selectedIds = [];
                if ($attemptAnswer && !empty($attemptAnswer['user_response'])) {
                    $selectedIds = json_decode($attemptAnswer['user_response'], true) ?: [];
                }

                $options = [];
                foreach ($optionsRaw as $opt) {
                    $options[] = [
                        'option_id' => (int)$opt['id'],
                        'text' => $opt['answer_text'],
                        'is_correct' => (bool)$opt['is_correct'],
                        'was_selected' => in_array((int)$opt['id'], $selectedIds, true),
                    ];
                }

                $details[] = [
                    'question_id' => (int)$q['id'],
                    'prompt' => $q['question_text'],
                    'user_score' => (float)($attemptAnswer['score_earned'] ?? 0),
                    'max_weight' => (float)$q['weight'],
                    // Use stored attempt_answer flag; avoid recalculating correctness here
                    'is_correct' => (bool)($attemptAnswer['is_correct'] ?? 0),
                    // Treat question weight as difficulty for UI display
                    'difficulty' => (int)$q['weight'],
                    'explanation' => $q['explanation'],
                    'options' => $options,
                ];
            }

            $this->json_respond([
                'success' => true,
                'attempt_id' => $attempt_id,
                'exercise_id' => (int)$attempt['exe_id'],
                'exercise_title' => $attempt['title'],
                'subject' => $attempt['subject_name'],
                // Score response uses stored attempt score (no recalculation)
                'raw_score' => (float)$attempt['score'],
                'max_score' => $maxScore,
                'percentage_score' => $maxScore > 0 ? round(((float)$attempt['score'] / $maxScore) * 100, 2) : 0,
                // Keep existing keys for backward compatibility
                'total_score' => (float)$attempt['score'],
                'total_max_score' => $maxScore,
                'attempted_at' => $attempt['date'],
                'details' => $details,
            ]);

        } catch (Exception $e) {
            error_log('api_get_attempt_details error: '.$e->getMessage());
            $this->json_error('Unable to load attempt details', 500);
        }
    }

    /**
     * API: POST /exercises/api/vote/{exercise_id}
     * Submits a vote (upvote/downvote) for an exercise (R8).
     */
    public function api_submit_vote($exercise_id = null)
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || empty($exercise_id)) {
            $this->json_error('Invalid request or missing exercise ID.', 400);
        }

        $exercise_id = (int)$exercise_id;
        // Get current user from session (fallback to test user 2 if not set)

        //$current_user = $_SESSION['user_id'] ?? null;
        $current_user = $_SESSION['user_id'] ?? 2;
        
        if (!$current_user) {
            $this->json_error('Authentication required', 401);
        }

        $input = $this->json_request();
        $vote_type = $input['vote_type'] ?? null;
        
        if (!in_array($vote_type, ['Upvoted', 'Downvoted'])) {
            $this->json_error('Invalid vote type', 400);
        }

        try {
            // Convert vote type from 'Upvoted'/'Downvoted' to 'upvote'/'downvote'
            $dbVoteType = strtolower(str_replace('d', '', $vote_type)); // 'Upvoted' -> 'upvote'
            
            $pdo = $this->db();
            
            // Check if vote already exists (composite primary key: exercise_id, u_id)
            $checkStmt = $pdo->prepare("SELECT votetype FROM uservoteexercise WHERE exercise_id = :exercise_id AND u_id = :u_id LIMIT 1");
            $checkStmt->execute([':exercise_id' => $exercise_id, ':u_id' => $current_user]);
            $existingVote = $checkStmt->fetch(PDO::FETCH_ASSOC);

            if ($existingVote) {
                // Update existing vote (use composite key in WHERE clause)
                $updateStmt = $pdo->prepare("UPDATE uservoteexercise SET votetype = :votetype WHERE exercise_id = :exercise_id AND u_id = :u_id");
                $updateStmt->execute([
                    ':votetype' => $dbVoteType,
                    ':exercise_id' => $exercise_id,
                    ':u_id' => $current_user
                ]);
            } else {
                // Insert new vote
                $insertStmt = $pdo->prepare("INSERT INTO uservoteexercise (exercise_id, u_id, votetype) VALUES (:exercise_id, :u_id, :votetype)");
                $insertStmt->execute([
                    ':exercise_id' => $exercise_id,
                    ':u_id' => $current_user,
                    ':votetype' => $dbVoteType
                ]);
            }

            // Return updated vote count for the UI
            $countStmt = $pdo->prepare("SELECT SUM(CASE WHEN votetype = 'upvote' THEN 1 WHEN votetype = 'downvote' THEN -1 ELSE 0 END) AS vote_count FROM uservoteexercise WHERE exercise_id = :exercise_id");
            $countStmt->execute([':exercise_id' => $exercise_id]);
            $voteCount = (int)($countStmt->fetch(PDO::FETCH_ASSOC)['vote_count'] ?? 0);

            $this->json_respond([
                'success' => true,
                'message' => 'Vote successfully registered.',
                'current_vote_status' => $vote_type,
                'vote_count' => $voteCount
            ]);
        } catch (Exception $e) {
            error_log('api_submit_vote error: '.$e->getMessage());
            $this->json_error('Failed to submit vote', 500);
        }
    }

    /**
     * API: GET /exercises/api/vote/{exercise_id}
     * Gets the current user's vote status for an exercise.
     */
    public function api_get_vote_status($exercise_id = null)
    {
        if (empty($exercise_id)) {
            $this->json_error('Missing exercise ID.', 400);
        }

        $exercise_id = (int)$exercise_id;
        // Get current user from session (fallback to test user 2 if not set)
        
        //$current_user = $_SESSION['user_id'] ?? null;
        $current_user = $_SESSION['user_id'] ?? 2;
        
        if (!$current_user) {
            $this->json_respond([
                'success' => true,
                'current_vote_status' => 'None'
            ]);
        }

        try {
            $pdo = $this->db();
            
            // Check user's vote using correct column name u_id
            $voteStmt = $pdo->prepare("SELECT votetype FROM uservoteexercise WHERE exercise_id = :exercise_id AND u_id = :u_id LIMIT 1");
            $voteStmt->execute([':exercise_id' => $exercise_id, ':u_id' => $current_user]);
            $vote = $voteStmt->fetch(PDO::FETCH_ASSOC);

            $status = 'None';
            if ($vote) {
                // Convert 'upvote' -> 'Upvoted', 'downvote' -> 'Downvoted'
                $status = ucfirst($vote['votetype']) . 'd';
            }

            // Get vote count (already have $pdo from above)
            $countStmt = $pdo->prepare("SELECT SUM(CASE WHEN votetype = 'upvote' THEN 1 WHEN votetype = 'downvote' THEN -1 ELSE 0 END) AS vote_count FROM uservoteexercise WHERE exercise_id = :exercise_id");
            $countStmt->execute([':exercise_id' => $exercise_id]);
            $voteCount = (int)($countStmt->fetch(PDO::FETCH_ASSOC)['vote_count'] ?? 0);

            $this->json_respond([
                'success' => true,
                'current_vote_status' => $status,
                'vote_count' => $voteCount
            ]);
        } catch (Exception $e) {
            error_log('api_get_vote_status error: '.$e->getMessage());
            $this->json_error('Failed to get vote status', 500);
        }
    }

    /**
     * Creates a PDO connection with consistent options.
     */
    private function db()
    {
        return new PDO(
            "mysql:host=".DBHOST.";dbname=".DBNAME.";charset=utf8mb4",
            DBUSER,
            DBPASS,
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ]
        );
    }

    /**
     * Fetches an exercise with its questions and options.
     */
    private function fetchExerciseBundle(PDO $pdo, int $exercise_id)
    {
        $exerciseStmt = $pdo->prepare("SELECT e.id, e.title, e.status, e.subject_id, e.creator_id, e.created_at, s.name AS subject_name, u.username AS creator_name, r.name AS creator_role FROM exercises e LEFT JOIN subjects s ON s.id = e.subject_id LEFT JOIN user u ON u.id = e.creator_id LEFT JOIN roles r ON r.role_id = u.role WHERE e.id = :id LIMIT 1");
        $exerciseStmt->execute([':id' => $exercise_id]);
        $exercise = $exerciseStmt->fetch(PDO::FETCH_ASSOC);

        if (!$exercise) {
            return null;
        }

        $tagStmt = $pdo->prepare("SELECT t.name FROM exercisetag et JOIN tags t ON t.id = et.tag_id WHERE et.exercise_id = :exercise_id ORDER BY t.name ASC");
        $tagStmt->execute([':exercise_id' => $exercise_id]);
        $exercise['tags'] = array_values(array_map(function ($row) {
            return $row['name'];
        }, $tagStmt->fetchAll(PDO::FETCH_ASSOC)));

        $questionStmt = $pdo->prepare("SELECT id, question_text, explanation, weight FROM exercisequestion WHERE exercise_id = :exercise_id ORDER BY display_order ASC, id ASC");
        $questionStmt->execute([':exercise_id' => $exercise_id]);
        $questions = $questionStmt->fetchAll(PDO::FETCH_ASSOC);

        $answerStmt = $pdo->prepare("SELECT id, answer_text, is_correct FROM exerciseanswer WHERE question_id = :question_id ORDER BY display_order ASC, id ASC");

        $questionPayload = [];
        foreach ($questions as $q) {
            $answerStmt->execute([':question_id' => $q['id']]);
            $options = [];
            foreach ($answerStmt->fetchAll(PDO::FETCH_ASSOC) as $opt) {
                $options[] = [
                    'option_id' => (int)$opt['id'],
                    'text' => $opt['answer_text'],
                    'is_correct' => (bool)$opt['is_correct'],
                ];
            }

            $questionPayload[] = [
                'question_id' => (int)$q['id'],
                'prompt' => $q['question_text'],
                'weight' => (float)$q['weight'],
                'explanation' => $q['explanation'],
                'options' => $options,
            ];
        }

        return [
            'exercise' => $exercise,
            'questions' => $questionPayload,
        ];
    }
    
    // Helper to send JSON error responses
    private function json_error($message, $code = 400)
    {
        http_response_code($code);
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => $message]);
        exit();
    }
    
    public function api_load_more() {
        $this->json_respond([
            "success" => true,
            "results_returned" => 4,
            "next_offset" => 14,
            "available_more" => false,
            "data" => [
                [
                    "id" => 11,
                    "title" => "Implement Dijkstra's Algorithm (Intermediate)",
                    "topic" => "Algorithms",
                    "difficulty" => "Intermediate",
                    "published_date" => "2025-11-20"
                ],
                [
                    "id" => 12,
                    "title" => "Design Pattern: Observer",
                    "topic" => "Software Design",
                    "difficulty" => "Advanced",
                    "published_date" => "2025-11-18"
                ],
                [
                    "id" => 13,
                    "title" => "Data Visualization Basics",
                    "topic" => "Data Science",
                    "difficulty" => "Beginner",
                    "published_date" => "2025-11-15"
                ],
                [
                    "id" => 14,
                    "title" => "Write a RESTful API specification",
                    "topic" => "Web Development",
                    "difficulty" => "Expert",
                    "published_date" => "2025-11-10"
                ]
            ]
        ]);
    }
    
    public function filter()
    {
        // 1. Validate Request
        if (!$this->is_get()) {
            $this->json_respond(['error' => 'Method not allowed']); // Uses Controller::json_respond
        }

        $exerciseModel = new ExercisesModel();
        
        // 2. Collect Inputs
        $tab = $_GET['tab'] ?? 'all';
        $search = $_GET['q'] ?? '';
        $subject = $_GET['subject'] ?? '';
        $sort = $_GET['sort'] ?? 'id-DESC'; // Format: "column-direction"
        $offset = isset($_GET['offset']) ? (int)$_GET['offset'] : 0;
        $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 5;
        $user_id = $_SESSION['user_id'] ?? 0; // Assuming session is active
        $role = strtolower(trim((string)($_SESSION['role'] ?? 'student')));

        if (!in_array($role, ['student', 'mentor', 'expert', 'admin'], true)) {
            $role = 'student';
        }

        if ($tab === 'pending' && !in_array($role, ['expert', 'admin'], true)) {
            $tab = 'all';
        }

        $limit = ($limit > 0 && $limit <= 100) ? $limit : 5;
        $offset = $offset >= 0 ? $offset : 0;

        $can_create = ($role === 'mentor');
        $can_edit = $can_create;

        $expert_subject_ids = $this->get_expert_subject_ids($role, $user_id);

        // 3. Fetch Data
        $result = $exerciseModel->get_browser_list([
            'role' => $role,
            'user_id' => $user_id,
            'tab' => $tab,
            'search' => $search,
            'subject' => $subject,
            'sort' => $sort,
            'offset' => $offset,
            'limit' => $limit,
            'expert_subject_ids' => $expert_subject_ids,
        ]);

        $total = $result['total'] ?? 0;
        $has_more = ($offset + $limit) < $total;

        // 4. Return JSON
        $this->json_respond([
            'exercises' => $result['rows'] ?? [],
            'has_more' => $has_more,
            'role' => $role,
            'permissions' => [
                'can_create' => $can_create,
                'can_edit' => $can_edit
            ]
        ]);
    }

    private function get_expert_subject_ids($role, $user_id)
    {
        if (!in_array($role, ['expert'], true)) {
            return [];
        }

        $experts = new Experts();
        $rows = $experts->where(['user_id' => $user_id], 0, null, ['subject_id']);
        if (!$rows) {
            return [];
        }

        $subject_ids = [];
        foreach ($rows as $row) {
            $subject_ids[] = (int)$row->subject_id;
        }

        return array_values(array_unique($subject_ids));
    }
}


