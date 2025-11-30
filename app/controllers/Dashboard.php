<?php

// Dashboard.php

// Assume the existence of a base Controller class and the DashboardModel class

class Dashboard extends Controller
{
    private $userId = 1; // 💡 ASSUMPTION: Replace with real user session ID
    private $dashboardModel;

    public function __construct()
    {
        // Initialize the single model instance
        $this->dashboardModel = new DashboardModel();
    }
    
    // --- Utility Methods (Unchanged) ---

    public function index()
    {
        return $this->view('dashboard/index');
    }

    private function getRequestParameter(string $key, $default)
    {
        // Simple simulation: in a real environment, this would parse $_GET/$_POST
        return $_GET[$key] ?? $default; 
    }

    private function jsonResponse(array $data)
    {
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }

    private function createPaginatedResponse(array $items, int $totalResults, int $limit, int $offset): array
    {
        return [
            "metadata" => [
                "limit" => $limit,
                "offset" => $offset,
                "total_results" => $totalResults,
                "end_of_results" => ($offset + count($items)) >= $totalResults
            ],
            "items" => $items
        ];
    }
    
    // Helper Method (Assumed to be available in a utility or base class)
    private function timeAgo($timestamp) {
        if (!$timestamp) return 'N/A';
        // Placeholder for complex timeAgo logic
        return date('M j, Y', strtotime($timestamp));
    }


    // =========================================================
    // 1. STATIC ENDPOINT IMPLEMENTATIONS (REAL DATA)
    // =========================================================

    public function getUserSummary()
    {
        $sql = "
            SELECT u.display_name, u.profile_picture, r.name AS role_name
            FROM user u
            LEFT JOIN roles r ON u.role = r.id
            WHERE u.id = :id
            LIMIT 1
        ";
        $userData = $this->dashboardModel->executeQuery($sql, ['id' => $this->userId]);
        $user = $userData[0] ?? null;

        if (!$user) {
            $this->jsonResponse(['error' => 'User not found'], 404);
        }

        $is_admin = $user->role_name === 'admin';
        $is_expert = $user->role_name === 'expert' || $is_admin;
        
        $response = [
            "name" => $user->display_name,
            "avatar_url" => $user->profile_picture,
            "is_expert" => (bool)$is_expert,
            "is_admin" => (bool)$is_admin,
            "can_start_work" => true // Placeholder for complex logic like `user.banned` check
        ];

        $this->jsonResponse($response);
    }

    public function getImpactMetrics()
    {
        // 1. Answers Shared & Avg Mark
        $answersQuery = "SELECT COUNT(id) AS answers_shared FROM answer WHERE creator_id = :id";
        $markQuery = "SELECT AVG(score) AS avg_score, COUNT(id) as attempt_count FROM exercise_attempt WHERE u_id = :id";
        
        $answers = $this->dashboardModel->executeQuery($answersQuery, ['id' => $this->userId])[0]->answers_shared ?? 0;
        $markData = $this->dashboardModel->executeQuery($markQuery, ['id' => $this->userId])[0];

        $avg_mark = $markData->avg_score > 0 ? number_format($markData->avg_score, 0) . '%' : 'N/A';

        // 2. Consistency (Heatmap from 'events' table)
        $consistencySql = "
            SELECT 
                DAYOFWEEK(event_time) AS day_index, 
                COUNT(*) AS count 
            FROM events 
            WHERE user_id = :id AND event_time >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
            GROUP BY day_index
            ORDER BY event_time DESC
        ";
        $consistencyData = $this->dashboardModel->executeQuery($consistencySql, ['id' => $this->userId]);

        $consistency_days = array_fill(0, 7, 0); 
        $max_count = 1;
        if (!empty($consistencyData)) {
            $max_count = max(array_column($consistencyData, 'count')) ?: 1;
        }

        foreach ($consistencyData as $day) {
            $level = min(3, ceil(($day->count / $max_count) * 3));
            $index = ($day->day_index + 5) % 7; // Map 1-7 (Sun-Sat) to 0-6 (Mon-Sun)
            $consistency_days[$index] = $level;
        }

        $response = [
            "answers_shared" => (int)$answers,
            "answers_change" => "+0 from last week", // Placeholder for advanced comparison logic
            "avg_exercise_mark" => $avg_mark,
            "avg_mark_subtext" => "Based on " . $markData->attempt_count . " attempts", 
            "consistency_days" => $consistency_days,
            "consistency_subtext" => "Keep up the work!"
        ];

        $this->jsonResponse($response);
    }

