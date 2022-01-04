<?php


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

    /**
     * fetch all info from this query ???
     * Or maybe just fetch more
     */
    public function getReviewInfo()
    {
        $query = 'SELECT 

                (SELECT COUNT(*) FROM farmers) AS no_farmers,
                                    
                (SELECT COUNT(*) FROM finance_applications) AS no_finance_applications,
                
                (SELECT COUNT(*) FROM farms) AS no_farms,
                
                (SELECT COUNT(*) FROM waiting_list) AS no_waiting_list';

        // Prepare statement
        $query_statement = $this->database_connection->prepare($query);

        // Execute query statement
        $query_statement->execute();

        return $query_statement;
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

    public function getModule($id)
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
            return false;
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
            return false;
        }
    }
}
