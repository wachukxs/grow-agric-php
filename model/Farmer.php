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

        $r = $stmt->execute();

        if ($r) {
            return $this->database_connection->lastInsertId();
            // return $this.getSingleOrderByID($this->database_connection->lastInsertId());
        } else {
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

}