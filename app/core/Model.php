<?php

trait Model
{
    use Database;

    public function test()
    {
        $result = $this->query("SELECT * FROM profile;");
        return $result;
    }

    public function where($data, $data_not = [])
    {
        //SELECT * FROM $table WHERE id = :id && id != :id;

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

        show($query);

        $result = $this->query($query, array_merge($data, $data_not));
        
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
        $query = "INSERT INTO $this->table(".implode(", ", array_keys($data)).") VALUES ( :".implode(", :", array_keys($data)).")";
        $result = $this->query($query, $data);

    }

    public function update($id, $data, $id_column = 'id')
    {
        $query = "UPDATE $this->table SET ";

        foreach (array_keys($data) as $column) {
            $query .= "$column = :$column, ";
        }

        $query = trim($query, ", ");
        $query .= " WHERE $id_column = '$id'";

        echo $query;

        $this->query($query, $data);
    }

    public function delete($id, $id_column = 'id')
    {
        $query = "DELETE FROM $this->table WHERE $id_column = '$id'";
        $this->query($query);
    }

}