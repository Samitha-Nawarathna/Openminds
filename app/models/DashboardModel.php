<?php

// DashboardModel.php

// Assumes the Model trait (which includes the Database trait) is accessible
class DashboardModel 
{
    use Model;

    public function executeQuery(string $sql, array $params = [], ?int $limit = null, int $offset = 0)
    {
        // Direct call to the trait's query method, assuming it is defined 
        // in the Database trait (used by the Model trait).
        return $this->query($sql, $params, $limit, $offset);
    }
}