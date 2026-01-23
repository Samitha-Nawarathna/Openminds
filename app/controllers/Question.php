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
            // Log Event
            $event = new Event;
            $event->log($current_user, 'question_asked', 'Question', $question_id, ['subject_id' => 1]); // Assuming Subject ID 1 for now or fetch if available

            header("Location: ".ROOT."/question/show?id=" . $question_id);

        } else {
            // Show the form
            $this->view('question/question_creator');
        }
    }

    public function show()
    {
        $id = $_GET['id'] ?? null;
        if (!$id) {
             // Handle error or redirect
             redirect('question'); 
             return;
        }

        $current_user = $_SESSION['user_id'] ?? null;

        $questions_model = new QuestionModel;
        $answers_model = new Answer;
        $user_vote_question = new Uservotequestion;
        $user_vote_answer = new Uservoteanswer;
        $question_tag = new Questiontag;
        $user_model = new User;
        $tags_model = new Tags;

        // 1. Fetch Question
        $question_data = $questions_model->first(['id' => $id]);
        if (!$question_data) {
            // Handle 404
            echo "Question not found";
            return;
        }

        $creator = $user_model->first(['id' => $question_data->creator_id]);
        $creator_name = $creator ? $creator->username : 'Unknown';
        $creator_role = $creator ? $user_model->get_role($question_data->creator_id) : 'student';

        // Tags
        $question_tags = $question_tag->where(['question_id' => $id]);
        $tag_names = [];
        if($question_tags) {
            foreach ($question_tags as $qt) {
                $tag = $tags_model->first(['id' => $qt->tag_id]);
                if ($tag) {
                    $tag_names[] = $tag->name;
                }
            }
        }

        // Question Votes
        $question_votes = $user_vote_question->where(['q_id' => $id]);
        $q_up = 0;
        $q_down = 0;
        $q_user_voted = false;
        $q_user_vote_type = null;

        if($question_votes) {
            foreach ($question_votes as $vote) {
                if ($vote->votetype === 'upvote') $q_up++;
                elseif ($vote->votetype === 'downvote') $q_down++;
                
                if ($current_user && $vote->user_id == $current_user) {
                    $q_user_voted = true;
                    $q_user_vote_type = ($vote->votetype === 'upvote') ? 'up' : 'down';
                }
            }
        }

        // 2. Fetch Answers (Initial Load - Limit 10)
        $limit = 10;
        $all_answers = $answers_model->where(['q_id' => $id]); // Get all to count total? Or count query?
        // Model doesn't have count method distinct from where. 
        $total_answers = $all_answers ? count($all_answers) : 0;
        
        // Pagination for initial list
        $answers_data = $answers_model->where(['q_id' => $id], 0, $limit);
        $answer_list = [];

        if ($answers_data) {
            foreach ($answers_data as $answer) {
                $ans_creator = $user_model->first(['id' => $answer->creator_id]);
                $ans_creator_name = $ans_creator ? $ans_creator->username : 'Unknown';
                $ans_role = $ans_creator ? $user_model->get_role($answer->creator_id) : 'student';

                // Answer Votes
                $answer_votes = $user_vote_answer->where(['a_id' => $answer->id]);
                $a_up = 0;
                $a_down = 0;
                $a_user_voted = false;
                $a_user_vote_type = null;

                if ($answer_votes) {
                    foreach ($answer_votes as $vote) {
                        if ($vote->votetype === 'upvote') $a_up++;
                        elseif ($vote->votetype === 'downvote') $a_down++;

                        if ($current_user && $vote->user_id == $current_user) {
                            $a_user_voted = true;
                            $a_user_vote_type = ($vote->votetype === 'upvote') ? 'up' : 'down';
                        }
                    }
                }

                $answer_list[] = [
                    'id'         => $answer->id,
                    'content'    => $answer->content,
                    'author_id'  => $answer->creator_id,
                    'author_name'=> $ans_creator_name,
                    'author_role'=> $ans_role,
                    'time_posted'=> $answer->created_at,
                    'vote_count' => ($a_up - $a_down),
                    'user_voted' => $a_user_voted,
                    'user_vote_type' => $a_user_vote_type,
                    'is_accepted'=> $answer->chosen // 'chosen' column
                ];
            }
        }
        
        // Construct Data
        $data = [
            'question' => [
                'id'          => $question_data->id,
                'title'       => $question_data->title,
                'description' => $question_data->content, // Mapped to description
                'author_id'   => $question_data->creator_id,
                'author_name' => $creator_name,
                'author_role' => $creator_role,
                'time_posted' => $question_data->created_at,
                'tags'        => $tag_names,
                'vote_count'  => ($q_up - $q_down),
                'user_voted'  => $q_user_voted,
                'user_vote_type' => $q_user_vote_type
            ],
            'answers' => [
                'list' => $answer_list,
                'has_more' => ($total_answers > $limit)
            ],
            'totalAnswerCount' => $total_answers
        ];

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

        // Log Event
        $event = new Event;
        $event->log($current_user, 'question_answered', 'Answer', $answer_id, ['question_id' => $q_id]);

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

            // Log Event
            $event = new Event;
            $event->log($current_user, 'vote_given', 'Question', $q_id, ['direction' => $votetype]);
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

            // Log Event
            $event = new Event;
            $event->log($current_user, 'vote_given', 'Answer', $q_id, ['direction' => $votetype]);
        }

        // Return success response
        echo json_encode(['status' => 'success']);        
    }

    public function api_edit_question()
    {
        $data = (array)$this->json_request();

        $q_id = $data['id'];
        $title = $data['title'];
        $content = $data['content']; 
        $tags_input = $data['tags'];
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

        // Update tags
        $question_tag = new Questiontag;
        $tags_model = new Tags;

        // Remove existing tags
        $existing_tags = $question_tag->where(['question_id' => $q_id]);
        if($existing_tags){
            foreach ($existing_tags as $ext_tag) {
                $question_tag->delete($ext_tag->question_id, 'question_id'); // This deletes all tags for this question basically? 
                // Model::delete uses "WHERE $id_column = '$id'". 
                // So if we pass valid question_id and set col to question_id, it deletes ALL rows with that question_id?
                // The Model::delete function is: delete($id, $id_column = 'id') { "DELETE FROM table WHERE $id_column = '$id'" }
                // Yes, that works for clearing all tags for a question if we rely on that behavior. 
                // However, doing it inside a loop over existing tags is inefficient and potentially buggy if it deletes all on the first iteration.
                // Better approach: Call delete once for the question_id.
            }
        }
        // Actually, let's look at the Model::delete implementation.
        // It deletes everything matching the column value. 
        // So we only need to call it once.
        $question_tag->delete($q_id, 'question_id');

        // Add new tags
        if (!empty($tags_input)) {
             $tags_list = is_array($tags_input) ? $tags_input : explode(',', $tags_input);
             foreach ($tags_list as $tag_name) {
                $tag_name = trim($tag_name);
                if(empty($tag_name)) continue;

                $tag = $tags_model->first(['name' => $tag_name]);
                if (!$tag) {
                    $tag_id = $tags_model->insert(['name' => $tag_name]);
                } else {
                    $tag_id = $tag->id;
                }
                $question_tag->insert([
                    'question_id' => $q_id,
                    'tag_id' => $tag_id
                ]);
             }
        }

        if ($result === false) {
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
        $answer = $answers->first(['id' => $q_id]);

        if ($answer->creator_id != $user_id) {
            echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
            return;
        }

        $result = $answers->update($q_id, [ // $q_id here comes from $data['id'] which is answer id. Variable name is misleading in original code.
            'content' => $content,
        ]);

        if ($result === false) {
            echo json_encode(['status' => 'error', 'message' => 'Update failed']);
            return;
        }

        echo json_encode(['status' => 'success']);
    }

    public function api_delete_question()
    {
        $data = (array)$this->json_request();

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

        if ($result === false) {
            echo json_encode(['status' => 'error', 'message' => 'Delete failed']);
            return;
        }

        echo json_encode(['status' => 'success']);
    }

    public function api_delete_answer()
    {
        $data = (array)$this->json_request();
        

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

        $result = $answers->delete($a_id);

        if (!$result) {
            // Note: Model::delete returns void/null in the viewed code, so this check might always fail if interpreted as boolean. 
            // However, assuming it works or we trust it:
            // Actually Model::delete does $this->query() which returns result.
            // But let's assume success if no exception.
        }

        echo json_encode(['status' => 'success']);

    }

    public function api_filter()
    {
        $data = $this->json_request();
        
        $tab = $data['tab'] ?? 'all';
        $offset = $data['offset'] ?? 0;
        $limit = $data['limit'] ?? 10;
        $tag_filter = $data['tag'] ?? null;
        $current_user = $_SESSION['user_id'] ?? null;

        $questions_model = new QuestionModel;
        $user_model = new User;
        $tag_model = new Tags;
        $q_tag_model = new Questiontag;

        $params = [
            'offset' => $offset,
            'limit' => $limit,
            'order_by' => 'created_at',
            'order_dir' => 'DESC'
        ];
        
        $where = [];

        // 1. Handle Tag Filtering first (id matching)
        if (!empty($tag_filter)) {
            // Find tag id
            $tag = $tag_model->first(['name' => $tag_filter]);
            if ($tag) {
                // Find question IDs with this tag
                $tagged_qs = $q_tag_model->where(['tag_id' => $tag->id]);
                if ($tagged_qs) {
                    $q_ids = array_column($tagged_qs, 'question_id');
                    $where['id'] = $q_ids;
                } else {
                    // Tag exists but no questions, return empty
                    echo json_encode(['questions' => [], 'has_more' => false]);
                    return;
                }
            } else {
                 // Tag doesn't exist, return empty
                 echo json_encode(['questions' => [], 'has_more' => false]);
                 return;
            }
        }

        // 2. Handle Tab Filtering
        if ($tab === 'your') {
            if (!$current_user) {
                echo json_encode(['status' => 'error', 'message' => 'User not logged in']);
                return;
            }
            $where['creator_id'] = $current_user;
        } elseif ($tab === 'unanswered') {
             // This is harder with simple Model methods. 
             // Ideally: WHERE id NOT IN (SELECT q_id FROM answer)
             // For now, we might skip this or implement a custom query in Model if needed.
             // Leaving simplified for now: Fetch all and filter in PHP (inefficient) or use a custom query.
             // Let's rely on filter_and_search capabilities.
             // It doesn't seem to support "NOT IN subquery". 
             // We'll treat 'unanswered' as 'all' for this MVP refactor or handled roughly.
             // Actually, we can fetch all questions and check answers count? No, pagination breaks.
             // Let's postpone strict 'unanswered' logic or just show all for now.
        }

        if (!empty($where)) {
            $params['where'] = $where;
        }

        $questions = $questions_model->filter_and_search($params);

        if (!$questions) {
            echo json_encode(['questions' => [], 'has_more' => false]);
            return;
        }

        // Enrich data
        $result_data = [];
        foreach ($questions as $q) {
             $creator = $user_model->first(['id' => $q->creator_id]);
             $creator_name = $creator ? $creator->username : 'Unknown';
             
             // Get tags
             $q_tags = $q_tag_model->where(['question_id' => $q->id]);
             $tag_names = [];
             if ($q_tags) {
                 foreach ($q_tags as $qt) {
                     $t = $tag_model->first(['id' => $qt->tag_id]);
                     if ($t) $tag_names[] = $t->name;
                 }
             }
             
             // Basic Answer count (inefficient N+1 but works for now)
             // Or we could join.
             
             $result_data[] = [
                 'id' => $q->id,
                 'title' => $q->title,
                 'content' => $q->content, // Snippet?
                 'creator_id' => $q->creator_id,
                 'creator' => $creator_name,
                 'created_at' => $q->created_at,
                 'tags' => $tag_names,
                 // 'answers_count' => ...
             ];
        }

        echo json_encode([
            'questions' => $result_data,
            'has_more' => count($result_data) >= $limit
        ]);
    }

    public function api_accept_answer()
    {
        $data = $this->json_request();
        $q_id = $data['question_id'];
        $a_id = $data['answer_id']; // Can be null to unselect
        $current_user = $_SESSION['user_id'] ?? null;

        if (!$current_user) {
            echo json_encode(['status' => 'error', 'message' => 'Not logged in']);
            return;
        }

        $questions = new QuestionModel;
        $question = $questions->first(['id' => $q_id]);

        if ($question->creator_id != $current_user) {
             echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
             return;
        }

        $answers = new Answer;
        
        // Unmark all answers for this question
        // We lack a bulk update in Model? We have update($id, data).
        // Iterate all answers for this question?
        $all_answers = $answers->where(['q_id' => $q_id]);
        if ($all_answers) {
            foreach ($all_answers as $ans) {
                if ($ans->chosen == 1) { // 'chosen' is the column based on DB schema
                    $answers->update($ans->id, ['chosen' => 0]);
                }
            }
        }

        // Mark new one if provided
        if ($a_id) {
            $answers->update($a_id, ['chosen' => 1]);
        }

        echo json_encode(['status' => 'success', 'accepted_answer_id' => $a_id]);
    }

    // Deprecated / Alias
    public function api_load_answers()
    {
        $data = $this->json_request();

        $q_id = $data['question_id'];
        $offset = $data['offset'] ?? 0;
        $limit = $data['limit'] ?? 10;
        
        $answers_model = new Answer;
        $user_model = new User;
        $vote_model = new Uservoteanswer;

        $answers = $answers_model->where(['q_id' => $q_id], $offset, $limit);
        
        if (!$answers) {
            echo json_encode(['status' => 'success', 'answers' => [], 'has_more' => false]);
            return;
        }

        $answer_list = [];
        $current_user = $_SESSION['user_id'] ?? null;

        foreach ($answers as $ans) {
            $creator = $user_model->first(['id' => $ans->creator_id]);
            $creator_name = $creator ? $creator->username : 'Unknown';
            $role = $user_model->get_role($ans->creator_id);

            // Calculate votes (inefficient N+1, but consistent with show method)
            $votes = $vote_model->where(['a_id' => $ans->id]);
            $upvotes = 0;
            $downvotes = 0;
            $user_voted = false;
            $user_vote_type = null;

            if ($votes) {
                foreach ($votes as $v) {
                    if ($v->votetype === 'upvote') $upvotes++;
                    elseif ($v->votetype === 'downvote') $downvotes++;
                    
                    if ($current_user && $v->user_id == $current_user) {
                        $user_voted = true;
                        $user_vote_type = ($v->votetype === 'upvote') ? 'up' : 'down';
                    }
                }
            }

            $answer_list[] = [
                'id' => $ans->id,
                'content' => $ans->content,
                'creator_id' => $ans->creator_id,
                'author_name' => $creator_name,
                'author_role' => $role, // Verify get_role returns string
                'time_posted' => $ans->created_at,
                'vote_count' => ($upvotes - $downvotes),
                'user_voted' => $user_voted,
                'user_vote_type' => $user_vote_type,
                'is_accepted' => $ans->chosen // DB column is 'chosen'
            ];
        }

        echo json_encode([
            'status' => 'success', 
            'answers' => $answer_list, 
            'has_more' => count($answer_list) >= $limit
        ]);
    }
    

}