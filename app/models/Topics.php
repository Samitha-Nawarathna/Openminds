<?php

class Topics
{
    use Model;

    protected $table = 'topics';

    public function is_name_available($name)
    {
        // Check if a topic with the given name exists
        $topic = $this->first(['name' => $name]);
        return $topic !== false;
    }

    public function is_available($id)
    {
        // Check if a topic with the given ID exists
        $topic = $this->first(['id' => $id]);
        return $topic !== false;
    }


}