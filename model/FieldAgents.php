<?php

class FieldAgents {
    // DB stuff
    public $database_connection;
    private $table = 'fieldagents';

    /**
     * Constructor taking db as params
     */
    public function __construct($a_database_connection)
    {
        $this->database_connection = $a_database_connection;
    }


    public function getFieldAgentByEmail($_email)
    {
        try {
            // Create query
            $query = 'SELECT * FROM `fieldagents`
                WHERE
                email = :_email
            ';

            // Prepare statement
            $query_statement = $this->database_connection->prepare($query);

            $e = htmlspecialchars(strip_tags($_email));

            // Execute query statement
            $query_statement->bindParam(':_email', $e);

            // Execute query statement
            $query_statement->execute();

            return $query_statement;
        } catch (\Throwable $err) {
            //throw $err;
            file_put_contents('php://stderr', print_r('FieldAgents.php->getFieldAgentByEmail error: ' . $err->getMessage() . "\n", TRUE));
            return false;
        }
    }

    // change query to include fieldagent disignated counties:
    public function getAllFieldAgentFarmVisitRecords($fieldagentid)
    {
        try {
            // Create query
            $query = 'SELECT * FROM fieldagents_farm_visits
                WHERE
                fieldagentid = :_fieldagentid
                ORDER BY `dateofvisit` DESC
            ';

            // Prepare statement
            $query_statement = $this->database_connection->prepare($query);

            $faid = htmlspecialchars(strip_tags($fieldagentid));

            // Execute query statement
            $query_statement->bindParam(':_fieldagentid', $faid);

            // Execute query statement
            $query_statement->execute();

            return $query_statement;
        } catch (\Throwable $err) {
            //throw $err;
            file_put_contents('php://stderr', print_r('FieldAgents.php->getAllFieldAgentFarmVisitRecords error: ' . $err->getMessage() . "\n", TRUE));
            return false;
        }
    }


    public function getFieldAgentAssignedSubCounties($fieldagentid)
    {
        try {
            // Create query
            $query = 'SELECT `assignedsubcounties` FROM fieldagents
                WHERE
                id = :_fieldagentid
            ';

            // Prepare statement
            $query_statement = $this->database_connection->prepare($query);

            $faid = htmlspecialchars(strip_tags($fieldagentid));

            // Execute query statement
            $query_statement->bindParam(':_fieldagentid', $faid);

            // Execute query statement
            $query_statement->execute();

            return $query_statement;
        } catch (\Throwable $err) {
            //throw $err;
            file_put_contents('php://stderr', print_r('FieldAgents.php->getFieldAgentAssignedSubCounties error: ' . $err->getMessage() . "\n", TRUE));
            return false;
        }
    }

    public function getAllFarmVisitsByFieldAgent($fieldagentid)
    {
        try {
            $query = 'SELECT * FROM `fieldagents_farm_visits`

            LEFT JOIN farmers
            
            ON farmers.id = fieldagents_farm_visits.farmerid
            
            WHERE fieldagents_farm_visits.fieldagentid = :_fieldagentid';

            // Prepare statement
            $query_statement = $this->database_connection->prepare($query);

            $faid = htmlspecialchars(strip_tags($fieldagentid));

            // Execute query statement
            $query_statement->bindParam(':_fieldagentid', $faid);

            // Execute query statement
            $query_statement->execute();

            return $query_statement;
        } catch (\Throwable $err) {
            //throw $err;
            file_put_contents('php://stderr', print_r('FieldAgents.php->getAllFarmVisitsByFieldAgent error: ' . $err->getMessage() . "\n", TRUE));
            return false;
        }


    }

    public function getAllFarmVisits()
    {
        $query = 'SELECT * FROM `fieldagents_farm_visits`

        LEFT JOIN farmers
        
        ON farmers.id = fieldagents_farm_visits.farmerid';


    }
}