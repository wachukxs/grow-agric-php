<?php
class Farmer {
    // DB stuff
    public $database_connection;
    private $table = 'farmers';

    // Client properties
    /* public $first_name;
    public $last_name;
    public $last_seen;
    public $middle_name;
    public $id;
    public $phone_numbers; */

    /**
     * Constructor taking db as params
     */
    public function __construct($a_database_connection)
    {
        $this->database_connection = $a_database_connection;
    }

    public function generateRandomString()
    {
        $random_string = "0" . rand(1, 64) . rand(0, 94) . rand(0, 9) . rand(0, 49) . rand(0, 29) . chr(rand(65, 90)) . chr(rand(65, 90)) . chr(rand(65, 90)) . chr(rand(65, 90)) . chr(rand(65, 90)) . chr(rand(65, 90)) . chr(rand(65, 90)) . chr(rand(65, 90)) . chr(rand(65, 90)) . chr(rand(65, 90)) . "-" . (new DateTime())->getTimestamp();
        return $random_string;
    }

    public function getAllFarmerPasswordResetRequest($farmeremail)
    {
        $query = 'SELECT farmers.id AS "farmerid", reset_password_requests.`request_id`, reset_password_requests.`time_created`, reset_password_requests.`used` FROM `reset_password_requests` 

        LEFT JOIN farmers
        ON farmers.id = reset_password_requests.farmerid
        
        WHERE farmers.email = :farmeremail
        AND reset_password_requests.used = false
        AND reset_password_requests.time_created >= NOW() - INTERVAL 1 DAY'; // in the last 24 hours

        $stmt = $this->database_connection->prepare($query);

        // Ensure safe data
        $fe = htmlspecialchars(strip_tags($farmeremail));

        // Bind parameters to prepared stmt
        $stmt->bindParam(':farmeremail', $fe);

        $r = $stmt->execute();

        if ($r) {
            return $stmt;
        } else {
            return false;
        }


    }

    public function createNewFarmerPasswordResetRequest($farmerid)
    {
        try {
            $query = 'INSERT INTO reset_password_requests
                SET
                `farmerid` = :farmerid
            ';

            $stmt = $this->database_connection->prepare($query);

            // Ensure safe data
            $fid = htmlspecialchars(strip_tags($farmerid));

            // Bind parameters to prepared stmt
            $stmt->bindParam(':farmerid', $fid);

            $r = $stmt->execute();

            if ($r) {
                return $this->database_connection->lastInsertId();
                // return $this.getSingleOrderByID($this->database_connection->lastInsertId());
            } else {
                return false;
            }
        } catch (\Throwable $err) {
            file_put_contents('php://stderr', print_r('Farmer.php->createNewFarmerPasswordResetRequest error: ' . $err->getMessage() . "\n", TRUE));
            return $err;
        }
    }


    public function createNewFarmerUploadedDocument($mediatype, $name, $farmerid, $url)
    {
        try {
            $query = 'INSERT INTO custom_farmer_uploads
                SET
                `farmerid` = :farmerid,
                `fileuploadname` = :fileuploadname,
                `url` = :url,
                `mediatype` = :mediatype
            ';

            $stmt = $this->database_connection->prepare($query);

            // Ensure safe data
            $n = htmlspecialchars(strip_tags($name));
            $fid = htmlspecialchars(strip_tags($farmerid));
            $mt = htmlspecialchars(strip_tags($mediatype));
            $u = htmlspecialchars(strip_tags($url));

            // Bind parameters to prepared stmt
            $stmt->bindParam(':fileuploadname', $n);
            $stmt->bindParam(':farmerid', $fid);
            $stmt->bindParam(':mediatype', $mt);
            $stmt->bindParam(':url', $u);

            $r = $stmt->execute();

            if ($r) {
                return $this->database_connection->lastInsertId();
                // return $this.getSingleOrderByID($this->database_connection->lastInsertId());
            } else {
                return false;
            }
        } catch (\Throwable $err) {
            file_put_contents('php://stderr', print_r('Farmer.php->getAllModules error: ' . $err->getMessage() . "\n", TRUE));
            return $err;
        }
    }


    public function getFarmerUploadedDocumentByID($id)
    {
        try {
            // Create query
            $query = 'SELECT * FROM custom_farmer_uploads
                WHERE
                id = :_id
            ';

            // Prepare statement
            $query_statement = $this->database_connection->prepare($query);

            $i = htmlspecialchars(strip_tags($id));

            // Execute query statement
            $query_statement->bindParam(':_id', $i);

            // Execute query statement
            $query_statement->execute();

            return $query_statement;
        } catch (\Throwable $err) {
            //throw $err;
            file_put_contents('php://stderr', print_r('Farmer.php->getFarmerUploadedDocumentByID error: ' . $err->getMessage() . "\n", TRUE));
            return false;
        }
    }



