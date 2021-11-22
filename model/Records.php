<?php

class Records {
        // DB stuff
        public $database_connection;
        private $table = 'inputs_records';

        /**
         * Constructor taking db as params
         */
        public function __construct($a_database_connection)
        {
            $this->database_connection = $a_database_connection;
        }

        // Create new input record, an entry
        public function createInputRecord($farmid, $chicken_supplier, $input_type, $notes, $price, $purchase_date, $quantity, $farmerid) {

            $query = 'INSERT INTO inputs_records 
                SET
                farm_id = :_farmid,
                chicken_supplier = :_chickensupplier,
                farmerid = :_farmerid,
                input_type = :_inputtype,
                notes = :_notes,
                price = :_price,
                purchase_date = :_purchasedate,
                quantity = :_quantity
            ';

            $stmt = $this->database_connection->prepare($query);

            // Ensure safe data
            $frmid = htmlspecialchars(strip_tags($farmid));
            $cs = htmlspecialchars(strip_tags($chicken_supplier));
            $it = htmlspecialchars(strip_tags($input_type));
            $n = htmlspecialchars(strip_tags($notes));
            $p = htmlspecialchars(strip_tags($price));

            $date1 = new DateTime($purchase_date); // Seems this isn't doing timezone conversion and is not accurate
            $pd = htmlspecialchars(strip_tags($date1->format('Y-m-d H:i:s')));
            $q = htmlspecialchars(strip_tags($quantity));
            $fi = htmlspecialchars(strip_tags($farmerid));

            // Bind parameters to prepared stmt
            $stmt->bindParam(':_farmid', $frmid);
            $stmt->bindParam(':_chickensupplier', $cs);
            $stmt->bindParam(':_farmerid', $fi);
            $stmt->bindParam(':_inputtype', $it);
            $stmt->bindParam(':_notes', $n);
            $stmt->bindParam(':_price', $p);
            $stmt->bindParam(':_purchasedate', $pd);
            $stmt->bindParam(':_quantity', $q);

            $r = $stmt->execute();

            if ($r) {
                return $this->database_connection->lastInsertId();
                // return $this.getSingleOrderByID($this->database_connection->lastInsertId());
            } else {
                return false;
            }
        }


        public function getAllChickenInputRecords($farmerid) {

            // SELECT * FROM `inputs_records` WHERE `farmerid` = 1 AND input_type = 'Chicken'
            $query = 'SELECT * FROM inputs_records
                WHERE
                farmerid = :_farmerid
                AND input_type = "Chicken"
            ';

            $stmt = $this->database_connection->prepare($query);

            // Ensure safe data
            $fi = htmlspecialchars(strip_tags($farmerid));

            // Bind parameters to prepared stmt
            $stmt->bindParam(':_farmerid', $fi);

            $r = $stmt->execute();

            return $stmt;
        }

}
