<?php

class Records
{
    // DB stuff
    public $database_connection;

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

    public function handleFileUpload($data, $row_id, $table, $farmerid)
    {
        // farmer_records_uploads
        try {
            // set up basic connection
            $ftp = ftp_connect(getenv("GROW_AGRIC_HOST_NAME"));
            $ftp_user_name = getenv("FTP_USERNAME") . "@" . getenv("GROW_AGRIC_HOST_NAME");
            // login with username and password
            $login_result = ftp_login($ftp, $ftp_user_name, getenv("FTP_PASSWORD"));

            // check connection
            if ((!$ftp) || (!$login_result)) {
                file_put_contents('php://stderr', "FTP connection has failed!" . "\n" . "\n", FILE_APPEND | LOCK_EX);

                file_put_contents('php://stderr', "Attempted to connect to " . getenv("GROW_AGRIC_HOST_NAME") . " for user $ftp_user_name" . "\n" . "\n", FILE_APPEND | LOCK_EX);

                // if we get here, we should exit ... return status code
                return false;
            } else {
                file_put_contents('php://stderr', "Connected to " . getenv("GROW_AGRIC_HOST_NAME") . " for user $ftp_user_name" . "\n" . "\n", FILE_APPEND | LOCK_EX);
                ftp_pasv($ftp, true);
            }

            $ext = explode('/', mime_content_type($data))[1]; // https://stackoverflow.com/a/52463011/9259701

            file_put_contents('php://stderr', "\nfile ext is: " . $ext . "\n" . "\n", FILE_APPEND | LOCK_EX);

            $new_file_name = $this->generateRandomString() . '.' . $ext; // https://stackoverflow.com/a/14600743/9259701
            file_put_contents('php://stderr', $new_file_name, FILE_APPEND | LOCK_EX);

            $target_path = './' . $new_file_name;

            // https://stackoverflow.com/a/39384867/9259701
            $content = base64_decode(preg_replace("/^data:[a-z]+\/[a-z]+;base64,/i", "", $data));

            $file = fopen($target_path, 'w');
            fwrite($file, $content);
            fclose($file);

            $destination_file = getenv("FARMER_RECORDS_UPLOAD_PATH") . $new_file_name;

            $upload = ftp_put($ftp, $destination_file, $target_path, FTP_BINARY);

            $url = substr_replace($destination_file, "https://" . getenv("GROW_AGRIC_HOST_NAME"), 0, strlen(explode('/', $destination_file)[0])); // not tryna hardcode

            if ($upload) {
                unlink($target_path);

                $mediaid = $this->recordUploadedMedia($url, $table, $farmerid, $row_id);

                file_put_contents('php://stderr', "Uploaded $url to" . "\n" . "\n", FILE_APPEND | LOCK_EX);

                return $mediaid;
            } else {
                file_put_contents('php://stderr', "FTP upload has failed!" . "\n" . "\n", FILE_APPEND | LOCK_EX);

                return false;
            }
        } catch (\Throwable $err) {
            // throw $err;
            file_put_contents('php://stderr', "handleFileUpload ERR! : " . $err->getMessage() . "\n" . "\n", FILE_APPEND | LOCK_EX);
            return false;
        }
    }

    public function recordUploadedMedia($url, $table, $farmerid, $row_id)
    {
        try {
            $query = 'INSERT INTO farmer_records_uploads 
                SET
                url = :_url,
                farmerid = :_farmerid,'
                .
                ($table == "inputs_records_chicken" ? 'chicken_input_id = :_input_id' : ($table == "input_records_brooding" ? 'brooding_input_id = :_input_id' : ($table == "input_records_diseases" ? 'diseases_input_id = :_input_id' : ($table == "inputs_records_feeds" ? 'feeds_input_id = :_input_id' : ($table == "input_records_income_expenses" ? 'income_expense_input_id = :_input_id' : ($table == "input_records_labour" ? 'labour_input_id = :_input_id' : ($table == "input_records_medicines" ? 'medicines_input_id = :_input_id' : ($table == "input_records_mortalities" ? 'mortalities_input_id = :_input_id' :
                    ''))))))));

            $stmt = $this->database_connection->prepare($query);

            // Ensure safe data
            $fid = htmlspecialchars(strip_tags($farmerid));
            $u = htmlspecialchars(strip_tags($url));
            $rid = htmlspecialchars(strip_tags($row_id));

            // Bind parameters to prepared stmt
            $stmt->bindParam(':_farmerid', $fid);
            $stmt->bindParam(':_url', $u);
            $stmt->bindParam(':_input_id', $rid);

            $r = $stmt->execute();

            if ($r) {
                $last_insert_id = $this->database_connection->lastInsertId();
                return $last_insert_id;
                // return $this.getSingleOrderByID($this->database_connection->lastInsertId());
            } else {
                return false;
            }
        } catch (\Throwable $err) {
            file_put_contents('php://stderr', print_r('ERROR in recordUploadedMedia(): ' . $err->getMessage() . "\n", TRUE));
            return false;
        }
    }

