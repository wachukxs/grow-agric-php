<?php
class Course {
    // DB stuff
    public $database_connection;
    private $table = 'learning_courses';

    /**
     * name: 'Financial Literacy',
        description: 'is something we do on farm lands to make them better, so we can yield more farm produce etc.',
        mediatype: 'pdf',
        progress: '', // or should it be number?
        url: 'https://chuks.name.ng/grow-agric/assets/pdfs/Understanding+Finance+v0.3.pdf',
        progresstype: '', // like seconds [for videos] or pages [for PDFs, PPTX?]
        timespent: [{
            start: '',
            end: 343535 // can be timestamp in js
        }],
        total: 5
     */

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
};
?>