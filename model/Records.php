<?php

class Records {
        // DB stuff
        public $database_connection;
        private $table = 'inputs_records_chicken';

        /**
         * Constructor taking db as params
         */
        public function __construct($a_database_connection)
        {
            $this->database_connection = $a_database_connection;
        }

        // Create new chicken input record, an entry
        public function createChickenInputRecord($farmid, $chicken_supplier, $input_type, $notes, $price, $purchase_date, $quantity, $farmerid) {

            $query = 'INSERT INTO inputs_records_chicken 
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

            // SELECT * FROM `inputs_records_chicken` WHERE `farmerid` = 1 AND input_type = 'Chicken'
            $query = 'SELECT * FROM inputs_records_chicken
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


        // Create new feeds input record, an entry
        public function createFeedsInputRecord($farmid, $feed_supplier, $feed_type, $input_type, $notes, $price, $purchase_date, $quantity, $farmerid) {

            $query = 'INSERT INTO inputs_records_feeds 
                SET
                farm_id = :_farmid,
                feed_supplier = :_feedsupplier,
                feed_type = :_feedtype,
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
            $cs = htmlspecialchars(strip_tags($feed_supplier));
            $ft = htmlspecialchars(strip_tags($feed_type));
            $it = htmlspecialchars(strip_tags($input_type));
            $n = htmlspecialchars(strip_tags($notes));
            $p = htmlspecialchars(strip_tags($price));

            $date1 = new DateTime($purchase_date); // Seems this isn't doing timezone conversion and is not accurate
            $pd = htmlspecialchars(strip_tags($date1->format('Y-m-d H:i:s')));
            $q = htmlspecialchars(strip_tags($quantity));
            $fi = htmlspecialchars(strip_tags($farmerid));

            // Bind parameters to prepared stmt
            $stmt->bindParam(':_farmid', $frmid);
            $stmt->bindParam(':_feedsupplier', $cs);
            $stmt->bindParam(':_feedtype', $ft);
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


        public function getAllFeedsInputRecords($farmerid) {

            $query = 'SELECT * FROM inputs_records_feeds
                WHERE
                farmerid = :_farmerid
                AND input_type = "Feeds"
            ';

            $stmt = $this->database_connection->prepare($query);

            // Ensure safe data
            $fi = htmlspecialchars(strip_tags($farmerid));

            // Bind parameters to prepared stmt
            $stmt->bindParam(':_farmerid', $fi);

            $r = $stmt->execute();

            return $stmt;
        }


        // Create new farmer employee, an entry
        public function createNewFarmerEmployee($emp_fullname, $farmerid) {

            $query = 'INSERT INTO farmer_employees 
                SET
                employeefullname = :_emp_fullname,
                farmerid = :_farmerid
            ';

            $stmt = $this->database_connection->prepare($query);

            // Ensure safe data
            $fi = htmlspecialchars(strip_tags($farmerid));
            $ef = htmlspecialchars(strip_tags($emp_fullname));

            // Bind parameters to prepared stmt
            $stmt->bindParam(':_farmerid', $fi);
            $stmt->bindParam(':_emp_fullname', $ef);

            $r = $stmt->execute();

            if ($r) {
                return $this->database_connection->lastInsertId();
                // return $this.getSingleOrderByID($this->database_connection->lastInsertId());
            } else {
                return false;
            }
        }


        public function getAllFarmerEmployees($farmerid) {

            $query = 'SELECT * FROM farmer_employees
                WHERE
                farmerid = :_farmerid
            ';

            $stmt = $this->database_connection->prepare($query);

            // Ensure safe data
            $fi = htmlspecialchars(strip_tags($farmerid));

            // Bind parameters to prepared stmt
            $stmt->bindParam(':_farmerid', $fi);

            $r = $stmt->execute();

            return $stmt;
        }

        // Create new farmer employee, an entry
        public function addFarmerLabourRecord($emp_id, $salary, $notes, $payment_date, $farmerid) {

            $query = 'INSERT INTO input_records_labour 
                SET
                employee_id = :_emp_id,
                salary = :_salary,
                notes = :_notes,
                payment_date = :_payment_date,
                farmerid = :_farmerid
            ';

            $stmt = $this->database_connection->prepare($query);

            // Ensure safe data
            $fi = htmlspecialchars(strip_tags($farmerid));
            $s = htmlspecialchars(strip_tags($salary));
            $n = htmlspecialchars(strip_tags($notes));

            $date1 = new DateTime($payment_date);
            $pd = htmlspecialchars(strip_tags($date1->format('Y-m-d H:i:s')));

            $ei = htmlspecialchars(strip_tags($emp_id));

            // Bind parameters to prepared stmt
            $stmt->bindParam(':_farmerid', $fi);
            $stmt->bindParam(':_salary', $s);
            $stmt->bindParam(':_notes', $n);
            $stmt->bindParam(':_emp_id', $ei);
            $stmt->bindParam(':_payment_date', $pd);

            $r = $stmt->execute();

            if ($r) {
                return $this->database_connection->lastInsertId();
                // return $this.getSingleOrderByID($this->database_connection->lastInsertId());
            } else {
                return false;
            }
        }


        public function getAllFarmerLabourRecords($farmerid) {

            $query = 'SELECT * FROM input_records_labour
                WHERE
                farmerid = :_farmerid
            ';

            $stmt = $this->database_connection->prepare($query);

            // Ensure safe data
            $fi = htmlspecialchars(strip_tags($farmerid));

            // Bind parameters to prepared stmt
            $stmt->bindParam(':_farmerid', $fi);

            $r = $stmt->execute();

            return $stmt;
        }

        // Create new farmer employee, an entry
        public function addFarmerMedicineInputRecord($medicine_type, $medicine_supplier, $type, $vet_name, $purchase_date, $notes, $price, $farmid, $farmerid) {

            $query = 'INSERT INTO input_records_medicines 
                SET
                medicine_type = :_medicine_type,
                medicine_supplier = :_medicine_supplier,
                type = :_type,
                vet_name = :_vet_name,
                notes = :_notes,
                purchase_date = :_purchase_date,
                price = :_price,
                farmid = :_farmid,
                farmerid = :_farmerid
            ';

            $stmt = $this->database_connection->prepare($query);

            // Ensure safe data
            $fi = htmlspecialchars(strip_tags($farmerid));
            $fid = htmlspecialchars(strip_tags($farmid));
            $ms = htmlspecialchars(strip_tags($medicine_supplier));
            $n = htmlspecialchars(strip_tags($notes));
            $mt = htmlspecialchars(strip_tags($medicine_type));
            $t = htmlspecialchars(strip_tags($type));
            $vn = htmlspecialchars(strip_tags($vet_name));

            $date1 = new DateTime($purchase_date);
            $pd = htmlspecialchars(strip_tags($date1->format('Y-m-d H:i:s')));
            
            $p = htmlspecialchars(strip_tags($price));

            // Bind parameters to prepared stmt
            $stmt->bindParam(':_farmerid', $fi);
            $stmt->bindParam(':_medicine_type', $mt);
            $stmt->bindParam(':_notes', $n);
            $stmt->bindParam(':_medicine_supplier', $ms);
            $stmt->bindParam(':_purchase_date', $pd);
            $stmt->bindParam(':_type', $t);
            $stmt->bindParam(':_price', $p);
            $stmt->bindParam(':_farmid', $fid);
            $stmt->bindParam(':_vet_name', $vn);

            $r = $stmt->execute();

            if ($r) {
                return $this->database_connection->lastInsertId();
                // return $this.getSingleOrderByID($this->database_connection->lastInsertId());
            } else {
                return false;
            }
        }


        public function getAllFarmerMedicineInputRecords($farmerid) {

            $query = 'SELECT * FROM input_records_medicines
                WHERE
                farmerid = :_farmerid
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