     // Get all farmers' personalInfo
     public function getAllFarmersPersonalInfo()
     {
         // Create query
         $query = 'SELECT `id`, `firstname`, `lastname`, `email`, `phonenumber`, `timejoined`, `highesteducationallevel`, `maritalstatus`, `age`, `yearsofexperience` FROM `farmers`';
         
         // Prepare statement
         $query_statement = $this->database_connection->prepare($query);
 
         // Execute query statement
         $query_statement->execute();
 
         return $query_statement;
     }

    // Create new order, an entry
    public function createFarmer($first_name, $last_name, $_email, $phone_number, $_password) {
        
        try {
            $query = 'INSERT INTO ' . $this->table . '
                SET
                firstname = :first_name,
                lastname = :last_name,
                email = :_email,
                password = :_password,
                phonenumber = :phone_number
            ';

            // Prepare the query statement
            $stmt = $this->database_connection->prepare($query);

            // Ensure safe data
            $fn = htmlspecialchars(strip_tags($first_name));
            $ln = htmlspecialchars(strip_tags($last_name));
            $e = htmlspecialchars(strip_tags($_email));
            $ph = htmlspecialchars(strip_tags($phone_number));
            $p = htmlspecialchars(strip_tags($_password));

            // Bind parameters to prepared stmt
            $stmt->bindParam(':first_name', $fn);
            $stmt->bindParam(':last_name', $ln);
            $stmt->bindParam(':_email', $e);
            $stmt->bindParam(':_password', $p);
            $stmt->bindParam(':phone_number', $ph);
            
            $r = $stmt->execute(); // returns true/false
            if ($r) {
                return $this->database_connection->lastInsertId();
                // return $this.getSingleOrderByID($this->database_connection->lastInsertId());
            } else {
                // echo $this->database_connection->errorInfo();
                return $this->database_connection->errorInfo(); // false;
            }
        } catch (\PDOException $err) {
            file_put_contents('php://stderr', print_r('ERROR Trying to sign up farmer: ' . $err->getMessage() . "\n", TRUE));
            return $err->getMessage(); // false;
            // throw $err;
        }

        
    }

    /**
     * @depreciated
     */
    // getSingleFarmerByEmailAndPassword
    public function loginFarmerByEmailAndPassword($_email, $_password)
    {
        try {
            // Create query
            $query = 'SELECT *, "" AS password FROM ' . $this->table . '
                WHERE
                email = :_email
                AND
                password = :_password
            ';

            // Prepare statement
            $query_statement = $this->database_connection->prepare($query);

            $e = htmlspecialchars(strip_tags($_email));
            $p = htmlspecialchars(strip_tags($_password));

            // Execute query statement
            $query_statement->bindParam(':_email', $e);
            $query_statement->bindParam(':_password', $p);

            // Execute query statement
            $query_statement->execute();

            return $query_statement;
        } catch (\Throwable $err) {
            //throw $err;
            file_put_contents('php://stderr', print_r('Farmer.php->createFarm error: ' . $err->getMessage() . "\n", TRUE));
            return false;
        }

    }

    // getSingleFarmerByEmail
    public function getFarmerByEmail($_email)
    {
        try {
            // Create query
            $query = 'SELECT * FROM ' . $this->table . '
                WHERE
                email = :_email
            ';

            // Prepare statement
            $query_statement = $this->database_connection->prepare($query);

            $e = htmlspecialchars(strip_tags($_email));

            file_put_contents('php://stderr', print_r('Farmer.php->getFarmerByEmail fetching ...: ' . "\n", TRUE));
            // file_put_contents('../../logs/api.log', 'Farmer.php->getFarmerByEmail fetching ...: ' . "\n", FILE_APPEND | LOCK_EX);

            // Execute query statement
            $query_statement->bindParam(':_email', $e);

            // Execute query statement
            $query_statement->execute();

            return $query_statement;
        } catch (\Throwable $err) {
            // throw $err;
            // file_put_contents('../../logs/api.log', 'Farmer.php->getFarmerByEmail error: ' . $err->getMessage() . "\n", FILE_APPEND | LOCK_EX);
            file_put_contents('php://stderr', print_r('Farmer.php->getFarmerByEmail error: ' . $err->getMessage() . "\n", TRUE));
            return false;
        }

    }

