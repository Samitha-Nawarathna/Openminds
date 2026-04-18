<?php

// Assumes the Model trait (which uses the Database trait) is loaded
// and all database constants (DBHOST, DBNAME, etc.) are defined.

class AnalyticsModel
{
    // Use the core Model trait to gain access to query(), get_row(), etc.
    use Model;

    protected $table = 'events';

    // Helper function to calculate percentage change
    private function calculateChangePercentage(float $current, float $previous): float {
        if ($previous == 0) {
            // Treat change from 0 as 100% if current is positive, otherwise 0%
            return ($current > 0) ? 100.0 : 0.0;
        }
        return round((($current - $previous) / $previous) * 100, 2);
    }
    
    // =========================================================================
    // Core Metric Calculation Logic (Private Methods)
    // =========================================================================

    /**
     * Retrieves raw data for Notes Created, Average Scores, and Votes for the Last Week (LW)
     * and Previous Week (PW) based on a calendar week (Mon-Sun).
     */
    private function getAllTimeMetricsRaw($user_id = 1): array {
        // --- 1. Fetch All-Time Notes & Scores (Single Query from events) ---
        $sql = "
            SELECT 
                SUM(CASE WHEN event_type = 'note_created' THEN 1 ELSE 0 END) AS note_count,
                AVG(CASE WHEN event_type = 'exercise_attempted' THEN JSON_UNQUOTE(JSON_EXTRACT(data, '$.score')) ELSE NULL END) AS avg_score
            FROM events
            WHERE user_id = :user_id AND event_type IN ('note_created', 'exercise_attempted');
        ";
        $params = ['user_id' => $user_id];
        $data_results = $this->get_row($sql, $params);
        $data = [
            'notes' => ['Total' => (float) ($data_results->note_count ?? 0)],
            'scores' => ['Total' => (float) ($data_results->avg_score ?? 0)],
            'votes' => ['Total' => 0] 
        ];
        
        // --- 2. Fetch All-Time Total Votes ---
        $vote_sql = "
            SELECT 
                (SELECT COUNT(*) FROM uservotequestion) +
                (SELECT COUNT(*) FROM uservoteanswer) AS total_votes;
        ";
        $vote_results = $this->get_row($vote_sql, []);
        
        $data['votes']['Total'] = (float) ($vote_results->total_votes ?? 0);

        return $data;
    }

    /**
     * Gets 52-week trends for general user activities.
     */
    private function get52WeekActivityTrends($user_id = 1): array {
        $sql = "
            SELECT 
                YEARWEEK(event_time, 1) as week_id,
                FROM_DAYS(TO_DAYS(DATE_ADD(DATE_SUB(event_time, INTERVAL WEEKDAY(event_time) DAY), INTERVAL 1 DAY))) AS week_start_date,
                SUM(CASE WHEN event_type = 'note_created' THEN 1 ELSE 0 END) as notes_created,
                SUM(CASE WHEN event_type = 'question_asked' THEN 1 ELSE 0 END) as questions_asked,
                SUM(CASE WHEN event_type = 'exercise_attempted' THEN 1 ELSE 0 END) as exercises_attempted
            FROM events
            WHERE user_id = :user_id AND event_time >= DATE_SUB(NOW(), INTERVAL 52 WEEK)
            GROUP BY week_id, week_start_date
            ORDER BY week_id DESC;
        ";

        $results = $this->query($sql, ['user_id' => $user_id]);
        
        // Map database results by week_id for fast lookup
        $results_map = [];
        if ($results) {
            foreach ($results as $row) {
                $results_map[$row->week_id] = $row;
            }
        }
        
        $formatted_trends = [];
        
        // Generate continuous array of 52 weeks ago up to the current week
        for ($i = 51; $i >= 0; $i--) {
            $monday = strtotime('monday this week -' . $i . ' weeks');
            $week_id = date('oW', $monday); 
            $timestamp = $monday * 1000;
            
            if (isset($results_map[$week_id])) {
                $row = $results_map[$week_id];
                $formatted_trends[] = [
                    'week_start_date' => $timestamp,
                    'notes_created' => (int) $row->notes_created,
                    'questions_asked' => (int) $row->questions_asked,
                    'exercises_attempted' => (int) $row->exercises_attempted
                ];
            } else {
                $formatted_trends[] = [
                    'week_start_date' => $timestamp,
                    'notes_created' => 0,
                    'questions_asked' => 0,
                    'exercises_attempted' => 0
                ];
            }
        }
        
        // Return oldest week to newest week (we iterated backwards from 51)
        // Wait, $i=51 is 51 weeks ago, so array index 0 is oldest. Line graph requires oldest to newest.
        return $formatted_trends;
    }

