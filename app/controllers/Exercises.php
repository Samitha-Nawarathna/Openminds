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
                ['id' => 'e1', 'title' => 'what is lagrangian method?', 'subject' => 'Physics', 'relation' => 'Created'],
                ['id' => 'e2', 'title' => 'how Jacobian related to gradient?', 'subject' => 'Maths', 'relation' => 'Created'],
                ['id' => 'e3', 'title' => 'solve in Hamiltonian mechanics?', 'subject' => 'Physics', 'relation' => 'Attempted'],
                ['id' => 'e4', 'title' => 'what does this operator do?', 'subject' => 'Quantum Computing', 'relation' => 'Created'],
                ['id' => 'e5', 'title' => 'how shadow work described by jung?', 'subject' => 'Psychology', 'relation' => 'Attempted'],
                ['id' => 'e6', 'title' => 'how to solve this in linear algebra?', 'subject' => 'Maths', 'relation' => 'Created'],
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

    public function create()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Process form submission to create a new exercise
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

        $exercise_id = $_GET['id'] ?? null;
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

    public function evaluate()
    {

    }

    public function show()
    {
        
    }

    public function edit()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Process form submission to update exercise
        } else {
            $id = $_GET['id'] ?? null;
            //check if user is authorized to edit the exercise

            $this->view('exercises/edit', ['exercise_id' => $id]);
        }
    }
}