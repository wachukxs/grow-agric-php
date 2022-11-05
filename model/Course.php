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
        try {
            // Create query
            $query = 'SELECT * FROM ' . $this->table . ' WHERE moduleid = ? '; // AND farmerid

            // Prepare statement
            $query_statement = $this->database_connection->prepare($query);

            // Execute query statement
            $query_statement->bindParam(1, $id);

            // Execute query statement
            $query_statement->execute();

            return $query_statement;
        } catch (\Throwable $err) {
            file_put_contents('php://stderr', print_r('Course.php->getAllCoursesInModuleForFarmerByModuleID error: ' . $err->getMessage() . "\n", TRUE));
            return false;
        }
        
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

    public function getAllFarmerSavedCourses($farmerid, $moduleid)
    {
        try {
            $query = 'SELECT 
                learning_courses.`mediatype`, learning_courses.`name`, learning_courses.`description`, 
                learning_courses.`id`, learning_courses.`url`, learning_courses.`moduleid` 
                FROM `learning_courses` 
                INNER JOIN saved_learnings 
                ON learning_courses.id = saved_learnings.course_id 
                WHERE learning_courses.moduleid = :_moduleid
                AND saved_learnings.farmerid = :_farmerid
            ';


            // Prepare statement
            $query_statement = $this->database_connection->prepare($query);

            $query_statement->bindParam(':_moduleid', $moduleid);
            $query_statement->bindParam(':_farmerid', $farmerid);

            // Execute query statement
            $query_statement->execute();

            return $query_statement;

        } catch (\Throwable $err) {
            file_put_contents('php://stderr', print_r('Course.php->getAllFarmerSavedCourses error: ' . $err->getMessage() . "\n", TRUE));
            return false;
        }
    }

    /**
     * 
     * if we want to make things exclusive
     * AND
        id NOT IN
        (
            SELECT DISTINCT saved_learnings.course_id FROM saved_learnings WHERE saved_learnings.farmerid = 1
       	)
     */
    public function getAllFarmerNotStartedCoursesInModule($farmerid, $moduleid)
    {
        try {
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
        } catch (\Throwable $err) {
            file_put_contents('php://stderr', print_r('Course.php->getAllFarmerNotStartedCoursesInModule error: ' . $err->getMessage() . "\n", TRUE));
            return false;
        }
        

    }

    public function getAllFarmerCompletedCoursesInModule($farmerid, $moduleid)
    {
        try {
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
        } catch (\Throwable $err) {
            file_put_contents('php://stderr', print_r('Course.php->getAllFarmerCompletedCoursesInModule error: ' . $err->getMessage() . "\n", TRUE));
            return false;
        }
        
    }

    public function getAllFarmerIncompletedCoursesInModule($farmerid, $moduleid)
    {
        try {
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
        } catch (\Throwable $err) {
            file_put_contents('php://stderr', print_r('Course.php->getAllFarmerIncompletedCoursesInModule error: ' . $err->getMessage() . "\n", TRUE));
            return false;
        }
    }

    public function getAllFarmerNotStartedCourses($farmerid)
    {
        try {
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
        } catch (\Throwable $err) {
            file_put_contents('php://stderr', print_r('Course.php->getAllFarmerNotStartedCourses error: ' . $err->getMessage() . "\n", TRUE));
            return false;
        }


    }

    public function getAllFarmerCompletedCourses($farmerid)
    {
        try {
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
        } catch (\Throwable $err) {
            file_put_contents('php://stderr', print_r('Course.php->getAllFarmerCompletedCourses error: ' . $err->getMessage() . "\n", TRUE));
            return false;
        }
    }

    public function getAllFarmerIncompletedCourses($farmerid)
    {
        try {
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
                MAX(ll.`totalpages`) > MAX(ll.`currentpage`)
            ';

            // Prepare statement
            $query_statement = $this->database_connection->prepare($query);

            // Execute query statement
            $query_statement->bindParam(1, $farmerid);

            // Execute query statement
            $query_statement->execute();

            return $query_statement;
        } catch (\Throwable $err) {
            file_put_contents('php://stderr', print_r('Course.php->getAllFarmerIncompletedCourses error: ' . $err->getMessage() . "\n", TRUE));
            return false;
        }
    }

    public function getAllCompletedAndIncompletedCourses()
    {
        try {
            $query = 'SELECT ANY_VALUE(lc.`mediatype`) AS mediatype, 
            ANY_VALUE(lc.`name`) AS name, 
            ANY_VALUE(lc.`description`) AS description, 
            ANY_VALUE(lc.`id`) AS courseid, 
            ANY_VALUE(lc.`url`) as url, 
            ANY_VALUE(lc.`moduleid`) AS moduleid, 
            IF(MAX(ll.`totalpages`) = MAX(ll.`currentpage`), true, false) AS completed, 
            MAX(ll.end) AS last_time, ll.farmerid AS farmerid, 
            ( SELECT currentpage FROM learning_info WHERE learning_info.end = MAX(ll.end) LIMIT 1 ) AS last_page 
            FROM learning_courses lc 
            LEFT JOIN learning_info ll 
            ON lc.id = ll.course_id 
            GROUP BY ll.course_id, ll.farmerid';

            // Prepare statement
            $query_statement = $this->database_connection->prepare($query);

            // Execute query statement
            $query_statement->execute();

            return $query_statement;
        } catch (\Throwable $err) {
            file_put_contents('php://stderr', print_r('Course.php->getAllCompletedAndIncompletedCourses error: ' . $err->getMessage() . "\n", TRUE));
            return false;
        }
    }

    /**
     * TODO: complete this query
     */
    public function getCoursesCompletionRateForFarmers()
    {
        $query = "SELECT ANY_VALUE(lc.`mediatype`) AS mediatype, 
        ANY_VALUE(lc.`name`) AS name, 
        ANY_VALUE(lc.`description`) AS description, 
        ANY_VALUE(lc.`id`) AS courseid, 
        ANY_VALUE(lc.`url`) as url, 
        ANY_VALUE(lc.`moduleid`) AS moduleid, 
        IF(MAX(ll.`totalpages`) = MAX(ll.`currentpage`), true, false) AS completed,
        
        CASE WHEN MAX(ll.`totalpages`) = MAX(ll.`currentpage`) 
        THEN 'COMPLETED'
        WHEN MAX(ll.`currentpage`) > 0 AND MAX(ll.`totalpages`) > MAX(ll.`currentpage`) THEN 'IN PROGRESS'
        
        ELSE 'NOT STARTED' END AS completion_rate,
        
        MAX(ll.end) AS last_time, ll.farmerid AS farmerid, 
        ( SELECT currentpage FROM learning_info WHERE learning_info.end = MAX(ll.end) LIMIT 1 ) AS last_page 
        FROM learning_courses lc 
        LEFT JOIN learning_info ll 
        ON lc.id = ll.course_id 
        GROUP BY ll.course_id, ll.farmerid"
        ;
    }

    public function getAllCourses()
    {
        try {
            // Create query
            $query = 'SELECT * FROM `learning_courses`';

            // Prepare statement
            $query_statement = $this->database_connection->prepare($query);

            // Execute query statement
            $query_statement->execute();

            return $query_statement;
        } catch (\Throwable $err) {
            file_put_contents('php://stderr', print_r('Course.php->getAllCourses error: ' . $err->getMessage() . "\n", TRUE));
            return false;
        }
    }


    public function getSingleCourseByCourseID($courseid, $farmerid)
    {
        try {
            // Create query, LIMIT 1 is like a hot fix?
            $query = 'SELECT lc.`mediatype`,lc.`name`,lc.`description`,lc.`id`,lc.`url`,lc.`moduleid`, MAX(li.end) AS last_time,
            (
                        SELECT currentpage FROM learning_info WHERE learning_info.end = MAX(li.end) LIMIT 1
                    ) AS last_page,
                    (
                        SELECT totalpages FROM learning_info WHERE learning_info.end = MAX(li.end) LIMIT 1
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
        } catch (\Throwable $err) {
            file_put_contents('php://stderr', print_r('Course.php->getSingleCourseByCourseID error: ' . $err->getMessage() . "\n", TRUE));
            return false;
        }
    }
};
?>