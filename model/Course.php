<?php
class Course {
    // DB stuff
    public $database_connection;
    private $table = 'learning_courses';

    /**
     * Constructor taking db as params
     */
    public function __construct($a_database_connection)
    {
        $this->database_connection = $a_database_connection;
    }

    public function getAllCoursesInModuleByModuleID($id)
    {
        // Create query
        $query = 'SELECT * FROM ' . $this->table . ' WHERE moduleid = ?';

        // Prepare statement
        $query_statement = $this->database_connection->prepare($query);

        // Execute query statement
        $query_statement->bindParam(1, $id);

        // Execute query statement
        $query_statement->execute();

        return $query_statement;

    }


    public function getSingleCourseByCourseID($id)
    {
        // Create query
        $query = 'SELECT * FROM ' . $this->table . ' WHERE id = ?';

        // Prepare statement
        $query_statement = $this->database_connection->prepare($query);

        // Execute query statement
        $query_statement->bindParam(1, $id);

        // Execute query statement
        $query_statement->execute();

        return $query_statement;

    }
};
?>