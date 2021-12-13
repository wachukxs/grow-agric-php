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


    public function getAdminByEmail($_email){
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
            file_put_contents('php://stderr', print_r('Farm.php->createFarm error: ' . $err->getMessage() . "\n", TRUE));
            return false;
        }
    }
}
