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

    /**
     * takes in
     * base64 string
     * the row id to insert
     * the table to insert it in
     * the farmer id
     */
    public function handleFileUpload($data, $row_id, $table, $farmerid, $column = NULL)
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
            file_put_contents('php://stderr', "uploading new name: $new_file_name \n", FILE_APPEND | LOCK_EX);

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

                $mediaid = false ;
                if ($table == "fieldagents_farm_visits") {
                    $mediaid = $this->recordFieldAgentFarmVisitUploadedMedia($url, $table, $column, $row_id);
                } else {
                    $mediaid = $this->recordUploadedMedia($url, $table, $farmerid, $row_id);
                }
                
                file_put_contents('php://stderr', "Uploaded as $url" . "\n" . "\n", FILE_APPEND | LOCK_EX);

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

    public function recordFieldAgentFarmVisitUploadedMedia($url, $table, $column, $row_id)
    {
        try {
            $query = 'UPDATE '. $table .' 
                SET 
                '. $column .' = :url
                WHERE
                id = :id
            ';

            $stmt = $this->database_connection->prepare($query);

            // Ensure safe data
            $u = htmlspecialchars(strip_tags($url));
            $rid = htmlspecialchars(strip_tags($row_id));

            // Bind parameters to prepared stmt
            $stmt->bindParam(':url', $u);
            $stmt->bindParam(':id', $rid);

            $r = $stmt->execute();

            if ($r) {

                file_put_contents('php://stderr', print_r('Just successfully ran recordFieldAgentFarmVisitUploadedMedia(): ' . "\n", TRUE));
                $last_insert_id = $this->database_connection->lastInsertId();
                return $last_insert_id;
                // return $this.getSingleOrderByID($this->database_connection->lastInsertId());
            } else {
                return false;
            }
        } catch (\Throwable $err) {
            file_put_contents('php://stderr', print_r('ERROR in recordFieldAgentFarmVisitUploadedMedia(): ' . $err->getMessage() . "\n", TRUE));
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

    public function getMinDateOfRecords($farmerid)
    {
        $earliestDate = 'SELECT MIN(entry_date0) AS "mindate" FROM
        (
            SELECT MIN(`entry_date`) as entry_date0 FROM `input_records_mortalities` WHERE `input_records_mortalities`.`farmerid` = :farmerid
              UNION
              SELECT MIN(`entry_date`) as entry_date1 FROM `input_records_medicines` WHERE `input_records_medicines`.`farmerid` = :farmerid
              UNION
              SELECT MIN(`entry_date`) as entry_date2  FROM `input_records_labour` WHERE `input_records_labour`.`farmerid` = :farmerid
              UNION
              SELECT MIN(`entry_date`) as entry_date3  FROM `input_records_income_expenses` WHERE `input_records_income_expenses`.`farmerid` = :farmerid
              UNION
              SELECT MIN(`entry_date`) as entry_date4  FROM `inputs_records_chicken` WHERE `inputs_records_chicken`.`farmerid` = :farmerid
              UNION
              SELECT MIN(`entry_date`) as entry_date5 FROM `input_records_diseases` WHERE `input_records_diseases`.`farmerid` = :farmerid
              UNION
              SELECT MIN(`entry_date`) as entry_date6 FROM `input_records_brooding` WHERE `input_records_brooding`.`farmerid` = :farmerid
              UNION
              SELECT MIN(`entry_date`) as entry_date7 FROM `inputs_records_feeds` WHERE `inputs_records_feeds`.`farmerid` = :farmerid
         ) AS dates';

        $stmt = $this->database_connection->prepare($earliestDate);

        // Ensure safe data
        $fi = htmlspecialchars(strip_tags($farmerid));

        // Bind parameters to prepared stmt
        $stmt->bindParam(':farmerid', $fi);

        $r = $stmt->execute();

        return $stmt;
    }

    public function totalFarmerFarms($farmerid)
    {
        $query = "SELECT COUNT(*) as totalfarms FROM `farms` WHERE `farms`.`farmerid` = :farmerid";

        $stmt = $this->database_connection->prepare($query);

            // Ensure safe data
            $fi = htmlspecialchars(strip_tags($farmerid));

            // Bind parameters to prepared stmt
            $stmt->bindParam(':farmerid', $fi);

            $r = $stmt->execute();

            return $stmt;
    }

    public function totalFarmerEmployess($farmerid)
    {
        $query = "SELECT COUNT(*) as totalemployees FROM `farmer_employees` WHERE `farmer_employees`.`farmerid` = :farmerid";

        $stmt = $this->database_connection->prepare($query);

        // Ensure safe data
        $fi = htmlspecialchars(strip_tags($farmerid));

        // Bind parameters to prepared stmt
        $stmt->bindParam(':farmerid', $fi);

        $r = $stmt->execute();

        return $stmt;
    }

    public function totalSalaryPaidByFarmer($farmerid)
    {
        $query = "SELECT SUM(`salary`) AS totalsalarypaid FROM `input_records_labour` WHERE `input_records_labour`.farmerid = :farmerid";

        $stmt = $this->database_connection->prepare($query);

        // Ensure safe data
        $fi = htmlspecialchars(strip_tags($farmerid));

        // Bind parameters to prepared stmt
        $stmt->bindParam(':farmerid', $fi);

        $r = $stmt->execute();

        return $stmt;
    }

    public function sumsOfRecords($farmerid)
    {
        $recordsSums = 'SELECT SUM(sum0) AS "sums" FROM
                    (
                        SELECT COUNT(*) as sum0 FROM `input_records_mortalities` WHERE `input_records_mortalities`.`farmerid` = :farmerid
                          UNION
                          SELECT COUNT(*) as sum1 FROM `input_records_medicines` WHERE `input_records_medicines`.`farmerid` = :farmerid
                          UNION
                          SELECT COUNT(*) as sum2  FROM `input_records_labour` WHERE `input_records_labour`.`farmerid` = :farmerid
                          UNION
                          SELECT COUNT(*) as sum3  FROM `input_records_income_expenses` WHERE `input_records_income_expenses`.`farmerid` = :farmerid
                          UNION
                          SELECT COUNT(*) as sum4  FROM `inputs_records_chicken` WHERE `inputs_records_chicken`.`farmerid` = :farmerid
                          UNION
                          SELECT COUNT(*) as sum5 FROM `input_records_diseases` WHERE `input_records_diseases`.`farmerid` = :farmerid
                          UNION
                          SELECT COUNT(*) as sum6 FROM `input_records_brooding` WHERE `input_records_brooding`.`farmerid` = :farmerid
                          UNION
                          SELECT COUNT(*) as sum7 FROM `inputs_records_feeds` WHERE `inputs_records_feeds`.`farmerid` = :farmerid
                     ) AS sums';


            $stmt = $this->database_connection->prepare($recordsSums);

            // Ensure safe data
            $fi = htmlspecialchars(strip_tags($farmerid));

            // Bind parameters to prepared stmt
            $stmt->bindParam(':farmerid', $fi);

            $r = $stmt->execute();

            return $stmt;
    }

    public function totalRecordsOfFarmers($farmerid)
    {
        $totalPerTable = "SELECT COUNT(*) as 'sum', 'mortalities' as name FROM `input_records_mortalities` WHERE `input_records_mortalities`.`farmerid` = :farmerid
                    UNION
                    SELECT COUNT(*) as sum1, 'medicines' as name FROM `input_records_medicines` WHERE `input_records_medicines`.`farmerid` = :farmerid
                    UNION
                    SELECT COUNT(*) as sum2, 'labour' as name  FROM `input_records_labour` WHERE `input_records_labour`.`farmerid` = :farmerid
                    UNION
                    SELECT COUNT(*) as sum3, 'income and expenses' as name  FROM `input_records_income_expenses` WHERE `input_records_income_expenses`.`farmerid` = :farmerid
                    UNION
                    SELECT COUNT(*) as sum4, 'chicken' as name  FROM `inputs_records_chicken` WHERE `inputs_records_chicken`.`farmerid` = :farmerid
                    UNION
                    SELECT COUNT(*) as sum5, 'diseases' as name FROM `input_records_diseases` WHERE `input_records_diseases`.`farmerid` = :farmerid
                    UNION
                    SELECT COUNT(*) as sum6, 'brooding' as name FROM `input_records_brooding` WHERE `input_records_brooding`.`farmerid` = :farmerid
                    UNION
                    SELECT COUNT(*) as sum7, 'feeds' as name FROM `inputs_records_feeds` WHERE `inputs_records_feeds`.`farmerid` = :farmerid
                    ";


            $stmt = $this->database_connection->prepare($totalPerTable);

            // Ensure safe data
            $fi = htmlspecialchars(strip_tags($farmerid));

            // Bind parameters to prepared stmt
            $stmt->bindParam(':farmerid', $fi);

            $r = $stmt->execute();

            return $stmt;
    }

    public function profitAndLoss($farmerid)
    {
        $profitOrLoss = "SELECT `input_records_income_expenses`.type, SUM(`input_records_income_expenses`.amount) as 'sum'
        FROM `input_records_income_expenses`
        WHERE `input_records_income_expenses`.`farmerid` = :farmerid
        GROUP BY `input_records_income_expenses`.type";

        $stmt = $this->database_connection->prepare($profitOrLoss);

        // Ensure safe data
        $fi = htmlspecialchars(strip_tags($farmerid));

        // Bind parameters to prepared stmt
        $stmt->bindParam(':farmerid', $fi);

        $r = $stmt->execute();

        return $stmt;
    }

    public function getMaxDateOfRecord($farmerid)
    {
        $lastestDate = 'SELECT MAX(entry_date0) AS "maxdate" FROM
        (
            SELECT MAX(`entry_date`) as entry_date0 FROM `input_records_mortalities` WHERE `input_records_mortalities`.`farmerid` = :farmerid
              UNION
              SELECT MAX(`entry_date`) as entry_date1 FROM `input_records_medicines` WHERE `input_records_medicines`.`farmerid` = :farmerid
              UNION
              SELECT MAX(`entry_date`) as entry_date2  FROM `input_records_labour` WHERE `input_records_labour`.`farmerid` = :farmerid
              UNION
              SELECT MAX(`entry_date`) as entry_date3  FROM `input_records_income_expenses` WHERE `input_records_income_expenses`.`farmerid` = :farmerid
              UNION
              SELECT MAX(`entry_date`) as entry_date4  FROM `inputs_records_chicken` WHERE `inputs_records_chicken`.`farmerid` = :farmerid
              UNION
              SELECT MAX(`entry_date`) as entry_date5 FROM `input_records_diseases` WHERE `input_records_diseases`.`farmerid` = :farmerid
              UNION
              SELECT MAX(`entry_date`) as entry_date6 FROM `input_records_brooding` WHERE `input_records_brooding`.`farmerid` = :farmerid
              UNION
              SELECT MAX(`entry_date`) as entry_date7 FROM `inputs_records_feeds` WHERE `inputs_records_feeds`.`farmerid` = :farmerid
        ) AS dates';

        


        $stmt = $this->database_connection->prepare($lastestDate);

        // Ensure safe data
        $fi = htmlspecialchars(strip_tags($farmerid));

        // Bind parameters to prepared stmt
        $stmt->bindParam(':farmerid', $fi);

        $r = $stmt->execute();

        return $stmt;

    }

    // Create new chicken input record, an entry
    public function createChickenInputRecord($farmid, $chicken_supplier, $other_chicken_supplier, $notes, $price, $purchase_date, $quantity, $farmerid, $documents)
    {
        try {
            $query = 'INSERT INTO inputs_records_chicken 
                SET
                farmid = :_farmid,
                chicken_supplier = :_chickensupplier,
                other_chicken_supplier = :_otherchickensupplier,
                farmerid = :_farmerid,
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
            $stmt->bindParam(':_notes', $n);
            $stmt->bindParam(':_price', $p);
            $stmt->bindParam(':_purchasedate', $pd);
            $stmt->bindParam(':_quantity', $q);

            $r = $stmt->execute();

            if ($r) {
                $last_insert_id = $this->database_connection->lastInsertId();

                if ($documents && !empty($documents)) { // check for empty string or array, if string or array

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
    public function createFeedsInputRecord($farmid, $feed_supplier, $other_feed_supplier, $feed_type, $notes, $price, $purchase_date, $quantity, $farmerid, $documents)
    {

        try {
            $query = 'INSERT INTO inputs_records_feeds 
                SET
                farmid = :_farmid,
                feed_supplier = :_feedsupplier,
                other_feed_supplier = :_other_feed_supplier,
                feed_type = :_feedtype,
                farmerid = :_farmerid,
                notes = :_notes,
                price = :_price,
                purchase_date = :_purchasedate,
                quantity = :_quantity
            ';

            $stmt = $this->database_connection->prepare($query);

            // Ensure safe data
            $frmid = htmlspecialchars(strip_tags($farmid));
            $fs = htmlspecialchars(strip_tags($feed_supplier));
            $ofs = htmlspecialchars(strip_tags($other_feed_supplier));
            $ft = htmlspecialchars(strip_tags($feed_type));
            $n = htmlspecialchars(strip_tags($notes));
            $p = htmlspecialchars(strip_tags(str_replace(',', '', $price)));

            $date1 = new DateTime($purchase_date); // Seems this isn't doing timezone conversion and is not accurate
            $pd = htmlspecialchars(strip_tags($date1->format('Y-m-d H:i:s')));
            $q = htmlspecialchars(strip_tags(str_replace(',', '', $quantity)));
            $fi = htmlspecialchars(strip_tags($farmerid));

            // Bind parameters to prepared stmt
            $stmt->bindParam(':_farmid', $frmid);
            $stmt->bindParam(':_feedsupplier', $fs);
            $stmt->bindParam(':_other_feed_supplier', $ofs);
            $stmt->bindParam(':_feedtype', $ft);
            $stmt->bindParam(':_farmerid', $fi);
            $stmt->bindParam(':_notes', $n);
            $stmt->bindParam(':_price', $p);
            $stmt->bindParam(':_purchasedate', $pd);
            $stmt->bindParam(':_quantity', $q);

            $r = $stmt->execute();

            if ($r) {
                $last_insert_id = $this->database_connection->lastInsertId();

                if ($documents && !empty($documents)) { // check for empty string or array, if string or array

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
    public function createNewFarmerEmployee($emp_fullname, $farmerid, $farmid)
    {

        try {
            $query = 'INSERT INTO farmer_employees 
                SET
                employeefullname = :_emp_fullname,
                farmerid = :_farmerid,
                farmid = :_farmid
            ';

            $stmt = $this->database_connection->prepare($query);

            // Ensure safe data
            $fi = htmlspecialchars(strip_tags($farmerid));
            $ef = htmlspecialchars(strip_tags($emp_fullname));
            $frmid = htmlspecialchars(strip_tags($farmid));

            // Bind parameters to prepared stmt
            $stmt->bindParam(':_farmerid', $fi);
            $stmt->bindParam(':_emp_fullname', $ef);
            $stmt->bindParam(':_farmid', $frmid);

            $r = $stmt->execute();

            if ($r) {
                // return $this->database_connection->lastInsertId();
                $_result = $this->getSingleEmployee($this->database_connection->lastInsertId());
                $_row = $_result->fetch(PDO::FETCH_ASSOC);

                return $_row;
            } else {
                return false;
            }
        } catch (\Throwable $err) {
            file_put_contents('php://stderr', print_r('ERROR in createNewFarmerEmployee(): ' . $err->getMessage() . "\n", TRUE));
            return false;
        }
    }

    public function getSingleEmployee($employeeid)
    {
        try {
            $query = 'SELECT * FROM farmer_employees
                WHERE
                id = :_employeeid
            ';

            $stmt = $this->database_connection->prepare($query);

            // Ensure safe data
            $ei = htmlspecialchars(strip_tags($employeeid));

            // Bind parameters to prepared stmt
            $stmt->bindParam(':_employeeid', $ei);

            $r = $stmt->execute();

            return $stmt;
        } catch (\Throwable $err) {
            file_put_contents('php://stderr', print_r('ERROR in getSingleFarmerEmployee(): ' . $err->getMessage() . "\n", TRUE));
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
            $s = htmlspecialchars(strip_tags(str_replace(',', '', $salary)));
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

                if ($documents && !empty($documents)) { // check for empty string or array, if string or array

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
            file_put_contents('php://stderr', print_r("******\n\nERROR in addFarmerLabourRecord(): " . $err->getMessage() . "\n*******\n\n", TRUE));
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

                if ($documents && !empty($documents)) { // check for empty string or array, if string or array

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
    public function addFarmerBroodingInputRecord($amount_spent, $brooding_date, $brooding_item_quantity, $brooding_item, $notes, $farmid, $farmerid, $chickenhouseid, $other_brooding_item)
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
                chickenhouseid = :_chickenhouseid,
                other_brooding_item = :_other_brooding_item,
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
            $chid = htmlspecialchars(strip_tags($chickenhouseid));

            $obi = htmlspecialchars(strip_tags($other_brooding_item));

            $date1 = new DateTime($brooding_date);
            $bd = htmlspecialchars(strip_tags($date1->format('Y-m-d H:i:s')));

            // Bind parameters to prepared stmt
            $stmt->bindParam(':_farmerid', $fi);
            $stmt->bindParam(':_amount_spent', $as);
            $stmt->bindParam(':_notes', $n);
            $stmt->bindParam(':_brooding_item_quantity', $biq);
            $stmt->bindParam(':_brooding_date', $bd);
            $stmt->bindParam(':_brooding_item', $bi);
            $stmt->bindParam(':_chickenhouseid', $chid);
            $stmt->bindParam(':_farmid', $fid);
            $stmt->bindParam(':_other_brooding_item', $obi);
            

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
            // should we select rows that don't have farmid as NULL ?
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


    public function addFarmerSaleInputRecord($customer_id, $solditem, $othersolditem, $sale_date, $quantity, $price, $farmid, $farmerid)
    {

        try {
            $query = 'INSERT INTO sales_farmer_sales 
                SET
                customer_id = :_customer_id,
                sale_date = :_sale_date,
                solditem = :_solditem,
                othersolditem = :_othersolditem,
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
            $osi = htmlspecialchars(strip_tags($othersolditem));

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
            $stmt->bindParam(':_othersolditem', $osi);

            $r = $stmt->execute();
            file_put_contents('php://stderr', print_r('addFarmerSaleInputRecord(): ' . "\n", TRUE));
            file_put_contents('php://stderr', print_r($r, TRUE));

            if ($r) {
                return $this->database_connection->lastInsertId();
                file_put_contents('php://stderr', print_r("\nEnd addFarmerSaleInputRecord(): what was r? \n", TRUE));
                // return $this.getSingleOrderByID($this->database_connection->lastInsertId());
            } else {
                return false;
            }
        } catch (\Throwable $err) {
            file_put_contents('php://stderr', print_r('ERROR in addFarmerSaleInputRecord(): ' . $err->getMessage() . "\n", TRUE));
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
                ORDER BY 
                date DESC
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

    public function calculateTotalIncome($farmerid)
    {
        // 
        try {
            $query = 'SELECT MAX(amount) 
            AS total_income 
            FROM `input_records_income_expenses` 
            WHERE type = "Income" AND farmerid = :_farmerid
            ';

            $stmt = $this->database_connection->prepare($query);

            // Ensure safe data
            $fid = htmlspecialchars(strip_tags($farmerid));

            // Bind parameters to prepared stmt
            $stmt->bindParam(':_farmerid', $fid);

            $r = $stmt->execute();

            return $stmt;
        } catch (\Throwable $err) {
            file_put_contents('php://stderr', print_r('ERROR in calculateTotalIncome(): ' . $err->getMessage() . "\n", TRUE));
            return false;
        }
    }


    public function calculateTotalExpense($farmerid)
    {
        // 
        try {
            $query = 'SELECT MAX(amount) 
            AS total_expense 
            FROM `input_records_income_expenses` 
            WHERE type = "Expense" AND farmerid = :_farmerid
            ';

            $stmt = $this->database_connection->prepare($query);

            // Ensure safe data
            $fid = htmlspecialchars(strip_tags($farmerid));

            // Bind parameters to prepared stmt
            $stmt->bindParam(':_farmerid', $fid);

            $r = $stmt->execute();

            return $stmt;
        } catch (\Throwable $err) {
            file_put_contents('php://stderr', print_r('ERROR in calculateTotalExpense(): ' . $err->getMessage() . "\n", TRUE));
            return false;
        }
    }

    public function getSingleMortalityRecordByID($mortalityrecordid)
    {
        try {
            $query = 'SELECT * FROM input_records_mortalities
                WHERE
                id = :_id
            ';

            $stmt = $this->database_connection->prepare($query);

            // Ensure safe data
            $mrid = htmlspecialchars(strip_tags($mortalityrecordid));

            // Bind parameters to prepared stmt
            $stmt->bindParam(':_id', $mrid);

            $r = $stmt->execute();

            return $stmt;
        } catch (\Throwable $err) {
            file_put_contents('php://stderr', print_r('ERROR in getSingleMortalityRecordByID(): ' . $err->getMessage() . "\n", TRUE));
            return false;
        }
    }

    public function addFarmerMortalityInputRecord($reason, $_date, $openingbalance, $numberofdeaths, $closingbalance, $farmid, $farmerid, $chickenhouseid)
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
                farmerid = :_farmerid,
                chickenhouseid = :_chickenhouseid
            ';

            $stmt = $this->database_connection->prepare($query);

            // Ensure safe data
            $fi = htmlspecialchars(strip_tags($farmerid));
            $fid = htmlspecialchars(strip_tags($farmid));
            $r = htmlspecialchars(strip_tags($reason));
            $chid = htmlspecialchars(strip_tags($chickenhouseid));
            $ob = htmlspecialchars(strip_tags(str_replace(',', '', $openingbalance)));

            $date1 = new DateTime($_date); // Seems this isn't doing timezone conversion and is not accurate
            $d = htmlspecialchars(strip_tags($date1->format('Y-m-d H:i:s')));

            $nod = htmlspecialchars(strip_tags(str_replace(',', '', $numberofdeaths)));
            $cb = htmlspecialchars(strip_tags(str_replace(',', '', $closingbalance)));

            // Bind parameters to prepared stmt
            $stmt->bindParam(':_farmerid', $fi);
            $stmt->bindParam(':_reason', $r);
            $stmt->bindParam(':_openingbalance', $ob);
            $stmt->bindParam(':_numberofdeaths', $nod);
            $stmt->bindParam(':_closingbalance', $cb);
            $stmt->bindParam(':_farmid', $fid);
            $stmt->bindParam(':_date', $d);
            $stmt->bindParam(':_chickenhouseid', $chid);

            $r = $stmt->execute();

            if ($r) {
                $_result = $this->getSingleMortalityRecordByID($this->database_connection->lastInsertId());
                $_row = $_result->fetch(PDO::FETCH_ASSOC);

                return $_row;
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

    public function addFarmerDiseasesInputRecord($notes, $_date, $diagonsis, $otherdiagonsis, $vet_name, $farmid, $farmerid, $documents)
    {
        try {
            // date can be auto filled in db though
            $query = 'INSERT INTO input_records_diseases 
                SET
                notes = :_notes,
                date = :_date,
                diagonsis = :_diagonsis,
                vet_name = :_vet_name,
                farmid = :_farmid,
                farmerid = :_farmerid,
                otherdiagonsis = :_otherdiagonsis 
            ';

            $stmt = $this->database_connection->prepare($query);

            // Ensure safe data
            $fi = htmlspecialchars(strip_tags($farmerid));
            $fid = htmlspecialchars(strip_tags($farmid));
            $n = htmlspecialchars(strip_tags($notes));
            $dia = htmlspecialchars(strip_tags($diagonsis));
            $odia = htmlspecialchars(strip_tags($otherdiagonsis));

            $date1 = new DateTime($_date); // Seems this isn't doing timezone conversion and is not accurate
            $d = htmlspecialchars(strip_tags($date1->format('Y-m-d H:i:s')));

            $vn = htmlspecialchars(strip_tags($vet_name));

            // Bind parameters to prepared stmt
            $stmt->bindParam(':_farmerid', $fi);
            $stmt->bindParam(':_notes', $n);
            $stmt->bindParam(':_diagonsis', $dia);
            $stmt->bindParam(':_otherdiagonsis', $odia);
            $stmt->bindParam(':_vet_name', $vn);
            $stmt->bindParam(':_farmid', $fid);
            $stmt->bindParam(':_date', $d);

            $r = $stmt->execute();

            if ($r) {
                $last_insert_id = $this->database_connection->lastInsertId();

                if ($documents && !empty($documents)) { // check for empty string or array, if string or array

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

                if ($documents && !empty($documents)) { // check for empty string or array, if string or array

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

    public function getAllFarmerUploadedDocuments($farmerid)
    {
        try {
            $query = 'SELECT * FROM `custom_farmer_uploads`
            WHERE custom_farmer_uploads.farmerid = ?
            AND custom_farmer_uploads.deleted = false';

            // Prepare statement
            $query_statement = $this->database_connection->prepare($query);

            // Ensure safe data
            $fi = htmlspecialchars(strip_tags($farmerid));

            // Bind parameters to prepared stmt
            $query_statement->bindParam(1, $fi);

            $query_statement->execute();

            return $query_statement;
        } catch (\Throwable $err) {
            file_put_contents('php://stderr', print_r('ERROR in getAllFarmerUploadedDocuments(): ' . $err->getMessage() . "\n", TRUE));
            return $err->getMessage();
        }
    }

    public function getFarmerRefferals($farmerid)
    {
        try {
            $query = 'SELECT * FROM `howwewereheardof`
                WHERE howwewereheardof.farmerid = ?
            ';

            // Prepare statement
            $query_statement = $this->database_connection->prepare($query);

            // Ensure safe data
            $fi = htmlspecialchars(strip_tags($farmerid));

            // Bind parameters to prepared stmt
            $query_statement->bindParam(1, $fi);

            $query_statement->execute();

            return $query_statement;
        } catch (\Throwable $err) {
            file_put_contents('php://stderr', print_r('ERROR in getFarmerRefferal(): ' . $err->getMessage() . "\n", TRUE));
            return $err->getMessage();
        }
    }

    public function getFarmerRefferalByID($id)
    {
        try {
            $query = 'SELECT * FROM `howwewereheardof`
                WHERE howwewereheardof.id = ?
            ';

            // Prepare statement
            $query_statement = $this->database_connection->prepare($query);

            // Ensure safe data
            $fi = htmlspecialchars(strip_tags($id));

            // Bind parameters to prepared stmt
            $query_statement->bindParam(1, $fi);

            $query_statement->execute();

            return $query_statement;
        } catch (\Throwable $err) {
            file_put_contents('php://stderr', print_r('ERROR in getFarmerRefferalByID(): ' . $err->getMessage() . "\n", TRUE));
            return $err->getMessage();
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
            LEFT JOIN 
            finance_application_statuses 
            ON 
            finance_applications.id = finance_application_statuses.finance_application_id 
            LEFT JOIN
            farms
            ON
            finance_applications.farmid = farms.id
            WHERE finance_applications.farmerid = ?
            AND farms.deleted = false';

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

    public function saveFieldAgentFarmVisit($fieldagentid, 
    $farmerid,    $dateofvisit,    $farmid,    $farmvisittype,    $didchickengainnecessaryrequiredweight,    $numberofdeadchickensincelastvisit,    $totalmortalitytodate,    $additionalobservations,    
    $advicegiventofarmer,    $dateofnextvisit,    $numberofchickenthatcanfitthecurrentchickenhouse,
    $farmerhousebuildingmaterial,    $numberoffinancedchicken,
    $farmernumberofchildren,    $farmernumberofchildrenlessthan18,    $farmernumberofoccupants,
    $numberofpeopleworkingonfarm,    $farmermobiledevicetype,
    $numberofchickenaddedbysupplierondelivery,    $numberofdeadchicksondayofdelivery,    $nameofinsurer,    $datefarmercanstartfarmingwithus,    
    $otherfarmedanimals,    $opinionofhowmanychickenweshouldfinancefarmerfor,    $howmuchfinancingisthefarmerseeking, $isfarmingontrack,
    $doesfarmerhavepreviousfarmingrecords, $takencopiesorphotosoffarmerpreviousfarmingrecords, $farmerchickenhousebuildingmaterial, $doesfarmerhaveexistinginsurance,
    $seenevidenceofexistinginsurance, $didfarmerfillcicinsuranceformcorrectly, $hasfarmerobtainedstampedvetreportwithvetregistrationnumber,
    $takencopiesoffarmeridsordocumentsandphonenumber, $doesfarmerkeeplayers, $seenproofthatfarmerhasbuyers, $farmerpobox,
    $farmerproofofbuyersfileinput,
    $farmerpincertfileinput,
    $farmeridfileinput,
    $farmerexistinginsurancefileinput,
    $farmerpreviousfarmingrecordsfileinput,
    $otheranimalkeptinfarm, $givenfarmerthecicinsuranceformtofill
    )
    {
        try {
            $query = 'INSERT INTO `fieldagents_farm_visits`
                SET
                `fieldagentid` = :_fieldagentid, 
                `farmerid` = :_farmerid,
                `farmid` = :_farmid,
                `farmvisittype` = :_farmvisittype,
                `farmerhousebuildingmaterial` = :_farmerhousebuildingmaterial,
                `dateofvisit` = :_dateofvisit,
                `didchickengainnecessaryrequiredweight` = :_didchickengainnecessaryrequiredweight, 
                `numberofdeadchickensincelastvisit` = :_numberofdeadchickensincelastvisit,
                `totalmortalitytodate` = :_totalmortalitytodate,
                `additionalobservations` = :_additionalobservations,
                `advicegiventofarmer` = :_advicegiventofarmer,
                `dateofnextvisit` = :_dateofnextvisit,
                `numberofchickenthatcanfitthecurrentchickenhouse` = :_numberofchickenthatcanfitthecurrentchickenhouse, 
                `numberoffinancedchicken` = :_numberoffinancedchicken,
                `farmernumberofchildren` = :_farmernumberofchildren,
                `farmernumberofchildrenlessthan18` = :_farmernumberofchildrenlessthan18,
                `farmernumberofoccupants` = :_farmernumberofoccupants,
                `numberofpeopleworkingonfarm` = :_numberofpeopleworkingonfarm,
                `farmermobiledevicetype` = :_farmermobiledevicetype,
                `numberofchickenaddedbysupplierondelivery` = :_numberofchickenaddedbysupplierondelivery, 
                `numberofdeadchicksondayofdelivery` = :_numberofdeadchicksondayofdelivery,
                `nameofinsurer` = :_nameofinsurer,
                `datefarmercanstartfarmingwithus` = :_datefarmercanstartfarmingwithus, 
                `otherfarmedanimals` = :_otherfarmedanimals,
                `opinionofhowmanychickenweshouldfinancefarmerfor` = :_opinionofhowmanychickenweshouldfinancefarmerfor, 
                `howmuchfinancingisthefarmerseeking` = :_howmuchfinancingisthefarmerseeking,
                isfarmingontrack = :_isfarmingontrack,
                doesfarmerhavepreviousfarmingrecords = :_doesfarmerhavepreviousfarmingrecords,
                takencopiesorphotosoffarmerpreviousfarmingrecords = :_takencopiesorphotosoffarmerpreviousfarmingrecords,
                farmerchickenhousebuildingmaterial = :_farmerchickenhousebuildingmaterial,
                doesfarmerhaveexistinginsurance = :_doesfarmerhaveexistinginsurance,
                seenevidenceofexistinginsurance = :_seenevidenceofexistinginsurance,
                didfarmerfillcicinsuranceformcorrectly = :_didfarmerfillcicinsuranceformcorrectly,
                hasfarmerobtainedstampedvetreportwithvetregistrationnumber = :_hasfarmerobtainedstampedvetreportwithvetregistrationnumber,
                takencopiesoffarmeridsordocumentsandphonenumber = :_takencopiesoffarmeridsordocumentsandphonenumber,
                doesfarmerkeeplayers = :_doesfarmerkeeplayers,
                seenproofthatfarmerhasbuyers = :_seenproofthatfarmerhasbuyers,
                farmerpobox = :_farmerpobox,
                otheranimalkeptinfarm = :_otheranimalkeptinfarm,
                givenfarmerthecicinsuranceformtofill = :_givenfarmerthecicinsuranceformtofill
            ';

            $stmt = $this->database_connection->prepare($query);

            // Ensure safe data
            $faid = htmlspecialchars(strip_tags($fieldagentid));
            $fi = htmlspecialchars(strip_tags($farmerid));
            $fid = htmlspecialchars(strip_tags($farmid));
            $fvt = htmlspecialchars(strip_tags($farmvisittype));



            // data can be NULL
            $nocabsod = empty($numberofchickenaddedbysupplierondelivery) ? NULL : htmlspecialchars(strip_tags(str_replace(',', '', $numberofchickenaddedbysupplierondelivery)));
            $dcgnrw = empty($didchickengainnecessaryrequiredweight) ? NULL : htmlspecialchars(strip_tags($didchickengainnecessaryrequiredweight));
            $addob = empty($additionalobservations) ? NULL : htmlspecialchars(strip_tags($additionalobservations));
            $fhbm = empty($farmerhousebuildingmaterial) ? NULL : htmlspecialchars(strip_tags($farmerhousebuildingmaterial));

            $oohmcwsfff = empty($opinionofhowmanychickenweshouldfinancefarmerfor) ? NULL : htmlspecialchars(strip_tags($opinionofhowmanychickenweshouldfinancefarmerfor));
            $fmdt = empty($farmermobiledevicetype) ? NULL : htmlspecialchars(strip_tags($farmermobiledevicetype));
            $agtf = empty($advicegiventofarmer) ? NULL : htmlspecialchars(strip_tags($advicegiventofarmer));

            $noi = empty($nameofinsurer) ? NULL : htmlspecialchars(strip_tags($nameofinsurer));
            $tcopor = empty($takencopiesorphotosoffarmerpreviousfarmingrecords) ? NULL : htmlspecialchars(strip_tags($takencopiesorphotosoffarmerpreviousfarmingrecords));

            
            $dfhei = empty($doesfarmerhaveexistinginsurance) ? NULL : htmlspecialchars(strip_tags($doesfarmerhaveexistinginsurance));
            
         
            $nodcslv = empty($numberofdeadchickensincelastvisit) ? NULL : htmlspecialchars(strip_tags(str_replace(',', '', $numberofdeadchickensincelastvisit)));
            
            $tmtd = empty($totalmortalitytodate) ? NULL : htmlspecialchars(strip_tags(str_replace(',', '', $totalmortalitytodate)));
            $noctcftcch = empty($numberofchickenthatcanfitthecurrentchickenhouse) ? NULL : htmlspecialchars(strip_tags(str_replace(',', '', $numberofchickenthatcanfitthecurrentchickenhouse)));
            $nofc = empty($numberoffinancedchicken) ? NULL : htmlspecialchars(strip_tags(str_replace(',', '', $numberoffinancedchicken)));

            $fnoc = empty($farmernumberofchildren) ? NULL : htmlspecialchars(strip_tags(str_replace(',', '', $farmernumberofchildren)));
            $fnoclt18 = empty($farmernumberofchildrenlessthan18) ? NULL : htmlspecialchars(strip_tags(str_replace(',', '', $farmernumberofchildrenlessthan18)));
            $fnoo = empty($farmernumberofoccupants) ? NULL : htmlspecialchars(strip_tags(str_replace(',', '', $farmernumberofoccupants)));
            $nopwof = empty($numberofpeopleworkingonfarm) ? NULL : htmlspecialchars(strip_tags(str_replace(',', '', $numberofpeopleworkingonfarm)));

            $nodcodod = empty($numberofdeadchicksondayofdelivery) ? NULL : htmlspecialchars(strip_tags(str_replace(',', '', $numberofdeadchicksondayofdelivery)));

            $hmfstfs = empty($howmuchfinancingisthefarmerseeking) ? NULL : htmlspecialchars(strip_tags(str_replace(',', '', $howmuchfinancingisthefarmerseeking)));

            $ifot = empty($isfarmingontrack) ? NULL : htmlspecialchars(strip_tags($isfarmingontrack));
            $dfhpfr = empty($doesfarmerhavepreviousfarmingrecords) ? NULL : htmlspecialchars(strip_tags($doesfarmerhavepreviousfarmingrecords));

            $mfchimo = empty($farmerchickenhousebuildingmaterial) ? NULL : htmlspecialchars(strip_tags($farmerchickenhousebuildingmaterial));
            $svoei = empty($seenevidenceofexistinginsurance) ? NULL : htmlspecialchars(strip_tags($seenevidenceofexistinginsurance));

            $dffcifc = empty($didfarmerfillcicinsuranceformcorrectly) ? NULL : htmlspecialchars(strip_tags($didfarmerfillcicinsuranceformcorrectly));

            $hfosvrwvrn = empty($hasfarmerobtainedstampedvetreportwithvetregistrationnumber) ? NULL : htmlspecialchars(strip_tags($hasfarmerobtainedstampedvetreportwithvetregistrationnumber));
            $tcofiodapn = empty($takencopiesoffarmeridsordocumentsandphonenumber) ? NULL : htmlspecialchars(strip_tags($takencopiesoffarmeridsordocumentsandphonenumber));

            $dfkl = empty($doesfarmerkeeplayers) ? NULL : htmlspecialchars(strip_tags($doesfarmerkeeplayers));

            $sptfhb = empty($seenproofthatfarmerhasbuyers) ? NULL : htmlspecialchars(strip_tags($seenproofthatfarmerhasbuyers));
            $fpob = empty($farmerpobox) ? NULL : htmlspecialchars(strip_tags($farmerpobox));
            $oakif = empty($otheranimalkeptinfarm) ? NULL : htmlspecialchars(strip_tags($otheranimalkeptinfarm));

            $gftcicftf = empty($givenfarmerthecicinsuranceformtofill) ? NULL : htmlspecialchars(strip_tags($givenfarmerthecicinsuranceformtofill));

            

            
            $dov = NULL;
            if (!empty($dateofvisit) && isset($dateofvisit)) {
                $date1 = new DateTime($dateofvisit); // Seems this isn't doing timezone conversion and is not accurate
                $dov = htmlspecialchars(strip_tags($date1->format('Y-m-d H:i:s')));
            }

            $donv = NULL;
            if (!empty($dateofnextvisit) && isset($dateofnextvisit)) {
                $date2 = new DateTime($dateofnextvisit); // Seems this isn't doing timezone conversion and is not accurate
                $donv = htmlspecialchars(strip_tags($date2->format('Y-m-d H:i:s')));
            }

            $dfcsfwu = NULL;
            if (!empty($datefarmercanstartfarmingwithus) && isset($datefarmercanstartfarmingwithus)) {
                $date3 = new DateTime($datefarmercanstartfarmingwithus); // Seems this isn't doing timezone conversion and is not accurate
                $dfcsfwu = htmlspecialchars(strip_tags($date3->format('Y-m-d H:i:s')));
            }
            


            $ofa = NULL;
            if (is_array($otherfarmedanimals)) {
                $fi = htmlspecialchars(strip_tags(implode(",", $otherfarmedanimals)));
            } else { // they are strings

                file_put_contents('php://stderr', print_r("\n\n" . 'see this in converint(): ' . serialize($otherfarmedanimals) . "\n", TRUE));

                $ofa = htmlspecialchars(serialize($otherfarmedanimals));
            }

            // Bind parameters to prepared stmt
            $stmt->bindParam(':_fieldagentid', $faid);
            $stmt->bindParam(':_numberofchickenaddedbysupplierondelivery', $nocabsod);
            $stmt->bindParam(':_farmvisittype', $fvt);
            $stmt->bindParam(':_didchickengainnecessaryrequiredweight', $dcgnrw);
            $stmt->bindParam(':_additionalobservations', $addob);
            $stmt->bindParam(':_farmid', $fid);
            $stmt->bindParam(':_farmerhousebuildingmaterial', $fhbm);

            $stmt->bindParam(':_farmerid', $fi);
            $stmt->bindParam(':_opinionofhowmanychickenweshouldfinancefarmerfor', $oohmcwsfff);
            $stmt->bindParam(':_farmermobiledevicetype', $fmdt);
            $stmt->bindParam(':_advicegiventofarmer', $agtf);
            $stmt->bindParam(':_nameofinsurer', $noi);
            $stmt->bindParam(':_numberofdeadchickensincelastvisit',  $nodcslv);
            $stmt->bindParam(':_otherfarmedanimals',  $ofa);

            $stmt->bindParam(':_totalmortalitytodate',  $tmtd);
            $stmt->bindParam(':_numberofchickenthatcanfitthecurrentchickenhouse',  $noctcftcch);
            $stmt->bindParam(':_numberoffinancedchicken',  $nofc);
            $stmt->bindParam(':_farmernumberofchildren',  $fnoc);
            $stmt->bindParam(':_farmernumberofchildrenlessthan18',  $fnoclt18);
            $stmt->bindParam(':_farmernumberofoccupants',  $fnoo);
            $stmt->bindParam(':_numberofpeopleworkingonfarm',  $nopwof);
            $stmt->bindParam(':_numberofdeadchicksondayofdelivery',  $nodcodod);
            $stmt->bindParam(':_howmuchfinancingisthefarmerseeking',  $hmfstfs);
            
            $stmt->bindParam(':_dateofvisit', $dov);
            $stmt->bindParam(':_datefarmercanstartfarmingwithus',  $dfcsfwu);
            $stmt->bindParam(':_dateofnextvisit',  $donv);
            $stmt->bindParam(':_isfarmingontrack',  $ifot);
            $stmt->bindParam(':_doesfarmerhavepreviousfarmingrecords',  $dfhpfr);
            $stmt->bindParam(':_takencopiesorphotosoffarmerpreviousfarmingrecords',  $tcopor);


            $stmt->bindParam(':_farmerchickenhousebuildingmaterial',  $mfchimo);
            $stmt->bindParam(':_doesfarmerhaveexistinginsurance',  $dfhei);
            $stmt->bindParam(':_didfarmerfillcicinsuranceformcorrectly',  $dffcifc);
             
            $stmt->bindParam(':_seenevidenceofexistinginsurance',  $svoei);

            $stmt->bindParam(':_hasfarmerobtainedstampedvetreportwithvetregistrationnumber',  $hfosvrwvrn);

            $stmt->bindParam(':_takencopiesoffarmeridsordocumentsandphonenumber',  $tcofiodapn);
            $stmt->bindParam(':_doesfarmerkeeplayers',  $dfkl);

            $stmt->bindParam(':_seenproofthatfarmerhasbuyers',  $sptfhb);
            $stmt->bindParam(':_farmerpobox',  $fpob);

            $stmt->bindParam(':_otheranimalkeptinfarm',  $oakif);

            $stmt->bindParam(':_givenfarmerthecicinsuranceformtofill',  $gftcicftf);
             
             
             

            $r = $stmt->execute();

            if ($r) {
                $last_insert_id = $this->database_connection->lastInsertId();
                // file upload status results array 
                $fileuploadstatusresult = array();
                if ($farmerproofofbuyersfileinput && !empty($farmerproofofbuyersfileinput)) { // check for empty string or array, if string or array
                    $upload1 = $this->handleFileUpload($farmerproofofbuyersfileinput, $last_insert_id, 'fieldagents_farm_visits', $fi, 'farmerproofofbuyersfileinput');
                    array_push($fileuploadstatusresult, $upload1);
                }
                if ($farmerpincertfileinput && !empty($farmerpincertfileinput)) { // check for empty string or array, if string or array
                    $upload2 = $this->handleFileUpload($farmerpincertfileinput, $last_insert_id, 'fieldagents_farm_visits', $fi, 'farmerpincertfileinput');
                    array_push($fileuploadstatusresult, $upload2);
                }
                if ($farmeridfileinput && !empty($farmeridfileinput)) { // check for empty string or array, if string or array
                    $upload3 = $this->handleFileUpload($farmeridfileinput, $last_insert_id, 'fieldagents_farm_visits', $fi, 'farmeridfileinput');
                    array_push($fileuploadstatusresult, $upload3);
                }
                if ($farmerexistinginsurancefileinput && !empty($farmerexistinginsurancefileinput)) { // check for empty string or array, if string or array
                    $upload4 = $this->handleFileUpload($farmerexistinginsurancefileinput, $last_insert_id, 'fieldagents_farm_visits', $fi, 'farmerexistinginsurancefileinput');
                    array_push($fileuploadstatusresult, $upload4);
                }
                if ($farmerpreviousfarmingrecordsfileinput && !empty($farmerpreviousfarmingrecordsfileinput)) { // check for empty string or array, if string or array
                    $upload5 = $this->handleFileUpload($farmerpreviousfarmingrecordsfileinput, $last_insert_id, 'fieldagents_farm_visits', $fi, 'farmerpreviousfarmingrecordsfileinput');
                    array_push($fileuploadstatusresult, $upload5);
                }

                if (in_array(false, $fileuploadstatusresult, true)) {

                    file_put_contents('php://stderr', print_r("\n\n\none of the uploads failed.\n\n", TRUE));
                    return false;
                } else {
                    return $last_insert_id;
                }
                

            } else {
                return false;
            }
        } catch (\Throwable $err) {
            file_put_contents('php://stderr', print_r( $err->getMessage(), TRUE));
            file_put_contents('php://stderr', print_r("\n\n" . 'ERROR in saveFieldAgentFarmVisit(): ' . $err->getMessage() . "\n", TRUE));
            return false;
        }
    }

    public function getAllFarmerFinanceApplications($farmerid)
    {
        try {
            $query = 'SELECT * FROM input_records_mortalities
                WHERE
                farmerid = :_farmerid
                ORDER BY 
                date DESC
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
}
