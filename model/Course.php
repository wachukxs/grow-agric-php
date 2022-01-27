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

    public function getAllCoursesInModuleForFarmerByModuleID($id)
    {
        // Create query
        $query = 'SELECT * FROM ' . $this->table . ' WHERE moduleid = ? '; // AND farmerid

        // Prepare statement
        $query_statement = $this->database_connection->prepare($query);

        // Execute query statement
        $query_statement->bindParam(1, $id);

        // Execute query statement
        $query_statement->execute();

        return $query_statement;

    }

    public function getAllFarmerLastInProgressLearning($farmerid)
    {
        /** query to fetch all progress
         * 
         */

         $query = 'SELECT
         lc.`mediatype`, lc.`name`, lc.`description`, lc.`id`, lc.`url`, lc.`moduleid`, IF(MAX(ll.`totalpages`) = MAX(ll.`currentpage`), true, false) AS completed, MAX(ll.end) AS last_time,
         (
             SELECT currentpage FROM learning_info WHERE learning_info.end = MAX(ll.end)
         ) AS last_page
         
         
         FROM learning_courses lc
         
         LEFT JOIN 
         learning_info ll
         
         ON lc.id = ll.course_id
         WHERE ll.farmerid = ?
         GROUP BY ll.course_id';


        // Prepare statement
        $query_statement = $this->database_connection->prepare($query);

        // Execute query statement
        $query_statement->bindParam(1, $farmerid);

        // Execute query statement
        $query_statement->execute();

        return $query_statement;

    }

    public function getAllFarmerNotStartedCoursesInModule($farmerid, $moduleid)
    {
        $query = 'SELECT * 

        FROM learning_courses
        WHERE
        moduleid = :moduleid
        AND
        id NOT IN 
        (
            SELECT DISTINCT learning_info.course_id FROM learning_info WHERE farmerid = :farmerid
        )';

         // Prepare statement
        $query_statement = $this->database_connection->prepare($query);

        // Execute query statement
        $query_statement->bindParam(':farmerid', $farmerid);
        $query_statement->bindParam(':moduleid', $moduleid);

        // Execute query statement
        $query_statement->execute();

        return $query_statement;

    }

    public function getAllFarmerCompletedCoursesInModule($farmerid, $moduleid)
    {
        $query = 'SELECT
        lc.`mediatype`, lc.`name`, lc.`description`, lc.`id`, lc.`url`, lc.`moduleid`, MAX(ll.end) AS last_time,
        (
            SELECT currentpage FROM learning_info WHERE learning_info.end = MAX(ll.end)
        ) AS last_page
        
        FROM learning_courses lc
        
        LEFT JOIN 

        learning_info ll
        
        ON lc.id = ll.course_id
        
        WHERE ll.farmerid = :farmerid

        AND lc.moduleid = :moduleid
        
        GROUP BY ll.course_id
        
        HAVING 
        MAX(ll.`totalpages`) = MAX(ll.`currentpage`)';

        // Prepare statement
        $query_statement = $this->database_connection->prepare($query);

        // Execute query statement
        $query_statement->bindParam(':farmerid', $farmerid);
        $query_statement->bindParam(':moduleid', $moduleid);

        // Execute query statement
        $query_statement->execute();

        return $query_statement;
    }

    public function getAllFarmerIncompletedCoursesInModule($farmerid, $moduleid)
    {
        $query = 'SELECT
        lc.`mediatype`, lc.`name`, lc.`description`, lc.`id`, lc.`url`, lc.`moduleid`, MAX(ll.end) AS last_time,
        (
        SELECT currentpage FROM learning_info WHERE learning_info.end = MAX(ll.end)
        ) AS last_page,
        (
        SELECT totalpages FROM learning_info WHERE learning_info.end = MAX(ll.end)
        ) AS total_pages
        
        FROM learning_courses lc
        
        
        LEFT JOIN 
        learning_info ll
        
        ON lc.id = ll.course_id
        
        WHERE ll.farmerid = :farmerid

        AND lc.moduleid = :moduleid
        
        GROUP BY ll.course_id
        
        HAVING 
        MAX(ll.`totalpages`) > MAX(ll.`currentpage`)';

        // Prepare statement
        $query_statement = $this->database_connection->prepare($query);

        // Execute query statement
        $query_statement->bindParam(':farmerid', $farmerid);
        $query_statement->bindParam(':moduleid', $moduleid);

        // Execute query statement
        $query_statement->execute();

        return $query_statement;
    }

    public function getAllFarmerNotStartedCourses($farmerid)
    {
        $query = 'SELECT * 

        FROM learning_courses
        
         WHERE id NOT IN 
         (
            SELECT DISTINCT learning_info.course_id FROM learning_info WHERE farmerid = ?
         )';

         // Prepare statement
        $query_statement = $this->database_connection->prepare($query);

        // Execute query statement
        $query_statement->bindParam(1, $farmerid);

        // Execute query statement
        $query_statement->execute();

        return $query_statement;


    }

    public function getAllFarmerCompletedCourses($farmerid)
    {
        $query = 'SELECT
        lc.`mediatype`, lc.`name`, lc.`description`, lc.`id`, lc.`url`, lc.`moduleid`, MAX(ll.end) AS last_time,
        (
        SELECT currentpage FROM learning_info WHERE learning_info.end = MAX(ll.end)
        ) AS last_page
        
        FROM learning_courses lc
        
        
        LEFT JOIN 
        learning_info ll
        
        ON lc.id = ll.course_id
        
        WHERE ll.farmerid = ?
        
        GROUP BY ll.course_id
        
        HAVING 
        MAX(ll.`totalpages`) = MAX(ll.`currentpage`)';

        // Prepare statement
        $query_statement = $this->database_connection->prepare($query);

        // Execute query statement
        $query_statement->bindParam(1, $farmerid);

        // Execute query statement
        $query_statement->execute();

        return $query_statement;
    }

    public function getAllFarmerIncompletedCourses($farmerid)
    {
        $query = 'SELECT
        lc.`mediatype`, lc.`name`, lc.`description`, lc.`id`, lc.`url`, lc.`moduleid`, MAX(ll.end) AS last_time,
        (
        SELECT currentpage FROM learning_info WHERE learning_info.end = MAX(ll.end)
        ) AS last_page
        
        FROM learning_courses lc
        
        
        LEFT JOIN 
        learning_info ll
        
        ON lc.id = ll.course_id
        
        WHERE ll.farmerid = ?
        
        GROUP BY ll.course_id
        
        HAVING 
        MAX(ll.`totalpages`) > MAX(ll.`currentpage`)';

        // Prepare statement
        $query_statement = $this->database_connection->prepare($query);

        // Execute query statement
        $query_statement->bindParam(1, $farmerid);

        // Execute query statement
        $query_statement->execute();

        return $query_statement;
    }

    public function getAllCompletedAndIncompletedCourses()
    {
        $query = 'SELECT 
        ANY_VALUE(lc.`mediatype`) AS mediatype, ANY_VALUE(lc.`name`) AS name, ANY_VALUE(lc.`description`) AS description, 
        ANY_VALUE(lc.`id`) AS courseid, ANY_VALUE(lc.`url`) as url, ANY_VALUE(lc.`moduleid`) AS moduleid, 
        IF(MAX(ll.`totalpages`) = MAX(ll.`currentpage`), true, false) AS completed, MAX(ll.end) AS last_time, 
        ll.farmerid AS farmerid, ( SELECT currentpage FROM learning_info WHERE learning_info.end = MAX(ll.end) ) AS last_page 
        FROM learning_courses lc 
        LEFT JOIN learning_info ll 
        ON lc.id = ll.course_id 
        GROUP BY ll.course_id, ll.farmerid';

        // Prepare statement
        $query_statement = $this->database_connection->prepare($query);

        // Execute query statement
        $query_statement->execute();

        return $query_statement;
    }

    public function getAllCourses()
    {
        // Create query
        $query = 'SELECT * FROM `learning_courses`';

        // Prepare statement
        $query_statement = $this->database_connection->prepare($query);

        // Execute query statement
        $query_statement->execute();

        return $query_statement;
    }


    public function getSingleCourseByCourseID($courseid, $farmerid)
    {
        // Create query
        $query = 'SELECT lc.`mediatype`,lc.`name`,lc.`description`,lc.`id`,lc.`url`,lc.`moduleid`, MAX(li.end) AS last_time,
        (
                     SELECT currentpage FROM learning_info WHERE learning_info.end = MAX(li.end)
                 ) AS last_page,
                 (
                     SELECT totalpages FROM learning_info WHERE learning_info.end = MAX(li.end)
                 ) AS total_pages
        
        FROM learning_courses lc
                 
                 LEFT JOIN 
                 learning_info li
        
                 ON lc.id = li.course_id
                 WHERE li.farmerid = :farmerid
                 AND 
                 lc.id = :courseid';

        // Prepare statement
        $query_statement = $this->database_connection->prepare($query);

        // Execute query statement
        $query_statement->bindParam(':farmerid', $farmerid);
        $query_statement->bindParam(':courseid', $courseid);

        // Execute query statement
        $query_statement->execute();

        return $query_statement;

    }
};
?>