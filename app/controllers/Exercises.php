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

//     Array
// (
//     [exercise_id] => new
//     [question_order] => q_umi95lpmgvx1px0,q_ao6tgqfmgvx1v1p
//     [exercise_title] => Test exercise
//     [subject_name] => art
//     [tags] => science,art,maths
//     [questions_data] => [{"id":"q_umi95lpmgvx1px0","question_text":"q1","answer_type":"multiple_choice","options":["Option A","Option B"]},{"id":"q_ao6tgqfmgvx1v1p","question_text":"q2","answer_type":"multiple_choice","options":["Option A","Option B"]}]
// )

public function create()
    {
        //validate if user is a mentor


        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Process form submission to create a new exercise
            $exercise_title = $_POST['exercise_title'] ?? '';
            $subject_name = $_POST['subject_name'] ?? '';
            $tag_string = $_POST['tags'] ?? '';



            $question_order = $_POST['question_order'] ?? '';
            // NOTE: We need to decode the JSON as an associative array (true) for better iteration
            $questions_data = json_decode($_POST['questions_data'], true) ?? [];

            // Validate required fields
            if (empty($exercise_title)) {
                // Handle validation error (e.g., redirect back with error message)
                header('Location: '.ROOT.'/exercises/create?message=Exercise title is required');
                exit();
            }


            //load required models
            $exercises = new ExercisesModel;
            $tags = new Tags;
            $exercise_tag = new ExerciseTag;
            $subject = new Subjects;
            $exercisequestion = new Exercisequestion;
            $exerciseanswer = new Exerciseanswer;

            //check if subject exists, if not show error message
            $subject_data = $subject->first(['name' => $subject_name]);
            if (!$subject_data) {
                header('Location: '.ROOT.'/exercises/create?message=Subject does not exist');
                exit();
            }

            $current_user_id = $_SESSION['user_id'] ?? null;
            //create new exercise
            $new_exercise_id = $exercises->insert([
                'title' => $exercise_title,
                'subject_id' => $subject_data->id,
                'creator_id' => $current_user_id, // Replace with actual logged-in user ID
                'status' => 'pending', // New exercises are pending review
                'created_at' => date('Y-m-d H:i:s')
            ]);

            //for all tags check if exists, if not create new tag and add to questiontag
            $tag_list = array_map('trim', explode(',', $tag_string));
            $tag_ids = [];
            foreach ($tag_list as $tag_name) {
                $tag_data = $tags->first(['name' => $tag_name]);
                if (!$tag_data) {
                    // Create new tag
                    $new_tag_id = $tags->insert(['name' => $tag_name]);
                    $tag_ids[] = $new_tag_id;
                } else {
                    $tag_ids[] = $tag_data->id;
                }
    
            }

            //insert all tagids with exercise id to exercisetag
            foreach ($tag_ids as $tag_id) {
                $exercise_tag->insert([
                    'exercise_id' => $new_exercise_id,
                    'tag_id' => $tag_id
                ]);
            }

            // Insert questions and answers (UPDATED LOGIC)
            foreach ($questions_data as $question_data) {
                $new_question_id = $exercisequestion->insert([
                    'exercise_id' => $new_exercise_id,
                    'question_text' => $question_data['question_text'],
                    // Note: You might also want to save question_data['answer_type'] if your schema supports it
                ]);

                // Extract the array of correct indices, defaulting to an empty array
                $correct_indices = $question_data['correct_indices'] ?? []; 

                // Use the option index as a counter
                foreach ($question_data['options'] as $option_index => $option_text) {
                    
                    // Check if the current option index is in the correct_indices array
                    $is_correct = in_array($option_index, $correct_indices) ? 1 : 0;

                    $exerciseanswer->insert([
                        'question_id' => $new_question_id,
                        'answer_text' => $option_text,
                        'is_correct' => $is_correct // Now correctly marked as 1 or 0
                    ]);
                }
            }

            header('Location: '.ROOT.'/exercises/show?id='.$new_exercise_id);
            exit();


            



        } else {
            // Show the create exercise form
            $this->view('exercises/create');
        }
    }

    public function attempt()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Process exercise attempt submission
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

            //load relevent models

            //read exercise id
            $exercise_id = $_POST['exercise_id'] ?? 1;

            //read other data from post

            //validate data and permissions if redirect to edit page with error message

            //update relevent models

            //after update redirect to show page
            header('Location: '.ROOT.'/exercises/show?id='.$exercise_id);
        } else {


            $exercise_id = $_GET['id'] ?? 1;

            if (!$exercise_id) {
                // Handle missing exercise ID (e.g., redirect or show error)
                header('Location: '.ROOT.'/exercises?message=Exercise ID is required to edit an exercise');
            }
    
            $exercises = new ExercisesModel;
            $exercise_data = $exercises->first(['id' => $exercise_id]);

            if (!$exercise_data) {
                header('Location: '.ROOT.'/exercises/show?id=1');
            }
    
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
                    'exercise_id' => $exercise_data->id,
                    'title' => $exercise_data->title,
                    'subject' => $subject_name,
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

            $this->view('exercises/edit', $data);
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
}


