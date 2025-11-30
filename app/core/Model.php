<?php

trait Model
{
    use Database;

    public function test()
    {
        $result = $this->query("SELECT * FROM profile;");
        return $result;
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
            $query .= "WHERE ";

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

}