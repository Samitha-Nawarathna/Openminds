<?php

class Roles
{
    use Model;

    protected $table = 'roles';

    public function get_role($role_id)
    {
        $results = $this->first(['role_id'=>$role_id]);
        return $results->name;
    } 

}