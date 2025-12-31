<?php

class Exercises extends Controller
{
    public function index()
    {
        // --- MOCK DATA SETUP ---

        // Helper function to generate mock exercise data (Moved the function outside $data setup)
        function generate_mock_exercises($type, $offset, $limit) {
            // Mock data for the 'All' exercises
            $all_exercises = [
                ['id' => 1, 'title' => 'what is lagrangian method?', 'subject' => 'Physics', 'relation' => 'Created'],
                ['id' => 1, 'title' => 'how Jacobian related to gradient?', 'subject' => 'Maths', 'relation' => 'Created'],
                ['id' => 1, 'title' => 'solve in Hamiltonian mechanics?', 'subject' => 'Physics', 'relation' => 'Attempted'],
                ['id' => 1, 'title' => 'what does this operator do?', 'subject' => 'Quantum Computing', 'relation' => 'Created'],
                ['id' => 1, 'title' => 'how shadow work described by jung?', 'subject' => 'Psychology', 'relation' => 'Attempted'],
                ['id' => 1, 'title' => 'how to solve this in linear algebra?', 'subject' => 'Maths', 'relation' => 'Created'],
            ];

            $data_source = $all_exercises;
            
            // Filter based on the requested tab (only filtering by 'all' for initial load mock)
            // Note: If initial tab was 'created', you'd apply that filter here.
            if ($type === 'created') {
                $data_source = array_filter($all_exercises, fn($e) => $e['relation'] === 'Created');
            } elseif ($type === 'attempted') {
                $data_source = array_filter($all_exercises, fn($e) => $e['relation'] === 'Attempted');
            }

            $notes_to_return = array_slice($data_source, $offset, $limit);
            $has_more = count($data_source) > ($offset + $limit); 

            return [
                'exercises' => array_values($notes_to_return),
                'has_more' => $has_more
            ];
        }

        // Initial load parameters
        $initial_tab = 'all';
        $initial_offset = 0;
        $initial_limit = 5; // Use 5 to demonstrate 'Load More' immediately

        $initial_load_result = generate_mock_exercises($initial_tab, $initial_offset, $initial_limit);

        $data = [
            'current_user_id' => 'user_1',
            'initial_tab' => $initial_tab, 
            'create_url' => '/your-backend-controller/create-exercise-view',
            // NEW: Initial exercises data is stored here
            'initial_exercises' => $initial_load_result['exercises'],
            'initial_has_more' => $initial_load_result['has_more'],
            'initial_limit' => $initial_limit,
            'initial_offset' => $initial_offset
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
        // if (!$current_user) {
        //     http_response_code(401);
        //     echo json_encode(['success' => false, 'message' => 'Authentication required to create exercises.']);
        //     return;
        // }

        $metadata = $data['metadata'] ?? null;
        $questions = $data['questions'] ?? null;

        if (empty($metadata) || empty($metadata['title']) || empty($metadata['subject']) || !is_array($questions)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Invalid request: missing required metadata or questions.']);
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

            // Resolve or create subject
            $subjects = new Subjects();
            $subject_name = trim($metadata['subject']);
            // Use helper in Subjects model to add or find
            $subject_id = $subjects->add_new_subject($subject_name);

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
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = $_POST['exercise_id'] ?? 1;
            // Process submitted answers here

            header('Location: '.ROOT.'/exercises/viewattempt/'.$id.'/12345');
            exit();
        }
        //check if same user is attempting again, creator attempting again etc..

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
            // $tag_info = $subject->first(['id' => $tag->tag_id]);
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
            
        }


        // --- MOCK DATA SETUP ---
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
                'user_vote_status' => 'upvote' // possible values: 'upvoted', 'downvoted', 'none'
            ],
            'questions' => $question_list
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
            // $tag_info = $subject->first(['id' => $tag->tag_id]);
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

        //remember to fetch review data too
        $review_data = [
            'average_score' => 0.8,
        ];


