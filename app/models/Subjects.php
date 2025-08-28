<?php

class Subjects
{
    use Model;

    protected $table = 'subjects';

    function add_new_subject(string $subject_name)
    {
        $existing = $this->first(['name' => $subject_name]);
        show($existing);
        
        if ($existing) {
            return $existing->id;
        }

        return $this->insert(['name' => $subject_name]);
    }

}