    /**
     * Generates a heatmap of user activity (Mon-Sun vs Week 1-52).
     * Structure matches ApexCharts Heatmap format.
     */
    private function getUserActivityHeatmap($user_id = 1, $year = null): array {
        // Default to current year if not provided
        if ($year === null) {
            $year = (int) date('Y');
        }

        // 1. Initialize the structure for 7 days (Mon-Sun) to ensure row order
        $dayNames = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];
        $heatmapSeries = [];

        foreach ($dayNames as $day) {
            $heatmapSeries[$day] = [
                'name' => $day,
                'data' => [] 
            ];
        }

        // 2. Fetch raw activity counts grouped by Day and Week
        // WEEK(..., 1) ensures weeks start on Monday (Mode 1)
        $sql = "
            SELECT 
                DATE_FORMAT(event_time, '%a') as day_name, 
                WEEK(event_time, 1) as week_number, 
                COUNT(*) as activity_count 
            FROM events 
            WHERE user_id = :user_id 
              AND YEAR(event_time) = :year 
            GROUP BY day_name, week_number
        ";

        $results = $this->query($sql, ['user_id' => $user_id, 'year' => $year]);

        // 3. Transform DB results into a lookup array: $map['Mon'][5] = 12
        $activityData = [];
        foreach ($results as $row) {
            // Ensure types are correct
            $day = $row->day_name;
            $week = (int) $row->week_number;
            $count = (int) $row->activity_count;
            
            $activityData[$day][$week] = $count;
        }

        // 4. Build the final dense structure (Weeks 1 to 52)
        // We loop strictly 1-52 to keep the chart grid consistent.
        $totalWeeks = 52; 

        for ($w = 1; $w <= $totalWeeks; $w++) {
            foreach ($dayNames as $day) {
                // Check if data exists for this Day+Week, otherwise 0
                $count = $activityData[$day][$w] ?? 0;

                // Push data point: { x: 'W1', y: 0 }
                $heatmapSeries[$day]['data'][] = [
                    'x' => 'W' . $w,
                    'y' => $count
                ];
            }
        }

