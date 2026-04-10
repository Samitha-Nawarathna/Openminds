<?php

class User
{
    use Model;

    protected $table = 'user';

    public function get_role($user_or_role_id)
    {
        $id = (int)$user_or_role_id;
        if ($id <= 0) {
            return 'student';
        }

        $roles = new Roles;

        // First, support callers that pass a direct role_id.
        $role_name = $roles->get_role($id);
        if (!$role_name) {
            // Backward compatibility: many callers pass a user_id.
            $user = $this->first(['id' => $id]);
            if ($user === false || !isset($user->role)) {
                return 'student';
            }

            $role_name = $roles->get_role((int)$user->role);
            if (!$role_name) {
                return 'student';
            }
        }

        return strtolower(trim((string)$role_name));

    }

    public function get_profile_picture_url($user_id)
    {
        $results = $this->first(['id'=>$user_id]);

        if ($results === false) {
            return $results;
        }

        return $results->profile_picture;
    }

    public function update_to_expert($user_id, $subject)
    {
        $result = $this->update($user_id, ['role' => 3]);

        

        if ($result === false) {
            return false;
        }



        $experts = new Experts;
        $subjects = new Subjects;

        $subject_id = $subjects->add_new_subject($subject);
        // show($subject_id);
        
        
        if ($subject_id === false) {
            return false;
        }

        return $experts->insert(['user_id' => $user_id, 'subject_id' => $subject_id]);
    
    }

}