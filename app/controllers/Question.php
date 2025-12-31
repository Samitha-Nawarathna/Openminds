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
            $data = [
                'question_id'     => 1,
                'question_title'  => 'What are myelinated axons?',
                'answer_id' => 'a_202',
                'answer_content' => 'This is the existing content of the answer that is being edited.',
                
            ];

            $this->view('question/edit_answer', $data);
        }
    }

    public function delete_answer()
    {
        $a_id = $_GET['id'];
        // Delete answer from the database using $a_id
        //show success message or error
        header("Location: /question/view?id=" . $_GET['q_id']);
    }

    //---------------------------------------------------------------//
    //-----------------------AJAX METHODS----------------------------//
    //---------------------------------------------------------------//

    public function api_create_answer()
    {
        $data = $this->json_request();


        $current_user = $_SESSION['user_id'] ?? null;
        $content = $data['content'];
        $q_id = $data['q_id'];

        // Save answer to the database
        $answer = new Answer;

        $answer_id = $answer->insert([
            'content' => $content,
            'q_id' => $q_id,
            'creator_id' => $current_user,
            'created_at' => date('Y-m-d H:i:s')
        ]);

        // Return success response
        echo json_encode(['status' => 'success', 'answer_id' => $answer_id]);

    }

    public function api_vote_question()
    {
        $data = $this->json_request();

        $current_user = $_SESSION['user_id'] ?? null;
        $q_id = $data['q_id'];
        $votetype = $data['votetype'];

        $user_vote_question = new Uservotequestion;

        // Check if user has already voted
        $existing_vote = $user_vote_question->first(['user_id' => $current_user, 'q_id' => $q_id]);

        if ($existing_vote) {
            // Update existing vote
            if ($existing_vote->votetype === $votetype) {
                $user_vote_question->delete($existing_vote->id);    
            }else
            {
                //fix vote id issue 
                $user_vote_question->update($existing_vote->id, [
                    'votetype' => $votetype
                ]);
            }
            
        } else {
            // Insert new vote
            $user_vote_question->insert([
                'user_id' => $current_user,
                'q_id' => $q_id,
                'votetype' => $votetype
            ]);
        }

        // Return success response
        echo json_encode(['status' => 'success']);
    }

    public function api_vote_answer()
    {
        $data = $this->json_request();

        $current_user = $_SESSION['user_id'] ?? null;
        $q_id = $data['q_id'];
        $votetype = $data['votetype'];

        $user_vote_answer = new Uservoteanswer;

        // Check if user has already voted
        $existing_vote = $user_vote_answer->first(['user_id' => $current_user, 'q_id' => $q_id]);

        if ($existing_vote) {
            // Update existing vote
            if ($existing_vote->votetype === $votetype) {
                $user_vote_answer->delete($existing_vote->id);    
            }else
            {
                //fix vote id issue 
                $user_vote_answer->update($existing_vote->id, [
                    'votetype' => $votetype
                ]);
            }
            
        } else {
            // Insert new vote
            $user_vote_answer->insert([
                'user_id' => $current_user,
                'q_id' => $q_id,
                'votetype' => $votetype
            ]);
        }

        // Return success response
        echo json_encode(['status' => 'success']);        
    }

    public function api_edit_question()
    {
        $data = $this->json_request();

        $q_id = $data['id'];
        $title = $data['title'];
        $content = $data['content']; 
        $user_id = $_SESSION['user_id'];

        if (!isset($user_id)) {
            echo json_encode(['status' => 'error', 'message' => 'User not logged in']);
            return;
        }

        $questions = new QuestionModel;
        $question = $questions->first(['id' => $q_id]);

        if ($question->creator_id != $user_id) {
            echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
            return;
        }

        $result = $questions->update($q_id, [
            'title' => $title,
            'content' => $content,
        ]);

        if (!$result) {
            echo json_encode(['status' => 'error', 'message' => 'Update failed']);
            return;
        }

        echo json_encode(['status' => 'success']);
    }

    public function api_edit_answer()
    {
        $data = $this->json_request();

        $q_id = $data['id'];
        $content = $data['content']; 
        $user_id = $_SESSION['user_id'];

        if (!isset($user_id)) {
            echo json_encode(['status' => 'error', 'message' => 'User not logged in']);
            return;
        }

        $answers = new Answer;
        $answer = $answer->first(['id' => $q_id]);

        if ($answer->creator_id != $user_id) {
            echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
            return;
        }

        $result = $answers->update($q_id, [
            'title' => $title,
            'content' => $content,
        ]);

        if (!$result) {
            echo json_encode(['status' => 'error', 'message' => 'Update failed']);
            return;
        }

        echo json_encode(['status' => 'success']);
    }

    public function api_delete_question()
    {
        $data = $this->json_request();

        $q_id = $data['id'];
        $user_id = $_SESSION['user_id'];

        if (!isset($user_id)) {
            echo json_encode(['status' => 'error', 'message' => 'User not logged in']);
            return;
        }

        $questions = new QuestionModel;
        $answers = new Answer;
        $question = $questions->first(['id' => $q_id]);

        $answer = $answers->first(['q_id' => $q_id]);

        if ($answer)
        {
            echo json_encode(['status' => 'error', 'message' => 'has answers']);
            return;
        }

        if ($question->creator_id != $user_id) {
            echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
            return;
        }

        $result = $questions->delete($q_id);

        if (!$result) {
            echo json_encode(['status' => 'error', 'message' => 'Delete failed']);
            return;
        }

        echo json_encode(['status' => 'success']);
    }

    public function api_delete_answer()
    {
        $data = $this->json_request();

        $a_id = $data['id'];
        $user_id = $_SESSION['user_id'];

        if (!isset($user_id)) {
            echo json_encode(['status' => 'error', 'message' => 'User not logged in']);
            return;
        }

        $answers = new Answer;
        $answer = $answers->first(['id' => $a_id]);


        if ($answer->creator_id != $user_id) {
            echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
            return;
        }

        $result = $answer->delete($q_id);

        if (!$result) {
            echo json_encode(['status' => 'error', 'message' => 'Delete failed']);
            return;
        }

        echo json_encode(['status' => 'success']);

    }

    public function api_load_more()
    {
        $data = json_request();

        $q_id = $data['$q_id'];
        $limit = $data['limit'];
        $lost_q_id = $data['last_q_id'];

        //implement here
        echo json_encode(['status' => 'success', 'topics'=> [['id'=>'t_10','name'=>'New Topic 1'],['id'=>'t_11','name'=>'New Topic 2']] , 'has_more' => false]);
    }
    

}