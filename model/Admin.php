<?php

require __DIR__ . "../../vendor/autoload.php"; // https://stackoverflow.com/a/44623787/9259701
file_put_contents('php://stderr', "Hitting upload" . "\n" . "\n", FILE_APPEND | LOCK_EX);

// Headers

$dotenv = Dotenv\Dotenv::createUnsafeImmutable(__DIR__ . "../.."); // https://github.com/vlucas/phpdotenv#putenv-and-getenv
$dotenv->safeLoad();

class Admin
{
    // DB stuff
    public $database_connection;

    public $table = 'admins';

    /**
     * Constructor taking db as params
     */
    public function __construct($a_database_connection)
    {
        $this->database_connection = $a_database_connection;
    }


    public function sendMessage($_message, $_time_sent, $_from, $_to)
    {
        try {
            file_put_contents('php://stderr', print_r("\n\n" . 'actually sending email' . "\n", TRUE));

            $query = 'INSERT INTO `messages`
                SET
                _from = :_from,
                the_message = :_the_message,
                _to = :_to,
                time_sent = :_time_sent
            ';

            $stmt = $this->database_connection->prepare($query);

            // Ensure safe data
            $m = htmlspecialchars(strip_tags($_message));
            $f = htmlspecialchars(strip_tags($_from));
            $t = htmlspecialchars(strip_tags($_to));

            $date1 = new DateTime($_time_sent); // Seems this isn't doing timezone conversion and is not accurate
            $d = htmlspecialchars(strip_tags($date1->format('Y-m-d H:i:s')));


            // Bind parameters to prepared stmt
            $stmt->bindParam(':_from', $f);
            $stmt->bindParam(':_the_message', $m);
            $stmt->bindParam(':_to', $t);
            $stmt->bindParam(':_time_sent', $d);

            $r = $stmt->execute();

            if ($r) {

                file_put_contents('php://stderr', print_r('created THe MessAGE ' . "\n", TRUE));
                $_result = $this->getSingleChatMessage($this->database_connection->lastInsertId());
                $_row = $_result->fetch(PDO::FETCH_ASSOC);

                return $_row;

            } else {
                file_put_contents('php://stderr', print_r('DID created THe MessAGE ' . "\n", TRUE));
                return false;
            }

        } catch (\Throwable $err) {
            //throw $th;
            file_put_contents('php://stderr', print_r('Admin.php->sendMessage error: ' . $err->getMessage() . "\n", TRUE));
            return false;
        }
    }

    public function getAllFarmerMessages($_farmerid)
    {
        try {

            // we should be escaping farmerid
        
            $query = 'SELECT * FROM `messages` WHERE `_from` = "FARMER-' . $_farmerid . '"
            ';

            // Prepare statement
            $query_statement = $this->database_connection->prepare($query);

            // Execute query statement
            $query_statement->execute();

            return $query_statement;
        } catch (\Throwable $err) {
            //throw $th;
            file_put_contents('php://stderr', print_r('Admin.php->getAllMessages error: ' . $err->getMessage() . "\n", TRUE));
            return false;
        }
    }

    public function getAllMessages()
    {
        try {
        
            $query = 'SELECT * FROM `messages`
            ';

            // Prepare statement
            $query_statement = $this->database_connection->prepare($query);

            // Execute query statement
            $query_statement->execute();

            return $query_statement;
        } catch (\Throwable $err) {
            //throw $th;
            file_put_contents('php://stderr', print_r('Admin.php->getAllMessages error: ' . $err->getMessage() . "\n", TRUE));
            return false;
        }
    }

