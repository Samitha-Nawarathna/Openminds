<?php

trait Database
{
    private function connect()
    {
        $string = "mysql:host=".DBHOST.";dbname=".DBNAME.";charset=utf8mb4";
        $con = new PDO($string, DBUSER, DBPASS, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_OBJ,
        ]);
        return $con;
    }

    // Generic SELECT query
    public function query($query, $data = [], $limit = null, $offset = 0)
    {
        if ($limit !== null) {
            $query .= " LIMIT $offset, $limit";
        }

        $con = $this->connect();
        $stm = $con->prepare($query);
        $check = $stm->execute($data);

        if ($check) {
            $result = $stm->fetchAll();
            return $result ?: [];
        }

        return false;
    }

    // Get only one row
    public function get_row($query, $data = [])
    {
        $con = $this->connect();
        $stm = $con->prepare($query);
        $check = $stm->execute($data);

        if ($check) {
            $result = $stm->fetchAll();
            return $result[0] ?? false;
        }

        return false;
    }

    // Fetch all (wrapper for readability)
    public function fetch_all($query, $data = [])
    {
        return $this->query($query, $data);
    }

    // Fetch single value (first column of first row)
    public function fetch_value($query, $data = [])
    {
        $con = $this->connect();
        $stm = $con->prepare($query);
        $check = $stm->execute($data);
        if ($check) {
            $value = $stm->fetchColumn();
            return $value !== false ? $value : null;
        }
        return null;
    }

    // Insert helper
    public function insert($query, $data = [])
    {
        $con = $this->connect();
        $stm = $con->prepare($query);
        $check = $stm->execute($data);

        if ($check) {
            return $con->lastInsertId();
        }

        return false;
    }

    // Update or Delete helper
    public function execute($query, $data = [])
    {
        $con = $this->connect();
        $stm = $con->prepare($query);
        return $stm->execute($data);
    }
}
