<?php

class Profileadmin extends Controller
{
    public function index()
    {
        $this->admin_guard();

        $this->view("profilebrowser");
    }

    private function admin_guard()
    {

    }

    public function profile()
    {
        // $this->login_guard();

        $user_id = $_GET['id'] ?? null;
        
        if ($user_id === false) {
            header("Location: ".ROOT."profileadmin?message=Invalid Profile ID");
        }


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


        $this->view("profileadmin/profile", [
            "id"=>$results->id,
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
            "banned"=>$results->banned,
        ]);
    }

    public function ban()
    {
        $this->admin_guard();

        $user_id = $_GET['id'] ?? null;

        if ($user_id === null) {
            header("Location: ".ROOT."profileadmin?message=Invalid Profile ID");
            exit;
        }

        $user = new User;

        $results = $user->first(['id'=>$user_id]);

        if ($results === false) {
            header("Location: ".ROOT."profileadmin?message=User not found");
            exit;
        }

        if ($results->banned) {
            header("Location: ".ROOT."profileadmin/profile?id=$user_id&message=User is already banned");
            exit;
        }

        $user->update($user_id, ['banned'=>1]);

        header("Location: ".ROOT."profileadmin/profile?id=$user_id&message=User has been banned successfully");
    }

    public function unban()
    {
        $this->admin_guard();

        $user_id = $_GET['id'] ?? null;

        if ($user_id === null) {
            header("Location: ".ROOT."profileadmin?message=Invalid Profile ID");
            exit;
        }

        $user = new User;

        $results = $user->first(['id'=>$user_id]);

        if ($results === false) {
            header("Location: ".ROOT."profileadmin?message=User not found");
            exit;
        }

        if (!$results->banned) {
            header("Location: ".ROOT."profileadmin/profile?id=$user_id&message=User is not banned");
            exit;
        }

        $user->update($user_id, ['banned'=>0]);

        header("Location: ".ROOT."profileadmin/profile?id=$user_id&message=User has been unbanned successfully");
    }

    public function changerole()
    {
    
    }

}