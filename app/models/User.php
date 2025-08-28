<?php

class User
{
    use Model;

    protected $table = 'user';

    public function get_role($user_id)
    {
        $roles = new Roles;

        $results = $this->first(['id'=>$user_id]);

        if ($results === false) {
            return $results;
        }

        return $roles->get_role($results->role);

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
        return $this->update($user_id, ['role' => 3]);

        $experts = new Experts;
        $subjects = new Subjects;

        $subject_id = $subjects->add_new_subject($subject);
        
        // if ($subject_id === false) {
        //     return false;
        // }

        return $experts->insert(['user_id' => $user_id, 'subject_id' => $subject_id]);


        
    }

}