    // getSingleFarmerByID
    public function getSingleFarmerByID($id)
    {
        // Create query
        // $query = 'SELECT ' .
        // 'farmer.name, ' .
        // 'farmer.price, ' .
        // 'farmer.`image`, ' .
        // 'farmer.price * orders.quantity AS total, ' .
        // 'orders.time as time_of_order, ' .
        // 'orders.address, ' .
        // 'orders.customer_name, ' .
        // 'orders.quantity ' .
        // 'FROM farmers ' .
        // 'RIGHT OUTER JOIN orders ON farmer.id = orders.id_of_food ' .
        // 'WHERE farmers.id = ?';
        $query = 'SELECT * FROM ' . $this->table . ' WHERE id = ?';

        // Prepare statement
        $query_statement = $this->database_connection->prepare($query);

        // Execute query statement
        $query_statement->bindParam(1, $id);

        // Execute query statement
        $query_statement->execute();

        return $query_statement;

    }

    public function updatePasswordResetStatus($requestid)
    {
        try {
            // Create query
            $query = 'UPDATE reset_password_requests 
                SET 
                used = true
                WHERE
                request_id = :requestid
            ';

            // Prepare statement
            $stmt = $this->database_connection->prepare($query);

            // Ensure safe data
            $ri = htmlspecialchars(strip_tags($requestid));

            // Bind parameters to prepared stmt
            $stmt->bindParam(':requestid', $ri);

            // Execute query statement
            if ($stmt->execute()) {
                file_put_contents('php://stderr', print_r('Executed updatePasswordResetStatus update query' . "\n", TRUE));
                return true;
            } else {
                return false;
            }
        } catch (\Throwable $err) {
            // throw $err; $err->getMessage()
            file_put_contents('php://stderr', print_r('Farmer.php->updatePasswordResetStatus error: ' . $err->getMessage() . "\n", TRUE));
        }
    }

    public function updateFarmerPassword($farmerid, $password)
    {
        try {
            // Create query
            $query = 'UPDATE ' . $this->table . ' 
                SET 
                password =:password
                WHERE
                id = :farmerid
            ';

            // Prepare statement
            $stmt = $this->database_connection->prepare($query);

            // Ensure safe data
            $pw = htmlspecialchars(strip_tags($password));
            $fi = htmlspecialchars(strip_tags($farmerid));

            // Bind parameters to prepared stmt
            $stmt->bindParam(':password', $pw);
            $stmt->bindParam(':farmerid', $fi);

            // Execute query statement
            if ($stmt->execute()) {
                file_put_contents('php://stderr', print_r('Executed updateFarmerPassword update query' . "\n", TRUE));
                return true;
            } else {
                return false;
            }
        } catch (\Throwable $err) {
            // throw $err; $err->getMessage()
            file_put_contents('php://stderr', print_r('Farmer.php->updateFarmerPassword error: ' . $err->getMessage() . "\n", TRUE));
        }
    }

    // getSingleFarmerByID
    public function updateFarmerProfile1ByID($firstname, $lastname, $email, $phonenumber, $age, $maritalstatus, $highesteducationallevel, $yearsofexperience, $farmerid)
    {
        try {
            // Create query
            $query = 'UPDATE ' . $this->table . ' 
                SET 
                firstname = :firstname,
                lastname = :lastname,
                email = :email,
                phonenumber = :phonenumber,
                age = :age,
                maritalstatus = :maritalstatus,
                highesteducationallevel = :highesteducationallevel,
                yearsofexperience =:yearsofexperience
                WHERE
                id = :farmerid
            ';

            // Prepare statement
            $stmt = $this->database_connection->prepare($query);

            // Ensure safe data
            $fn = htmlspecialchars(strip_tags($firstname));
            $ln = htmlspecialchars(strip_tags($lastname));
            $e = htmlspecialchars(strip_tags($email));
            $pn = htmlspecialchars(strip_tags($phonenumber));
            $a = htmlspecialchars(strip_tags($age));
            $ms = htmlspecialchars(strip_tags($maritalstatus));
            $hel = htmlspecialchars(strip_tags($highesteducationallevel));
            $yoe = htmlspecialchars(strip_tags($yearsofexperience));

            // Bind parameters to prepared stmt
            $stmt->bindParam(':firstname', $fn);
            $stmt->bindParam(':lastname', $ln);
            $stmt->bindParam(':email', $e);
            $stmt->bindParam(':phonenumber', $pn);
            $stmt->bindParam(':age', $a);
            $stmt->bindParam(':maritalstatus', $ms);
            $stmt->bindParam(':highesteducationallevel', $hel);
            $stmt->bindParam(':yearsofexperience', $yoe);
            $stmt->bindParam(':farmerid', $farmerid);

            // Execute query statement
            if ($stmt->execute()) {
                file_put_contents('php://stderr', print_r('Executed update query' . "\n", TRUE));
                return true;
            } else {
                return false;
            }
        } catch (\Throwable $err) {
            // throw $err; $err->getMessage()
            file_put_contents('php://stderr', print_r('Farmer.php->updateFarmerProfile1ByID error: ' . $err->getMessage() . "\n", TRUE));
        }

    }


