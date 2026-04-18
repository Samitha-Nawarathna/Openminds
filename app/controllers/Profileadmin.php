<?php

class Profileadmin extends Controller
{
    public function index()
    {
        $this->admin_guard();
        $subjects_model = new Subjects;
        $data['subjects'] = $subjects_model->findAll();
        // show($data);


        $this->view("profilebrowser", $data);
    }

    public function admin_guard()
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
        // show($role);

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
        $experts_model = new Experts;
        $subject_model = new Subjects;

        $subjects = $experts_model->where(['user_id'=>$user_id], ['subject_id']);
        $subjects_str = [];

        foreach ($subjects as $key => $subject) {
            $result_name = $subject_model->first(['id'=>$subject->subject_id])->name;

            if ($result_name)
            {
                $subjects_str[] = $result_name;
            }
            
        }
        $data['subjects_str'] = $subjects_str;
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
            "subjects_str"=>$subjects_str,
        ]);
    }

    public function ban()
    {
        $this->admin_guard();

        $user_id = $_POST['user_id'] ?? null;

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

        $reason_for_ban = $_POST['reason_for_ban'] ?? 'No reason provided';

        $user->update($user_id, ['banned'=>1, 'reason_for_ban' => $reason_for_ban]);

        header("Location: ".ROOT."profileadmin/profile?id=$user_id&message=User has been banned successfully");
    }

    public function unban()
    {
        $this->admin_guard();

        $user_id = $_POST['user_id'] ?? null;

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
        
        $this->admin_guard();

        $user_id = $_POST['user_id'] ?? null;
        $new_role = $_POST['new_role'] ?? null;
        $subjects = $_POST['subjects'] ?? []; // This is now an array

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

        // Handle Expert Role Assignment
        if ($new_role == 3) {
            if (empty($subjects)) {
                header("Location: ".ROOT."profileadmin/profile?id=$user_id&message=Expert role requires at least one subject");
                exit;
            }

            $subject_model = new Subjects;
            $experts_model = new Experts;

            foreach ($subjects as $subject_name) {
                $subject_id = $subject_model->add_new_subject($subject_name);

                $existing_expert = $experts_model->first([
                    'user_id' => $user_id,
                    'subject_id' => $subject_id
                ]);

                if (!$existing_expert) {
                    $experts_model->insert([
                        'user_id' => $user_id,
                        'subject_id' => $subject_id
                    ]);
                }
            }
        }

        $user->update($user_id, ['role'=>$new_role]);
        $roles_model = new Roles;
        $role_name = $roles_model->first(['role_id'=>$new_role])->name;

        $notification_service = new NotificationServices;

        $sender_id = $_SESSION['user_id'];
        $notification_content = "Your user role has been changed to $role_name.";
        $notification_service->send_notification($sender_id, $notification_content, $user_id);
        
        header("Location: ".ROOT."profileadmin/profile?id=$user_id&message=User role has been changed to $role_name successfully");
        
    }

}