    public function getCommunityBanner()
    {
        $limit = (int) $this->getRequestParameter('limit', 10);
        $offset = (int) $this->getRequestParameter('offset', 0);

        $totalResults = $this->dashboardModel->executeQuery("SELECT COUNT(id) as total FROM announcements WHERE is_active = 1")[0]->total ?? 0;

        // 2. Get Items
        $sql = "
            SELECT 
                id, title, created_at
            FROM announcements 
            WHERE is_active = 1 
            ORDER BY created_at DESC
        ";
        $rawAnnouncements = $this->dashboardModel->executeQuery($sql, [], $limit, $offset);

        // 3. Format Items
        $formattedItems = [];
        foreach ($rawAnnouncements as $announcement) {
            $formattedItems[] = [
                "id" => $announcement->id,
                "title" => $announcement->title,
                "meta_details" => [
                    ["key" => "Published", "value" => $this->timeAgo($announcement->created_at)]
                ],
                "tags" => null,
                "status_badge" => null,
                "secondary_info" => null
            ];
        }

        $this->jsonResponse($this->createPaginatedResponse($formattedItems, $totalResults, $limit, $offset));
        }


    public function getNotifications()
    {
        $limit = (int) $this->getRequestParameter('limit', 5);
        $offset = (int) $this->getRequestParameter('offset', 0);

        // 1. Get Total Count
        $totalResults = $this->dashboardModel->executeQuery("SELECT COUNT(id) as total FROM notifications WHERE receiver_id = :id", ['id' => $this->userId])[0]->total ?? 0;

        // 2. Get Items
        $sql = "
            SELECT id, content, created_at
            FROM notifications 
            WHERE receiver_id = :id 
            ORDER BY created_at DESC
        ";
        $items = $this->dashboardModel->executeQuery($sql, ['id' => $this->userId], $limit, $offset);

        // 3. Format Items
        $formattedItems = [];
        foreach ($items as $item) {
            $formattedItems[] = [
                "id" => $item->id,
                "content_html" => $item->content,
                "time_ago" => $this->timeAgo($item->created_at), 
                "icon_color" => "var(--color-primary-accent)" // Placeholder
            ];
        }

        $this->jsonResponse($this->createPaginatedResponse($formattedItems, $totalResults, $limit, $offset));
    }

    public function getAnnouncements()
    {
        $limit = (int) $this->getRequestParameter('limit', 10);
        $offset = (int) $this->getRequestParameter('offset', 0);

        $totalResults = $this->dashboardModel->executeQuery("SELECT COUNT(id) as total FROM announcements WHERE is_active = 1")[0]->total ?? 0;

        // 2. Get Items
        $sql = "
            SELECT 
                id, title, created_at
            FROM announcements 
            WHERE is_active = 1 AND creator_id = :id
            ORDER BY created_at DESC
        ";
        $rawAnnouncements = $this->dashboardModel->executeQuery($sql, ['id'=>$this->userId], $limit, $offset);

        // 3. Format Items
        $formattedItems = [];
        foreach ($rawAnnouncements as $announcement) {
            $formattedItems[] = [
                "id" => $announcement->id,
                "title" => $announcement->title,
                "meta_details" => [
                    ["key" => "Published", "value" => $this->timeAgo($announcement->created_at)]
                ],
                "tags" => null,
                "status_badge" => null,
                "secondary_info" => null
            ];
        }

        $this->jsonResponse($this->createPaginatedResponse($formattedItems, $totalResults, $limit, $offset));
    }

