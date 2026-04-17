<?php

class AnalysisServices
{
    private $db;

    public function __construct()
    {
        $this->db = new DashboardModel();
    }

    public function get_total_notes($user_id)
    {
        $result = $this->db->query("SELECT COUNT(*) AS total FROM notes WHERE owner_id = :user_id", ['user_id' => $user_id]);
        return (int)($result[0]->total ?? 0);
    } 

    public function get_total_exercises($user_id)
    {
        $result = $this->db->query("SELECT COUNT(*) AS total FROM exercises WHERE creator_id = :user_id", ['user_id' => $user_id]);
        return (int)($result[0]->total ?? 0);
    } 
    
    public function get_total_questions($user_id)
    {
        $result = $this->db->query("SELECT COUNT(*) AS total FROM question WHERE creator_id = :user_id", ['user_id' => $user_id]);
        return (int)($result[0]->total ?? 0);
    } 
    
    public function get_total_answers($user_id)
    {
        $result = $this->db->query("SELECT COUNT(*) AS total FROM answer WHERE creator_id = :user_id", ['user_id' => $user_id]);
        return (int)($result[0]->total ?? 0);
    } 

    public function get_total_upvotes($user_id)
    {
        // Total upvotes received by the user on their questions, answers, and notes.
        $sql = "
            SELECT SUM(upvotes) AS total FROM (
                SELECT COUNT(*) AS upvotes FROM events e JOIN question q ON e.entity_id = q.id AND e.entity_type = 'Question' WHERE e.event_type = 'vote_given' AND q.creator_id = :user_id AND JSON_UNQUOTE(JSON_EXTRACT(e.data, '$.direction')) = 'upvote' AND e.user_id != :user_id
                UNION ALL
                SELECT COUNT(*) AS upvotes FROM events e JOIN answer a ON e.entity_id = a.id AND e.entity_type = 'Answer' WHERE e.event_type = 'vote_given' AND a.creator_id = :user_id AND JSON_UNQUOTE(JSON_EXTRACT(e.data, '$.direction')) = 'upvote' AND e.user_id != :user_id
                UNION ALL
                SELECT COUNT(*) AS upvotes FROM events e JOIN notes n ON e.entity_id = n.id AND e.entity_type = 'Note' WHERE e.event_type = 'vote_given' AND n.owner_id = :user_id AND JSON_UNQUOTE(JSON_EXTRACT(e.data, '$.direction')) = 'upvote' AND e.user_id != :user_id
            ) AS t
        ";
        $result = $this->db->query($sql, ['user_id' => $user_id]);
        return (int)($result[0]->total ?? 0);
    } 

    public function get_total_points($user_id)
    {
        $result = $this->db->query("SELECT points FROM user WHERE id = :user_id", ['user_id' => $user_id]);
        // show((float)($result[0]->points ?? 0));
        return (int)($result[0]->points ?? 0);
    } 
}