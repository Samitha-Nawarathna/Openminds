<?php

trait Model
{
    use Database;

    public function test()
    {
        $result = $this->query("SELECT * FROM profile;");
        return $result;
    }

    public function findAll()
    {
        $query = "SELECT * FROM $this->table";
        return $this->query($query);
    }

    public function where($data, $offset = 0, $limit = Null, $columns = [], $data_not = [])
    {
        //SELECT * FROM $table WHERE id = :id && id != :id;

        $query = "";

        if (!$columns)
        {
            $query = "SELECT * FROM $this->table ";
        }else
        {
            $query = "SELECT ";

            foreach ($columns as $key => $column) {
                $query .= "`$column`,";
            }

            $query = trim($query, ",");

            $query .= " FROM $this->table";
        }

        if (!empty($data) || !empty($data_not))
        {
            $query .= " WHERE ";

            foreach (array_keys($data) as $key)
            {
                $query .= "$key=:$key &&";
            }
            foreach (array_keys($data_not) as $key)
            {
                $query .= "$key!=:$key &&";
            }
            $query = trim($query, " &&");
        }



        // show($query);

        $result = $this->query($query, array_merge($data, $data_not), $limit, $offset);
        
        return $result;    

    }

    public function first($data, $data_not = [])
    {
        $query = "SELECT * FROM $this->table WHERE ";

        foreach (array_keys($data) as $key)
        {
            $query .= "$key=:$key &&";
        }
        foreach (array_keys($data_not) as $key)
        {
            $query .= "$key!=:$key &&";
        }
        $query = trim($query, " &&");

        $result = $this->query($query, array_merge($data, $data_not));
        
        if ($result)
        {
            return $result[0];
        }

        return false;
          
    }


    public function insert($data)
    {
        // try {
            // create PDO connection
            $pdo = new PDO("mysql:host=".DBHOST.";dbname=".DBNAME, DBUSER, DBPASS);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
            // build query
            $query = "INSERT INTO {$this->table} (".implode(", ", array_keys($data)).") 
                        VALUES (:".implode(", :", array_keys($data)).")";
    
            $stmt = $pdo->prepare($query);
    
            // execute and return last inserted ID
            if ($stmt->execute($data)) {
                return $pdo->lastInsertId();
            }
    
            return false;
        // } catch (PDOException $e) {
        //     // handle error (optional: log it)
        //     return false;
        // }
    }



    public function update($id, $data, $id_column = 'id')
    {
        $pdo = $this->connect();
        $bind_data = [];
        $set_clauses = [];
    
        // 1. Build SET clause and bind data (excluding the ID column)
        foreach ($data as $column => $value) {
            // Skip adding the ID column to the SET clause if it was passed in $data
            if ($column === $id_column) continue;
            
            $set_clauses[] = "$column = :$column";
            $bind_data[":$column"] = $value;
        }
        
        // Check if there is anything to update
        if (empty($set_clauses)) {
            return 0; // No columns to update
        }
    
        $query = "UPDATE $this->table SET " . implode(', ', $set_clauses);
        $query .= " WHERE $id_column = :id_value"; // Use a distinct placeholder for the WHERE clause
    
        // 2. Add the ID for the WHERE clause binding
        $bind_data[":id_value"] = $id;
    
        $stmt = $pdo->prepare($query);
        
        // 3. Execute and return affected rows
        if ($stmt->execute($bind_data)) {
            return $stmt->rowCount(); // Correctly returns the number of affected rows
        }
    
        return false;
    }

    public function delete($id, $id_column = 'id')
    {
        $query = "DELETE FROM $this->table WHERE $id_column = '$id'";
        $this->query($query);
    }

    public function search_by_name($name, $column)
    {
        //implement the query for name matching
        $sql = "SELECT * FROM " . $this->table . " WHERE ".$column." LIKE :name";
        $params = [':name' => '%' . $name . '%'];
        return $this->query($sql, $params);
    }