    public function getPinnedNotes()
    {
        $limit = (int) $this->getRequestParameter('limit', 10);
        $offset = (int) $this->getRequestParameter('offset', 0);

        // 💡 Tag Style Map (Hardcoded since style is not in the DB)
        // You would typically define this as a class constant or fetch it from a configuration file.
        $tagStyleMap = [
            'Vector' => 'blue',
            'Code' => 'orange',
            'Science' => 'green',
            'History' => 'purple',
            'Algorithm' => 'red',
            'Mathematics' => 'teal',
            // Default style for any tag not mapped
            'default' => 'grey' 
        ];

        // 1. Get Total Count
        $totalResults = $this->dashboardModel->executeQuery("SELECT COUNT(id) as total FROM notes WHERE owner_id = :id AND pinned = 1", ['id' => $this->userId])[0]->total ?? 0;

        // 2. Get Items (Notes + Topic + Tags)
        // MODIFIED: Remove 'tg.style' from the GROUP_CONCAT since it's not in the DB.
        $sql = "
            SELECT 
                n.id, n.title, n.updated_at, t.name AS topic_name,
                GROUP_CONCAT(tg.name) AS tags_data -- Only retrieve tag names
            FROM notes n
            LEFT JOIN topics t ON n.topic_id = t.id
            LEFT JOIN note_tags nt ON n.id = nt.note_id
            LEFT JOIN tags tg ON nt.tag_id = tg.id
            WHERE n.owner_id = :id AND n.pinned = 1
            GROUP BY n.id, n.title, n.updated_at, t.name
            ORDER BY n.updated_at DESC
        ";
        $rawNotes = $this->dashboardModel->executeQuery($sql, ['id' => $this->userId], $limit, $offset);

        // 3. Format Items
        $formattedItems = [];
        foreach ($rawNotes as $note) {
            $tags = [];
            if ($note->tags_data) {
                // Parse the grouped tag names (e.g., "Vector,Code")
                foreach (explode(',', $note->tags_data) as $tag_name) {
                    $tag_name = trim($tag_name); // Clean up whitespace
                    
                    if ($tag_name) {
                        // Assign style using the hardcoded map
                        $style = $tagStyleMap[$tag_name] ?? $tagStyleMap['default'];
                        
                        $tags[] = ['text' => $tag_name, 'style' => $style];
                    }
                }
            }
            
            $formattedItems[] = [
                "id" => $note->id,
                "title" => $note->title,
                "meta_details" => [
                    ["key" => "Topic", "value" => $note->topic_name ?? 'N/A'],
                    ["key" => "Updated", "value" => $this->timeAgo($note->updated_at)],
                ],
                "tags" => $tags,
                "status_badge" => null,
                "secondary_info" => null
            ];
        }

        $this->jsonResponse($this->createPaginatedResponse($formattedItems, $totalResults, $limit, $offset));
    }

    public function getAskedQuestions()
    {
        $limit = (int) $this->getRequestParameter('limit', 10);
        $offset = (int) $this->getRequestParameter('offset', 0);

        // 1. Get Total Count
        $totalResults = $this->dashboardModel->executeQuery("SELECT COUNT(id) as total FROM question WHERE creator_id = :id", ['id' => $this->userId])[0]->total ?? 0;

        // 2. Get Items (Questions + Answer Aggregates)
        $sql = "
            SELECT 
                q.id, q.title, COUNT(a.id) AS answer_count, MAX(a.created_at) AS last_reply, SUM(a.chosen) AS accepted_count
            FROM question q
            LEFT JOIN answer a ON q.id = a.q_id
            WHERE q.creator_id = :id
            GROUP BY q.id, q.title
            ORDER BY q.created_at DESC
        ";
        $rawQuestions = $this->dashboardModel->executeQuery($sql, ['id' => $this->userId], $limit, $offset);

        // 3. Format Items
        $formattedItems = [];
        foreach ($rawQuestions as $question) {
            $isAnswered = $question->accepted_count > 0;

            $status_badge = [
                "text" => $isAnswered ? "Answered" : "No Answers",
                "style" => $isAnswered ? "success" : "warning"
            ];

            $formattedItems[] = [
                "id" => $question->id,
                "title" => $question->title,
                "meta_details" => [
                    ["key" => "Answers", "value" => $question->answer_count . " (" . $question->accepted_count . " chosen)"],
                    ["key" => "Last Reply", "value" => $question->last_reply ? $this->timeAgo($question->last_reply) : 'N/A']
                ],
                "tags" => null,
                "status_badge" => $status_badge,
                "secondary_info" => null
            ];
        }

        $this->jsonResponse($this->createPaginatedResponse($formattedItems, $totalResults, $limit, $offset));
    }

