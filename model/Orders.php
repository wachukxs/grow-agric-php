<?php
class Orders {
    // DB stuff
    public $database_connection;
    private $table = 'orders';

    // Client properties
    /*
    public $first_name;
    public $last_name;
    public $last_seen;
    public $middle_name;
    public $id;
    public $phone_numbers; 
    */

    /**
     * Constructor taking db as params
     */
    public function __construct($a_database_connection)
    {
        $this->database_connection = $a_database_connection;
    }

    // Create new order, an entry
    public function createOrder($cus_name, $qty, $addr, $food_id) {
        $query = 'INSERT INTO ' . $this->table . '
            SET
            customer_name = :cus_name,
            quantity = :qty,
            address = :addr,
            id_of_food = :food_id
        ';

        $stmt = $this->database_connection->prepare($query);

        // Ensure safe data
        $cn = htmlspecialchars(strip_tags($cus_name));
        $q = htmlspecialchars(strip_tags($qty));
        $a = htmlspecialchars(strip_tags($addr));
        $fi = htmlspecialchars(strip_tags($food_id));

        // Bind parameters to prepared stmt
        $stmt->bindParam(':cus_name', $cn);
        $stmt->bindParam(':qty', $q);
        $stmt->bindParam(':addr', $a);
        $stmt->bindParam(':food_id', $fi);

        $r = $stmt->execute();

        if ($r) {
            return $this->database_connection->lastInsertId();
            // return $this.getSingleOrderByID($this->database_connection->lastInsertId());
        } else {
            return false;
        }
    }

    public function getSingleOrderByID($id)
    {
        // Create query
        $query = 'SELECT ' .
        'inventory.name, ' .
        'inventory.price, ' .
        'inventory.`image`, ' .
        'inventory.price * orders.quantity AS total, ' .
        'orders.time as time_of_order, ' .
        'orders.address, ' .
        'orders.customer_name, ' .
        'orders.quantity ' .
        'FROM inventory ' .
        'RIGHT OUTER JOIN orders ON inventory.id = orders.id_of_food ' .
        'WHERE orders.id = ?';
        // $query = 'SELECT * FROM ' . $this->table . ' WHERE id = ?';

        // Prepare statement
        $query_statement = $this->database_connection->prepare($query);

        // Execute query statement
        $query_statement->bindParam(1, $id);

        // Execute query statement
        $query_statement->execute();

        return $query_statement;

    }

    // Verify new client, an entry
    public function verifyOrder($id) {
        $query = 'SELECT * FROM ' . $this->table . '
            WHERE
            id = ?
        ';

        $stmt = $this->database_connection->prepare($query);

        // Ensure safe data
        $i = htmlspecialchars(strip_tags($id));

        // Bind parameters to prepared stmt
        $stmt->bindParam(1, $i);

        if ($stmt->execute() && $stmt->rowCount() == 1) {

            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            extract($row);

            // Create array
            return array(
                'id' => $id,
                'customer_name' => $customer_name,
                'quantity' => $quantity,
                'address' => $address,
                'id_of_food' => $id_of_food,
                'time' => $time_of_order
            ); // SHOULD RETURN AN OBJECT OF THE agent data
        } else {
            return array();
        }
    }

    // Get all orders
    public function getAllOrders()
    {
        // Create query
        // $query = 'SELECT * FROM ' . $this->table;
        $query = 'SELECT ' .
        'inventory.name, ' .
        'inventory.price, ' .
        'inventory.`image`, ' .
        'inventory.price * orders.quantity AS total, ' .
        'orders.time as time_of_order, ' .
        'orders.address, ' .
        'orders.customer_name, ' .
        'orders.quantity ' .
        'FROM inventory ' .
        'RIGHT OUTER JOIN orders ON inventory.id = orders.id_of_food';
        
        // Prepare statement
        $query_statement = $this->database_connection->prepare($query);

        // Execute query statement
        $query_statement->execute();

        return $query_statement;
    }

}