    // Create new chicken input record, an entry
    public function createChickenInputRecord($farmid, $chicken_supplier, $other_chicken_supplier, $input_type, $notes, $price, $purchase_date, $quantity, $farmerid, $documents)
    {
        try {
            $query = 'INSERT INTO inputs_records_chicken 
                SET
                farm_id = :_farmid,
                chicken_supplier = :_chickensupplier,
                other_chicken_supplier = :_otherchickensupplier,
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
            $ocs = htmlspecialchars(strip_tags($other_chicken_supplier));
            $it = htmlspecialchars(strip_tags($input_type));
            $n = htmlspecialchars(strip_tags($notes));
            $p = htmlspecialchars(strip_tags(str_replace(',', '', $price)));

            $date1 = new DateTime($purchase_date); // Seems this isn't doing timezone conversion and is not accurate
            $pd = htmlspecialchars(strip_tags($date1->format('Y-m-d H:i:s')));
            $q = htmlspecialchars(strip_tags(str_replace(',', '', $quantity)));
            $fi = htmlspecialchars(strip_tags($farmerid));

            // Bind parameters to prepared stmt
            $stmt->bindParam(':_farmid', $frmid);
            $stmt->bindParam(':_chickensupplier', $cs);
            $stmt->bindParam(':_otherchickensupplier', $ocs);
            $stmt->bindParam(':_farmerid', $fi);
            $stmt->bindParam(':_inputtype', $it);
            $stmt->bindParam(':_notes', $n);
            $stmt->bindParam(':_price', $p);
            $stmt->bindParam(':_purchasedate', $pd);
            $stmt->bindParam(':_quantity', $q);

            $r = $stmt->execute();

            if ($r) {
                $last_insert_id = $this->database_connection->lastInsertId();

                if ($documents) { // check for empty string or array, if string or array

                    $upload = $this->handleFileUpload($documents, $last_insert_id, 'inputs_records_chicken', $farmerid);

                    if ($upload) {
                        return $last_insert_id;
                        // return $this.getSingleOrderByID($this->database_connection->lastInsertId());
                    } else {
                        return false;
                    }
                } else {
                    return $last_insert_id;
                }
            } else {
                return false;
            }
        } catch (\Throwable $err) {
            file_put_contents('php://stderr', print_r('ERROR in createChickenInputRecord(): ' . $err->getMessage() . "\n", TRUE));
            return false;
        }
    }


    public function getAllChickenInputRecords($farmerid)
    {

        try {
            // SELECT * FROM `inputs_records_chicken` WHERE `farmerid` = 1 AND input_type = 'Chicken'
            $query = 'SELECT * FROM inputs_records_chicken
                WHERE
                farmerid = :_farmerid
                AND 
                input_type = "Chicken"
            ';

            $stmt = $this->database_connection->prepare($query);

            // Ensure safe data
            $fi = htmlspecialchars(strip_tags($farmerid));

            // Bind parameters to prepared stmt
            $stmt->bindParam(':_farmerid', $fi);

            $r = $stmt->execute();

            return $stmt;
        } catch (\Throwable $err) {
            file_put_contents('php://stderr', print_r('ERROR in getAllChickenInputRecords(): ' . $err->getMessage() . "\n", TRUE));
            return false;
        }
    }


