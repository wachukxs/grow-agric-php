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

    // Get all farmer
    public function getAllInventory()
    {
        // Create query
        $query = 'SELECT * FROM ' . $this->table;
        
        // Prepare statement
        $query_statement = $this->database_connection->prepare($query);

        // Execute query statement
        $query_statement->execute();

        return $query_statement;
    }

    public function getSingleInventoryByID($id)
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


    // TODO
    public function addToInventory($name, $available, $category, $price, $description)
    {

        // Create query
        $query = 'INSERT INTO ' . $this->table . '
            SET
            name = :name,
            available = :available,
            category = :category,
            price = :price,
            description = :description
        ';

        // Prepare statement
        $stmt = $this->database_connection->prepare($query);

        // Ensure safe data
        $n = htmlspecialchars(strip_tags($name));
        $a = htmlspecialchars(strip_tags($available));
        $c = htmlspecialchars(strip_tags($category));
        $p = htmlspecialchars(strip_tags($price));
        $d = htmlspecialchars(strip_tags($description));

        // Bind parameters to prepared stmt
        $stmt->bindParam(':name', $n);
        $stmt->bindParam(':available', $a);
        $stmt->bindParam(':category', $c);
        $stmt->bindParam(':price', $p);
        $stmt->bindParam(':description', $d);

        // Execute query statement
        if ($stmt->execute()) {
            return true;
        } else {
            return false;
        }

    }

    // Create new order, an entry
    public function createFarmer($first_name, $last_name, $_email, $phone_number, $_password) {
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

        

        try {
            $r = $stmt->execute(); // returns true/false
            if ($r) {
                return $this->database_connection->lastInsertId();
                // return $this.getSingleOrderByID($this->database_connection->lastInsertId());
            } else {
                // echo $this->database_connection->errorInfo();
                return $this->database_connection->errorInfo(); // false;
            }
        } catch (\PDOException $err) {
            return $err->getMessage();
            // throw $th;
        }

        
    }

    // getSingleFarmerByID
    public function loginFarmerByEmailAndPassword($_email, $_password)
    {
        // Create query
        $query = 'SELECT * FROM ' . $this->table . '
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

    // getSingleFarmerByID
    public function updateFarmerProfile1ByID($firstname, $lastname, $email, $phonenumber, $age, $maritalstatus, $highesteducationallevel, $farmerid)
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
                highesteducationallevel = :highesteducationallevel
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

            // Bind parameters to prepared stmt
            $stmt->bindParam(':firstname', $fn);
            $stmt->bindParam(':lastname', $ln);
            $stmt->bindParam(':email', $e);
            $stmt->bindParam(':phonenumber', $pn);
            $stmt->bindParam(':age', $a);
            $stmt->bindParam(':maritalstatus', $ms);
            $stmt->bindParam(':highesteducationallevel', $hel);
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

}