    public function sendMail($_message = NULL)
    {
        try {
            

            file_put_contents('php://stderr', print_r("\n\n" . 'actually sending email' . "\n", TRUE));

            $to      = getenv("TEST_EMAIL");
            $subject = 'the subject';
            // The message
            $message = "Line 1\r\nLine 2\r\nLine 3";

            // In case any of our lines are larger than 70 characters, we should use wordwrap()
            $message = wordwrap($message, 70, "\r\n");
            $headers = 'From: ' . getenv("OUR_EMAIL") . "\r\n" .
                'Reply-To: ' . getenv("OUR_EMAIL") . "\r\n" .
                'X-Mailer: PHP/' . phpversion();

            $mailsent = mail($to, $subject, $message, $headers);

            file_put_contents('php://stderr', print_r('SEnt THe MaiL ' . $mailsent . "\n", TRUE));
        } catch (\Throwable $err) {
            //throw $th;
            file_put_contents('php://stderr', print_r('Admin.php->sendMail error: ' . $err->getMessage() . "\n", TRUE));
            return false;
        }
    }

    /**
     * fetch all info from this query ???
     * Or maybe just fetch more
     */
    public function getReviewInfo()
    {
        try {
        
            $query = 'SELECT 

                (SELECT COUNT(*) FROM farmers) AS no_farmers,
                                    
                (SELECT COUNT(*) FROM finance_applications) AS no_finance_applications,
                
                (SELECT COUNT(*) FROM farms) AS no_farms,
                
                (SELECT COUNT(*) FROM waiting_list) AS no_waiting_list'
            ;

            // Prepare statement
            $query_statement = $this->database_connection->prepare($query);

            // Execute query statement
            $query_statement->execute();

            return $query_statement;
        } catch (\Throwable $err) {
            //throw $th;
            file_put_contents('php://stderr', print_r('Admin.php->getReviewInfo error: ' . $err->getMessage() . "\n", TRUE));
            return false;
        }
    }


    public function getAdminByEmail(string $_email)
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

            // Execute query statement
            $query_statement->bindParam(':_email', $e);

            // Execute query statement
            $query_statement->execute();

