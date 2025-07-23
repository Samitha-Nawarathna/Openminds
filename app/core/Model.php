<?php

class Model
{
    use Database;
    protected $table = 'profile';

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
        //INSERT INTO $table(keys) VALUES (), ();
    }

    public function update($id, $data, $id_column = 'id')
    {
        
    }

    public function delete($id, $id_column)
    {
        
    }

}