    public function getCreatedExercises()
    {
        $limit = (int) $this->getRequestParameter('limit', 10);
        $offset = (int) $this->getRequestParameter('offset', 0);

        // 1. Get Total Count
        $totalResults = $this->dashboardModel->executeQuery("SELECT COUNT(id) as total FROM exercises WHERE creator_id = :id", ['id' => $this->userId])[0]->total ?? 0;

        // 2. Get Items (Exercises + Attempts Aggregates)
        $sql = "
            SELECT 
                e.id, e.title, e.status, s.name AS subject_name,
                COUNT(a.id) AS attempt_count, AVG(a.score) AS avg_mark
            FROM exercises e
            LEFT JOIN exercise_attempt a ON e.id = a.exe_id
            LEFT JOIN subjects s ON e.subject_id = s.id
            WHERE e.creator_id = :id
            GROUP BY e.id, e.title, e.status, s.name
            ORDER BY e.created_at DESC
        ";
        $rawExercises = $this->dashboardModel->executeQuery($sql, ['id' => $this->userId], $limit, $offset);

        // 3. Format Items
        $formattedItems = [];
        foreach ($rawExercises as $exercise) {
            $status_map = [
                'approved' => ["text" => "Published", "style" => "success"],
                'pending' => ["text" => "Pending Approval", "style" => "warning"],
                'rejected' => ["text" => "Rejected", "style" => "danger"],
            ];
            $avg_mark_text = $exercise->avg_mark ? number_format($exercise->avg_mark, 0) . '%' : 'N/A';
            
            $formattedItems[] = [
                "id" => $exercise->id,
                "title" => $exercise->title,
                "meta_details" => [
                    ["key" => "Subject", "value" => $exercise->subject_name ?? 'N/A'],
                    ["key" => "Attempts", "value" => (int)$exercise->attempt_count],
                ],
                "tags" => null,
                "status_badge" => $status_map[$exercise->status] ?? null,
                "secondary_info" => "Avg. Mark: " . $avg_mark_text
            ];
        }

        $this->jsonResponse($this->createPaginatedResponse($formattedItems, $totalResults, $limit, $offset));
    }

    public function getAnsweredExercises()
    {
        $limit = (int) $this->getRequestParameter('limit', 10);
        $offset = (int) $this->getRequestParameter('offset', 0);

        // 1. Get Total Count
        $totalResults = $this->dashboardModel->executeQuery("SELECT COUNT(id) as total FROM exercise_attempt WHERE u_id = :id", ['id' => $this->userId])[0]->total ?? 0;

        // 2. Get Items (Attempts + Exercise Details)
        $sql = "
            SELECT 
                a.id, a.score, a.date, e.title, s.name AS subject_name
            FROM exercise_attempt a
            LEFT JOIN exercises e ON a.exe_id = e.id
            LEFT JOIN subjects s ON e.subject_id = s.id
            WHERE a.u_id = :id
            ORDER BY a.date DESC
        ";
        $rawAttempts = $this->dashboardModel->executeQuery($sql, ['id' => $this->userId], $limit, $offset);

        // 3. Format Items
        $formattedItems = [];
        foreach ($rawAttempts as $attempt) {
            $score = (int)$attempt->score;
            
            if ($score >= 80) {
                $status_badge = ["text" => "Completed", "style" => "success"];
            } elseif ($score < 60) {
                $status_badge = ["text" => "Low Score", "style" => "danger"];
            } else {
                $status_badge = ["text" => "Average", "style" => "info"];
            }

            $formattedItems[] = [
                "id" => $attempt->id,
                "title" => $attempt->title,
                "meta_details" => [
                    ["key" => "Subject", "value" => $attempt->subject_name ?? 'N/A'],
                    ["key" => "Completed", "value" => $this->timeAgo($attempt->created_at)],
                ],
                "tags" => null,
                "status_badge" => $status_badge,
                "secondary_info" => "Your Mark: " . $score . "%"
            ];
        }

        $this->jsonResponse($this->createPaginatedResponse($formattedItems, $totalResults, $limit, $offset));
    }

