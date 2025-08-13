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

}