        // --- MOCK DATA SETUP ---
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
            'review_data' => $review_data
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
        $id = $_GET['id'] ?? null;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Process expert review submission
            exit();
        }

        //check user is not the creator and also expert in same subject which exercise in
        $data = [
            'exercise_details' => [
                'id' => 'ex_123',
                'title' => 'Advanced Color Theory in UI Design',
                'creator' => 'Alice',
                'role' => 'under graphic design',
                'created_at' => '26-02-2027',
                'tags' => ['art', 'color', 'design principles'],
                'upvotes' => 10000,
                'downvotes' => 2000
            ],
            // NEW: Mock review data
            'review_data' => [
                'average_score' => 0.8,
                'analysis_link' => '/exercises/ex_123/analysis',
                'edit_link' => '/exercises/ex_123/edit' // Points to your editor view
            ],
            'questions' => [
                [
                    'id' => 'q1',
                    'question_text' => 'Which of the following is considered a "cool" color?',
                    'options' => ['Red', 'Yellow', 'Blue', 'Orange'],
                    'correct_index' => 2 // Mock correct answer for display logic
                ],
                [
                    'id' => 'q2',
                    'question_text' => 'Which color harmony is most effective for creating contrast while maintaining visual balance?',
                    'options' => ['Analogous', 'Monochromatic', 'Complementary', 'Triadic'],
                    'correct_index' => 2
                ],
                [
                    'id' => 'q3',
                    'question_text' => 'The HSL color model stands for Hue, Saturation, and what?',
                    'options' => ['Luminance', 'Lightness', 'Level', 'Layer'],
                    'correct_index' => 1
                ],
            ]
        ];


        $this->view('exercises/expertreview', $data);
    }

    public function viewattempt($exercise_id, $attempt_id)
    {
        // $id = $_GET['id'] ?? null;

        $data = [
            'exercise_details' => [
                'id' => 'ex_123',
                'title' => 'Advanced Color Theory in UI Design',
                'creator' => 'Alice',
                'role' => 'under graphic design',
                'created_at' => '26-02-2027',
                'tags' => ['art', 'color', 'design principles'],
                'upvotes' => 10000,
                'downvotes' => 2000
            ],
            // NEW: Mock review data
            'review_data' => [
                'average_score' => 0.8,
                'analysis_link' => '/exercises/ex_123/analysis',
            ],
            'questions' => [
                [
                    'id' => 'q1',
                    'question_text' => 'Which of the following is considered a "cool" color?',
                    'options' => ['Red', 'Yellow', 'Blue', 'Orange'],
                    'correct_index' => 2,// Mock correct answer for display logic
                    'selected_index' => 1 // Mock user selected answer for display logic
                ],
                [
                    'id' => 'q2',
                    'question_text' => 'Which color harmony is most effective for creating contrast while maintaining visual balance?',
                    'options' => ['Analogous', 'Monochromatic', 'Complementary', 'Triadic'],
                    'correct_index' => 2,
                    'selected_index' => 2
                ],
                [
                    'id' => 'q3',
                    'question_text' => 'The HSL color model stands for Hue, Saturation, and what?',
                    'options' => ['Luminance', 'Lightness', 'Level', 'Layer'],
                    'correct_index' => 1,
                    'selected_index' => 0
                ],
            ]
        ];

        $data = [
            'exercise_id' => $exercise_id,
            'attempt_id' => $attempt_id
        ];

        $this->view('exercises/viewattempt', $data);
    }

    public function approve()
    {
        $exercise_id = $_POST['exercise_id'] ?? null;

        //implement here

        header('Location: '.ROOT.'/exercises?message=Exercise '.$exercise_id.' approved successfully');
    }

    public function reject()
    {
        $exercise_id = $_POST['exercise_id'] ?? null;

        //implement here

        header('Location: '.ROOT.'/exercises?message=Exercise '.$exercise_id.' rejected successfully');
    }

    /**
     * API: GET /exercises/api/load_attempt_data/{exercise_id}
     * Loads the full exercise structure including answers and explanations for client-side use.
     * This replaces the security-conscious separation for self-assessment mode.
     */
    public function api_load_attempt_data($exercise_id = null)
    {
        if (empty($exercise_id)) {
            $this->json_error("Missing exercise ID.", 400);
        }
        
        // --- MOCK DATA: Full structure with correct flags and explanations ---
        $mock_full_data = [
          "id" => (int)$exercise_id,
          "title" => "Basic Financial Accounting (Self-Assessment)",
          "subject" => "Finance",
          "questions" => [
            [
              "question_id" => 601,
              "prompt" => "Which of these is a current asset?",
              "weight" => 3,
              "explanation" => "Accounts Receivable is a current asset, representing money owed by customers expected to be collected within one year. Land and Equipment are long-term assets.",
              "options" => [
                ["option_id" => 701, "text" => "Land", "is_correct" => false],
                ["option_id" => 702, "text" => "Accounts Receivable", "is_correct" => true],
                ["option_id" => 703, "text" => "Equipment", "is_correct" => false]
              ]
            ],
            [
              "question_id" => 602,
              "prompt" => "Identify the elements of the accounting equation. (Select two)",
              "weight" => 5,
              "explanation" => "The fundamental accounting equation is Assets = Liabilities + Equity. Both Assets and Liabilities are core elements.",
              "options" => [
                ["option_id" => 704, "text" => "Assets", "is_correct" => true],
                ["option_id" => 705, "text" => "Profit", "is_correct" => false],
                ["option_id" => 706, "text" => "Liabilities", "is_correct" => true],
                ["option_id" => 707, "text" => "Market Share", "is_correct" => false]
              ]
            ]
          ]
        ];

        header('Content-Type: application/json');
        echo json_encode($mock_full_data);
        exit();
    }

