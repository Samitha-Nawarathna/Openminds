<?php

class NoteModel
{
    use Model;

    protected $table = 'notes';

    private $topic_model;

    public function __construct()
    {
        $this->topic_model = new Topics();
    }

    public function change_topic($note_id, $new_topic_id)
    {
        //check if topic available

        if (!$this->topic_model->is_available($new_topic_id)) {

            //idea way to prepare a object to return model output and set errors in controller, but this is done for the simplicity.
            set_message("Topic not available", "error");
            return false; // Topic not available
        }

        // Find the note by its ID
        $note = $this->first(['id' => $note_id]);
        if ($note) {
            // Save the changes
            return $this->update($note_id, ['topic_id' => $new_topic_id]);
        }
        set_message("Note not found", "error");
        return false; // Note not found
    }

    public function search_by_tags($tags, $user_id)
    {
        $placeholders = [];
        $params = [];

        foreach ($tags as $index => $tag) {
            $placeholder = ":tag" . $index;
            $placeholders[] = "tags LIKE " . $placeholder;
            $params[$placeholder] = "%" . trim($tag) . "%";
        }


        $query = "SELECT * FROM " . $this->table . " WHERE (" . implode(" OR ", $placeholders).")";

        // If user_id is provided, add it to the query
        if ($user_id) {
            $query .= " AND owner_id = :owner_id";
            $params['owner_id'] = $user_id;

            return $this->query($query, $params);
        }

        set_message("Owner not provided", 'error');
        return false;

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