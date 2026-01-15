<?php

class Topics
{
    use Model;

    protected $table = 'topics';

    public function is_name_available($name)
    {
        // Check if a topic with the given name exists
        // Returns TRUE if available (not found), FALSE if taken.
        $topic = $this->first(['name' => $name]);
        return $topic === false;
    }

    public function is_available($id)
    {
        // Check if a topic with the given ID exists
        $topic = $this->first(['id' => $id]);
        return $topic !== false;
    }

    public function pin($id)
    {
        return $this->update($id, ['pinned' => 1]);
    }

    public function unpin($id)
    {
        return $this->update($id, ['pinned' => 0]);
    }

}