<?php

class Exercises extends Controller
{
    public function index()
    {
        $role = strtolower(trim((string)($_SESSION['role'] ?? 'student')));
        $user_id = $_SESSION['user_id'] ?? 0;

        if (!in_array($role, ['student', 'mentor', 'expert', 'admin'], true)) {
        $role = 'student';
        }
        $can_create = in_array($role, ['mentor', 'expert', 'admin'], true);
        $can_edit = $can_create;

        // Initial load parameters
        $initial_tab = 'all';
        $initial_offset = 0;
        $initial_limit = 5;

        $exerciseModel = new ExercisesModel();
        $expert_subject_ids = $this->get_expert_subject_ids($role, $user_id);
        $initial_load_result = $exerciseModel->get_browser_list([
            'role' => $role,
            'user_id' => $user_id,
            'tab' => $initial_tab,
            'offset' => $initial_offset,
            'limit' => $initial_limit,
            'expert_subject_ids' => $expert_subject_ids,
            
        ]);

        $initial_exercises = array_map(function ($row) {
            return (array)$row;
        }, $initial_load_result['rows'] ?? []);
        $initial_exercises = $this->apply_hidden_exercise_visibility($initial_exercises, (int)$user_id);

        $data = [
            'current_user_id' => $user_id,
            'initial_tab' => $initial_tab, 
            'create_url' => '/your-backend-controller/create-exercise-view',
            // NEW: Initial exercises data is stored here
            'initial_exercises' => $initial_exercises,
            'initial_has_more' => ($initial_offset + $initial_limit) < ($initial_load_result['total'] ?? 0),
            'initial_limit' => $initial_limit,
            'initial_offset' => $initial_offset,
            'role' => $role,
            'can_create' => $can_create,
            'can_edit' => $can_edit,
        ];
        $this->view('exercises/browser', $data);
    }



    public function create(){

        // GET: show the editor page
        if ($this->is_get()) {
            $this->view('exercises/create');
            return;
        }

        // POST to /exercises/create (non-API form submit) - keep behavior minimal for now
        if ($this->is_post()) {
            // If called as a normal form post, redirect to the view (not used by SPA)
            header('Location: '.ROOT.'/exercises/create');
            exit;
        }
    }