    public function getAttemptExercises()
    {
        $limit = (int) $this->getRequestParameter('limit', 10);
        $offset = (int) $this->getRequestParameter('offset', 0);

        // 1. Get Total Count (Available and NOT attempted)
        $totalResults = $this->dashboardModel->executeQuery("
            SELECT COUNT(e.id) AS total
            FROM exercises e
            LEFT JOIN exercise_attempt a ON e.id = a.exe_id AND a.u_id = :id
            WHERE e.status = 'approved' AND a.id IS NULL
        ", ['id' => $this->userId])[0]->total ?? 0;

        // 2. Get Items (Available + Not Attempted + Question Count)
        // MODIFIED: Removed 'e.difficulty' from the SELECT clause
        $sql = "
            SELECT 
                e.id, e.title, COUNT(q.id) AS question_count
            FROM exercises e
            LEFT JOIN exercise_attempt a ON e.id = a.exe_id AND a.u_id = :id
            LEFT JOIN exercisequestion eq ON e.id = eq.id
            LEFT JOIN question q ON eq.id = q.id
            WHERE e.status = 'approved' AND a.id IS NULL
            GROUP BY e.id, e.title
            ORDER BY e.created_at DESC
        ";
        $rawExercises = $this->dashboardModel->executeQuery($sql, ['id' => $this->userId], $limit, $offset);

        // 3. Format Items
        $formattedItems = [];
        foreach ($rawExercises as $exercise) {
            $formattedItems[] = [
                "id" => $exercise->id,
                "title" => $exercise->title,
                "meta_details" => [
                    // MODIFIED: Manually setting difficulty to 'N/A'
                    ["key" => "Difficulty", "value" => 'N/A'],
                ],
                "tags" => null,
                "status_badge" => ["text" => "New", "style" => "info"],
                "secondary_info" => (int)$exercise->question_count . " Questions"
            ];
        }

        $this->jsonResponse($this->createPaginatedResponse($formattedItems, $totalResults, $limit, $offset));
    }    
    // Original method was getPendingReviewRequests, renamed to match context
    public function getExpertRequests()
    {
        $limit = (int) $this->getRequestParameter('limit', 10);
        $offset = (int) $this->getRequestParameter('offset', 0);

        // 1. Get Total Count
        $totalResults = $this->dashboardModel->executeQuery("SELECT COUNT(id) as total FROM request WHERE review = 'pending'")[0]->total ?? 0;

        // 2. Get Items (Requests + Requester Name)
        $sql = "
            SELECT 
                r.id, r.title, r.subject, r.proof_link, u.username AS requester_username
            FROM request r
            LEFT JOIN user u ON r.user_id = u.id
            WHERE r.review = 'pending'
            ORDER BY r.created_at DESC
        ";
        $rawRequests = $this->dashboardModel->executeQuery($sql, [], $limit, $offset);

        // 3. Format Items
        $formattedItems = [];
        foreach ($rawRequests as $request) {
            $formattedItems[] = [
                "id" =>$request->id,
                "title" => $request->title,
                "meta_details" => [
                    ["key" => "Requester", "value" => "@" . $request->requester_username],
                    ["key" => "Subject", "value" => $request->subject],
                    ["key" => "Proof", "value" => $request->proof_link ? "File Attached" : "None"],
                ],
                "tags" => null,
                "status_badge" => ["text" => "Pending Review", "style" => "warning"],
                "secondary_info" => null
            ];
        }

        $this->jsonResponse($this->createPaginatedResponse($formattedItems, $totalResults, $limit, $offset));
    }
}