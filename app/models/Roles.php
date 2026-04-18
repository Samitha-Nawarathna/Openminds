<?php

class Roles
{
    use Model;

    protected $table = 'roles';

    public function get_role($role_id)
    {
        $results = $this->first(['role_id' => (int)$role_id]);
        if ($results === false || !isset($results->name)) {
            return null;
        }
        return $results->name;
    } 

}