    public function addToWaitingList($fullname, $email, $farmeditems, $phonenumber, $countrylocation) {
        try {
            $query = 'INSERT INTO waiting_list' . '
                SET
                fullname = :full_name,
                farmeditems = :farmed_items,
                email = :_email,
                phonenumber = :phonenumber,
                countrylocation = :countrylocation
            ';

            // Prepare the query statement
            $stmt = $this->database_connection->prepare($query);

            file_put_contents('php://stderr', print_r('Seeing farmed items ' . "\n", TRUE));

            file_put_contents('php://stderr', print_r($farmeditems));

            // Ensure safe data
            $fn = htmlspecialchars(strip_tags($fullname));
            $fi = htmlspecialchars(strip_tags($farmeditems)); // implode(',', get_object_vars($farmeditems))
            $e = htmlspecialchars(strip_tags($email));

            $ph = htmlspecialchars(strip_tags($phonenumber)); // implode(',', get_object_vars($farmeditems))
            $cl = htmlspecialchars(strip_tags($countrylocation));

            // Bind parameters to prepared stmt
            $stmt->bindParam(':full_name', $fn);
            $stmt->bindParam(':farmed_items', $fi);
            $stmt->bindParam(':_email', $e);
            $stmt->bindParam(':phonenumber', $ph);
            $stmt->bindParam(':countrylocation', $cl);
            
            $r = $stmt->execute(); // returns true/false
            if ($r) {
                return $this->database_connection->lastInsertId();
                // return $this.getSingleOrderByID($this->database_connection->lastInsertId());
            } else {
                // echo $this->database_connection->errorInfo();
                return $this->database_connection->errorInfo(); // false;
            }
        } catch (\PDOException $err) {
            file_put_contents('php://stderr', print_r('ERROR Trying to add farmer to wait list: ' . $err->getMessage() . "\n", TRUE));
            return $err->getMessage(); // false;
            // throw $th;
        }
    }

    // why are we not returning boolean of fail?
    public function addLearningData($courseid, $currentpage, $readendtime, $readstarttime, $totalpages, $farmerid) {
        try {
            $query = 'INSERT INTO learning_info' . '
                SET
                course_id = :courseid,
                currentpage = :currentpage,
                totalpages = :totalpages,
                start = :_starttime,
                end = :_endtime,
                farmerid = :farmerid
            ';

            // Prepare the query statement
            $stmt = $this->database_connection->prepare($query);

            file_put_contents('php://stderr', print_r('Inserting learning data' . "\n", TRUE));

            // Ensure safe data
            $cid = htmlspecialchars(strip_tags($courseid));
            $cp = htmlspecialchars(strip_tags($currentpage));
            $tp = htmlspecialchars(strip_tags($totalpages));
            // https://www.php.net/manual/en/class.datetime.php
            // https://www.php.net/manual/en/class.datetime.php#99309
            $date1 = new DateTime($readstarttime);
            // $date->setTimestamp($readstarttime);
            $st = htmlspecialchars(strip_tags($date1->format('Y-m-d H:i:s'))); // $date->format('Y-m-d H:i:s')
            // $date->setTimestamp($readendtime);
            $date2 = new DateTime($readendtime);
            $et = htmlspecialchars(strip_tags($date2->format('Y-m-d H:i:s'))); // $date->format('Y-m-d H:i:s')
            $fid = htmlspecialchars(strip_tags($farmerid));

            // Bind parameters to prepared stmt
            $stmt->bindParam(':courseid', $cid);
            $stmt->bindParam(':currentpage', $cp);
            $stmt->bindParam(':totalpages', $tp);
            $stmt->bindParam(':_starttime', $st);
            $stmt->bindParam(':_endtime', $et);
            $stmt->bindParam(':farmerid', $fid);
            
            $r = $stmt->execute(); // returns true/false
            if ($r) {
                return $this->database_connection->lastInsertId();
                // return $this.getSingleOrderByID($this->database_connection->lastInsertId());
            } else {
                // echo $this->database_connection->errorInfo();
                return $this->database_connection->errorInfo(); // false;
            }
        } catch (\PDOException $err) {
            file_put_contents('php://stderr', print_r('ERROR in addLearningData(): ' . $err->getMessage() . "\n", TRUE));
            return $err->getMessage(); // false;
            // throw $th;
        }
    }