    /**
     * API: GET /exercises/api/subjects
     * Returns subject list for exercise create form dropdown.
     */
    public function api_subjects()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Method Not Allowed']);
            return;
        }

        try {
            $subjectsModel = new Subjects();
            $rows = $subjectsModel->where([]) ?: [];

            $subjects = array_map(function ($row) {
                return [
                    'id' => (int)($row->id ?? 0),
                    'name' => trim((string)($row->name ?? '')),
                ];
            }, $rows);

            $subjects = array_values(array_filter($subjects, function ($subject) {
                return $subject['id'] > 0 && $subject['name'] !== '';
            }));

            usort($subjects, function ($a, $b) {
                return strcasecmp($a['name'], $b['name']);
            });

            $this->json_respond([
                'success' => true,
                'subjects' => $subjects,
            ]);
        } catch (Exception $e) {
            error_log('api_subjects error: ' . $e->getMessage());
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Failed to load subjects.']);
            return;
        }
    }

    /**
     * API: POST /exercises/api/save_draft
     * Creates or updates a mentor-owned draft exercise with lenient validation.
     */
    public function api_save_draft()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->json_error('Method not allowed', 405);
        }

        $current_user = (int)($_SESSION['user_id'] ?? 0);
        $role = strtolower(trim((string)($_SESSION['role'] ?? 'student')));

        if ($current_user <= 0 || !in_array($role, ['mentor', 'expert', 'admin'], true)) {
            $this->json_error('Only authorized roles can save drafts', 403);
        }

        $data = $this->json_request();
        $metadata = is_array($data['metadata'] ?? null) ? $data['metadata'] : [];
        $questions = is_array($data['questions'] ?? null) ? $data['questions'] : [];

        $exercise_id = (int)($metadata['id'] ?? $data['exercise_id'] ?? 0);
        $title = trim((string)($metadata['title'] ?? ''));
        if ($title === '') {
            $title = 'Untitled Draft';
        }

        $description = isset($metadata['description']) ? trim((string)$metadata['description']) : null;
        $description = ($description === '') ? null : $description;
        $subject_candidate = (int)($metadata['subject'] ?? $metadata['subjectId'] ?? 0);
        $tags_csv = (string)($metadata['tags'] ?? '');

        try {
            $pdo = $this->db();

            $existing = null;
            if ($exercise_id > 0) {
                $existing = $this->get_owned_exercise($pdo, $exercise_id, $current_user);
                if (!$existing) {
                    $this->json_error('Draft not found or access denied', 403);
                }
            }

            $subject_id = $this->resolve_subject_id($pdo, $subject_candidate, $existing);

            $pdo->beginTransaction();

            $exercise_id = $this->upsert_exercise_shell($pdo, [
                'id' => $exercise_id,
                'subject_id' => $subject_id,
                'title' => $title,
                'description' => $description,
                'status' => 'draft',
            ], $current_user, $existing);

            $this->sync_exercise_tags($pdo, $exercise_id, $tags_csv);
            $this->sync_exercise_questions($pdo, $exercise_id, $questions, false);

            $pdo->commit();

            $this->json_respond([
                'success' => true,
                'message' => 'Draft saved successfully',
                'id' => (int)$exercise_id,
                'status' => 'draft',
            ]);
        } catch (Exception $e) {
            if (isset($pdo) && $pdo->inTransaction()) {
                $pdo->rollBack();
            }
            error_log('api_save_draft error: ' . $e->getMessage());
            $this->json_error('Failed to save draft', 500);
        }
    }

    public function mentorview()
    {
        $exercise_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        $current_user = (int)($_SESSION['user_id'] ?? 0);
        $role = strtolower(trim((string)($_SESSION['role'] ?? 'student')));

        if ($exercise_id <= 0 || $current_user <= 0 || !in_array($role, ['mentor', 'expert', 'admin'], true)) {
            header('Location: ' . ROOT . '/exercises?message=Unauthorized access');
            return;
        }

        try {
            $pdo = $this->db();
            $exerciseStmt = $pdo->prepare("SELECT id, title, description, status, subject_id, creator_id, created_at, updated_at, subject_id FROM exercises WHERE id = :id LIMIT 1");
            $exerciseStmt->execute([':id' => $exercise_id]);
            $exercise = $exerciseStmt->fetch(PDO::FETCH_ASSOC);

            if (!$exercise || (int)$exercise['creator_id'] !== $current_user) {
                header('Location: ' . ROOT . '/exercises?message=Exercise not found or access denied');
                return;
            }

            $bundle = $this->fetchExerciseBundle($pdo, $exercise_id);
            if (!$bundle) {
                header('Location: ' . ROOT . '/exercises?message=Exercise not found');
                return;
            }

            $exerciseModel = new ExercisesModel();
            $stats = $exerciseModel->get_exercise_attempt_stats($exercise_id);

            $data = [
                'exercise' => $bundle['exercise'],
                'questions' => $bundle['questions'],
                'stats' => [
                    'attempt_count' => $stats['attempt_count'],
                    'average_score' => $stats['average_score'],
                    'question_count' => count($bundle['questions'] ?? []),
                ],
                'can_edit' => true,
                'can_hide' => true,
                'edit_url' => ROOT . '/exercises/edit?id=' . $exercise_id,
                'hide_url' => ROOT . '/exercises/hide?id=' . $exercise_id,
                'visibility' => $this->get_exercise_visibility_state($exercise_id, (int)$exercise['creator_id'], $current_user),
            ];

            $this->view('exercises/mentorview', $data);
        } catch (Exception $e) {
            error_log('mentorview error: ' . $e->getMessage());
            header('Location: ' . ROOT . '/exercises?message=Unable to load mentor view');
        }
    }

    /**
     * API: POST /api/exercises/create
     * Accepts JSON body and inserts exercise, questions, answers, and tags in a single transaction.
     */
    public function api_create()
    {
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Method Not Allowed']);
            return;
        }

        $data = $this->json_request();
        $current_user = (int)($_SESSION['user_id'] ?? 0);
        $role = strtolower(trim((string)($_SESSION['role'] ?? 'student')));

        // Require authentication
        if (!$current_user) {
            http_response_code(401);
            echo json_encode(['success' => false, 'message' => 'Authentication required to create exercises.']);
            return;
        }

        if (!in_array($role, ['mentor', 'expert', 'admin'], true)) {
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'Only authorized roles can submit exercises.']);
            return;
        }

        $metadata = $data['metadata'] ?? null;
        $questions = $data['questions'] ?? null;

        if (empty($metadata) || empty($metadata['title']) || !isset($metadata['subject']) || !is_array($questions)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Invalid request: missing required metadata or questions.']);
            return;
        }

        $exercise_id = (int)($metadata['id'] ?? $data['exercise_id'] ?? 0);

        $subject_id = (int)$metadata['subject'];
        if ($subject_id <= 0) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Invalid subject selection.']);
            return;
        }

        if (count($questions) < 3) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'An exercise must have at least 3 questions.']);
            return;
        }

        // Validate questions and options
        foreach ($questions as $qi => $q) {
            $q_text = trim($q['question_text'] ?? $q['prompt'] ?? '');
            $q_expl = trim($q['explanation'] ?? '');
            $weight = isset($q['weight']) ? intval($q['weight']) : (isset($q['difficulty']) ? intval($q['difficulty']) : 0);
            $opts = $q['options'] ?? [];

            if ($q_text === '' || $q_expl === '' || $weight < 1) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => "Invalid question at index $qi: ensure question_text, explanation and weight >= 1 are provided."]); 
                return;
            }

            if (!is_array($opts) || count($opts) < 2) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => "Question at index $qi must have at least two options."]); 
                return;
            }

            $hasCorrect = false;
            foreach ($opts as $opt) {
                if (!isset($opt['answer_text']) && !isset($opt['text'])) {
                    http_response_code(400);
                    echo json_encode(['success' => false, 'message' => "Each option must include answer_text/text for question index $qi."]); 
                    return;
                }
                if (!empty($opt['is_correct']) || !empty($opt['isCorrect'])) $hasCorrect = true;
            }

            if (!$hasCorrect) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => "Please mark at least one correct option for question index $qi."]); 
                return;
            }
        }

        // Good to go --- perform DB operations in a transaction
        try {
            $pdo = $this->db();

            $existing = null;
            if ($exercise_id > 0) {
                $existing = $this->get_owned_exercise($pdo, $exercise_id, $current_user);
                if (!$existing) {
                    http_response_code(403);
                    echo json_encode(['success' => false, 'message' => 'Exercise not found or access denied.']);
                    return;
                }
            }

            if (!$this->subject_exists($pdo, $subject_id)) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Selected subject does not exist.']);
                return;
            }

            $pdo->beginTransaction();

            $exercise_id = $this->upsert_exercise_shell($pdo, [
                'id' => $exercise_id,
                'subject_id' => $subject_id,
                'title' => trim((string)$metadata['title']),
                'description' => $metadata['description'] ?? null,
                'status' => 'pending',
            ], $current_user, $existing);

            $this->sync_exercise_tags($pdo, (int)$exercise_id, (string)($metadata['tags'] ?? ''));
            $this->sync_exercise_questions($pdo, (int)$exercise_id, $questions, true);

            $pdo->commit();

            // Log Event
            $event = new Event;
            $event->log($current_user, $existing ? 'exercise_updated' : 'exercise_created', 'Exercise', $exercise_id, ['subject_id' => $subject_id]);

            http_response_code($existing ? 200 : 201);
            echo json_encode(['success' => true, 'message' => 'Exercise created successfully', 'id' => $exercise_id]);
            return;

        } catch (Exception $e) {
            if (isset($pdo) && $pdo->inTransaction()) $pdo->rollBack();
            // Log detailed error for server logs
            error_log('Exercise creation error: ' . $e->getMessage() . "\n" . $e->getTraceAsString());
            // Return a useful message in development to aid debugging
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Database error while creating exercise', 'error' => $e->getMessage()]);
            return;
        }
    }

    private function subject_exists(PDO $pdo, int $subject_id): bool
    {
        if ($subject_id <= 0) {
            return false;
        }

        $stmt = $pdo->prepare("SELECT id FROM subjects WHERE id = :id LIMIT 1");
        $stmt->execute([':id' => $subject_id]);
        return (bool)$stmt->fetch(PDO::FETCH_ASSOC);
    }

    private function get_owned_exercise(PDO $pdo, int $exercise_id, int $current_user)
    {
        $stmt = $pdo->prepare("SELECT id, creator_id, subject_id, status FROM exercises WHERE id = :id LIMIT 1");
        $stmt->execute([':id' => $exercise_id]);
        $exercise = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$exercise || (int)$exercise['creator_id'] !== $current_user) {
            return null;
        }

        return $exercise;
    }

    private function resolve_subject_id(PDO $pdo, int $subject_candidate, $existing = null): int
    {
        if ($subject_candidate > 0 && $this->subject_exists($pdo, $subject_candidate)) {
            return $subject_candidate;
        }

        $existing_subject = (int)($existing['subject_id'] ?? 0);
        if ($existing_subject > 0 && $this->subject_exists($pdo, $existing_subject)) {
            return $existing_subject;
        }

        $fallback = $pdo->query("SELECT id FROM subjects ORDER BY id ASC LIMIT 1")->fetch(PDO::FETCH_ASSOC);
        if (!$fallback) {
            throw new Exception('No subject found for draft save');
        }

        return (int)$fallback['id'];
    }

    private function upsert_exercise_shell(PDO $pdo, array $payload, int $current_user, $existing = null): int
    {
        $exercise_id = (int)($payload['id'] ?? 0);
        $subject_id = (int)($payload['subject_id'] ?? 0);
        $title = trim((string)($payload['title'] ?? 'Untitled Draft'));
        $description = $payload['description'] ?? null;
        $status = trim((string)($payload['status'] ?? 'draft'));

        if ($title === '') {
            $title = 'Untitled Draft';
        }

        if ($description === '') {
            $description = null;
        }

        if ($exercise_id > 0 && $existing) {
            $update = $pdo->prepare("UPDATE exercises SET subject_id = :subject_id, title = :title, description = :description, status = :status, updated_at = NOW() WHERE id = :id AND creator_id = :creator_id");
            $update->execute([
                ':subject_id' => $subject_id,
                ':title' => $title,
                ':description' => $description,
                ':status' => $status,
                ':id' => $exercise_id,
                ':creator_id' => $current_user,
            ]);
            return $exercise_id;
        }

        $insert = $pdo->prepare("INSERT INTO exercises (subject_id, title, description, creator_id, status, created_at, updated_at) VALUES (:subject_id, :title, :description, :creator_id, :status, NOW(), NOW())");
        $insert->execute([
            ':subject_id' => $subject_id,
            ':title' => $title,
            ':description' => $description,
            ':creator_id' => $current_user,
            ':status' => $status,
        ]);

        return (int)$pdo->lastInsertId();
    }

    private function sync_exercise_tags(PDO $pdo, int $exercise_id, string $tags_csv): void
    {
        $deleteRel = $pdo->prepare("DELETE FROM exercisetag WHERE exercise_id = :exercise_id");
        $deleteRel->execute([':exercise_id' => $exercise_id]);

        $tags = array_values(array_unique(array_filter(array_map('trim', explode(',', $tags_csv)))));
        if (empty($tags)) {
            return;
        }

        $tagSelect = $pdo->prepare("SELECT id FROM tags WHERE name = :name LIMIT 1");
        $tagInsert = $pdo->prepare("INSERT INTO tags (name) VALUES (:name)");
        $relInsert = $pdo->prepare("INSERT INTO exercisetag (exercise_id, tag_id) VALUES (:exercise_id, :tag_id)");

        foreach ($tags as $tag_name) {
            $tagSelect->execute([':name' => $tag_name]);
            $tagRow = $tagSelect->fetch(PDO::FETCH_ASSOC);

            if ($tagRow) {
                $tag_id = (int)$tagRow['id'];
            } else {
                $tagInsert->execute([':name' => $tag_name]);
                $tag_id = (int)$pdo->lastInsertId();
            }

            $relInsert->execute([
                ':exercise_id' => $exercise_id,
                ':tag_id' => $tag_id,
            ]);
        }
    }

    private function sync_exercise_questions(PDO $pdo, int $exercise_id, array $questions, bool $strict): void
    {
        $questionIdsStmt = $pdo->prepare("SELECT id FROM exercisequestion WHERE exercise_id = :exercise_id");
        $questionIdsStmt->execute([':exercise_id' => $exercise_id]);
        $question_ids = array_map('intval', array_column($questionIdsStmt->fetchAll(PDO::FETCH_ASSOC), 'id'));

        if (!empty($question_ids)) {
            $answerDelete = $pdo->prepare("DELETE FROM exerciseanswer WHERE question_id = :question_id");
            foreach ($question_ids as $question_id) {
                $answerDelete->execute([':question_id' => $question_id]);
            }
        }

        $questionDelete = $pdo->prepare("DELETE FROM exercisequestion WHERE exercise_id = :exercise_id");
        $questionDelete->execute([':exercise_id' => $exercise_id]);

        if (empty($questions)) {
            return;
        }

        $questionInsert = $pdo->prepare("INSERT INTO exercisequestion (question_text, explanation, weight, exercise_id, display_order) VALUES (:question_text, :explanation, :weight, :exercise_id, :display_order)");
        $answerInsert = $pdo->prepare("INSERT INTO exerciseanswer (answer_text, is_correct, display_order, question_id) VALUES (:answer_text, :is_correct, :display_order, :question_id)");

        foreach ($questions as $qi => $q) {
            $question_text = trim((string)($q['question_text'] ?? $q['prompt'] ?? ''));
            $explanation = trim((string)($q['explanation'] ?? ''));
            $weight = isset($q['weight']) ? (int)$q['weight'] : (isset($q['difficulty']) ? (int)$q['difficulty'] : 1);
            if ($weight < 1) {
                $weight = 1;
            }

            if (!$strict && $question_text === '' && $explanation === '') {
                continue;
            }

            $questionInsert->execute([
                ':question_text' => $question_text,
                ':explanation' => $explanation,
                ':weight' => $weight,
                ':exercise_id' => $exercise_id,
                ':display_order' => (int)$qi,
            ]);

            $question_id = (int)$pdo->lastInsertId();
            $options = is_array($q['options'] ?? null) ? $q['options'] : [];

            foreach ($options as $oi => $opt) {
                $answer_text = trim((string)($opt['answer_text'] ?? $opt['text'] ?? ''));
                if (!$strict && $answer_text === '') {
                    continue;
                }

                $is_correct = (!empty($opt['is_correct']) || !empty($opt['isCorrect'])) ? 1 : 0;

                $answerInsert->execute([
                    ':answer_text' => $answer_text,
                    ':is_correct' => $is_correct,
                    ':display_order' => (int)$oi,
                    ':question_id' => $question_id,
                ]);
            }
        }
    }


    public function attempt()
    {
        $exercise_id = isset($_GET['id']) ? (int)$_GET['id'] : null;

        if (!$exercise_id) {
            header('Location: '.ROOT.'/exercises?message=Exercise ID is required to attempt an exercise');
            return;
        }

        $exercises = new ExercisesModel;
        $exercise = $exercises->first(['id' => $exercise_id]);

        if (!$exercise || !$this->isExerciseVisibleForAttempt($exercise)) {
            header('Location: '.ROOT.'/exercises?message=Exercise is not approved for attempts');
            return;
        }

        $data = [
            'exercise_id' => (int)$exercise_id,
            'exercise_title' => $exercise->title,
        ];

        $this->view('exercises/attempt', $data);
    }


    public function show()
    {
        $exercise_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        if ($exercise_id <= 0) {
            header('Location: '.ROOT.'/exercises?message=Exercise ID is required to review an exercise');
            return;
        }

        $exercises = new ExercisesModel;
        $exercise_data = $exercises->first(['id' => $exercise_id]);

        if (!$exercise_data || !$this->isExerciseVisibleForAttempt($exercise_data)) {
            header('Location: '.ROOT.'/exercises?message=Exercise is not approved for attempts');
            return;
        }

        $user = new User;
        $subject = new Subjects;
        $exercise_tag = new ExerciseTag;
        $user_vote_exercise = new UserVoteExercise;
        $tags = new Tags;
        $exercisequestion = new Exercisequestion;
        $exerciseanswer = new Exerciseanswer;

        $creator = $user->first(['id' => $exercise_data->creator_id])->username ?? 'Unknown';
        $subject_name = $subject->first(['id' => $exercise_data->subject_id])->name ?? 'Unknown Subject';

        $tag_in_exercise = $exercise_tag->where(['exercise_id' => $exercise_id]);
        $tag_list = [];

        foreach ($tag_in_exercise as $tag) {
            $tag_list[] = $tags->first(['id' => $tag->tag_id])->name ?? 'Unknown Tag';
        }

        $votes = $user_vote_exercise->where(['exercise_id' => $exercise_id]);
        $upvotes = 0;
        $downvotes = 0;
        $user_vote_status = 'none';

        foreach ($votes as $vote) {
            if ($vote->votetype === 'upvote') {
                $upvotes++;
            } elseif ($vote->votetype === 'downvote') {
                $downvotes++;
            }
        }

        $questions = $exercisequestion->where(['exercise_id' => $exercise_id]);
        if (empty($questions)) {
            header('Location: '.ROOT.'/exercises/attempt?id='.$exercise_id.'&message=No questions found for this exercise');
            return;
        }

        $current_user = (int)($_SESSION['user_id'] ?? 0);
        $average_score = 0;
        $user_answers = [];
        $correct_answers = [];
        $selected_by_question = [];

        if ($current_user > 0) {
            try {
                $pdo = $this->db();

                $attemptStmt = $pdo->prepare("SELECT id, score FROM exercise_attempt WHERE exe_id = :exercise_id AND u_id = :user_id ORDER BY latest DESC, date DESC, id DESC LIMIT 1");
                $attemptStmt->execute([
                    ':exercise_id' => $exercise_id,
                    ':user_id' => $current_user,
                ]);
                $latestAttempt = $attemptStmt->fetch(PDO::FETCH_ASSOC);

                if ($latestAttempt) {
                    $average_score = (float)$latestAttempt['score'];

                    $attemptAnswersStmt = $pdo->prepare("SELECT question_id, user_response FROM attempt_answer WHERE attempt_id = :attempt_id");
                    $attemptAnswersStmt->execute([':attempt_id' => (int)$latestAttempt['id']]);
                    $attemptAnswers = $attemptAnswersStmt->fetchAll(PDO::FETCH_ASSOC);

                    foreach ($attemptAnswers as $row) {
                        $selected_ids = [];
                        if (!empty($row['user_response'])) {
                            $decoded = json_decode($row['user_response'], true);
                            if (is_array($decoded)) {
                                $selected_ids = array_map('intval', $decoded);
                            }
                        }

                        $q_id = (int)($row['question_id'] ?? 0);
                        if ($q_id > 0) {
                            $selected_by_question[$q_id] = $selected_ids;
                        }
                    }
                }
            } catch (Exception $e) {
                error_log('show() attempt mapping error: ' . $e->getMessage());
            }
        }

        $question_list = [];

        foreach ($questions as $question) {
            $answers = $exerciseanswer->where(['question_id' => $question->id]);
            $selected_ids = $selected_by_question[(int)$question->id] ?? [];
            $answer_options = [];
            $correct_ids = [];

            foreach ($answers as $ans) {
                $option_id = (int)$ans->id;
                $is_correct = (bool)$ans->is_correct;
                $is_selected = in_array($option_id, $selected_ids, true);

                if ($is_correct) {
                    $correct_ids[] = $option_id;
                }

                $answer_options[] = [
                    'option_id' => $option_id,
                    'text' => (string)$ans->answer_text,
                    'is_correct' => $is_correct,
                    'was_selected' => $is_selected,
                ];
            }

            $question_id = (int)$question->id;
            $question_list[] = [
                'id' => $question_id,
                'question_text' => (string)$question->question_text,
                'options' => $answer_options,
            ];

            $user_answers[$question_id] = $selected_ids;
            $correct_answers[$question_id] = $correct_ids;
        }

        error_log('show() user_answers: ' . json_encode($user_answers));
        error_log('show() correct_answers: ' . json_encode($correct_answers));

        $review_data = [
            'average_score' => $average_score,
            'total_questions' => count($question_list),
            'user_answers' => $user_answers,
            'correct_answers' => $correct_answers,
        ];

        $role = $_SESSION['role'] ?? 'student';
        $can_edit = in_array($role, ['expert', 'admin'], true);

        $data = [
            'exercise_details' => [
                'id' => $exercise_data->id,
                'title' => $exercise_data->title,
                'creator' => $creator,
                'role' => 'under '.$subject_name,
                'created_at' => $exercise_data->created_at,
                'tags' => $tag_list,
                'upvotes' => $upvotes,
                'downvotes' => $downvotes,
                'user_vote_status' => $user_vote_status,
            ],
            'questions' => $question_list,
            'review_data' => $review_data,
            'can_edit' => $can_edit,
        ];

        $this->view('exercises/view', $data);

    }

    public function edit()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // --- POST (Update Logic) ---
            
            // Load relevant models (ensure ExerciseQuestion and ExerciseAnswer models are used)
            $exercise_id = $_POST['exercise_id'] ?? 1;
    
            // Validation and Permission checks (omitted for brevity, but should be here)
    
            // **TODO: Implement the update logic for all exercise, question, and answer data**
    
            // After update redirect to show page
            header('Location: '.ROOT.'/exercises/show?id='.$exercise_id);
        } else {
            // --- GET (Load Edit View Logic) ---
            $exercise_id = $_GET['id'] ?? null; // Changed default to null for proper check
    
            if (!$exercise_id) {
                header('Location: '.ROOT.'/exercises?message=Exercise ID is required to edit an exercise');
                return; // Use return after header
            }
    
            // --- Load Models ---
            // Assuming your models are named as shown in the original code
            $exercises = new ExercisesModel;
            $exercisequestion = new Exercisequestion;
            $exerciseanswer = new Exerciseanswer;
            $tags = new Tags;
            $subject = new Subjects;
            $exercise_tag = new ExerciseTag;
            
            // --- 1. Fetch Exercise Metadata ---
            $exercise_data = $exercises->first(['id' => $exercise_id]);
    
            if (!$exercise_data) {
                header('Location: '.ROOT.'/exercises?message=Exercise not found');
                return;
            }
    
            $current_user = (int)($_SESSION['user_id'] ?? 0);
            $role = strtolower(trim((string)($_SESSION['role'] ?? 'student')));

            if ($current_user <= 0 || $role == 'student' || (int)$exercise_data->creator_id !== $current_user) {
                header('Location: '.ROOT.'/exercises?message=Unauthorized access');
                return;
            }
    
            // --- 2. Fetch Questions and Answers ---
            // Fetch questions, ideally ordered by the new `display_order` column
            // Assuming the model supports a simple WHERE condition for now:
            $questions = $exercisequestion->where(['exercise_id' => $exercise_id]);
            
            $question_list = [];
    
            foreach ($questions as $question) {
                // Fetch answers for the current question, ideally ordered by the new `display_order` column
                $answers = $exerciseanswer->where(['question_id' => $question->id]);
                $answer_options = [];
    
                foreach ($answers as $ans) {
                    // Construct the front-end option object, including new fields
                    $answer_options[] = [
                        // 'id' => $ans->id, // Use if needed for internal JS tracking
                        'text' => $ans->answer_text,
                        'isCorrect' => (bool)$ans->is_correct, // Cast to boolean for JS
                        // 'displayOrder' => $ans->display_order // Use if fetched from model
                    ];
                }
    
                // Construct the front-end question object, including new fields
                $question_list[] = [
                    'id' => $question->id,
                    'prompt' => $question->question_text,
                    'explanation' => $question->explanation, // NEW FIELD
                    'weight' => $question->weight,           // NEW FIELD
                    'options' => $answer_options,
                    // 'displayOrder' => $question->display_order // Use if fetched from model
                ];
            }
            
            // --- 3. Format Metadata (for front-end) ---
            $tag_in_exercise = $exercise_tag->where(['exercise_id' => $exercise_id]);
            $tags_list = [];
    
            foreach ($tag_in_exercise as $tag) {
                $tags_list[] = $tags->first(['id' => $tag->tag_id])->name ?? 'Unknown Tag';
            }
    
            // --- 4. Package all data for the view ---
            // We must fetch and pass the subject name and tags list as a comma-separated string
            $subject_name = $subject->first(['id' => $exercise_data->subject_id])->name ?? 'Unknown Subject';
    
            $data = [
                'is_edit_mode' => true,
                'initial_data' => [
                    'metadata' => [
                        'id' => $exercise_data->id,
                        'title' => $exercise_data->title,
                        'subjectId' => $exercise_data->subject_id,
                        'subject' => $subject_name,
                        'description' => $exercise_data->description ?? '',
                        'tags' => implode(', ', $tags_list),
                    ],
                    'questions' => $question_list,
                ],
            ];
    
            $this->view('exercises/create', $data);
        }
    }
    public function delete()
    {
        // Process exercise deletion
        $exercise_id = $_POST['id'] ?? null;

        if ($exercise_id) {
            //load relevant models

            //delete related data first due to foreign key constraints in database following order:
            //delete answers
            //delete questions

            //finally delete the exercise
            
            //show success message
            header('Location: '.ROOT.'/exercises?message=Exercise deleted successfully');
        } else {
            header('Location: '.ROOT.'/exercises?message=Invalid exercise ID');
        }
    }

    public function hide()
    {
        $this->toggleVisibility();
    }

    public function toggleVisibility()
    {
        $input = $this->json_request();
        $exercise_id = (int)($_POST['exercise_id'] ?? $_POST['id'] ?? ($_GET['id'] ?? ($input['exercise_id'] ?? $input['id'] ?? 0)));
        $current_user = (int)($_SESSION['user_id'] ?? 0);
        $role = strtolower(trim((string)($_SESSION['role'] ?? 'student')));

        if ($exercise_id <= 0 || $current_user <= 0 || !in_array($role, ['mentor', 'expert', 'admin'], true)) {
            $this->json_error('Unauthorized request', 403);
        }

        try {
            $pdo = $this->db();
            $stmt = $pdo->prepare("SELECT id, creator_id FROM exercises WHERE id = :id LIMIT 1");
            $stmt->execute([':id' => $exercise_id]);
            $exercise = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$exercise || (int)$exercise['creator_id'] !== $current_user) {
                $this->json_error('Exercise not found or access denied', 403);
            }

            $current_visibility = $this->get_exercise_visibility_state($exercise_id, (int)$exercise['creator_id'], $current_user);
            $next_visibility = ($current_visibility === 'hidden') ? 'visible' : 'hidden';
            $this->set_exercise_visibility_state($exercise_id, $next_visibility, (int)$exercise['creator_id'], $current_user);

            $payload = [
                'success' => true,
                'status' => 'success',
                'exercise_id' => $exercise_id,
                'visibility' => $next_visibility,
            ];

            if ($_SERVER['REQUEST_METHOD'] === 'POST' || !empty($input)) {
                $this->json_respond($payload);
            }

            $message = $next_visibility === 'hidden'
                ? 'Exercise hidden from other users for this session.'
                : 'Exercise shown to other users.';

            header('Location: ' . ROOT . '/exercises/mentorview?id=' . $exercise_id . '&message=' . urlencode($message));
            exit;
        } catch (Exception $e) {
            error_log('toggleVisibility error: ' . $e->getMessage());
            $this->json_error('Failed to update exercise visibility', 500);
        }
    }

    public function expertreview()
    {
        // ============= EXPERT ROLE CHECK =============
        // Only expert users can access pending exercise reviews
        if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'expert') {
            header('Location: ' . ROOT . 'home?message=Only experts can review exercises');
            exit;
        }

        $current_expert_id = $_SESSION['user_id'];
        
        // If GET request with exercise ID, load specific exercise for review
        if ($this->is_get()) {
            $exercise_id = $_GET['id'] ?? null;

            if (!$exercise_id) {
                header('Location: ' . ROOT . 'exercises?message=Exercise ID is required');
                exit;
            }

            $exercise_id = (int)$exercise_id;

            try {
                $pdo = $this->db();
                
                // Fetch exercise with creator and subject info
                $exerciseStmt = $pdo->prepare("
                    SELECT e.id, e.title, e.status, e.subject_id, e.creator_id, e.created_at, 
                           s.name AS subject_name, u.username AS creator_name
                    FROM exercises e 
                    LEFT JOIN subjects s ON s.id = e.subject_id
                    LEFT JOIN user u ON u.id = e.creator_id
                    WHERE e.id = :id AND e.status = 'pending'
                    LIMIT 1
                ");
                $exerciseStmt->execute([':id' => $exercise_id]);
                $exercise = $exerciseStmt->fetch(PDO::FETCH_ASSOC);

                if (!$exercise) {
                    header('Location: ' . ROOT . 'exercises?message=Exercise not found or not pending');
                    exit;
                }

                // Check: Expert cannot review their own exercises
                if ($exercise['creator_id'] == $current_expert_id) {
                    header('Location: ' . ROOT . 'exercises?message=You cannot review your own exercises');
                    exit;
                }

                // Fetch bundle data (questions, options, etc)
                $bundle = $this->fetchExerciseBundle($pdo, $exercise_id);

                $data = [
                    'exercise_id' => $exercise_id,
                    'exercise_title' => $exercise['title'],
                    'creator_name' => $exercise['creator_name'],
                    'subject_name' => $exercise['subject_name'],
                    'created_at' => $exercise['created_at'],
                    'mode' => 'review',
                ];

                $this->view('exercises/attempt', $data);

            } catch (Exception $e) {
                error_log('expertreview error: ' . $e->getMessage());
                header('Location: ' . ROOT . 'exercises?message=Error loading exercise');
                exit;
            }
        }
        // If POST request, this is the actual review submission (handled by JS)
        else if ($this->is_post()) {
            // This is typically handled by api_approve_exercise() or api_reject_exercise()
            header('Location: ' . ROOT . 'exercises');
            exit;
        }
    }

    public function viewattempt($exercise_id, $attempt_id)
    {
        $exercise_id = (int)$exercise_id;
        $attempt_id = (int)$attempt_id;

        if ($exercise_id <= 0 || $attempt_id <= 0) {
            header('Location: '.ROOT.'/exercises?message=Invalid attempt reference');
            return;
        }

        $exercises = new ExercisesModel;
        $exercise = $exercises->first(['id' => $exercise_id]);

        if (!$exercise || $exercise->status !== 'approved') {
            header('Location: '.ROOT.'/exercises?message=Exercise not available');
            return;
        }

        $data = [
            'exercise_id' => $exercise_id,
            'attempt_id' => $attempt_id,
            'exercise_title' => $exercise->title,
        ];

        $this->view('exercises/viewattempt', $data);
    }

    /**
     * API: POST /exercises/api/approve
     * Approves a pending exercise.
     */
    public function api_approve_exercise()
    {
        // Only experts can approve
        if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'expert') {
            $this->json_error('Only experts can approve exercises', 403);
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->json_error('Method not allowed', 405);
        }

        $data = $this->json_request();
        $exercise_id = (int)($data['exercise_id'] ?? 0);
        $current_expert_id = $_SESSION['user_id'];

        if (!$exercise_id) {
            $this->json_error('Missing exercise ID', 400);
        }

        try {
            $pdo = $this->db();

            // Fetch the exercise
            $exerciseStmt = $pdo->prepare("SELECT id, status, creator_id FROM exercises WHERE id = :id LIMIT 1");
            $exerciseStmt->execute([':id' => $exercise_id]);
            $exercise = $exerciseStmt->fetch(PDO::FETCH_ASSOC);

            if (!$exercise) {
                $this->json_error('Exercise not found', 404);
            }

            if ($exercise['status'] !== 'pending') {
                $this->json_error('Exercise is not pending', 400);
            }

            // Check: Expert cannot review their own exercises
            if ($exercise['creator_id'] == $current_expert_id) {
                $this->json_error('You cannot approve your own exercises', 403);
            }

            // Update exercise status to 'approved'
            // IMPORTANT: Only update status, reviewed_by, and updated_at - DO NOT modify schema
            $updateStmt = $pdo->prepare("
                UPDATE exercises 
                SET status = 'approved', reviewed_by = :reviewed_by, updated_at = NOW()
                WHERE id = :id
            ");
            $updateStmt->execute([
                ':id' => $exercise_id,
                ':reviewed_by' => $current_expert_id
            ]);

            $this->json_respond([
                'success' => true,
                'message' => 'Exercise approved successfully',
                'exercise_id' => $exercise_id,
                'new_status' => 'approved'
            ]);

        } catch (Exception $e) {
            error_log('api_approve_exercise error: ' . $e->getMessage());
            $this->json_error('Database error while approving exercise', 500);
        }
    }

    /**
     * API: POST /exercises/api/reject
     * Rejects a pending exercise with optional feedback.
     */
    public function api_reject_exercise()
    {
        // Only experts can reject
        if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'expert') {
            $this->json_error('Only experts can reject exercises', 403);
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->json_error('Method not allowed', 405);
        }

        $data = $this->json_request();
        $exercise_id = (int)($data['exercise_id'] ?? 0);
        $feedback = trim($data['feedback'] ?? '');
        $current_expert_id = $_SESSION['user_id'];

        if (!$exercise_id) {
            $this->json_error('Missing exercise ID', 400);
        }

        try {
            $pdo = $this->db();

            // Fetch the exercise
            $exerciseStmt = $pdo->prepare("SELECT id, status, creator_id FROM exercises WHERE id = :id LIMIT 1");
            $exerciseStmt->execute([':id' => $exercise_id]);
            $exercise = $exerciseStmt->fetch(PDO::FETCH_ASSOC);

            if (!$exercise) {
                $this->json_error('Exercise not found', 404);
            }

            if ($exercise['status'] !== 'pending') {
                $this->json_error('Exercise is not pending', 400);
            }

            // Check: Expert cannot review their own exercises
            if ($exercise['creator_id'] == $current_expert_id) {
                $this->json_error('You cannot reject your own exercises', 403);
            }

            // Update exercise: keep status as 'pending', save feedback, and update timestamp
            // Feedback is stored but exercise remains pending for creator to revise
            $updateStmt = $pdo->prepare("
                UPDATE exercises 
                SET feedback = :feedback, reviewed_by = :reviewed_by, updated_at = NOW()
                WHERE id = :id
            ");
            $updateStmt->execute([
                ':id' => $exercise_id,
                ':feedback' => !empty($feedback) ? $feedback : null,
                ':reviewed_by' => $current_expert_id
            ]);

            // Optional: Send notification to creator about feedback
            // This can be implemented by creating a notification record
            if (!empty($feedback)) {
                $this->send_feedback_notification($pdo, $exercise['creator_id'], $exercise_id, $feedback);
            }

            $this->json_respond([
                'success' => true,
                'message' => 'Feedback sent to exercise creator',
                'exercise_id' => $exercise_id,
                'status' => 'pending',
                'feedback_sent' => !empty($feedback)
            ]);

        } catch (Exception $e) {
            error_log('api_reject_exercise error: ' . $e->getMessage());
            $this->json_error('Database error while sending feedback', 500);
        }
    }

    /**
     * Helper: Sends a notification to the creator with feedback
     */
    private function send_feedback_notification($pdo, $creator_id, $exercise_id, $feedback)
    {
        try {
            // Create notification entry for the creator
            $notificationStmt = $pdo->prepare("
                INSERT INTO notifications (sender_id, receiver_id, content, created_at)
                VALUES (:sender_id, :receiver_id, :content, NOW())
            ");
            $notificationStmt->execute([
                ':sender_id' => $_SESSION['user_id'],
                ':receiver_id' => $creator_id,
                ':content' => "Your exercise #$exercise_id received feedback: " . substr($feedback, 0, 100)
            ]);
        } catch (Exception $e) {
            error_log('send_feedback_notification error: ' . $e->getMessage());
            // Don't throw - this is auxiliary functionality
        }
    }

    /**
     * API: GET /exercises/api/pending
     * Fetches pending exercises for expert review.
     * Supports pagination and optional subject filtering.
     */
    public function api_get_pending_exercises()
    {
        // Only experts can view pending exercises for review
        if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'expert') {
            $this->json_error('Only experts can view pending exercises', 403);
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            $this->json_error('Method not allowed', 405);
        }

        $offset = (int)($_GET['offset'] ?? 0);
        $limit = (int)($_GET['limit'] ?? 10);
        $subject_id = (int)($_GET['subject_id'] ?? 0);

        // Validate pagination params
        if ($limit < 1 || $limit > 100) $limit = 10;
        if ($offset < 0) $offset = 0;

        try {
            $pdo = $this->db();

            // Build query to fetch pending exercises
            $query = "
                SELECT e.id, e.title, e.description, e.status, e.created_at, 
                       e.subject_id, s.name AS subject_name, 
                       e.creator_id, u.username AS creator_name,
                       (SELECT COUNT(*) FROM exercisequestion WHERE exercise_id = e.id) AS question_count
                FROM exercises e
                LEFT JOIN subjects s ON s.id = e.subject_id
                LEFT JOIN user u ON u.id = e.creator_id
                WHERE e.status = 'pending'
            ";

            $params = [];

            // // Optional: Filter by subject
            // if ($subject_id > 0) {
            //     $query .= " AND e.subject_id = :subject_id";
            //     $params[':subject_id'] = $subject_id;
            // }

            // Exclude creator's own exercises
            $query .= " AND e.creator_id != :current_user_id";
            $params[':current_user_id'] = $_SESSION['user_id'];

            // Order by creation date (newest first) and paginate
            $query .= " ORDER BY e.created_at DESC LIMIT :limit OFFSET :offset";

            $stmt = $pdo->prepare($query);
            $stmt->execute(array_merge($params, [
                ':limit' => $limit,
                ':offset' => $offset
            ]));
            $exercises = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Get total count for pagination info
            $countQuery = "
                SELECT COUNT(*) as total FROM exercises e
                WHERE e.status = 'pending' AND e.creator_id != :current_user_id
            ";
            if ($subject_id > 0) {
                $countQuery .= " AND e.subject_id = :subject_id";
            }

            $countStmt = $pdo->prepare($countQuery);
            $countStmt->execute(array_merge(
                [':current_user_id' => $_SESSION['user_id']],
                ($subject_id > 0) ? [':subject_id' => $subject_id] : []
            ));
            $total = $countStmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;

            $this->json_respond([
                'success' => true,
                'exercises' => array_map(function ($e) {
                    return [
                        'id' => (int)$e['id'],
                        'title' => $e['title'],
                        'description' => $e['description'],
                        'creator_name' => $e['creator_name'],
                        'subject_name' => $e['subject_name'],
                        'question_count' => (int)$e['question_count'],
                        'created_at' => $e['created_at']
                    ];
                }, $exercises),
                'total' => (int)$total,
                'offset' => $offset,
                'limit' => $limit,
                'has_more' => ($offset + $limit) < $total
            ]);

        } catch (Exception $e) {
            error_log('api_get_pending_exercises error: ' . $e->getMessage());
            $this->json_error('Failed to load pending exercises', 500);
        }
    }

    /**
     * API: GET /exercises/api/load_review_data/{exercise_id}
     * Loads exercise data for review (similar to attempt data but with read-only mode).
     */
    public function api_load_review_data($exercise_id = null)
    {
        // Only experts can load review data
        if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'expert') {
            $this->json_error('Only experts can review exercises', 403);
        }

        $exercise_id = (int)$exercise_id;
        if ($exercise_id <= 0) {
            $this->json_error('Missing exercise ID', 400);
        }

        try {
            $pdo = $this->db();
            
            // Fetch exercise
            $exerciseStmt = $pdo->prepare("
                SELECT e.id, e.title, e.status, e.subject_id, e.creator_id, e.created_at,
                       s.name AS subject_name, u.username AS creator_name
                FROM exercises e
                LEFT JOIN subjects s ON s.id = e.subject_id
                LEFT JOIN user u ON u.id = e.creator_id
                WHERE e.id = :id AND e.status = 'pending'
                LIMIT 1
            ");
            $exerciseStmt->execute([':id' => $exercise_id]);
            $exercise = $exerciseStmt->fetch(PDO::FETCH_ASSOC);

            if (!$exercise || $exercise['status'] !== 'pending') {
                $this->json_error('Exercise not found or not pending', 404);
            }

            // Check: Expert cannot review their own exercises
            if ($exercise['creator_id'] == $_SESSION['user_id']) {
                $this->json_error('You cannot review your own exercises', 403);
            }

            // Fetch bundle (questions and options)
            $bundle = $this->fetchExerciseBundle($pdo, $exercise_id);

            $this->json_respond([
                'success' => true,
                'id' => (int)$bundle['exercise']['id'],
                'title' => $bundle['exercise']['title'],
                'subject' => $bundle['exercise']['subject_name'],
                'tags' => $bundle['exercise']['tags'] ?? [],
                'creator_name' => $exercise['creator_name'],
                'creator_role' => $bundle['exercise']['creator_role'] ?? 'Unknown',
                'created_at' => $bundle['exercise']['created_at'],
                'creator_id' => (int)$bundle['exercise']['creator_id'],
                'questions' => $bundle['questions']
            ]);

        } catch (Exception $e) {
            error_log('api_load_review_data error: ' . $e->getMessage());
            $this->json_error('Unable to load review data', 500);
        }
    }

    /**
     * API: GET /exercises/api/load_attempt_data/{exercise_id}
     * Loads the full exercise structure including answers and explanations for client-side use.
     * This replaces the security-conscious separation for self-assessment mode.
     */
    public function api_load_attempt_data($exercise_id = null)
    {
        // Allow requesting all approved exercises using a sentinel value
        if ($exercise_id === 'all' || $exercise_id === 'list') {
            try {
                $pdo = $this->db();
                $rows = $pdo->query("SELECT e.id, e.title, e.creator_id, s.name AS subject, e.created_at FROM exercises e LEFT JOIN subjects s ON s.id = e.subject_id WHERE e.status = 'approved' ORDER BY e.created_at DESC")->fetchAll(PDO::FETCH_ASSOC);
                $rows = $this->apply_hidden_exercise_visibility($rows, (int)($_SESSION['user_id'] ?? 0));

                $this->json_respond([
                    'success' => true,
                    'exercises' => array_map(function ($row) {
                        return [
                            'id' => (int)$row['id'],
                            'title' => $row['title'],
                            'subject' => $row['subject'],
                            'created_at' => $row['created_at'],
                        ];
                    }, $rows)
                ]);
            } catch (Exception $e) {
                $this->json_error('Failed to load exercises list', 500);
            }
        }

        $exercise_id = (int)$exercise_id;
        if ($exercise_id <= 0) {
            $this->json_error('Missing exercise ID.', 400);
        }

        try {
            $pdo = $this->db();
            $bundle = $this->fetchExerciseBundle($pdo, $exercise_id);

            if (!$bundle) {
                $this->json_error('Exercise not found', 404);
            }

            if (!$this->isExerciseVisibleForAttempt((object)$bundle['exercise'])) {
                $this->json_error('Exercise is not approved for attempts', 403);
            }

            $this->json_respond([
                'success' => true,
                'id' => (int)$bundle['exercise']['id'],
                'title' => $bundle['exercise']['title'],
                'subject' => $bundle['exercise']['subject_name'],
                'tags' => $bundle['exercise']['tags'] ?? [],
                'created_at' => $bundle['exercise']['created_at'],
                'creator_name' => $bundle['exercise']['creator_name'] ?? 'Unknown',
                'creator_role' => $bundle['exercise']['creator_role'] ?? 'Unknown',
                'creator_id' => (int)$bundle['exercise']['creator_id'],
                'questions' => $bundle['questions'],
            ]);
        } catch (Exception $e) {
            error_log('api_load_attempt_data error: '.$e->getMessage());
            $this->json_error('Unable to load exercise data', 500);
        }
    }