        // 5. Return indexed array (remove 'Mon', 'Tue' keys)
        return array_values($heatmapSeries);
    }
    
    /**
     * Retrieves top subject scores (all-time and last month).
     * Assumes a 'subjects' table exists with columns 'id' and 'name'.
     */
    private function getTopSubjectScores($user_id = 1): array {
        // Last Month boundaries
        $month_start = date('Y-m-d', strtotime('first day of last month'));
        $month_end = date('Y-m-d 23:59:59', strtotime('last day of last month'));

        // All Time Top Scores
        $sql_all_time = "
            SELECT 
                s.name AS subject_name,
                ROUND(AVG(JSON_UNQUOTE(JSON_EXTRACT(e.data, '$.score'))), 2) AS average_score
            FROM events e
            JOIN subjects s ON JSON_UNQUOTE(JSON_EXTRACT(e.data, '$.subject_id')) = s.id
            WHERE e.event_type = 'exercise_attempted' AND e.user_id = :user_id
            GROUP BY s.name
            ORDER BY average_score DESC
            LIMIT 10;
        ";

        // Last Month Top Scores
        $sql_last_month = "
            SELECT 
                s.name AS subject_name,
                ROUND(AVG(JSON_UNQUOTE(JSON_EXTRACT(e.data, '$.score'))), 2) AS average_score
            FROM events e
            JOIN subjects s ON JSON_UNQUOTE(JSON_EXTRACT(e.data, '$.subject_id')) = s.id
            WHERE e.event_type = 'exercise_attempted' AND e.user_id = :user_id
              AND e.event_time BETWEEN :month_start AND :month_end
            GROUP BY s.name
            ORDER BY average_score DESC
            LIMIT 10;
        ";
        
        $all_time_data = array_map(function($item) {
            $item->average_score = (float) $item->average_score;
            return $item;
        }, $this->query($sql_all_time, ['user_id' => $user_id]));
        
        $last_month_data = array_map(function($item) {
            $item->average_score = (float) $item->average_score;
            return $item;
        }, $this->query($sql_last_month, ['user_id' => $user_id, 'month_start' => $month_start, 'month_end' => $month_end]));

        return [
            'all_time' => $all_time_data,
            'last_month' => $last_month_data
        ];
    }
    
    /**
     * Retrieves top tag usage for all-time and last week based on note and question tagging.
     */
    private function getTopTagUsage(): array {
        // Last Week boundaries
        $lw_start = date('Y-m-d 00:00:00', strtotime('monday last week'));
        $lw_end = date('Y-m-d 23:59:59', strtotime('sunday last week'));
        
        // All Time Tag Activity: Count activity related to tags across notes and questions
        $sql_all_time = "
            SELECT 
                t.name AS tag_name,
                COUNT(DISTINCT nt.note_id) + COUNT(DISTINCT qt.question_id) AS activity_count
            FROM tags t
            LEFT JOIN note_tags nt ON t.id = nt.tag_id
            LEFT JOIN questiontag qt ON t.id = qt.tag_id
            GROUP BY t.name
            ORDER BY activity_count DESC
            LIMIT 10;
        ";

        // Last Week Tag Activity: Count activity between last week's dates
        $sql_last_week = "
            SELECT 
                t.name AS tag_name,
                (SELECT COUNT(DISTINCT n.id) FROM note_tags nt JOIN notes n ON nt.note_id = n.id WHERE nt.tag_id = t.id AND n.created_at BETWEEN :lw_start AND :lw_end) +
                (SELECT COUNT(DISTINCT q.id) FROM questiontag qt JOIN question q ON qt.question_id = q.id WHERE qt.tag_id = t.id AND q.created_at BETWEEN :lw_start AND :lw_end) 
            AS activity_count
            FROM tags t
            ORDER BY activity_count DESC
            LIMIT 10;
        ";
        
        $all_time_data = array_map(function($item) {
            $item->tag_name = '#' . $item->tag_name;
            $item->activity_count = (int) $item->activity_count;
            return $item;
        }, $this->query($sql_all_time));
        
        $last_week_data = array_map(function($item) {
            $item->tag_name = '#' . $item->tag_name;
            $item->activity_count = (int) $item->activity_count;
            return $item;
        }, $this->query($sql_last_week, ['lw_start' => $lw_start, 'lw_end' => $lw_end]));

        return [
            'all_time' => $all_time_data,
            'last_week' => $last_week_data
        ];
    }

    /**
     * Gets system-wide or user-specific Q/A contributions (corrected to be user-specific).
     */
    private function getQAContribution($user_id = 1): array {
        // Changed to user-specific totals
        $sql = "
            SELECT 
                (SELECT COUNT(*) FROM question WHERE creator_id = :user_id) AS asked_questions,
                (SELECT COUNT(*) FROM answer WHERE creator_id = :user_id) AS answered_questions,
                (SELECT COUNT(*) FROM answer WHERE creator_id = :user_id AND chosen = 1) AS accepted_answers
        ";
        
        $raw_data = $this->get_row($sql, ['user_id' => $user_id]);
        $data = $raw_data ?? (object)['asked_questions' => 0, 'answered_questions' => 0, 'accepted_answers' => 0];

        return [
            [ 'name' => "Asked Questions", 'count' => (int) $data->asked_questions ],
            [ 'name' => "Answered Questions", 'count' => (int) $data->answered_questions ],
            [ 'name' => "Accepted Answers", 'count' => (int) $data->accepted_answers ]
        ];
    }
    
    /**
     * Retrieves the top 6 answers based on total votes, providing question context.
     */
    private function getTopAnswers(): array {
        $sql = "
            SELECT
                q.title AS question,
                a.chosen AS accepted,
                COUNT(uva.a_id) AS votes,
                CONCAT('/questions/', q.id) AS link
            FROM answer a
            JOIN question q ON a.q_id = q.id
            LEFT JOIN uservoteanswer uva ON a.id = uva.a_id 
            GROUP BY a.id, q.title, a.chosen, q.id
            ORDER BY votes DESC
            LIMIT 6;
        ";
        
        $results = $this->query($sql);
        
        return array_map(function($item) {
            $item->votes = (int) $item->votes;
            $item->accepted = (bool) $item->accepted;
            return $item;
        }, $results);
    }

    /**
     * Calculates the weekly trend for all votes (question + answer) for the last 52 weeks.
     */
    private function getWeeklyVoteTrends(): array {
        $sql = "
            SELECT
                YEARWEEK(created_at, 1) as week_id,
                FROM_DAYS(TO_DAYS(DATE_ADD(DATE_SUB(created_at, INTERVAL WEEKDAY(created_at) DAY), INTERVAL 1 DAY))) AS week_start_date,
                COUNT(*) AS votes
            FROM uservotequestion
            WHERE created_at >= DATE_SUB(NOW(), INTERVAL 52 WEEK)
            GROUP BY week_id, week_start_date
            
            UNION ALL

            SELECT
                YEARWEEK(created_at, 1) as week_id,
                FROM_DAYS(TO_DAYS(DATE_ADD(DATE_SUB(created_at, INTERVAL WEEKDAY(created_at) DAY), INTERVAL 1 DAY))) AS week_start_date,
                COUNT(*) AS votes
            FROM uservoteanswer
            WHERE created_at >= DATE_SUB(NOW(), INTERVAL 52 WEEK)
            GROUP BY week_id, week_start_date
            
            ORDER BY week_id DESC;
        ";
        
        $raw_results = $this->query($sql);
        
        $weekly_totals = [];
        foreach ($raw_results as $row) {
            $week_id = $row->week_id;
            if (!isset($weekly_totals[$week_id])) {
                $weekly_totals[$week_id] = [
                    'date' => strtotime($row->week_start_date) * 1000,
                    'votes' => 0
                ];
            }
            $weekly_totals[$week_id]['votes'] += (int) $row->votes;
        }

        // Return chronological order (oldest first)
        return array_values(array_reverse($weekly_totals));
    }

    // --- Reflection Methods (Corrected from previous steps) ---
    
    private function getReflectionOverview($user_id = 1): array {
        // CW: Last 7 days, PW: 7 days before CW
        $current_week_start = date('Y-m-d', strtotime('-7 days'));
        $previous_week_start = date('Y-m-d', strtotime('-14 days'));
        
        // 1. Fetch Notes & Attempts in CW and PW and Avg. Scores in a single query
        $sql_metrics = "
            WITH WeeklyScores AS (
                SELECT
                    event_time,
                    event_type,
                    CAST(JSON_EXTRACT(data, '$.score') AS DECIMAL(5, 2)) AS score
                FROM events
                WHERE user_id = :user_id AND event_time >= :previous_week_start
            )
            SELECT
                SUM(CASE WHEN event_time >= :current_week_start AND event_type = 'note_created' THEN 1 ELSE 0 END) AS notes_current,
                SUM(CASE WHEN event_time < :current_week_start AND event_time >= :previous_week_start AND event_type = 'note_created' THEN 1 ELSE 0 END) AS notes_previous,
                SUM(CASE WHEN event_time >= :current_week_start AND event_type = 'exercise_attempted' THEN 1 ELSE 0 END) AS attempts_current,
                SUM(CASE WHEN event_time < :current_week_start AND event_type = 'exercise_attempted' THEN 1 ELSE 0 END) AS attempts_previous,
                COALESCE(AVG(CASE WHEN event_time >= :current_week_start AND event_type = 'exercise_attempted' THEN score END), 0) AS avg_score_current,
                COALESCE(AVG(CASE WHEN event_time < :current_week_start AND event_type = 'exercise_attempted' THEN score END), 0) AS avg_score_previous
            FROM WeeklyScores;
        ";

        $metrics = $this->get_row($sql_metrics, [
            'user_id' => $user_id, 
            'current_week_start' => $current_week_start, 
            'previous_week_start' => $previous_week_start
        ]);

        // 2. Calculate 52-Week Average Notes (for the 'count' field)
        $sql_52w_avg = "
            SELECT COUNT(id) / 52 AS avg_notes 
            FROM notes 
            WHERE owner_id = :user_id AND created_at >= DATE_SUB(NOW(), INTERVAL 52 WEEK);
        ";
        $avg_notes_per_week_52w = $this->get_row($sql_52w_avg, ['user_id' => $user_id])->avg_notes ?? 0;

        $notes_current = (int) ($metrics->notes_current ?? 0);
        $notes_previous = (int) ($metrics->notes_previous ?? 0);
        $attempts_current = (int) ($metrics->attempts_current ?? 0);
        $attempts_previous = (int) ($metrics->attempts_previous ?? 0);
        $avg_score_current = (float) ($metrics->avg_score_current ?? 0);
        $avg_score_previous = (float) ($metrics->avg_score_previous ?? 0);

        // A. Avg. Notes per Week
        $notes_per_week = [
            "count" => (int) round($avg_notes_per_week_52w),
            "change_percentage" => $this->calculateChangePercentage($notes_current, $notes_previous),
            "title" => "Avg. Notes per Week"
        ];

        // B. Avg. Mark Improvement
        $avg_mark_improvement_score = round($avg_score_current - $avg_score_previous, 1);
        $avg_mark_improvement = [
            "score" => $avg_mark_improvement_score,
            "change_percentage" => $this->calculateChangePercentage($avg_score_current, $avg_score_previous),
            "title" => "Avg. Mark Improvement"
        ];

        // C. Note:Exercise Ratio
        $ratio_current = $attempts_current > 0 ? $notes_current / $attempts_current : ($notes_current > 0 ? $notes_current : 0);
        $ratio_previous = $attempts_previous > 0 ? $notes_previous / $attempts_previous : ($notes_previous > 0 ? $notes_previous : 0);
        
        $note_exercise_ratio_display = "0:0";
        if ($notes_current > 0 || $attempts_current > 0) {
            if ($notes_current >= $attempts_current) {
                $ratio_value = $attempts_current > 0 ? $notes_current / $attempts_current : $notes_current;
                $note_exercise_ratio_display = round($ratio_value, 1) . ":1"; 
            } else {
                $ratio_value = $notes_current > 0 ? $attempts_current / $notes_current : $attempts_current;
                $note_exercise_ratio_display = "1:" . round($ratio_value, 1);
            }
        }

        $note_exercise_fraction = [
            "fraction" => $note_exercise_ratio_display,
            "change_percentage" => $this->calculateChangePercentage($ratio_current, $ratio_previous),
            "title" => "Note:Exercise Ratio"
        ];

        return [
            "avg_notes_per_week" => $notes_per_week,
            "avg_mark_improvement" => $avg_mark_improvement,
            "note_exercise_fraction" => $note_exercise_fraction
        ];
    }
    
    private function getWeeklyNoteActivity($user_id = 1): array {
        $sql = "
            SELECT
                YEARWEEK(event_time, 1) as week_id,
                FROM_DAYS(TO_DAYS(DATE_ADD(DATE_SUB(event_time, INTERVAL WEEKDAY(event_time) DAY), INTERVAL 1 DAY))) AS week_start_date,
                SUM(CASE WHEN event_type = 'note_created' THEN 1 ELSE 0 END) AS created,
                SUM(CASE WHEN event_type = 'note_updated' THEN 1 ELSE 0 END) AS updated,
                SUM(CASE WHEN event_type = 'note_deleted' THEN 1 ELSE 0 END) AS deleted
            FROM events
            WHERE user_id = :user_id 
              AND entity_type = 'Note' 
              AND event_time >= DATE_SUB(NOW(), INTERVAL 52 WEEK)
            GROUP BY week_id, week_start_date
            ORDER BY week_id ASC
        ";
        
        $results = $this->query($sql, ['user_id' => $user_id]);
        
        $results_map = [];
        if ($results) {
            foreach ($results as $row) {
                $results_map[$row->week_id] = $row;
            }
        }
        
        $formatted_activity = [];
        
        // Generate continuous array of 52 weeks ago up to the current week
        // We push oldest values first for chronological order
        for ($i = 51; $i >= 0; $i--) {
            $monday = strtotime('monday this week -' . $i . ' weeks');
            $week_id = date('oW', $monday); 
            $timestamp = $monday * 1000;
            
            if (isset($results_map[$week_id])) {
                $row = $results_map[$week_id];
                $formatted_activity[] = [
                    'date' => $timestamp, 
                    'created' => (int) $row->created,
                    'updated' => (int) $row->updated,
                    'deleted' => (int) $row->deleted,
                ];
            } else {
                $formatted_activity[] = [
                    'date' => $timestamp, 
                    'created' => 0,
                    'updated' => 0,
                    'deleted' => 0,
                ];
            }
        }
        
        return $formatted_activity;
    }
    
    private function getSubjectProficiency($user_id = 1): array {
        $sql = "
            WITH WeeklySubjectAvg AS (
                SELECT
                    CAST(JSON_EXTRACT(e.data, '$.subject_id') AS UNSIGNED) AS subject_id,
                    FROM_DAYS(TO_DAYS(DATE_ADD(DATE_SUB(e.event_time, INTERVAL WEEKDAY(e.event_time) DAY), INTERVAL 1 DAY))) AS week_start_date,
                    AVG(CAST(JSON_EXTRACT(e.data, '$.score') AS DECIMAL(5, 2))) AS avg_score
                FROM
                    events e
                WHERE
                    e.user_id = :user_id
                    AND e.event_type = 'exercise_attempted'
                    AND e.event_time >= DATE_SUB(NOW(), INTERVAL 53 WEEK)
                GROUP BY
                    subject_id, week_start_date
            ),
            WeeklyMarkChange AS (
                SELECT
                    subject_id,
                    week_start_date,
                    avg_score - COALESCE(
                        LAG(avg_score, 1) OVER (PARTITION BY subject_id ORDER BY week_start_date),
                        avg_score
                    ) AS weekly_mark_change
                FROM
                    WeeklySubjectAvg
            )
            SELECT
                s.name,
                wmc.weekly_mark_change
            FROM
                WeeklyMarkChange wmc
            JOIN
                subjects s ON wmc.subject_id = s.id
            WHERE
                wmc.week_start_date >= DATE_SUB(NOW(), INTERVAL 52 WEEK)
            ORDER BY
                s.name, wmc.week_start_date;
        ";
        
        $results = $this->query($sql, ['user_id' => $user_id]);
        
        $final_proficiency = [];
        foreach ($results as $row) {
            $name = $row->name;
            if (!isset($final_proficiency[$name])) {
                $final_proficiency[$name] = [
                    'name' => $name,
                    'weekly_marks' => []
                ];
            }
            $final_proficiency[$name]['weekly_marks'][] = round((float)$row->weekly_mark_change, 2);
        }

        return array_values($final_proficiency);
    }
    
    private function getTopTagsLast4Weeks($user_id = 1): array {
        $week_ids = [];
        for ($i = 3; $i >= 0; $i--) {
            $week_ids[] = (int) date('YW', strtotime("-{$i} week"));
        }
        $start_date_4_weeks = 'DATE_SUB(NOW(), INTERVAL 4 WEEK)';
        
        // 1. Get Top 10 Tags 
        $top_tags_sql = "
            SELECT t.id, t.name AS tag
            FROM tags t
            JOIN note_tags nt ON t.id = nt.tag_id
            JOIN notes n ON nt.note_id = n.id
            WHERE n.owner_id = :user_id AND n.created_at >= $start_date_4_weeks
            GROUP BY t.id, t.name
            ORDER BY COUNT(n.id) DESC
            LIMIT 10
        ";
        $top_tags = $this->query($top_tags_sql, ['user_id' => $user_id]);

        $final_data = [];
        foreach ($top_tags as $tag_info) {
            $tag_data = ['tag' => '#' . $tag_info->tag, 'total' => 0];
            $tag_id = $tag_info->id;

            // 2. Count note creation usage per week (w1-w4)
            $usage_sql = "
                SELECT YEARWEEK(n.created_at, 1) AS week_id, COUNT(DISTINCT n.id) AS usage_count
                FROM notes n JOIN note_tags nt ON n.id = nt.note_id
                WHERE n.owner_id = :user_id AND nt.tag_id = :tag_id AND n.created_at >= $start_date_4_weeks
                GROUP BY week_id
            ";
            $usage_results = $this->query($usage_sql, ['user_id' => $user_id, 'tag_id' => $tag_id]);
            $usage_map = array_column(array_map(fn($r) => (array)$r, $usage_results), 'usage_count', 'week_id');

            // 3. Count note updates (Drift) per week using the 'events' table
            $drift_sql = "
                SELECT 
                    YEARWEEK(e.event_time, 1) AS week_id, 
                    COUNT(DISTINCT e.entity_id) AS updated_count
                FROM events e
                JOIN note_tags nt ON e.entity_id = nt.note_id
                WHERE e.user_id = :user_id AND nt.tag_id = :tag_id 
                  AND e.entity_type = 'Note' AND e.event_type = 'note_updated'
                  AND e.event_time >= $start_date_4_weeks
                GROUP BY week_id
            ";
            $drift_results = $this->query($drift_sql, ['user_id' => $user_id, 'tag_id' => $tag_id]);
            $drift_map = array_column(array_map(fn($r) => (array)$r, $drift_results), 'updated_count', 'week_id');

            $total = 0;
            for ($i = 0; $i < 4; $i++) {
                $week_key = "w" . ($i + 1);
                $drift_key = "drift_w" . ($i + 1);
                $week_id = $week_ids[$i]; 
                
                $usage = (int) ($usage_map[$week_id] ?? 0);
                $tag_data[$week_key] = $usage;
                $total += $usage;
                
                $tag_data[$drift_key] = (int) ($drift_map[$week_id] ?? 0);
            }
            
            $tag_data['total'] = $total;
            $final_data[] = $tag_data;
        }
        
        return $final_data;
    }

    // =========================================================================
    // NEW ADMIN METRIC CALCULATION LOGIC
    // =========================================================================

    /**
     * Retrieves the total count of active and banned users.
     */
    private function getProfileStatus(): array {
        $sql = "
            SELECT 
                SUM(CASE WHEN banned = 0 THEN 1 ELSE 0 END) AS active,
                SUM(CASE WHEN banned = 1 THEN 1 ELSE 0 END) AS banned
            FROM user;
        ";
        
        $results = $this->get_row($sql);
        
        return [
            "active" => (int) ($results->active ?? 0),
            "banned" => (int) ($results->banned ?? 0)
        ];
    }

    /**
     * Calculates system-wide performance metrics for all subjects.
     */
    private function getSubjectPerformance(): array {
        // Calculate calendar week boundaries (Mon-Sun)
        $lw_end = date('Y-m-d 23:59:59', strtotime('sunday last week'));
        $lw_start = date('Y-m-d 00:00:00', strtotime('monday last week'));
        $pw_end = date('Y-m-d 23:59:59', strtotime('-1 day', strtotime($lw_start)));
        $pw_start = date('Y-m-d 00:00:00', strtotime('-7 days', strtotime($pw_end)));
        
        $sql = "
            SELECT 
                s.id AS subject_id,
                s.name AS subject_name,
                
                -- All-Time Exercise Count (Uses 'exercises' table directly)
                (SELECT COUNT(e.id) FROM exercises e WHERE e.subject_id = s.id) AS exercise_count,
                
                -- All-Time Average Score (Uses 'events' table, extracts JSON data)
                (SELECT AVG(CAST(JSON_UNQUOTE(JSON_EXTRACT(evt.data, '$.score')) AS DECIMAL(5, 2)))
                 FROM events evt
                 WHERE evt.event_type = 'exercise_attempted' 
                   AND JSON_UNQUOTE(JSON_EXTRACT(evt.data, '$.subject_id')) = s.id
                ) AS avg_score,

                -- EXPERT COUNT: Corrected to use the 'expert' table
                (SELECT COUNT(ex.user_id) FROM experts ex WHERE ex.subject_id = s.id) AS expert_count,
                
                -- Activity Count Last Week (LW): Attempts + Creations
                (SELECT COUNT(evt_lw.id) 
                 FROM events evt_lw
                 WHERE JSON_UNQUOTE(JSON_EXTRACT(evt_lw.data, '$.subject_id')) = s.id
                   AND evt_lw.event_type IN ('exercise_attempted', 'exercise_created')
                   AND evt_lw.event_time BETWEEN :lw_start AND :lw_end
                ) AS activity_lw,

                -- Activity Count Prior Week (PW): Attempts + Creations
                (SELECT COUNT(evt_pw.id) 
                 FROM events evt_pw
                 WHERE JSON_UNQUOTE(JSON_EXTRACT(evt_pw.data, '$.subject_id')) = s.id
                   AND evt_pw.event_type IN ('exercise_attempted', 'exercise_created')
                   AND evt_pw.event_time BETWEEN :pw_start AND :pw_end
                ) AS activity_pw

            FROM subjects s;
        ";
        
        $params = [
            'lw_start' => $lw_start, 'lw_end' => $lw_end, 
            'pw_start' => $pw_start, 'pw_end' => $pw_end
        ];
        
        $results = $this->query($sql, $params);
        
        $formatted_subjects = [];
        foreach ($results as $row) {
            $activity_lw = (float) $row->activity_lw;
            $activity_pw = (float) $row->activity_pw;
            
            $formatted_subjects[] = [
                'name' => $row->subject_name,
                'growth_rate' => $this->calculateChangePercentage($activity_lw, $activity_pw),
                'avg_score' => round((float) ($row->avg_score ?? 0), 1),
                'exercise_count' => (int) ($row->exercise_count ?? 0),
                'expert_count' => (int) ($row->expert_count ?? 0) // Now using the fetched value
            ];
        }
        
        return $formatted_subjects;
    }

    /**
     * Retrieves high-level KPI metrics for the Admin dashboard overview.
     */
    private function getAdminOverviewKPIs(int $total_users, int $active_users, int $banned_users): array {
        
        // 1. Pending Expert Requests (using 'expert.approved_at IS NULL')
        $sql_requests = "
            SELECT COUNT(user_id) AS pending_requests
            FROM request
            WHERE review = 'pending';
        ";
        $requests_result = $this->get_row($sql_requests);
        $pending_expert_requests = (int) ($requests_result->pending_requests ?? 0);
        
        // 2. System Health Score (Proxy Calculation)
        $system_health_score = 100.0;
        if ($total_users > 0) {
            // Formula: 100 - (Banned Users / Total Users) * 100
            $banned_ratio = ($banned_users / $total_users);
            $system_health_score = round(100.0 - ($banned_ratio * 100), 1);
        }

        return [
            "pending_expert_requests" => $pending_expert_requests,
            "total_active_profiles" => $active_users, // Re-use from getProfileStatus
            "system_health_score" => $system_health_score,
        ];
    }
    

    // =========================================================================
    // PUBLIC COMPILATION METHODS
    // =========================================================================

    /**
     * Compiles all Influence data into the specified JSON structure.
     */
    public function generateInfluenceData($user_id = 1): array {
        $top_answers = $this->getTopAnswers();
        $weekly_vote_trends = $this->getWeeklyVoteTrends();
        
        return [
            "qa_contribution" => $this->getQAContribution($user_id),
            "top_answers" => $top_answers,
            "weekly_vote_data" => $weekly_vote_trends,
        ];
    }
    
    /**
     * Compiles all analytics data into the specified JavaScript JSON structure.
     */
    public function generateAllAnalyticsData($user_id = 1): array {
        // --- 1. Get Raw Data ---
        $weekly_raw = $this->getAllTimeMetricsRaw($user_id);
        $weekly_trends = $this->get52WeekActivityTrends($user_id);
        $subject_scores = $this->getTopSubjectScores($user_id);
        $tag_usage = $this->getTopTagUsage(); 
        $activity_heatmap = $this->getUserActivityHeatmap($user_id);

        // --- 2. Calculate Weekly Trend Metrics (CW vs PW) ---
        $cw_start = date('Y-m-d H:i:s', strtotime('-7 days'));
        $pw_start = date('Y-m-d H:i:s', strtotime('-14 days'));

        $sql_trends = "
            SELECT 
                SUM(CASE WHEN event_time >= :cw_start AND event_type = 'note_created' THEN 1 ELSE 0 END) AS notes_cw,
                SUM(CASE WHEN event_time < :cw_start AND event_time >= :pw_start AND event_type = 'note_created' THEN 1 ELSE 0 END) AS notes_pw,
                
                COALESCE(AVG(CASE WHEN event_time >= :cw_start AND event_type = 'exercise_attempted' THEN CAST(JSON_EXTRACT(data, '$.score') AS DECIMAL(5, 2)) END), 0) AS score_cw,
                COALESCE(AVG(CASE WHEN event_time < :cw_start AND event_time >= :pw_start AND event_type = 'exercise_attempted' THEN CAST(JSON_EXTRACT(data, '$.score') AS DECIMAL(5, 2)) END), 0) AS score_pw,
                
                (SELECT COUNT(*) FROM uservotequestion WHERE user_id = :user_id AND created_at >= :cw_start) +
                (SELECT COUNT(*) FROM uservoteanswer WHERE user_id = :user_id AND created_at >= :cw_start) AS votes_cw,
                
                (SELECT COUNT(*) FROM uservotequestion WHERE user_id = :user_id AND created_at < :cw_start AND created_at >= :pw_start) +
                (SELECT COUNT(*) FROM uservoteanswer WHERE user_id = :user_id AND created_at < :cw_start AND created_at >= :pw_start) AS votes_pw,

                (SELECT COUNT(DISTINCT DATE(event_time)) FROM events WHERE user_id = :user_id AND event_time >= :cw_start) AS consistency_cw,
                (SELECT COUNT(DISTINCT DATE(event_time)) FROM events WHERE user_id = :user_id AND event_time < :cw_start AND event_time >= :pw_start) AS consistency_pw
            FROM events
            WHERE user_id = :user_id AND event_time >= :pw_start;
        ";

        $trends = $this->get_row($sql_trends, ['user_id' => $user_id, 'cw_start' => $cw_start, 'pw_start' => $pw_start]);

        // --- 3. Format into Desired 'analyticsData' Structure ---
        return [
            "overview" => [
                "notes_created" => [
                    "count" => (int) ($weekly_raw['notes']['Total'] ?? 0),
                    "change_percentage" => $this->calculateChangePercentage((float)($trends->notes_cw ?? 0), (float)($trends->notes_pw ?? 0))
                ],
                "average_exercise_score" => [
                    "score" => round(($weekly_raw['scores']['Total'] ?? 0), 1),
                    "change_percentage" => $this->calculateChangePercentage((float)($trends->score_cw ?? 0), (float)($trends->score_pw ?? 0))
                ],
                "all_votes" => [
                    "count" => (int) ($weekly_raw['votes']['Total'] ?? 0),
                    "change_percentage" => $this->calculateChangePercentage((float)($trends->votes_cw ?? 0), (float)($trends->votes_pw ?? 0))
                ],
                "learning_consistency" => [
                    "fraction" => ($trends->consistency_cw ?? 0) . "/7",
                    "change_percentage" => $this->calculateChangePercentage((float)($trends->consistency_cw ?? 0), (float)($trends->consistency_pw ?? 0))
                ]
            ],
            "weekly_trends" => $weekly_trends,
            "top_subjects" => $subject_scores,
            "top_tags" => $tag_usage,
            "activity_heatmap" => $activity_heatmap 
        ];
    }

    /**
     * Compiles all Reflection data into the specified JSON structure.
     */
    public function generateReflectionData($user_id = 1): array {
        
        $overview = $this->getReflectionOverview($user_id);
        $weekly_note_activity = $this->getWeeklyNoteActivity($user_id);
        $subject_proficiency = $this->getSubjectProficiency($user_id);
        $top_tags = $this->getTopTagsLast4Weeks($user_id);

        return [
            "overview" => $overview,
            "weekly_note_activity" => $weekly_note_activity,
            "subject_proficiency" => $subject_proficiency,
            "top_tags_last_4_weeks" => $top_tags,
        ];
    }

/**
     * Compiles all Admin dashboard data. (UPDATED)
     */
    public function generateAdminData(): array {
        
        $profile_status = $this->getProfileStatus();
        $total_users = $profile_status['active'] + $profile_status['banned'];
        
        // NEW: Get the overview KPIs
        $overview_kpis = $this->getAdminOverviewKPIs(
            $total_users, 
            $profile_status['active'], 
            $profile_status['banned']
        );
        
        $subjects_performance = $this->getSubjectPerformance();
        
        return [
            "overview_kpis" => $overview_kpis, // NEW STRUCTURE
            "profile_status" => $profile_status,
            "subjects" => $subjects_performance
        ];
    }
}