<?php

class Profile extends Controller
{
    public function index()
    {
        $this->login_guard();

        $user_id = $_SESSION['user_id'];


        $user = new User;
        
        $role = $user->get_role($user_id);

        $results = $user->first(['id'=>$user_id]);

        if ($results === false) {
            echo "user not found!";
        }


        $analysis_services = new AnalysisServices;

        $total_notes = $analysis_services->get_total_notes($user_id);

        $total_exercises = $analysis_services->get_total_exercises($user_id);

        $total_questions = $analysis_services->get_total_questions($user_id);

        $total_answers = $analysis_services->get_total_answers($user_id);

        $total_upvotes = $analysis_services->get_total_upvotes($user_id);

        $total_points = $analysis_services->get_total_points($user_id);


        $this->view("profile", [
            "profile_picture_path"=>ROOT.$results->profile_picture,
            "display_name"=>$results->display_name,
            "created_at"=>$results->created_at,
            "role"=>$role,
            "total_notes"=>$total_notes,
            "total_exercises"=>$total_exercises,
            "total_questions"=>$total_questions,
            "total_answers"=>$total_answers,
            "total_upvotes"=>$total_upvotes,
            "total_points"=>$total_points,
        ]);
    }


    public function delete()
    {
        $_SESSION['verification_for'] = 'delete_profile';

        header('Location: '.ROOT.'accountverification');
        exit;
    }
}