    // use https://www.w3schools.com/sql/func_mysql_extract.asp to imporve sql queries, for calculating miutes, hours


    public function getLearningOverviewInfo($farmerid) {
        try {
            // https://www.tutorialspoint.com/how-to-sum-time-in-mysql-by-converting-into-seconds
            // 
            // SELECT TIMEDIFF(`end`, `start`) FROM learning_info
            // SELECT SEC_TO_TIME(SUM(TIME_TO_SEC(TIMEDIFF(`end`, `start`)))) as sum_time FROM learning_info
            // SELECT HOUR(SEC_TO_TIME(SUM(TIME_TO_SEC(TIMEDIFF(`end`, `start`))))) as sum_time FROM learning_info [get hours]

            // SELECT HOUR(SEC_TO_TIME(SUM(TIME_TO_SEC(TIMEDIFF(`end`, `start`))))) as total_learning_hours, SUM(TIME_TO_SEC(TIMEDIFF(`end`, `start`)))/60 as total_learning_minutes FROM learning_info

            $query = 'SELECT HOUR(SEC_TO_TIME(SUM(TIME_TO_SEC(TIMEDIFF(`end`, `start`))))) as total_learning_hours, SUM(TIME_TO_SEC(TIMEDIFF(`end`, `start`)))/60 as total_learning_minutes, (SUM(TIME_TO_SEC(TIMEDIFF(`end`, `start`)))/60)/60 as detailed_total_learning_hours FROM learning_info WHERE farmerid = ?';
            // SELECT COUNT(DISTINCT course_id) AS completed_learning FROM `learning_info` WHERE currentpage = totalpages AND farmerid = 1

            // Prepare statement
            $query_statement = $this->database_connection->prepare($query);

            // Execute query statement
            $query_statement->bindParam(1, $farmerid);

            // Execute query statement
            $query_statement->execute();

        return $query_statement;
        } catch (\Throwable $err) {
            file_put_contents('php://stderr', print_r('ERROR in getLearningOverviewInfo(): ' . $err->getMessage() . "\n", TRUE));
            return $err->getMessage(); // false;
            //throw $th;
        }
    }


    public function getLearningProgressInfo($farmerid) {
        try {
  
            // https://stackoverflow.com/a/1775272/9259701
            // TODO: mix with learning_courses, total values should be same with number of courses we have
            $query = 'SELECT 

            (SELECT COUNT(DISTINCT course_id) FROM `learning_info` WHERE currentpage = totalpages AND farmerid = ?) AS completed_learning, 
                       
            (SELECT COUNT(DISTINCT `course_id`) FROM `learning_info` WHERE currentpage > 0 AND currentpage < totalpages AND farmerid = ? AND id NOT IN (SELECT course_id FROM learning_info WHERE currentpage = totalpages AND farmerid = ?)) AS in_progress_learning,
                        
            (SELECT COUNT(id) FROM learning_courses WHERE id NOT IN (SELECT DISTINCT(`course_id`) FROM `learning_info` WHERE farmerid = ?)) AS not_started_learning';

            // Prepare statement
            $query_statement = $this->database_connection->prepare($query);

            // Execute query statement
            $query_statement->bindParam(1, $farmerid);
            $query_statement->bindParam(2, $farmerid);
            $query_statement->bindParam(3, $farmerid);
            $query_statement->bindParam(4, $farmerid);

            // Execute query statement
            $query_statement->execute();

        return $query_statement;
        } catch (\Throwable $err) {
            file_put_contents('php://stderr', print_r('ERROR in getCompletedLearningInfo(): ' . $err->getMessage() . "\n", TRUE));
            return $err->getMessage(); // false;
            //throw $th;
        }
    }