    // Create new feeds input record, an entry
    public function createFeedsInputRecord($farmid, $feed_supplier, $feed_type, $input_type, $notes, $price, $purchase_date, $quantity, $farmerid, $documents)
    {

        try {
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
            $p = htmlspecialchars(strip_tags(str_replace(',', '', $price)));

            $date1 = new DateTime($purchase_date); // Seems this isn't doing timezone conversion and is not accurate
            $pd = htmlspecialchars(strip_tags($date1->format('Y-m-d H:i:s')));
            $q = htmlspecialchars(strip_tags(str_replace(',', '', $quantity)));
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
                $last_insert_id = $this->database_connection->lastInsertId();

                if ($documents) { // check for empty string or array, if string or array

                    $upload = $this->handleFileUpload($documents, $last_insert_id, 'inputs_records_feeds', $farmerid);

                    if ($upload) {
                        return $last_insert_id;
                        // return $this.getSingleOrderByID($this->database_connection->lastInsertId());
                    } else {
                        return false;
                    }
                } else {
                    return $last_insert_id;
                }
            } else {
                return false;
            }
        } catch (\Throwable $err) {
            file_put_contents('php://stderr', print_r('ERROR in createFeedsInputRecord(): ' . $err->getMessage() . "\n", TRUE));
            return false;
        }
    }


    public function getAllFeedsInputRecords($farmerid)
    {

        try {
            $query = 'SELECT * FROM inputs_records_feeds
                WHERE
                farmerid = :_farmerid
                AND 
                input_type = "Feeds"
            ';

            $stmt = $this->database_connection->prepare($query);

            // Ensure safe data
            $fi = htmlspecialchars(strip_tags($farmerid));

            // Bind parameters to prepared stmt
            $stmt->bindParam(':_farmerid', $fi);

            $r = $stmt->execute();

            return $stmt;
        } catch (\Throwable $err) {
            file_put_contents('php://stderr', print_r('ERROR in getAllFeedsInputRecords(): ' . $err->getMessage() . "\n", TRUE));
            return false;
        }
    }


    // Create new farmer employee, an entry
    public function createNewFarmerEmployee($emp_fullname, $farmerid)
    {

        try {
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
        } catch (\Throwable $err) {
            file_put_contents('php://stderr', print_r('ERROR in createNewFarmerEmployee(): ' . $err->getMessage() . "\n", TRUE));
            return false;
        }
    }


    public function getAllFarmerEmployees($farmerid)
    {

        try {
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
        } catch (\Throwable $err) {
            file_put_contents('php://stderr', print_r('ERROR in getAllFarmerEmployees(): ' . $err->getMessage() . "\n", TRUE));
            return false;
        }
    }

    public function getSingleFarmerEmployee($farmerid, $employeeid)
    {
        try {
            $query = 'SELECT * FROM farmer_employees
                WHERE
                farmerid = :_farmerid
                AND
                id = :_employeeid
            ';

            $stmt = $this->database_connection->prepare($query);

            // Ensure safe data
            $fi = htmlspecialchars(strip_tags($farmerid));
            $ei = htmlspecialchars(strip_tags($employeeid));

            // Bind parameters to prepared stmt
            $stmt->bindParam(':_farmerid', $fi);
            $stmt->bindParam(':_employeeid', $ei);

            $r = $stmt->execute();

            return $stmt;
        } catch (\Throwable $err) {
            file_put_contents('php://stderr', print_r('ERROR in getSingleFarmerEmployee(): ' . $err->getMessage() . "\n", TRUE));
            return false;
        }
    }

    // Create new farmer employee, an entry
    public function addFarmerLabourRecord($emp_id, $salary, $notes, $payment_date, $farmerid, $documents)
    {
        try {
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
                $last_insert_id = $this->database_connection->lastInsertId();

                if ($documents) { // check for empty string or array, if string or array

                    $upload = $this->handleFileUpload($documents, $last_insert_id, 'input_records_labour', $farmerid);

                    if ($upload) {
                        return $last_insert_id;
                        // return $this.getSingleOrderByID($this->database_connection->lastInsertId());
                    } else {
                        return false;
                    }
                } else {
                    return $last_insert_id;
                }
            } else {
                return false;
            }
        } catch (\Throwable $err) {
            file_put_contents('php://stderr', print_r('ERROR in addFarmerLabourRecord(): ' . $err->getMessage() . "\n", TRUE));
            return false;
        }
    }


    public function getAllFarmerLabourRecords($farmerid)
    {
        try {
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
        } catch (\Throwable $err) {
            file_put_contents('php://stderr', print_r('ERROR in getAllFarmerLabourRecords(): ' . $err->getMessage() . "\n", TRUE));
            return false;
        }
    }

    // Create new administered medicine, an entry
    public function addFarmerMedicineInputRecord($medicine_type, $medicine_supplier, $type, $vet_name, $purchase_date, $notes, $price, $farmid, $farmerid, $documents)
    {

        try {
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

            $p = htmlspecialchars(strip_tags(str_replace(',', '', $price)));

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
                $last_insert_id = $this->database_connection->lastInsertId();

                if ($documents) { // check for empty string or array, if string or array

                    $upload = $this->handleFileUpload($documents, $last_insert_id, 'input_records_medicines', $farmerid);

                    if ($upload) {
                        return $last_insert_id;
                        // return $this.getSingleOrderByID($this->database_connection->lastInsertId());
                    } else {
                        return false;
                    }
                } else {
                    return $last_insert_id;
                }
            } else {
                return false;
            }
        } catch (\Throwable $err) {
            file_put_contents('php://stderr', print_r('ERROR in addFarmerMedicineInputRecord(): ' . $err->getMessage() . "\n", TRUE));
            return false;
        }
    }


    public function getAllFarmerMedicineInputRecords($farmerid)
    {
        try {
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
        } catch (\Throwable $err) {
            file_put_contents('php://stderr', print_r('ERROR in getAllFarmerMedicineInputRecords(): ' . $err->getMessage() . "\n", TRUE));
            return false;
        }
    }


    // Create new brooding, an entry
    public function addFarmerBroodingInputRecord($amount_spent, $brooding_date, $brooding_item_quantity, $brooding_item, $notes, $farmid, $farmerid)
    {

        try {
            $query = 'INSERT INTO input_records_brooding 
                SET
                amount_spent = :_amount_spent,
                brooding_date = :_brooding_date,
                notes = :_notes,
                brooding_item_quantity = :_brooding_item_quantity,
                brooding_item = :_brooding_item,
                farmid = :_farmid,
                farmerid = :_farmerid
            ';

            $stmt = $this->database_connection->prepare($query);

            // Ensure safe data
            $fi = htmlspecialchars(strip_tags($farmerid));
            $fid = htmlspecialchars(strip_tags($farmid));
            $as = htmlspecialchars(strip_tags(str_replace(',', '', $amount_spent)));
            $n = htmlspecialchars(strip_tags($notes));
            $biq = htmlspecialchars(strip_tags(str_replace(',', '', $brooding_item_quantity)));
            $bi = htmlspecialchars(strip_tags($brooding_item));

            $date1 = new DateTime($brooding_date);
            $bd = htmlspecialchars(strip_tags($date1->format('Y-m-d H:i:s')));

            // Bind parameters to prepared stmt
            $stmt->bindParam(':_farmerid', $fi);
            $stmt->bindParam(':_amount_spent', $as);
            $stmt->bindParam(':_notes', $n);
            $stmt->bindParam(':_brooding_item_quantity', $biq);
            $stmt->bindParam(':_brooding_date', $bd);
            $stmt->bindParam(':_brooding_item', $bi);
            $stmt->bindParam(':_farmid', $fid);

            $r = $stmt->execute();

            if ($r) {
                return $this->database_connection->lastInsertId();
                // return $this.getSingleOrderByID($this->database_connection->lastInsertId());
            } else {
                return false;
            }
        } catch (\Throwable $err) {
            file_put_contents('php://stderr', print_r('ERROR in addFarmerBroodingInputRecord(): ' . $err->getMessage() . "\n", TRUE));
            return false;
        }
    }


    public function getAllFarmerBroodingInputRecords($farmerid)
    {

        try {
            $query = 'SELECT * FROM input_records_brooding
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
        } catch (\Throwable $err) {
            file_put_contents('php://stderr', print_r('ERROR in getAllFarmerBroodingInputRecords(): ' . $err->getMessage() . "\n", TRUE));
            return false;
        }
    }


    public function addFarmerCustomerInputRecord($customer_fullname, $customer_phone, $customer_county_location, $farmid, $farmerid)
    {

        try {
            $query = 'INSERT INTO sales_farmer_customer 
                SET
                customerfullname = :_customer_fullname,
                customerphone = :_customer_phone,
                customercountylocation = :_customer_county_location,
                farmid = :_farmid,
                farmerid = :_farmerid
            ';

            $stmt = $this->database_connection->prepare($query);

            // Ensure safe data
            $fi = htmlspecialchars(strip_tags($farmerid));
            $fid = htmlspecialchars(strip_tags($farmid));
            $cf = htmlspecialchars(strip_tags($customer_fullname));
            $cp = htmlspecialchars(strip_tags($customer_phone));
            $ccl = htmlspecialchars(strip_tags($customer_county_location));

            // Bind parameters to prepared stmt
            $stmt->bindParam(':_farmerid', $fi);
            $stmt->bindParam(':_customer_fullname', $cf);
            $stmt->bindParam(':_customer_phone', $cp);
            $stmt->bindParam(':_customer_county_location', $ccl);
            $stmt->bindParam(':_farmid', $fid);

            $r = $stmt->execute();

            if ($r) {
                return $this->database_connection->lastInsertId();
                // return $this.getSingleOrderByID($this->database_connection->lastInsertId());
            } else {
                return false;
            }
        } catch (\Throwable $err) {
            file_put_contents('php://stderr', print_r('ERROR in addFarmerCustomerInputRecord(): ' . $err->getMessage() . "\n", TRUE));
            return false;
        }
    }


    public function getAllFarmerCustomers($farmerid)
    {

        try {
            $query = 'SELECT * FROM sales_farmer_customer
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
        } catch (\Throwable $err) {
            file_put_contents('php://stderr', print_r('ERROR in getAllFarmerCustomers(): ' . $err->getMessage() . "\n", TRUE));
            return false;
        }
    }





    public function addFarmerSaleInputRecord($customer_id, $solditem, $sale_date, $quantity, $price, $farmid, $farmerid)
    {

        try {
            $query = 'INSERT INTO sales_farmer_sales 
                SET
                customer_id = :_customer_id,
                sale_date = :_sale_date,
                solditem = :_solditem,
                quantity = :_quantity,
                price = :_price,
                farmid = :_farmid,
                farmerid = :_farmerid
            ';

            $stmt = $this->database_connection->prepare($query);

            // Ensure safe data
            $fi = htmlspecialchars(strip_tags($farmerid));
            $fid = htmlspecialchars(strip_tags($farmid));
            $ci = htmlspecialchars(strip_tags($customer_id));
            $si = htmlspecialchars(strip_tags($solditem));

            $date1 = new DateTime($sale_date); // Seems this isn't doing timezone conversion and is not accurate
            $sd = htmlspecialchars(strip_tags($date1->format('Y-m-d H:i:s')));

            $q = htmlspecialchars(strip_tags(str_replace(',', '', $quantity)));
            $p = htmlspecialchars(strip_tags(str_replace(',', '', $price)));

            // Bind parameters to prepared stmt
            $stmt->bindParam(':_farmerid', $fi);
            $stmt->bindParam(':_customer_id', $ci);
            $stmt->bindParam(':_sale_date', $sd);
            $stmt->bindParam(':_quantity', $q);
            $stmt->bindParam(':_price', $p);
            $stmt->bindParam(':_farmid', $fid);
            $stmt->bindParam(':_solditem', $si);

            $r = $stmt->execute();

            if ($r) {
                return $this->database_connection->lastInsertId();
                // return $this.getSingleOrderByID($this->database_connection->lastInsertId());
            } else {
                return false;
            }
        } catch (\Throwable $err) {
            file_put_contents('php://stderr', print_r('ERROR in getAllFarmerMortalitiesInputRecords(): ' . $err->getMessage() . "\n", TRUE));
            return false;
        }
    }


    public function getAllFarmerSalesInputRecords($farmerid)
    {

        $query = 'SELECT * FROM sales_farmer_sales
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

    public function getAllFarmerMortalitiesInputRecords($farmerid)
    {
        try {
            $query = 'SELECT * FROM input_records_mortalities
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
        } catch (\Throwable $err) {
            file_put_contents('php://stderr', print_r('ERROR in getAllFarmerMortalitiesInputRecords(): ' . $err->getMessage() . "\n", TRUE));
            return false;
        }
    }

    public function addFarmerMortalityInputRecord($reason, $_date, $openingbalance, $numberofdeaths, $closingbalance, $farmid, $farmerid)
    {
        try {
            $query = 'INSERT INTO input_records_mortalities 
                SET
                reason = :_reason,
                date = :_date,
                openingbalance = :_openingbalance,
                numberofdeaths = :_numberofdeaths,
                closingbalance = :_closingbalance,
                farmid = :_farmid,
                farmerid = :_farmerid
            ';

            $stmt = $this->database_connection->prepare($query);

            // Ensure safe data
            $fi = htmlspecialchars(strip_tags($farmerid));
            $fid = htmlspecialchars(strip_tags($farmid));
            $r = htmlspecialchars(strip_tags($reason));
            $ob = htmlspecialchars(strip_tags($openingbalance));

            $date1 = new DateTime($_date); // Seems this isn't doing timezone conversion and is not accurate
            $d = htmlspecialchars(strip_tags($date1->format('Y-m-d H:i:s')));

            $nod = htmlspecialchars(strip_tags($numberofdeaths));
            $cb = htmlspecialchars(strip_tags($closingbalance));

            // Bind parameters to prepared stmt
            $stmt->bindParam(':_farmerid', $fi);
            $stmt->bindParam(':_reason', $r);
            $stmt->bindParam(':_openingbalance', $ob);
            $stmt->bindParam(':_numberofdeaths', $nod);
            $stmt->bindParam(':_closingbalance', $cb);
            $stmt->bindParam(':_farmid', $fid);
            $stmt->bindParam(':_date', $d);

            $r = $stmt->execute();

            if ($r) {
                return $this->database_connection->lastInsertId();
                // return $this.getSingleOrderByID($this->database_connection->lastInsertId());
            } else {
                return false;
            }
        } catch (\Throwable $err) {
            file_put_contents('php://stderr', print_r('ERROR in addFarmerMortalityInputRecord(): ' . $err->getMessage() . "\n", TRUE));
            return false;
        }
    }

    public function getAllFarmerDiseasesInputRecords($farmerid)
    {
        $query = 'SELECT * FROM input_records_diseases
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

    public function addFarmerDiseasesInputRecord($notes, $_date, $diagonsis, $disease, $vet_name, $farmid, $farmerid, $documents)
    {
        try {
            // date can be auto filled in db though
            $query = 'INSERT INTO input_records_diseases 
                SET
                notes = :_notes,
                date = :_date,
                diagonsis = :_diagonsis,
                disease = :_disease,
                vet_name = :_vet_name,
                farmid = :_farmid,
                farmerid = :_farmerid
            ';

            $stmt = $this->database_connection->prepare($query);

            // Ensure safe data
            $fi = htmlspecialchars(strip_tags($farmerid));
            $fid = htmlspecialchars(strip_tags($farmid));
            $n = htmlspecialchars(strip_tags($notes));
            $dia = htmlspecialchars(strip_tags($diagonsis));

            $date1 = new DateTime($_date); // Seems this isn't doing timezone conversion and is not accurate
            $d = htmlspecialchars(strip_tags($date1->format('Y-m-d H:i:s')));

            $dis = htmlspecialchars(strip_tags($disease));
            $vn = htmlspecialchars(strip_tags($vet_name));

            // Bind parameters to prepared stmt
            $stmt->bindParam(':_farmerid', $fi);
            $stmt->bindParam(':_notes', $n);
            $stmt->bindParam(':_diagonsis', $dia);
            $stmt->bindParam(':_disease', $dis);
            $stmt->bindParam(':_vet_name', $vn);
            $stmt->bindParam(':_farmid', $fid);
            $stmt->bindParam(':_date', $d);

            $r = $stmt->execute();

            if ($r) {
                $last_insert_id = $this->database_connection->lastInsertId();

                if ($documents) { // check for empty string or array, if string or array

                    $upload = $this->handleFileUpload($documents, $last_insert_id, 'input_records_diseases', $farmerid);

                    if ($upload) {
                        return $last_insert_id;
                        // return $this.getSingleOrderByID($this->database_connection->lastInsertId());
                    } else {
                        return false;
                    }
                } else {
                    return $last_insert_id;
                }
            } else {
                return false;
            }
        } catch (\Throwable $err) {
            file_put_contents('php://stderr', print_r('ERROR in addFarmerDiseasesInputRecord(): ' . $err->getMessage() . "\n", TRUE));
            return false;
        }
    }


    public function getAllFarmerOtherIncomeOrExpenseInputRecords($farmerid)
    {
        try {
            $query = 'SELECT * FROM input_records_income_expenses
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
        } catch (\Throwable $err) {
            file_put_contents('php://stderr', print_r('ERROR in getAllFarmerOtherIncomeOrExpenseInputRecords(): ' . $err->getMessage() . "\n", TRUE));
            return false;
        }
    }

    public function addFarmerOtherIncomeOrExpenseInputRecord($notes, $source, $_date, $amount, $type, $farmid, $farmerid, $documents)
    {
        try {
            $query = 'INSERT INTO input_records_income_expenses 
                SET
                source = :_source,
                date = :_date,
                amount = :_amount,
                notes = :_notes,
                type = :_type,
                farmid = :_farmid,
                farmerid = :_farmerid
            ';

            $stmt = $this->database_connection->prepare($query);

            // Ensure safe data
            $fi = htmlspecialchars(strip_tags($farmerid));
            $fid = htmlspecialchars(strip_tags($farmid));
            $a = htmlspecialchars(strip_tags(str_replace(',', '', $amount)));
            $s = htmlspecialchars(strip_tags($source));

            $date1 = new DateTime($_date); // Seems this isn't doing timezone conversion and is not accurate
            $d = htmlspecialchars(strip_tags($date1->format('Y-m-d H:i:s')));

            $t = htmlspecialchars(strip_tags($type));
            $n = htmlspecialchars(strip_tags($notes));

            // Bind parameters to prepared stmt
            $stmt->bindParam(':_farmerid', $fi);
            $stmt->bindParam(':_source', $s);
            $stmt->bindParam(':_notes', $n);
            $stmt->bindParam(':_amount', $a);
            $stmt->bindParam(':_type', $t);
            $stmt->bindParam(':_farmid', $fid);
            $stmt->bindParam(':_date', $d);

            $r = $stmt->execute();

            if ($r) {
                $last_insert_id = $this->database_connection->lastInsertId();

                if ($documents) { // check for empty string or array, if string or array

                    $upload = $this->handleFileUpload($documents, $last_insert_id, 'input_records_income_expenses', $farmerid);

                    if ($upload) {
                        return $last_insert_id;
                        // return $this.getSingleOrderByID($this->database_connection->lastInsertId());
                    } else {
                        return false;
                    }
                } else {
                    return $last_insert_id;
                }
            } else {
                return false;
            }
        } catch (\Throwable $err) {
            file_put_contents('php://stderr', print_r('ERROR in addFarmerOtherIncomeOrExpenseInputRecord(): ' . $err->getMessage() . "\n", TRUE));
            return false;
        }
    }


    public function getAllFarmerFinanceApplicationStatusByFarmerID($farmerid)
    {
        try {
            $query = 'SELECT 
            finance_applications.`farmerid`, 
            finance_applications.`farmid`, 
            finance_applications.`id`, 
            finance_applications.created_on, 
            finance_application_statuses.status 
            FROM 
            `finance_applications` 
            RIGHT OUTER JOIN 
            finance_application_statuses 
            ON 
            finance_applications.id = finance_application_statuses.finance_application_id 
            WHERE farmerid = ?';

            // Prepare statement
            $query_statement = $this->database_connection->prepare($query);

            // Ensure safe data
            $fi = htmlspecialchars(strip_tags($farmerid));

            // Bind parameters to prepared stmt
            $query_statement->bindParam(1, $fi);

            $query_statement->execute();

            return $query_statement;
        } catch (\Throwable $err) {
            file_put_contents('php://stderr', print_r('ERROR in getAllFarmerFinanceApplicationStatusByFarmerID(): ' . $err->getMessage() . "\n", TRUE));
            return $err->getMessage();
        }
    }
}
