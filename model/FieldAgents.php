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
    public function getFieldAgentByEmail(string $_email)
    {
        try {
            // Create query
            $query = 'SELECT * FROM fieldagents
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
}