/**
     * API: GET /exercises/api/published
     * Fetches exercises available for browsing, supporting filtering/pagination (R6).
     */
    public function api_get_published()
    {
        $offset = $_GET['offset'] ?? 0;
        $limit = $_GET['limit'] ?? 10;
        
        // --- MOCK DATA for published exercises list ---
        $mock_published_data = $this->generate_mock_exercises('all', $offset, $limit);
        
        header('Content-Type: application/json');
        echo json_encode($mock_published_data['exercises']);
        exit();
    }

    /**
     * API: GET /exercises/api/pending_review
     * Fetches the list of exercises pending review for the current Subject Expert (R5).
     */
    public function api_get_pending_review()
    {
        // Assume current user is Expert in 'Physics' and 'Maths'
        // --- MOCK DATA for pending review list ---
        $mock_pending_list = [
            [
                "id" => 150,
                "title" => "Newtonian Gravity Concepts",
                "subject" => "Physics",
                "creator_name" => "Mentor Alex",
                "created_at" => "2025-11-01 10:00:00"
            ],
            [
                "id" => 151,
                "title" => "Advanced Vector Spaces",
                "subject" => "Maths",
                "creator_name" => "Admin Bob",
                "created_at" => "2025-11-02 15:30:00"
            ]
        ];

        header('Content-Type: application/json');
        echo json_encode($mock_pending_list);
        exit();
    }
    
    /**
     * API: POST /exercises/api/attempt/{exercise_id}
     * Submits a user's answers and returns the calculated results (R7).
     */
    public function api_submit_attempt($exercise_id = null)
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || empty($exercise_id)) {
             $this->json_error("Invalid request or missing exercise ID.", 400);
        }
        // $input = json_decode(file_get_contents('php://input'), true); // Use this to get the input

        // --- MOCK DATA for attempt results (simulating scoring) ---
        $mock_result = [
            "attempt_id" => 2001,
            "total_score" => 12.5, 
            "total_max_score" => 15,
            "details" => [
                [
                    "question_id" => 301,
                    "prompt" => "What is the primary purpose of Encapsulation?",
                    "user_score" => 5,
                    "max_weight" => 5,
                    "explanation" => "Encapsulation hides implementation details, improving code maintainability and security.",
                    "options" => [
                        ["option_id" => 401, "text" => "To hide implementation details", "is_correct" => true, "was_selected" => true],
                        ["option_id" => 402, "text" => "To allow classes to inherit properties", "is_correct" => false, "was_selected" => false],
                    ]
                ]
            ]
        ];

        header('Content-Type: application/json');
        echo json_encode($mock_result);
        exit();
    }
    
    /**
     * API: GET /exercises/api/history
     * Fetches a list of the user's previously attempted exercises (R9).
     */
    public function api_get_attempt_history()
    {
        // --- MOCK DATA for attempt history list ---
        $mock_history = [
            [
                "attempt_id" => 2001,
                "exercise_title" => "Introduction to OOP Fundamentals",
                "subject" => "Computer Science",
                "score" => 12.5,
                "max_score" => 15,
                "attempted_at" => "2025-11-04 10:05:00"
            ],
            [
                "attempt_id" => 2002,
                "exercise_title" => "Advanced Algebra Practice",
                "subject" => "Maths",
                "score" => 7,
                "max_score" => 10,
                "attempted_at" => "2025-11-03 09:15:00"
            ]
        ];

        header('Content-Type: application/json');
        echo json_encode($mock_history);
        exit();
    }

    /**
     * API: GET /exercises/api/history/{attempt_id}
     * Fetches the detailed results and explanations for a past attempt (R9).
     */
    public function api_get_attempt_details($attempt_id = null)
    {
        if (empty($attempt_id)) {
            $this->json_error("Missing attempt ID.", 400);
        }
        
        // --- MOCK DATA for attempt details ---
        $mock_details = [
            "attempt_id" => $attempt_id,
            "exercise_title" => "Introduction to OOP Fundamentals",
            "total_score" => 12.5, 
            "total_max_score" => 15,
            "details" => [
                [
                    "question_id" => 301,
                    "prompt" => "What is the primary purpose of Encapsulation?",
                    "user_score" => 5,
                    "max_weight" => 5,
                    "explanation" => "Encapsulation hides implementation details, improving code maintainability and security.",
                    "options" => [
                        ["option_id" => 401, "text" => "To hide implementation details", "is_correct" => true, "was_selected" => true],
                        ["option_id" => 402, "text" => "To allow classes to inherit properties", "is_correct" => false, "was_selected" => false],
                    ]
                ]
            ]
        ];

        header('Content-Type: application/json');
        echo json_encode($mock_details);
        exit();
    }

    /**
     * API: POST /exercises/api/vote/{exercise_id}
     * Submits a vote (upvote/downvote) for an exercise (R8).
     */
    public function api_submit_vote($exercise_id = null)
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || empty($exercise_id)) {
            $this->json_error("Invalid request or missing exercise ID.", 400);
        }
        // $input = json_decode(file_get_contents('php://input'), true); // Get input
        // $vote_type = $input['vote_type'] ?? 'Upvote'; // Use this for actual logic

        // --- MOCK DATA for vote submission (Simulating Upvote) ---
        $mock_response = [
            "success" => true,
            "message" => "Vote successfully registered.",
            "current_vote_status" => 'Upvoted'
        ];

        header('Content-Type: application/json');
        echo json_encode($mock_response);
        exit();
    }

    /**
     * API: GET /exercises/api/vote/{exercise_id}
     * Gets the current user's vote status for an exercise.
     */
    public function api_get_vote_status($exercise_id = null)
    {
        if (empty($exercise_id)) {
            $this->json_error("Missing exercise ID.", 400);
        }
        
        // --- MOCK DATA for vote status ---
        $mock_response = [
            "current_vote_status" => 'None' 
        ];

        header('Content-Type: application/json');
        echo json_encode($mock_response);
        exit();
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
        $offset = $_GET['offset'] ?? 0;
        $limit = $_GET['limit'] ?? 5;
        $user_id = $_SESSION['user_id'] ?? 0; // Assuming session is active

        // 3. Build Filter Params for Model::filter_and_search
        $filter_params = [
            'select' => ['*'], // Or specific columns
            'limit' => $limit,
            'offset' => $offset,
            'where' => [],
            'like' => []
        ];

        // A. Handle "Tabs" (Business Logic)
        if ($tab === 'created') {
            $filter_params['where']['user_id'] = $user_id; // "Created by you"
        } 
        elseif ($tab === 'attempted') {
            // Note: This might require a JOIN or a separate lookup in a real app if 'attempted' status is in another table.
            // For this example, assuming 'relation' or similar logic exists, or we query a pivot table first.
            // Simplified: $filter_params['where']['status'] = 'attempted'; 
        }
        elseif ($tab === 'pending') {
             // Admin/Expert guard check recommended here
             $filter_params['where']['status'] = 'pending';
        }

        // B. Handle Search (Title)
        if (!empty($search)) {
            $filter_params['like']['title'] = $search; // Matches Model's LIKE logic
        }

        // C. Handle Advanced Filters
        if (!empty($subject)) {
            $filter_params['where']['subject'] = $subject;
        }

        // D. Handle Sorting
        if (!empty($sort)) {
            $parts = explode('-', $sort);
            if (count($parts) === 2) {
                $filter_params['order_by'] = $parts[0];   // e.g., 'title'
                $filter_params['order_dir'] = $parts[1];  // e.g., 'ASC'
            }
        }

        // 4. Fetch Data
        $exercises = $exerciseModel->filter_and_search($filter_params);

        // 5. Check if there are more results (for Load More button)
        // A common trick is to fetch limit + 1, then pop the last one to know if more exist.
        // But since we are using offset/limit standard, we can just check if count == limit
        // or run a separate count query. For simplicity here:
        $has_more = (count($exercises) == $limit); 
        // Note: The most accurate way is a separate count query or fetching +1.

        // 6. Return JSON
        $this->json_respond([
            'exercises' => $exercises,
            'has_more' => $has_more
        ]);
    }
}


