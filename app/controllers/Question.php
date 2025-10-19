<?php

class Question extends Controller
{
    public function index()
    {

        $this->view('question/browser');
    }


//     Array
// (
//     [title] => test_question
//     [content] => question content
//     [tags] => r,re
// )


    public function create()
    {
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            $current_user = $_SESSION['user_id'] ?? null;

            $title = $_POST['title'];
            $content = $_POST['content'];
            $tags_list = explode(',',$_POST['tags']);

            // Save question to the database
            $tags = new Tags;
            $question = new QuestionModel;
            $question_tag = new Questiontag;

            $question_id = $question->insert([
                'title' => $title,
                'content' => $content,
                'creator_id' => $current_user,
                'created_at' => date('Y-m-d H:i:s')
            ]);

            foreach ($tags_list as $tag_name) {
                $tag = $tags->first(['name' => trim($tag_name)]);
                if (!$tag) {
                    // If tag does not exist, create it
                    $tag_id = $tags->insert(['name' => trim($tag_name)]);
                } else {
                    $tag_id = $tag->id;
                }

                // Associate tag with question
                $question_tag->insert([
                    'question_id' => $question_id,
                    'tag_id' => $tag_id
                ]);
            }

            // Redirect to the question view page
            header("Location: ".ROOT."/question/show?id=" . $question_id);

        } else {
            // Show the form
            $this->view('question/question_creator');
        }
    }

    public function show()
    {
        $id = $_GET['id'] ?? 1;
        // Fetch question from the database using $id
        $current_user = $_SESSION['user_id'] ?? 'user_2';

        $questions = new QuestionModel;
        $answers = new Answer;
        $user_vote_question = new Uservotequestion;
        $user_vote_answer = new Uservoteanswer;
        $question_tag = new Questiontag;
        $user = new User;
        $tags = new Tags;


        $question_data = $questions->first(['id' => $id]);
        $creator = $user->first(['id' => $question_data->creator_id])->username;

        $question_tags = $question_tag->where(['question_id' => $id]);
        $tag_names = [];
        foreach ($question_tags as $qt) {
            $tag = $tags->first(['id' => $qt->tag_id]);
            if ($tag) {
                $tag_names[] = $tag->name;
            }
        }

        $question_upvotes = 0;
        $question_downvotes = 0;
        $question_votes = $user_vote_question->where(['q_id' => $id]);

        foreach ($question_votes as $vote) {
            if ($vote->votetype === 'upvote') {
                $question_upvotes++;
            } elseif ($vote->votetype === 'downvote') {
                $question_downvotes++;
            }
        }

        $answers_data = $answers->where(['q_id' => $id]);
        $answer_list = [];

        foreach ($answers_data as $answer) {
            $answer_upvotes = 0;
            $answer_downvotes = 0;
            $answer_votes = $user_vote_answer->where(['a_id' => $answer->id]);

            foreach ($answer_votes as $vote) {
                if ($vote->votetype === 'upvote') {
                    $answer_upvotes++;
                } elseif ($vote->votetype === 'downvote') {
                    $answer_downvotes++;
                }
            }

            $answer_list[] = [
                'id'         => $answer->id,
                'is_chosen'  => $answer->is_chosen ?? false,
                'content'    => $answer->content,
                'creator'    => $user->first(['id' => $answer->creator_id])->username,
                'creator_id' => $answer->creator_id,
                'role'       => $user->get_role($answer->creator_id),
                'created_at' => $answer->created_at,
                'upvotes'    => $answer_upvotes,
                'downvotes'  => $answer_downvotes
            ];
        }
        
        $data = [
            'current_user_id' => $current_user, // Simulating that 'Bob' is the logged-in user.
                                           // Change to 'user_1' to see buttons on the question.
                                           // Change to 'user_3' to see the default view for a visitor.
            'question' => [
                'id'         => $question_data->id,
                'title'      => $question_data->title,
                'content'    => $question_data->content,
                'creator'    => $creator,
                'creator_id' => $question_data->creator_id, // Added creator ID
                'created_at' => $question_data->created_at,
                'upvotes'    => $question_upvotes,
                'downvotes'  => $question_downvotes,
                'tags'       => $tag_names
            ],
            'answers' => 
                $answer_list
                // You can add more answers here to test
        ];


        // Display the question
        $this->view('question/view', $data);
    }

    public function edit()
    {
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            //read question id
            $q_id = $_POST['id'];

            //read form data

            //load relevent models

            // retrive question from database

            //validate data and ownership of question if not by creator then redirect to show page with error message

            //if question has atleast one answer then cannot edit and redirect to show page with error message

            // Update question in the database


            header("Location: ".ROOT."/question/show?id=" . $q_id);
        } else {
            $id = $_GET['id'] ?? 2;
            //load relevant models

            // Fetch question from the database using $id

            // Validate ownership and check for existing answers if answers exist or not by creator then cannot edit

            // If valid, proceed to show edit form

            // populate data array

            // MOCK DATA FOR NOW UNTIL DB INTEGRATION --- should be replaced by actual data from DB

            $data = [
                'id'      => 2,
                'title'   => 'What are myelinated axons?',
                'content' => 'I have heard about myelinated axons in my biology class, but I am not sure what they are. Can someone explain?',
                'tags'    => ['biology', 'neuroscience', 'anatomy'],
            ];
            
            $this->view('question/edit_question', $data);
        }
    }

    public function delete()
    {
        //read question id from post method
        
        //load relevant models

        // retrive question from database

        //validate data and ownership of question if not by creator then redirect to show page with error message

        //cannot edit atleast one answer is given

        // Delete question from the database using $id

        //show success message or error
        header("Location: ".ROOT."/question");
    }

    public function answer()
    {
        $q_id = $_GET['id'] ?? 1;
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            // Save answer to the database

            header("Location: ".ROOT."/question/show?id=" . $_POST['question_id']);
        } else {
            // Show the answer form
            $this->view('question/answer_creator', ['q_id' => $q_id]);
        }
    }

    public function edit_answer()
    {
        $a_id = 1;//$_GET['id'];
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            // Update answer in the database
            header("Location: ".ROOT."/question/show?id=" . $_POST['question_id']);
        } else {
            // Fetch answer from the database using $a_id
            // Show the edit form
            $this->view('question/edit_answer', ['a_id' => $a_id]);
        }
    }

    public function delete_answer()
    {
        $a_id = $_GET['id'];
        // Delete answer from the database using $a_id
        //show success message or error
        header("Location: /question/view?id=" . $_GET['q_id']);
    }
}