/**
     * API: GET /exercises/api/published
     * Fetches exercises available for browsing, supporting filtering/pagination (R6).
     */
    public function api_get_published()
    {
        $offset = (int)($_GET['offset'] ?? 0);
        $limit = (int)($_GET['limit'] ?? 10);
        
        try {
            $exerciseModel = new ExercisesModel();
            $result = $exerciseModel->get_browser_list([
                'role' => 'student',
                'user_id' => (int)($_SESSION['user_id'] ?? 0),
                'tab' => 'all',
                'offset' => $offset,
                'limit' => $limit,
                'subject' => '',
                'search' => '',
                'sort' => 'created_at-DESC',
            ]);

            $rows = array_map(function ($row) {
                return (array)$row;
            }, $result['rows'] ?? []);
            $rows = $this->apply_hidden_exercise_visibility($rows, (int)($_SESSION['user_id'] ?? 0));

            $this->json_respond([
                'success' => true,
                'exercises' => $rows,
                'total' => $result['total'] ?? 0
            ]);
        } catch (Exception $e) {
            error_log('api_get_published error: '.$e->getMessage());
            $this->json_error('Failed to load exercises', 500);
        }
    }

    /**
     * API: GET /exercises/api/pending_review
     * Fetches the list of exercises pending review for the current Subject Expert (R5).
     */
    public function api_get_pending_review()
    {
        try {
            $exerciseModel = new ExercisesModel();
            $exercises = $exerciseModel->filter_and_search([
                'where' => ['status' => 'pending'],
                'order_by' => 'created_at',
                'order_dir' => 'DESC'
            ]);

            $this->json_respond([
                'success' => true,
                'exercises' => $exercises
            ]);
        } catch (Exception $e) {
            error_log('api_get_pending_review error: '.$e->getMessage());
            $this->json_error('Failed to load pending exercises', 500);
        }
    }
    
    /**
     * API: POST /exercises/api/attempt/{exercise_id}
     * Submits a user's answers and returns the calculated results (R7).
     */
    public function api_submit_attempt($exercise_id = null)
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || empty($exercise_id)) {
             $this->json_error('Invalid request or missing exercise ID.', 400);
        }

        $exercise_id = (int)$exercise_id;
        if ($exercise_id <= 0) {
            $this->json_error('Invalid exercise ID.', 400);
        }

        $payload = $this->json_request();
        $answers = $payload['answers'] ?? [];
        if (!is_array($answers) || empty($answers)) {
            $this->json_error('No answers submitted.', 400);
        }

        // Get current user from session (fallback to test user 2 if not set)
        //$current_user = $_SESSION['user_id'] ?? null;
        
        $current_user = $_SESSION['user_id'] ?? null;
        if (!$current_user) {
            $this->json_error('Authentication required.', 401);
        }

        try {
            $pdo = $this->db();
            $bundle = $this->fetchExerciseBundle($pdo, $exercise_id);

            if (!$bundle) {
                $this->json_error('Exercise not found', 404);
            }

            if ($bundle['exercise']['status'] !== 'approved') {
                $this->json_error('Exercise is not approved for attempts', 403);
            }

            // Map questions for quick access
            $questionMap = [];
            $maxScore = 0;
            foreach ($bundle['questions'] as $q) {
                $questionMap[$q['question_id']] = $q;
                $maxScore += (float)$q['weight'];
            }

            // Normalize user answers into map
            $userSelections = [];
            foreach ($answers as $item) {
                $qid = isset($item['question_id']) ? (int)$item['question_id'] : 0;
                $selected = isset($item['selected_option_ids']) && is_array($item['selected_option_ids'])
                    ? array_map('intval', $item['selected_option_ids'])
                    : [];
                if ($qid > 0) {
                    $userSelections[$qid] = $selected;
                }
            }

            $details = [];
            $totalScore = 0.0;

            foreach ($questionMap as $qid => $qData) {
                $selected = $userSelections[$qid] ?? [];
                sort($selected);

                $correct = array_map('intval', array_column(array_filter($qData['options'], function ($opt) {
                    return $opt['is_correct'] === true;
                }), 'option_id'));
                sort($correct);

                $isCorrect = !empty($selected) && $selected === $correct;
                $scoreEarned = $isCorrect ? (float)$qData['weight'] : 0.0;
                $totalScore += $scoreEarned;

                $options = [];
                foreach ($qData['options'] as $opt) {
                    $options[] = [
                        'option_id' => (int)$opt['option_id'],
                        'text' => $opt['text'],
                        'is_correct' => (bool)$opt['is_correct'],
                        'was_selected' => in_array((int)$opt['option_id'], $selected, true),
                    ];
                }

                $details[] = [
                    'question_id' => (int)$qid,
                    'prompt' => $qData['prompt'],
                    'user_score' => $scoreEarned,
                    'max_weight' => (float)$qData['weight'],
                    'explanation' => $qData['explanation'],
                    'options' => $options,
                ];
            }

            // Persist attempt and answers in one transaction
            $pdo->beginTransaction();

            // Mark previous attempts as not latest
            $markOld = $pdo->prepare("UPDATE exercise_attempt SET latest = 0 WHERE exe_id = :exe_id AND u_id = :user_id AND latest = 1");
            $markOld->execute([':exe_id' => $exercise_id, ':user_id' => $current_user]);

            $attemptStmt = $pdo->prepare("INSERT INTO exercise_attempt (exe_id, u_id, score, latest) VALUES (:exe_id, :user_id, :score, 1)");
            $attemptStmt->execute([
                ':exe_id' => $exercise_id,
                ':user_id' => $current_user,
                ':score' => $totalScore,
            ]);

            $attemptId = (int)$pdo->lastInsertId();

            $answerStmt = $pdo->prepare("INSERT INTO attempt_answer (attempt_id, question_id, user_response, is_correct, score_earned) VALUES (:attempt_id, :question_id, :user_response, :is_correct, :score_earned)");

            foreach ($details as $detail) {
                try {
                    $exerciseQId = (int)$detail['question_id'];  // exercisequestion.id
                    $userResp = $userSelections[$exerciseQId] ?? [];
                    $answerStmt->execute([
                        ':attempt_id' => $attemptId,
                        ':question_id' => $exerciseQId,  // Use exercisequestion.id directly
                        ':user_response' => json_encode($userResp),
                        ':is_correct' => $detail['user_score'] >= $detail['max_weight'] ? 1 : 0,
                        ':score_earned' => (float)$detail['user_score'],
                    ]);
                } catch (Exception $answerError) {
                    error_log('Answer insert error for exerciseQ' . $exerciseQId . ': ' . $answerError->getMessage());
                    throw $answerError;
                }
            }

            $pdo->commit();

            // Log Event for Analytics
            try {
                $exercise_data = $bundle['exercise'];
                $event = new Event;
                $event->log($current_user, 'exercise_attempted', 'Exercise', $exercise_id, [
                    'score' => (float)$totalScore,
                    'max_score' => (float)$maxScore,
                    'subject_id' => (int)($exercise_data['subject_id'] ?? 1)
                ]);
            } catch (Exception $e) {
                error_log("Failed to log exercise_attempted event: " . $e->getMessage());
            }

            $this->json_respond([
                'success' => true,
                'attempt_id' => $attemptId,
                'exercise_id' => $exercise_id,
                'total_score' => $totalScore,
                'total_max_score' => $maxScore,
                'details' => $details,
            ]);

        } catch (Exception $e) {
            if (isset($pdo) && $pdo->inTransaction()) {
                $pdo->rollBack();
            }
            $errorMsg = $e->getMessage();
            error_log('api_submit_attempt error: ' . $errorMsg . "\n" . $e->getTraceAsString());
            // Always return detailed error for now (remove in production)
            $this->json_error('Database Error: ' . $errorMsg, 500);
        }
    }
    
    /**
     * API: GET /exercises/api/history
     * Fetches a list of the user's previously attempted exercises (R9).
     */
    public function api_get_attempt_history()
    {
        // Get current user from session (fallback to test user 2 if not set)
        $current_user = $_SESSION['user_id'] ?? 2;
        
        if (!$current_user) {
            $this->json_error('Authentication required', 401);
        }

        try {
            $attemptModel = new ExerciseAttempt();
            $attempts = $attemptModel->filter_and_search([
                'where' => ['u_id' => $current_user],
                'order_by' => 'date',
                'order_dir' => 'DESC'
            ]);

            $this->json_respond([
                'success' => true,
                'attempts' => $attempts
            ]);
        } catch (Exception $e) {
            error_log('api_get_attempt_history error: '.$e->getMessage());
            $this->json_error('Failed to load attempt history', 500);
        }
    }

    /**
     * API: GET /exercises/api/history/{attempt_id}
     * Fetches the detailed results and explanations for a past attempt (R9).
     */
    public function api_get_attempt_details($attempt_id = null)
    {
        $attempt_id = (int)$attempt_id;
        if ($attempt_id <= 0) {
            $this->json_error('Missing attempt ID.', 400);
        }

        try {
            $pdo = $this->db();

            $attemptStmt = $pdo->prepare("SELECT ea.id, ea.exe_id, ea.u_id, ea.score, ea.date, ex.title, ex.status, s.name AS subject_name FROM exercise_attempt ea JOIN exercises ex ON ex.id = ea.exe_id LEFT JOIN subjects s ON s.id = ex.subject_id WHERE ea.id = :attempt_id LIMIT 1");
            $attemptStmt->execute([':attempt_id' => $attempt_id]);
            $attempt = $attemptStmt->fetch(PDO::FETCH_ASSOC);

            if (!$attempt) {
                $this->json_error('Attempt not found', 404);
            }

            if ($attempt['status'] !== 'approved') {
                $this->json_error('Exercise is not approved', 403);
            }

            $questionStmt = $pdo->prepare("SELECT q.id, q.question_text, q.explanation, q.weight FROM exercisequestion q WHERE q.exercise_id = :exercise_id ORDER BY q.display_order ASC, q.id ASC");
            $questionStmt->execute([':exercise_id' => $attempt['exe_id']]);
            $questions = $questionStmt->fetchAll(PDO::FETCH_ASSOC);

            $answerStmt = $pdo->prepare("SELECT id, answer_text, is_correct, question_id FROM exerciseanswer WHERE question_id = :question_id ORDER BY display_order ASC, id ASC");
            $attemptAnswerStmt = $pdo->prepare("SELECT user_response, is_correct, score_earned FROM attempt_answer WHERE attempt_id = :attempt_id AND question_id = :question_id LIMIT 1");

            $details = [];
            $userAnswers = [];
            $correctAnswers = [];
            $maxScore = 0;

            foreach ($questions as $q) {
                $maxScore += (float)$q['weight'];
                $answerStmt->execute([':question_id' => $q['id']]);
                $optionsRaw = $answerStmt->fetchAll(PDO::FETCH_ASSOC);

                $attemptAnswerStmt->execute([
                    ':attempt_id' => $attempt_id,
                    ':question_id' => $q['id']
                ]);
                $attemptAnswer = $attemptAnswerStmt->fetch(PDO::FETCH_ASSOC);

                $selectedIds = [];
                if ($attemptAnswer && !empty($attemptAnswer['user_response'])) {
                    $selectedIds = json_decode($attemptAnswer['user_response'], true) ?: [];
                }

                $options = [];
                $correctIds = [];
                foreach ($optionsRaw as $opt) {
                    $optionId = (int)$opt['id'];
                    $isCorrectOption = (bool)$opt['is_correct'];
                    if ($isCorrectOption) {
                        $correctIds[] = $optionId;
                    }

                    $options[] = [
                        'option_id' => $optionId,
                        'text' => $opt['answer_text'],
                        'is_correct' => $isCorrectOption,
                        'was_selected' => in_array($optionId, $selectedIds, true),
                    ];
                }

                $questionId = (int)$q['id'];
                $userAnswers[$questionId] = array_map('intval', $selectedIds);
                $correctAnswers[$questionId] = $correctIds;

                $details[] = [
                    'question_id' => $questionId,
                    'prompt' => $q['question_text'],
                    'user_score' => (float)($attemptAnswer['score_earned'] ?? 0),
                    'max_weight' => (float)$q['weight'],
                    // Use stored attempt_answer flag; avoid recalculating correctness here
                    'is_correct' => (bool)($attemptAnswer['is_correct'] ?? 0),
                    // Treat question weight as difficulty for UI display
                    'difficulty' => (int)$q['weight'],
                    'explanation' => $q['explanation'],
                    'options' => $options,
                ];
            }

            error_log('api_get_attempt_details user_answers: ' . json_encode($userAnswers));
            error_log('api_get_attempt_details correct_answers: ' . json_encode($correctAnswers));

            $this->json_respond([
                'success' => true,
                'attempt_id' => $attempt_id,
                'exercise_id' => (int)$attempt['exe_id'],
                'exercise_title' => $attempt['title'],
                'subject' => $attempt['subject_name'],
                'exercise' => [
                    'id' => (int)$attempt['exe_id'],
                    'title' => $attempt['title'],
                ],
                // Score response uses stored attempt score (no recalculation)
                'raw_score' => (float)$attempt['score'],
                'average_score' => (float)$attempt['score'],
                'max_score' => $maxScore,
                'percentage_score' => $maxScore > 0 ? round(((float)$attempt['score'] / $maxScore) * 100, 2) : 0,
                // Keep existing keys for backward compatibility
                'total_score' => (float)$attempt['score'],
                'total_max_score' => $maxScore,
                'attempted_at' => $attempt['date'],
                'questions' => array_map(function ($detail) {
                    return [
                        'id' => $detail['question_id'],
                        'question_text' => $detail['prompt'],
                        'options' => array_map(function ($opt) {
                            return [
                                'option_id' => $opt['option_id'],
                                'text' => $opt['text'],
                                'is_correct' => $opt['is_correct'],
                                'was_selected' => $opt['was_selected'],
                            ];
                        }, $detail['options'] ?? []),
                    ];
                }, $details),
                'options' => array_map(function ($detail) {
                    return $detail['options'] ?? [];
                }, $details),
                'user_answers' => $userAnswers,
                'correct_answers' => $correctAnswers,
                'total_questions' => count($details),
                'details' => $details,
            ]);

        } catch (Exception $e) {
            error_log('api_get_attempt_details error: '.$e->getMessage());
            $this->json_error('Unable to load attempt details', 500);
        }
    }

    /**
     * API: POST /exercises/api/vote/{exercise_id}
     * Submits a vote (upvote/downvote) for an exercise (R8).
     */
    public function api_submit_vote($exercise_id = null)
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || empty($exercise_id)) {
            $this->json_error('Invalid request or missing exercise ID.', 400);
        }

        $exercise_id = (int)$exercise_id;
        // Get current user from session (fallback to test user 2 if not set)

        //$current_user = $_SESSION['user_id'] ?? null;
        $current_user = $_SESSION['user_id'] ?? 2;
        
        if (!$current_user) {
            $this->json_error('Authentication required', 401);
        }

        $input = $this->json_request();
        $vote_type = $input['vote_type'] ?? null;
        
        if (!in_array($vote_type, ['Upvoted', 'Downvoted'])) {
            $this->json_error('Invalid vote type', 400);
        }

        try {
            // Convert vote type from 'Upvoted'/'Downvoted' to 'upvote'/'downvote'
            $dbVoteType = strtolower(str_replace('d', '', $vote_type)); // 'Upvoted' -> 'upvote'
            
            $pdo = $this->db();
            
            // Check if vote already exists (composite primary key: exercise_id, u_id)
            $checkStmt = $pdo->prepare("SELECT votetype FROM uservoteexercise WHERE exercise_id = :exercise_id AND u_id = :u_id LIMIT 1");
            $checkStmt->execute([':exercise_id' => $exercise_id, ':u_id' => $current_user]);
            $existingVote = $checkStmt->fetch(PDO::FETCH_ASSOC);

            if ($existingVote) {
                // Update existing vote (use composite key in WHERE clause)
                $updateStmt = $pdo->prepare("UPDATE uservoteexercise SET votetype = :votetype WHERE exercise_id = :exercise_id AND u_id = :u_id");
                $updateStmt->execute([
                    ':votetype' => $dbVoteType,
                    ':exercise_id' => $exercise_id,
                    ':u_id' => $current_user
                ]);
            } else {
                // Insert new vote
                $insertStmt = $pdo->prepare("INSERT INTO uservoteexercise (exercise_id, u_id, votetype) VALUES (:exercise_id, :u_id, :votetype)");
                $insertStmt->execute([
                    ':exercise_id' => $exercise_id,
                    ':u_id' => $current_user,
                    ':votetype' => $dbVoteType
                ]);
            }

            // Return updated vote count for the UI
            $countStmt = $pdo->prepare("SELECT SUM(CASE WHEN votetype = 'upvote' THEN 1 WHEN votetype = 'downvote' THEN -1 ELSE 0 END) AS vote_count FROM uservoteexercise WHERE exercise_id = :exercise_id");
            $countStmt->execute([':exercise_id' => $exercise_id]);
            $voteCount = (int)($countStmt->fetch(PDO::FETCH_ASSOC)['vote_count'] ?? 0);

            $this->json_respond([
                'success' => true,
                'message' => 'Vote successfully registered.',
                'current_vote_status' => $vote_type,
                'vote_count' => $voteCount
            ]);
        } catch (Exception $e) {
            error_log('api_submit_vote error: '.$e->getMessage());
            $this->json_error('Failed to submit vote', 500);
        }
    }

    /**
     * API: GET /exercises/api/vote/{exercise_id}
     * Gets the current user's vote status for an exercise.
     */
    public function api_get_vote_status($exercise_id = null)
    {
        if (empty($exercise_id)) {
            $this->json_error('Missing exercise ID.', 400);
        }

        $exercise_id = (int)$exercise_id;
        // Get current user from session (fallback to test user 2 if not set)
        
        //$current_user = $_SESSION['user_id'] ?? null;
        $current_user = $_SESSION['user_id'] ?? 2;
        
        if (!$current_user) {
            $this->json_respond([
                'success' => true,
                'current_vote_status' => 'None'
            ]);
        }

        try {
            $pdo = $this->db();
            
            // Check user's vote using correct column name u_id
            $voteStmt = $pdo->prepare("SELECT votetype FROM uservoteexercise WHERE exercise_id = :exercise_id AND u_id = :u_id LIMIT 1");
            $voteStmt->execute([':exercise_id' => $exercise_id, ':u_id' => $current_user]);
            $vote = $voteStmt->fetch(PDO::FETCH_ASSOC);

            $status = 'None';
            if ($vote) {
                // Convert 'upvote' -> 'Upvoted', 'downvote' -> 'Downvoted'
                $status = ucfirst($vote['votetype']) . 'd';
            }

            // Get vote count (already have $pdo from above)
            $countStmt = $pdo->prepare("SELECT SUM(CASE WHEN votetype = 'upvote' THEN 1 WHEN votetype = 'downvote' THEN -1 ELSE 0 END) AS vote_count FROM uservoteexercise WHERE exercise_id = :exercise_id");
            $countStmt->execute([':exercise_id' => $exercise_id]);
            $voteCount = (int)($countStmt->fetch(PDO::FETCH_ASSOC)['vote_count'] ?? 0);

            $this->json_respond([
                'success' => true,
                'current_vote_status' => $status,
                'vote_count' => $voteCount
            ]);
        } catch (Exception $e) {
            error_log('api_get_vote_status error: '.$e->getMessage());
            $this->json_error('Failed to get vote status', 500);
        }
    }

    /**
     * Creates a PDO connection with consistent options.
     */
    private function db()
    {
        return new PDO(
            "mysql:host=".DBHOST.";dbname=".DBNAME.";charset=utf8mb4",
            DBUSER,
            DBPASS,
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ]
        );
    }

    /**
     * Fetches an exercise with its questions and options.
     */
    private function fetchExerciseBundle(PDO $pdo, int $exercise_id)
    {
        $exerciseStmt = $pdo->prepare("SELECT e.id, e.title, e.status, e.subject_id, e.creator_id, e.created_at, s.name AS subject_name, u.username AS creator_name, r.name AS creator_role FROM exercises e LEFT JOIN subjects s ON s.id = e.subject_id LEFT JOIN user u ON u.id = e.creator_id LEFT JOIN roles r ON r.role_id = u.role WHERE e.id = :id LIMIT 1");
        $exerciseStmt->execute([':id' => $exercise_id]);
        $exercise = $exerciseStmt->fetch(PDO::FETCH_ASSOC);

        if (!$exercise) {
            return null;
        }

        $tagStmt = $pdo->prepare("SELECT t.name FROM exercisetag et JOIN tags t ON t.id = et.tag_id WHERE et.exercise_id = :exercise_id ORDER BY t.name ASC");
        $tagStmt->execute([':exercise_id' => $exercise_id]);
        $exercise['tags'] = array_values(array_map(function ($row) {
            return $row['name'];
        }, $tagStmt->fetchAll(PDO::FETCH_ASSOC)));

        $questionStmt = $pdo->prepare("SELECT id, question_text, explanation, weight FROM exercisequestion WHERE exercise_id = :exercise_id ORDER BY display_order ASC, id ASC");
        $questionStmt->execute([':exercise_id' => $exercise_id]);
        $questions = $questionStmt->fetchAll(PDO::FETCH_ASSOC);

        $answerStmt = $pdo->prepare("SELECT id, answer_text, is_correct FROM exerciseanswer WHERE question_id = :question_id ORDER BY display_order ASC, id ASC");

        $questionPayload = [];
        foreach ($questions as $q) {
            $answerStmt->execute([':question_id' => $q['id']]);
            $options = [];
            foreach ($answerStmt->fetchAll(PDO::FETCH_ASSOC) as $opt) {
                $options[] = [
                    'option_id' => (int)$opt['id'],
                    'text' => $opt['answer_text'],
                    'is_correct' => (bool)$opt['is_correct'],
                ];
            }

            $questionPayload[] = [
                'question_id' => (int)$q['id'],
                'prompt' => $q['question_text'],
                'weight' => (float)$q['weight'],
                'explanation' => $q['explanation'],
                'options' => $options,
            ];
        }

        return [
            'exercise' => $exercise,
            'questions' => $questionPayload,
        ];
    }

    private function isExerciseVisibleForAttempt($exercise): bool
    {
        if (!$exercise) {
            return false;
        }

        $exercise_id = (int)($exercise->id ?? $exercise['id'] ?? 0);
        $creator_id = (int)($exercise->creator_id ?? $exercise['creator_id'] ?? 0);
        $current_user_id = (int)($_SESSION['user_id'] ?? 0);

        if ($exercise_id > 0 && $this->is_exercise_hidden_for_viewer($exercise_id, $creator_id, $current_user_id)) {
            return false;
        }

        $status = strtolower(trim((string)($exercise->status ?? $exercise['status'] ?? '')));
        return $status === 'approved';
    }

    private function get_exercise_visibility_state(int $exercise_id, int $creator_id = 0, int $viewer_id = 0): string
    {
        if ($exercise_id <= 0) {
            return 'visible';
        }

        if (!isset($_SESSION['exercise_visibility']) || !is_array($_SESSION['exercise_visibility'])) {
            $_SESSION['exercise_visibility'] = [];
        }

        $exercise_key = (string)$exercise_id;
        $session_state = $_SESSION['exercise_visibility'][$exercise_key] ?? null;
        if (is_string($session_state) && $session_state !== '') {
            return $session_state === 'hidden' ? 'hidden' : 'visible';
        }

        if ($creator_id > 0 && $viewer_id > 0 && $viewer_id === $creator_id) {
            return 'visible';
        }

        $store = $this->read_hidden_exercise_store();
        $entry = $store[$exercise_key] ?? null;
        if (!is_array($entry)) {
            return 'visible';
        }

        $expires_at = (int)($entry['expires_at'] ?? 0);
        if ($expires_at > 0 && $expires_at <= time()) {
            unset($store[$exercise_key]);
            $this->write_hidden_exercise_store($store);
            return 'visible';
        }

        return ((string)($entry['visibility'] ?? 'hidden')) === 'hidden' ? 'hidden' : 'visible';
    }

    private function set_exercise_visibility_state(int $exercise_id, string $visibility, int $creator_id = 0, int $viewer_id = 0): void
    {
        if ($exercise_id <= 0) {
            return;
        }

        if (!isset($_SESSION['exercise_visibility']) || !is_array($_SESSION['exercise_visibility'])) {
            $_SESSION['exercise_visibility'] = [];
        }

        $exercise_key = (string)$exercise_id;
        $normalized_visibility = ($visibility === 'hidden') ? 'hidden' : 'visible';
        $_SESSION['exercise_visibility'][$exercise_key] = $normalized_visibility;

        $store = $this->read_hidden_exercise_store();
        if ($normalized_visibility === 'hidden') {
            $store[$exercise_key] = [
                'creator_id' => $creator_id > 0 ? $creator_id : $viewer_id,
                'hidden_by' => $viewer_id > 0 ? $viewer_id : (int)($_SESSION['user_id'] ?? 0),
                'hidden_at' => time(),
                'expires_at' => time() + $this->get_hide_ttl_seconds(),
                'visibility' => 'hidden',
            ];
        } else {
            unset($store[$exercise_key]);
        }

        $this->write_hidden_exercise_store($store);
    }

    private function is_exercise_hidden_for_viewer(int $exercise_id, int $creator_id, int $viewer_id): bool
    {
        if ($exercise_id <= 0) {
            return false;
        }

        if ($creator_id > 0 && $viewer_id === $creator_id) {
            return false;
        }

        $visibility = $this->get_exercise_visibility_state($exercise_id, $creator_id, $viewer_id);
        return $visibility === 'hidden';
    }

    private function apply_hidden_exercise_visibility(array $rows, int $viewer_id): array
    {
        if (empty($rows)) {
            return [];
        }

        $filtered = [];
        foreach ($rows as $row) {
            $normalized = (array)$row;
            $exercise_id = (int)($normalized['id'] ?? 0);
            $creator_id = (int)($normalized['creator_id'] ?? $normalized['created_by'] ?? 0);

            if ($this->is_exercise_hidden_for_viewer($exercise_id, $creator_id, $viewer_id)) {
                continue;
            }

            $filtered[] = $normalized;
        }

        return $filtered;
    }

    private function get_hidden_exercise_store_path(): string
    {
        $basePath = dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'private' . DIRECTORY_SEPARATOR . 'cache';
        if (!is_dir($basePath)) {
            @mkdir($basePath, 0775, true);
        }

        return $basePath . DIRECTORY_SEPARATOR . 'hidden_exercises.json';
    }

    private function read_hidden_exercise_store(): array
    {
        $path = $this->get_hidden_exercise_store_path();
        if (!file_exists($path)) {
            return [];
        }

        $raw = @file_get_contents($path);
        if ($raw === false || $raw === '') {
            return [];
        }

        $decoded = json_decode($raw, true);
        if (!is_array($decoded)) {
            return [];
        }

        $now = time();
        $dirty = false;
        foreach ($decoded as $exerciseId => $entry) {
            $expiresAt = (int)($entry['expires_at'] ?? 0);
            if ($expiresAt > 0 && $expiresAt <= $now) {
                unset($decoded[$exerciseId]);
                $dirty = true;
            }
        }

        if ($dirty) {
            $this->write_hidden_exercise_store($decoded);
        }

        return $decoded;
    }

    private function write_hidden_exercise_store(array $store): void
    {
        $path = $this->get_hidden_exercise_store_path();
        @file_put_contents($path, json_encode($store, JSON_UNESCAPED_SLASHES), LOCK_EX);
    }

    private function get_hide_ttl_seconds(): int
    {
        $sessionTtl = (int)ini_get('session.gc_maxlifetime');
        if ($sessionTtl <= 0) {
            return 1800;
        }

        return $sessionTtl;
    }
    
    // Helper to send JSON error responses
    private function json_error($message, $code = 400)
    {
        http_response_code($code);
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => $message]);
        exit();
    }
    
    
    public function filter()
    {
        // 1. Validate Request
        if (!$this->is_get()) {
            $this->json_respond(['error' => 'Method not allowed']); // Uses Controller::json_respond
        }

        $exerciseModel = new ExercisesModel();
        
        // 2. Collect Inputs
        $tab = $_GET['tab'] ?? 'all';
        $search = $_GET['q'] ?? '';
        $subject = $_GET['subject'] ?? '';
        $sort = $_GET['sort'] ?? 'id-DESC'; // Format: "column-direction"
        $offset = isset($_GET['offset']) ? (int)$_GET['offset'] : 0;
        $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 5;
        $user_id = $_SESSION['user_id'] ?? 0; // Assuming session is active
        $role = strtolower(trim((string)($_SESSION['role'] ?? 'student')));

        if (!in_array($role, ['student', 'mentor', 'expert', 'admin'], true)) {
            $role = 'student';
        }

        $allowed_tabs = ['all', 'created', 'created_published', 'created_draft', 'attempted', 'pending'];
        if (!in_array($tab, $allowed_tabs, true)) {
            $tab = 'all';
        }

        if ($tab === 'pending' && !in_array($role, ['expert', 'admin'], true)) {
            $tab = 'all';
        }

        if (in_array($tab, ['created', 'created_published', 'created_draft'], true) && $role === 'student') {
            $tab = 'all';
        }

        $limit = ($limit > 0 && $limit <= 100) ? $limit : 5;
        $offset = $offset >= 0 ? $offset : 0;

        $can_create = in_array($role, ['mentor', 'expert', 'admin'], true);
        $can_edit = $can_create;

        $expert_subject_ids = $this->get_expert_subject_ids($role, $user_id);

        // 3. Fetch Data
        $result = $exerciseModel->get_browser_list([
            'role' => $role,
            'user_id' => $user_id,
            'tab' => $tab,
            'search' => $search,
            'subject' => $subject,
            'sort' => $sort,
            'offset' => $offset,
            'limit' => $limit,
            'expert_subject_ids' => $expert_subject_ids,
        ]);

        $filtered_rows = $this->apply_hidden_exercise_visibility(
            array_map(function ($row) {
                return (array)$row;
            }, $result['rows'] ?? []),
            (int)$user_id
        );

        $total = $result['total'] ?? 0;
        $has_more = ($offset + $limit) < $total;

        // 4. Return JSON
        $this->json_respond([
            'exercises' => $filtered_rows,
            'has_more' => $has_more,
            'role' => $role,
            'permissions' => [
                'can_create' => $can_create,
                'can_edit' => $can_edit
            ]
        ]);
    }

    private function get_expert_subject_ids($role, $user_id)
    {
        if (!in_array($role, ['expert'], true)) {
            return [];
        }

        $experts = new Experts();
        $rows = $experts->where(['user_id' => $user_id], 0, null, ['subject_id']);
        if (!$rows) {
            return [];
        }

        $subject_ids = [];
        foreach ($rows as $row) {
            $subject_ids[] = (int)$row->subject_id;
        }

        return array_values(array_unique($subject_ids));
    }
}