            return $query_statement;
        } catch (\Throwable $err) {
            //throw $err;
            file_put_contents('php://stderr', print_r('Admin.php->getAdminByEmail error: ' . $err->getMessage() . "\n", TRUE));
            return false;
        }
    }

    public function getSingleChatMessage($_id)
    {
        try {
            // Create query // we need to specify every column we need, this just selects everything from both table
            $query = '
                SELECT * FROM `messages` WHERE `id` = :id
            ';

            // Prepare statement
            $query_statement = $this->database_connection->prepare($query);

            $i = htmlspecialchars(strip_tags($_id));

            // Execute query statement
            $query_statement->bindParam(':id', $i);

            // Execute query statement
            $query_statement->execute();

            return $query_statement;
        } catch (\Throwable $err) {
            file_put_contents('php://stderr', print_r('Admin.php->getSingleChatMessage error: ' . $err->getMessage() . "\n", TRUE));
            return false;
        }
    }

    public function getAllFinanceApplications()
    {
        try {
            // Create query // we need to specify every column we need, this just selects everything from both table
            $query = 'SELECT *, finance_application_statuses.status 
            FROM finance_applications 
            RIGHT OUTER JOIN 
            finance_application_statuses 
            ON 
            finance_applications.id = finance_application_statuses.finance_application_id
            ';

            // Prepare statement
            $query_statement = $this->database_connection->prepare($query);

            // Execute query statement
            $query_statement->execute();

            return $query_statement;
        } catch (\Throwable $err) {
            file_put_contents('php://stderr', print_r('Admin.php->getAllFinanceApplications error: ' . $err->getMessage() . "\n", TRUE));
            return false;
        }
    }


    public function getAllFarms()
    {
        try {
            // Create query
            $query = 'SELECT * FROM `farms`
            ';

            // Prepare statement
            $query_statement = $this->database_connection->prepare($query);

            // Execute query statement
            $query_statement->execute();

            return $query_statement;
        } catch (\Throwable $err) {
            file_put_contents('php://stderr', print_r('Admin.php->getAllFinanceApplications error: ' . $err->getMessage() . "\n", TRUE));
            return false;
        }
    }


    public function getAllFarmers()
    {
        try {
            // Create query
            $query = 'SELECT * FROM `farmers`
            ';

            // Prepare statement
            $query_statement = $this->database_connection->prepare($query);

            // Execute query statement
            $query_statement->execute();

            return $query_statement;
        } catch (\Throwable $err) {
            file_put_contents('php://stderr', print_r('Admin.php->getAllFarmers error: ' . $err->getMessage() . "\n", TRUE));
            return false;
        }
    }

    public function getModuleByID($id)
    {
        try {
            // Create query
            $query = 'SELECT * FROM learning_modules
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
            file_put_contents('php://stderr', print_r('Admin.php->getModule error: ' . $err->getMessage() . "\n", TRUE));
            return false;
        }
    }

    public function getCourseByID($id)
    {
        try {
            // Create query
            $query = 'SELECT * FROM learning_courses
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
            file_put_contents('php://stderr', print_r('Admin.php->getCourseByID error: ' . $err->getMessage() . "\n", TRUE));
            return false;
        }
    }

    public function addNewModule($name, $description,)
    {
        $query = 'INSERT INTO learning_modules
            SET
            name = :name,
            description = :description
        ';

        $stmt = $this->database_connection->prepare($query);

        // Ensure safe data
        $n = htmlspecialchars(strip_tags($name));
        $d = htmlspecialchars(strip_tags($description));

        // Bind parameters to prepared stmt
        $stmt->bindParam(':name', $n);
        $stmt->bindParam(':description', $d);

        $r = $stmt->execute();

        if ($r) {
            return $this->database_connection->lastInsertId();
            // return $this.getSingleOrderByID($this->database_connection->lastInsertId());
        } else {
            return false;
        }
    }

    public function addNewCourse($mediatype, $name, $description, $url, $moduleid)
    {
        try {
            $query = 'INSERT INTO learning_courses
                SET
                name = :name,
                mediatype = :mediatype,
                description = :description,
                url = :url,
                moduleid = :moduleid
            ';

            $stmt = $this->database_connection->prepare($query);

            // Ensure safe data
            $n = htmlspecialchars(strip_tags($name));
            $desc = htmlspecialchars(strip_tags($description));
            $mt = htmlspecialchars(strip_tags($mediatype));
            $u = htmlspecialchars(strip_tags($url));
            $mid = htmlspecialchars(strip_tags($moduleid));

            // Bind parameters to prepared stmt
            $stmt->bindParam(':name', $n);
            $stmt->bindParam(':description', $desc);
            $stmt->bindParam(':mediatype', $mt);
            $stmt->bindParam(':url', $u);
            $stmt->bindParam(':moduleid', $mid);

            $r = $stmt->execute();

            if ($r) {
                return $this->database_connection->lastInsertId();
                // return $this.getSingleOrderByID($this->database_connection->lastInsertId());
            } else {
                return false;
            }
        } catch (\Throwable $err) {
            file_put_contents('php://stderr', print_r('Admin.php->getAllModules error: ' . $err->getMessage() . "\n", TRUE));
            return $err;
        }
    }

    public function getAllModules()
    {
        try {
            // Create query
            $query = 'SELECT * FROM `learning_modules`
            ';

            // Prepare statement
            $query_statement = $this->database_connection->prepare($query);

            // Execute query statement
            $query_statement->execute();

            return $query_statement;
        } catch (\Throwable $err) {
            file_put_contents('php://stderr', print_r('Admin.php->getAllModules error: ' . $err->getMessage() . "\n", TRUE));
            return $err;
        }
    }

    public function getAllCourses()
    {
        try {
            // Create query
            $query = 'SELECT * FROM `learning_courses`
            ';

            // Prepare statement
            $query_statement = $this->database_connection->prepare($query);

            // Execute query statement
            $query_statement->execute();

            return $query_statement;
        } catch (\Throwable $err) {
            file_put_contents('php://stderr', print_r('Admin.php->getAllCourses error: ' . $err->getMessage() . "\n", TRUE));
            return $err;
        }
    }
}