    public function filter_and_search(array $params)
    {
        // Default values
        $offset = $params['offset'] ?? 0;
        $limit = $params['limit'] ?? null;
        $columns = $params['select'] ?? ['*'];
        $where_filters = $params['where'] ?? [];
        $where_not_filters = $params['where_not'] ?? [];
        $range_filters = $params['range'] ?? [];
        $like_filters = $params['like'] ?? [];
        $order_by = $params['order_by'] ?? null;
        $order_dir = $params['order_dir'] ?? 'ASC';
        $unique = $params['unique'] ?? false;
    
        // NEW
        $group_by = $params['group_by'] ?? [];
        $having_filters = $params['having'] ?? [];
    
        // Logic Operator (Default to AND)
        $logic_operator = isset($params['logic']) && strtoupper($params['logic']) === 'OR' ? ' OR ' : ' AND ';
    
        // Construct SELECT
        $select_clause = $unique ? "SELECT DISTINCT " : "SELECT ";
        $sql = $select_clause . implode(', ', $columns) . " FROM {$this->table}";
        $bind_data = [];
        $where_clauses = [];
        $placeholder_counter = 0;
    
        // --- WHERE filters ---
        foreach ($where_filters as $column => $values) {
            if (!is_array($values)) $values = [$values];
            if (empty($values)) continue;
    
            $placeholders = [];
            foreach ($values as $value) {
                ++$placeholder_counter;
                $p = ":w_{$column}_{$placeholder_counter}";
                $placeholders[] = $p;
                $bind_data[$p] = $value;
            }
    
            if (count($placeholders) === 1)
                $where_clauses[] = "$column = {$placeholders[0]}";
            else
                $where_clauses[] = "$column IN (" . implode(', ', $placeholders) . ")";
        }
    
        // --- WHERE NOT ---
        foreach ($where_not_filters as $column => $values) {
            if (!is_array($values)) $values = [$values];
            if (empty($values)) continue;
    
            $placeholders = [];
            foreach ($values as $value) {
                ++$placeholder_counter;
                $p = ":wn_{$column}_{$placeholder_counter}";
                $placeholders[] = $p;
                $bind_data[$p] = $value;
            }
    
            if (count($placeholders) === 1)
                $where_clauses[] = "$column != {$placeholders[0]}";
            else
                $where_clauses[] = "$column NOT IN (" . implode(', ', $placeholders) . ")";
        }
    
        // --- RANGE ---
        foreach ($range_filters as $column => $r) {
            if (!is_array($r) || count($r) != 2) continue;
            $pmin = ":r_{$column}_min";
            $pmax = ":r_{$column}_max";
            $bind_data[$pmin] = $r[0];
            $bind_data[$pmax] = $r[1];
            $where_clauses[] = "($column BETWEEN $pmin AND $pmax)";
        }
    
        // --- LIKE ---
        foreach ($like_filters as $column => $term) {
            if (empty($term)) continue;
            ++$placeholder_counter;
            $p = ":l_{$column}_{$placeholder_counter}";
            $where_clauses[] = "$column LIKE $p";
            $bind_data[$p] = (strpos($term, '%') !== false) ? $term : "%$term%";
        }
    
        // WHERE clause
        if (!empty($where_clauses)) {
            $sql .= " WHERE " . implode($logic_operator, $where_clauses);
        }
    
        // --- GROUP BY (NEW) ---
        if (!empty($group_by)) {
            $sql .= " GROUP BY " . implode(', ', $group_by);
        }
    
        // --- HAVING (NEW) ---
        if (!empty($having_filters)) {
            $having_clauses = [];
            foreach ($having_filters as $expr => $value) {
                ++$placeholder_counter;
                $p = ":h_" . preg_replace('/[^a-zA-Z0-9_]/', '', $expr) . "_$placeholder_counter";
                $having_clauses[] = "$expr = $p";
                $bind_data[$p] = $value;
            }
            $sql .= " HAVING " . implode(' AND ', $having_clauses);
        }
    
        // ORDER BY
        if ($order_by) {
            $sql .= " ORDER BY $order_by " . (strtoupper($order_dir) === 'DESC' ? 'DESC' : 'ASC');
        }

        // show($sql);
    
        return $this->query($sql, $bind_data, $limit, $offset);
    }
    
}