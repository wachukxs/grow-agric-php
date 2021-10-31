<?php
class Module {
    // DB stuff
    public $database_connection;
    private $table = 'learning_modules';

    /**
     * Constructor taking db as params
     */
    public function __construct($a_database_connection)
    {
        $this->database_connection = $a_database_connection;
    }

    public function getSingleModuleByID($id)
    {
        // Create query
        $query = 'SELECT * FROM ' . $this->table . ' WHERE id = ?';

        // Prepare statement
        $query_statement = $this->database_connection->prepare($query);

        // Bind to query params
        $query_statement->bindParam(1, $id);

        // Execute query statement
        $query_statement->execute();

        return $query_statement;

    }


    public function getAllModules()
    {
        // Create query
        $query = 'SELECT *, (SELECT COUNT(*) FROM learning_courses WHERE `learning_courses`.moduleid = `learning_modules`.id) AS "numberofcourses" FROM `learning_modules`'; // SELECT *, (SELECT COUNT(*) FROM learning_courses WHERE `learning_courses`.moduleid = `learning_modules`.id) AS "numberofcourses" FROM `learning_modules`, `learning_courses`

        // Prepare statement
        $query_statement = $this->database_connection->prepare($query);

        // Execute query statement
        $query_statement->execute();

        return $query_statement;

    }
};
?>