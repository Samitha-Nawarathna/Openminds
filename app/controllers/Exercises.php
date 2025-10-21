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


            $id = $_GET['id'] ?? null;
            //check if user is authorized to edit the exercise if not redirect to show page with error message

            //load relevent models and data to prefill the edit form

            //populated $data array with existing exercise data
            
            // --- MOCK DATA SETUP --- should be replaced with actual data from database
            $data = [
                'exercise_id' => 1,
                'exercise_title' => 'Fundamental Physics and Maths',
                'subject_name' => 'Physics',
                'current_tags' => ['mechanics', 'quantum', 'maths'],
                'form_action_url' => '/your-backend-controller/update-exercise',
                
                'top_tags' => [
                    ['name' => 'Physics', 'count' => 12],
                    ['name' => 'Psychology', 'count' => 9],
                    ['name' => 'Maths', 'count' => 15],
                ],
                
                // Mock Questions Data - ALL ARE NOW MULTIPLE CHOICE
                'questions' => [
                    [
                        'id' => 'q1',
                        'question_text' => 'What is the relationship between the Lagrangian and Hamiltonian functions?',
                        'answer_type' => 'multiple_choice', // Only MCQs
                        'options' => ['They are Legendre transforms.', 'They are inverses.', 'They are independent.'],
                        'author' => 'Alice'
                    ],
                    [
                        'id' => 'q2',
                        'question_text' => 'Which color harmony creates the highest contrast?',
                        'answer_type' => 'multiple_choice', 
                        'options' => ['Analogous', 'Monochromatic', 'Complementary'],
                        'author' => 'Bob a student'
                    ],
                    [
                        'id' => 'q3',
                        'question_text' => 'The Hamiltonian in classical mechanics typically represents the total energy. Which concept is its quantum counterpart?',
                        'answer_type' => 'multiple_choice', 
                        'options' => ['Momentum operator', 'Schrödinger operator', 'Hamiltonian operator'],
                        'author' => 'Alice'
                    ],
                ]
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

    public function viewattempt()
    {
        $id = $_GET['id'] ?? null;

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
}
