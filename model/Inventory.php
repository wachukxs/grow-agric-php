<?php
class Inventory {
    // DB stuff
    public $database_connection;
    private $table = 'inventory';

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

    // Get all inventory
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

}