
<?php
class ExercisesModel
{
    use Model;

    protected $table = 'exercises';

    public function get_browser_list(array $params)
    {
        $role = strtolower(trim($params['role'] ?? 'student'));
        $tab = strtolower(trim($params['tab'] ?? 'all'));
        $user_id = (int)($params['user_id'] ?? 0);
        $subject_filter = trim($params['subject'] ?? '');
        $search = trim($params['search'] ?? '');
        $sort = trim($params['sort'] ?? 'id-DESC');
        $limit = isset($params['limit']) ? (int)$params['limit'] : 5;
        $offset = isset($params['offset']) ? (int)$params['offset'] : 0;
        $expert_subject_ids = $params['expert_subject_ids'] ?? [];

        $limit = $limit > 0 ? $limit : 5;
        $offset = $offset >= 0 ? $offset : 0;

        $joins = " LEFT JOIN subjects s ON s.id = e.subject_id LEFT JOIN user u ON u.id = e.creator_id";
        $where = [];
        $bind = [];

        $attempt_join = "";

        if ($tab === 'attempted') {
            $attempt_join = " INNER JOIN exercise_attempt ea ON ea.exe_id = e.id AND ea.u_id = :attempt_user_id";
            $bind[':attempt_user_id'] = $user_id;
        }

        $add_in_clause = function ($column, $values, $prefix) use (&$bind, &$where) {
            if (empty($values)) {
                $where[] = '1 = 0';
                return;
            }

            $placeholders = [];
            foreach (array_values($values) as $idx => $value) {
                $key = ":{$prefix}_{$idx}";
                $placeholders[] = $key;
                $bind[$key] = $value;
            }
            $where[] = $column . ' IN (' . implode(', ', $placeholders) . ')';
        };

        if ($role === 'student') {
            if ($tab === 'created' || $tab === 'pending') {
                $where[] = '1 = 0';
            } else {
                $where[] = "e.status = 'approved'";
            }
        } elseif ($role === 'mentor') {
            if ($tab === 'pending') {
                $where[] = '1 = 0';
            } elseif ($tab === 'created') {
                $where[] = 'e.creator_id = :creator_id';
                $bind[':creator_id'] = $user_id;
            } elseif ($tab === 'attempted') {
                $where[] = "e.status = 'approved'";
            } else {
                $where[] = "(e.status = 'approved' OR e.creator_id = :creator_id)";
                $bind[':creator_id'] = $user_id;
            }
        } elseif ($role === 'expert' || $role === 'admin') {
            if ($tab === 'pending') {
                $where[] = "e.status = 'pending'";
                $where[] = 'e.creator_id != :current_user_id';
                $bind[':current_user_id'] = $user_id;
                if ($role === 'admin') {
                    // Admins can review all pending exercises.
                } else {
                    $add_in_clause('e.subject_id', $expert_subject_ids, 'subject');
                }
            } elseif ($tab === 'created') {
                $where[] = 'e.creator_id = :creator_id';
                $bind[':creator_id'] = $user_id;
            } elseif ($tab === 'attempted') {
                $where[] = "e.status = 'approved'";
            } else {
                if ($role === 'admin') {
                    $where[] = "(e.status = 'approved' OR e.status = 'pending')";
                } else {
                    if (!empty($expert_subject_ids)) {
                        $subject_placeholders = [];
                        foreach (array_values($expert_subject_ids) as $idx => $val) {
                            $key = ':exp_sub_' . $idx;
                            $subject_placeholders[] = $key;
                            $bind[$key] = $val;
                        }
                        $where[] = "(e.status = 'approved' OR (e.status = 'pending' AND e.creator_id != :current_user_id AND e.subject_id IN (" . implode(', ', $subject_placeholders) . ")))";
                        $bind[':current_user_id'] = $user_id;
                    } else {
                        $where[] = "e.status = 'approved'";
                    }
                }
            }
        } else {
            $where[] = "e.status = 'approved'";
        }

        if ($subject_filter !== '') {
            $where[] = 's.name = :subject_name';
            $bind[':subject_name'] = $subject_filter;
        }

        if ($search !== '') {
            $where[] = '(e.title LIKE :search_term OR u.username LIKE :search_term)';
            $bind[':search_term'] = '%' . $search . '%';
        }

        $sort_parts = explode('-', $sort);
        $sort_column = $sort_parts[0] ?? 'id';
        $sort_dir = strtoupper($sort_parts[1] ?? 'DESC');
        $allowed_sort_columns = [
            'id' => 'e.id',
            'title' => 'e.title',
            'created_at' => 'e.created_at',
        ];
        $order_by = $allowed_sort_columns[$sort_column] ?? 'e.id';
        $order_dir = $sort_dir === 'ASC' ? 'ASC' : 'DESC';

        $where_sql = '';
        if (!empty($where)) {
            $where_sql = ' WHERE ' . implode(' AND ', $where);
        }

        $select_sql = "SELECT DISTINCT e.id, e.title, e.status, e.created_at, s.name AS subject, u.username AS creator_name";
        $from_sql = " FROM exercises e" . $attempt_join . $joins;
        $order_sql = " ORDER BY {$order_by} {$order_dir}";

        $rows = $this->query($select_sql . $from_sql . $where_sql . $order_sql, $bind, $limit, $offset);

        $count_sql = "SELECT COUNT(DISTINCT e.id)" . $from_sql . $where_sql;
        $total = (int)$this->fetch_value($count_sql, $bind);

        return [
            'rows' => $rows ?: [],
            'total' => $total,
        ];
    }


}
?>