    public function getLearningChartDataInfo($farmerid) {
        try {
  
            // https://www.w3schools.com/sql/func_mysql_date.asp
            // Select distinct date, and sum it ...
            $query = 'SELECT DATE(start) AS "date", SUM( TIME_TO_SEC(TIMEDIFF(`end`, `start`))/60 ) AS total_learning_minutes FROM `learning_info` WHERE farmerid = ? GROUP BY DATE(start)';

            // Prepare statement
            $query_statement = $this->database_connection->prepare($query);

            // Execute query statement
            $query_statement->bindParam(1, $farmerid);

            // Execute query statement
            $query_statement->execute();

            return $query_statement;
        } catch (\Throwable $err) {
            file_put_contents('php://stderr', print_r('ERROR in getCompletedLearningInfo(): ' . $err->getMessage() . "\n", TRUE));
            return $err->getMessage(); // false;
            //throw $th;
        }
    }


    public function saveCourseForFarmer($courseid, $farmerid)
    {

        try {
            // Create query
            $query = 'INSERT INTO ' . 'saved_learnings' . '
                SET
                course_id = :_courseid,
                farmerid = :_farmerid
            ';

            // Prepare statement
            $query_statement = $this->database_connection->prepare($query);

            // Ensure safe data
            $ci = htmlspecialchars(strip_tags($courseid));
            $fi = htmlspecialchars(strip_tags($farmerid));

            // Bind parameters to prepared stmt
            $query_statement->bindParam(':_courseid', $ci);
            $query_statement->bindParam(':_farmerid', $fi);

            // Execute query statement
            if ($query_statement->execute()) {
                return $this->database_connection->lastInsertId();
                // return $this.getSingleOrderByID($this->database_connection->lastInsertId());
            } else {
                return false;
            }
        } catch (\Throwable $err) {
            //throw $err;
            file_put_contents('php://stderr', print_r('ERROR in saveCourseForFarmer(): ' . $err->getMessage() . "\n", TRUE));
            return false; // $err->getMessage(); 
        }

    }


    public function deleteSavedCourseForFarmer($courseid, $farmerid)
    {

        try {
            // Create query
            $query = 'DELETE FROM saved_learnings 
                WHERE
                course_id = ?
                AND
                farmerid = ?
            ';

            // Prepare statement
            $query_statement = $this->database_connection->prepare($query);

            // Ensure safe data
            $ci = htmlspecialchars(strip_tags($courseid));
            $fi = htmlspecialchars(strip_tags($farmerid));

            // Bind parameters to prepared stmt
            $query_statement->bindParam(1, $ci);
            $query_statement->bindParam(2, $fi);

            // Execute query statement
            if ($query_statement->execute()) {
                return $this->database_connection->lastInsertId();
                // return $this.getSingleOrderByID($this->database_connection->lastInsertId());
            } else {
                return false;
            }
        } catch (\Throwable $err) {
            //throw $err;
            file_put_contents('php://stderr', print_r('ERROR in deleteSavedCourseForFarmer(): ' . $err->getMessage() . "\n", TRUE));
            return false; // $err->getMessage(); 
        }

    }


    public function getSavedCoursesForFarmer($farmerid)
    {

        try {
            // Create query
            // too basic for needs
            // $query = 'SELECT * FROM saved_learnings 
            //     WHERE
            //     farmerid = ?
            // ';

            // doesn't have last_page, etc
            // $query = 'SELECT
            // lc.`mediatype`, lc.`name`, lc.`description`, lc.`id`, lc.`url`, lc.`moduleid`
            
            // FROM learning_courses lc
            
            // RIGHT JOIN 
            // saved_learnings sl
            
            // ON lc.id = sl.course_id
            // WHERE sl.farmerid = ?';

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
            AND
            lc.id IN 
            (
                SELECT DISTINCT saved_learnings.course_id FROM saved_learnings WHERE saved_learnings.farmerid = ?
            )
            
            GROUP BY ll.course_id';

            // Prepare statement
            $query_statement = $this->database_connection->prepare($query);

            // Ensure safe data
            $fi = htmlspecialchars(strip_tags($farmerid));

            // Bind parameters to prepared stmt
            $query_statement->bindParam(1, $fi);
            $query_statement->bindParam(2, $fi);

            $query_statement->execute();

            return $query_statement;

        } catch (\Throwable $err) {
            //throw $err;
            file_put_contents('php://stderr', print_r('ERROR in getSavedCoursesForFarmer(): ' . $err->getMessage() . "\n", TRUE));
            return $err->getMessage();
        }

    }

    public function createdResetPasswordRequest()
    {
        
    }

}