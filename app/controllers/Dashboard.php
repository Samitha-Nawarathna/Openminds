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
        $userId = $_SESSION['user_id'] ?? $this->userId;
        $pointsQuery = "SELECT points FROM user WHERE id = :id";
        try {
            $points = $this->dashboardModel->executeQuery($pointsQuery, ['id' => $userId])[0]->points ?? 0;
        } catch (Exception $e) {
            $points = 0;
        }
        
        return $this->view('dashboard/index', [
            'total_points' => number_format((float)$points, 2)
        ]);
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

    public function api_user_summary()
    {
        $userId = $_SESSION['user_id'];
        $sql = "
            SELECT u.display_name, u.profile_picture, r.name AS role_name
            FROM user u
            LEFT JOIN roles r ON u.role = r.id
            WHERE u.id = :id
            LIMIT 1
        ";
        $userData = $this->dashboardModel->executeQuery($sql, ['id' => $userId]);
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

    public function api_impact_metrics()
    {
        $userId = $_SESSION['user_id'];
        
        // 1. Points
        $pointsQuery = "SELECT points FROM user WHERE id = :id";
        $points = $this->dashboardModel->executeQuery($pointsQuery, ['id' => $userId])[0]->points ?? 0;

        // 2. Answers Shared & Avg Mark
        $answersQuery = "SELECT COUNT(id) AS answers_shared FROM answer WHERE creator_id = :id";
        $markQuery = "SELECT AVG(score) AS avg_score, COUNT(id) as attempt_count FROM exercise_attempt WHERE u_id = :id";
        
        $answers = $this->dashboardModel->executeQuery($answersQuery, ['id' => $userId])[0]->answers_shared ?? 0;
        $markData = $this->dashboardModel->executeQuery($markQuery, ['id' => $userId])[0];

        $avg_mark = $markData->avg_score > 0 ? number_format($markData->avg_score, 0) . '%' : 'N/A';

        // 3. Consistency (Heatmap from 'events' table)
        // Ensure 'events' table exists, otherwise wrap in try-catch or mock
        try {
            $consistencySql = "
                SELECT 
                    DAYOFWEEK(date) AS day_index, 
                    COUNT(*) AS count 
                FROM events 
                WHERE user_id = :id AND date >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
                GROUP BY day_index
                ORDER BY date DESC
            ";
             // NOTE: Assuming table is 'events' and column 'date' based on standard log patterns. 
             // If table differs, adjust here.
            $consistencyData = $this->dashboardModel->executeQuery($consistencySql, ['id' => $userId]);
        } catch (Exception $e) {
            $consistencyData = [];
        }


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
            "points" => (int)$points,
            "answers_shared" => (int)$answers,
            "answers_change" => "+0 from last week", // Placeholder for advanced comparison logic
            "avg_exercise_mark" => $avg_mark,
            "avg_mark_subtext" => "Based on " . ($markData->attempt_count ?? 0) . " attempts", 
            "consistency_days" => $consistency_days,
            "consistency_subtext" => "Keep up the work!"
        ];

        $this->jsonResponse($response);
    }

    public function api_community_banner()
    {
        $limit = (int) $this->getRequestParameter('limit', 10);
        $offset = (int) $this->getRequestParameter('offset', 0);

        // Check if table exists simply by query attempt
        try {
             $totalResults = $this->dashboardModel->executeQuery("SELECT COUNT(id) as total FROM announcements WHERE is_active = 1")[0]->total ?? 0;
             $sql = "SELECT id, title, created_at FROM announcements WHERE is_active = 1 ORDER BY created_at DESC";
             $rawAnnouncements = $this->dashboardModel->executeQuery($sql, [], $limit, $offset);
        } catch (Exception $e) {
             $totalResults = 0;
             $rawAnnouncements = [];
        }


        // 3. Format Items
        $formattedItems = [];
        foreach ($rawAnnouncements as $announcement) {
            $formattedItems[] = [
                "id" => "an-" . $announcement->id, // Prefix to identify type
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


    public function api_notifications()
    {
        $userId = $_SESSION['user_id'];
        $limit = (int) $this->getRequestParameter('limit', 5);
        $offset = (int) $this->getRequestParameter('offset', 0);

        try {
            $totalResults = $this->dashboardModel->executeQuery("SELECT COUNT(id) as total FROM notifications WHERE receiver_id = :id", ['id' => $userId])[0]->total ?? 0;
             $sql = "SELECT id, content, created_at FROM notifications WHERE receiver_id = :id ORDER BY created_at DESC";
            $items = $this->dashboardModel->executeQuery($sql, ['id' => $userId], $limit, $offset);
        } catch (Exception $e) {
             $totalResults = 0;
             $items = [];
        }

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

    public function api_announcements()
    {
        $userId = $_SESSION['user_id'];
        $limit = (int) $this->getRequestParameter('limit', 10);
        $offset = (int) $this->getRequestParameter('offset', 0);

        try {
            $totalResults = $this->dashboardModel->executeQuery("SELECT COUNT(id) as total FROM announcements WHERE is_active = 1")[0]->total ?? 0;

            // 2. Get Items
            $sql = "
                SELECT 
                    id, title, created_at
                FROM announcements 
                WHERE is_active = 1 AND creator_id = :id
                ORDER BY created_at DESC
            ";
            $rawAnnouncements = $this->dashboardModel->executeQuery($sql, ['id'=>$userId], $limit, $offset);
        } catch (Exception $e) {
            $totalResults = 0;
            $rawAnnouncements = [];
        }

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

    // --- Tab Data Methods ---

    public function api_pinned_notes()
    {
        $userId = $_SESSION['user_id'];
        $limit = (int) $this->getRequestParameter('limit', 10);
        $offset = (int) $this->getRequestParameter('offset', 0);

        $tagStyleMap = [
            'Vector' => 'blue', 'Code' => 'orange', 'Science' => 'green', 
            'History' => 'purple', 'Algorithm' => 'red', 'Mathematics' => 'teal', 'default' => 'grey' 
        ];

        try {
            $totalResults = $this->dashboardModel->executeQuery("SELECT COUNT(id) as total FROM notes WHERE owner_id = :id AND pinned = 1", ['id' => $userId])[0]->total ?? 0;
            
            // Note: DB structure dependencies
            $sql = "
                SELECT 
                    n.id, n.title, n.updated_at, t.name AS topic_name
                FROM notes n
                LEFT JOIN topics t ON n.topic_id = t.id
                WHERE n.owner_id = :id AND n.pinned = 1
                ORDER BY n.updated_at DESC
            ";
            // Removed Tag Join for stability if tables differ
            $rawNotes = $this->dashboardModel->executeQuery($sql, ['id' => $userId], $limit, $offset);
        } catch (Exception $e) {
             $totalResults = 0;
             $rawNotes = [];
        }

        $formattedItems = [];
        foreach ($rawNotes as $note) {
            $formattedItems[] = [
                "id" => $note->id,
                "title" => $note->title,
                "meta_details" => [
                    ["key" => "Topic", "value" => $note->topic_name ?? 'N/A'],
                    ["key" => "Updated", "value" => $this->timeAgo($note->updated_at)],
                ],
                "tags" => [], // Simplified
                "status_badge" => null,
                "secondary_info" => null
            ];
        }

        $this->jsonResponse($this->createPaginatedResponse($formattedItems, $totalResults, $limit, $offset));
    }

    public function api_asked_questions()
    {
        $userId = $_SESSION['user_id'];
        $limit = (int) $this->getRequestParameter('limit', 10);
        $offset = (int) $this->getRequestParameter('offset', 0);

        try {
             $totalResults = $this->dashboardModel->executeQuery("SELECT COUNT(id) as total FROM question WHERE creator_id = :id", ['id' => $userId])[0]->total ?? 0;
             $sql = "SELECT q.id, q.title FROM question q WHERE q.creator_id = :id ORDER BY q.created_at DESC";
             $rawQuestions = $this->dashboardModel->executeQuery($sql, ['id' => $userId], $limit, $offset);
        } catch(Exception $e) {
             $totalResults = 0;
             $rawQuestions = [];
        }

        $formattedItems = [];
        foreach ($rawQuestions as $question) {
            // Simplified logic to retrieve answer count separately if join fails or complex
            $formattedItems[] = [
                "id" => $question->id,
                "title" => $question->title,
                "meta_details" => [
                   // ["key" => "Created", "value" => $this->timeAgo($question->created_at)]
                ],
                "tags" => null,
                "status_badge" => ["text" => "Sent", "style" => "info"],
                "secondary_info" => null
            ];
        }

        $this->jsonResponse($this->createPaginatedResponse($formattedItems, $totalResults, $limit, $offset));
    }

    public function api_created_exercises()
    {
        $userId = $_SESSION['user_id'];
        $limit = (int) $this->getRequestParameter('limit', 10);
        $offset = (int) $this->getRequestParameter('offset', 0);

        try {
            $totalResults = $this->dashboardModel->executeQuery("SELECT COUNT(id) as total FROM exercises WHERE creator_id = :id", ['id' => $userId])[0]->total ?? 0;
            $sql = "SELECT e.id, e.title, e.status FROM exercises e WHERE e.creator_id = :id ORDER BY e.created_at DESC";
            $rawExercises = $this->dashboardModel->executeQuery($sql, ['id' => $userId], $limit, $offset);
        } catch (Exception $e) {
            $totalResults = 0;
            $rawExercises = [];
        }

        $formattedItems = [];
        foreach ($rawExercises as $exercise) {
            $status_map = ['approved' => ["text" => "Published", "style" => "success"], 'pending' => ["text" => "Pending", "style" => "warning"]];
            $formattedItems[] = [
                "id" => $exercise->id,
                "title" => $exercise->title,
                "meta_details" => [],
                "tags" => null,
                "status_badge" => $status_map[$exercise->status] ?? null,
                "secondary_info" => null
            ];
        }
        $this->jsonResponse($this->createPaginatedResponse($formattedItems, $totalResults, $limit, $offset));
    }

    public function api_answered_exercises()
    {
        $userId = $_SESSION['user_id'];
        $limit = (int) $this->getRequestParameter('limit', 10);
        $offset = (int) $this->getRequestParameter('offset', 0);
        
        try{ 
            $totalResults = $this->dashboardModel->executeQuery("SELECT COUNT(id) as total FROM exercise_attempt WHERE u_id = :id", ['id' => $userId])[0]->total ?? 0;
             $sql = "SELECT a.id, a.score, e.title, a.date FROM exercise_attempt a LEFT JOIN exercises e ON a.exe_id = e.id WHERE a.u_id = :id ORDER BY a.date DESC";
            $rawAttempts = $this->dashboardModel->executeQuery($sql, ['id' => $userId], $limit, $offset);
        } catch(Exception $e) {
             $totalResults = 0;
             $rawAttempts = [];
        }

        $formattedItems = [];
        foreach ($rawAttempts as $attempt) {
            $score = (int)$attempt->score;
            $status_badge = $score >= 80 ? ["text" => "High", "style" => "success"] : ["text" => "Avg", "style" => "info"];
            $formattedItems[] = [
                "id" => $attempt->id,
                "title" => $attempt->title,
                "meta_details" => [["key" => "Date", "value" => $this->timeAgo($attempt->date)]],
                "tags" => null,
                "status_badge" => $status_badge,
                "secondary_info" => "Mark: " . $score . "%"
            ];
        }
        $this->jsonResponse($this->createPaginatedResponse($formattedItems, $totalResults, $limit, $offset));
    }

    public function api_attempt_exercises()
    {
        $userId = $_SESSION['user_id'];
        $limit = (int) $this->getRequestParameter('limit', 10);
        $offset = (int) $this->getRequestParameter('offset', 0);
        
        $totalResults = 0; 
        $formattedItems = [];
        // Placeholder as query is complex and optional
        
        $this->jsonResponse($this->createPaginatedResponse($formattedItems, $totalResults, $limit, $offset));
    }    

    public function api_expert_requests()
    {
        $limit = (int) $this->getRequestParameter('limit', 10);
        $offset = (int) $this->getRequestParameter('offset', 0);

        try {
             $totalResults = $this->dashboardModel->executeQuery("SELECT COUNT(id) as total FROM request WHERE review = 'pending'")[0]->total ?? 0;
             $sql = "SELECT r.id, r.title, r.subject FROM request r WHERE r.review = 'pending' ORDER BY r.created_at DESC";
             $rawRequests = $this->dashboardModel->executeQuery($sql, [], $limit, $offset);
        } catch (Exception $e) { $rawRequests = []; $totalResults = 0; }

        $formattedItems = [];
        foreach ($rawRequests as $request) {
            $formattedItems[] = [
                "id" =>$request->id,
                "title" => $request->title,
                "meta_details" => [["key" => "Subject", "value" => $request->subject]],
                "tags" => null,
                "status_badge" => ["text" => "Pending", "style" => "warning"],
                "secondary_info" => null
            ];
        }
        $this->jsonResponse($this->createPaginatedResponse($formattedItems, $totalResults, $limit, $offset));
    }
}