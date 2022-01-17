<?php
class Farm {
    // DB stuff
    public $database_connection;
    private $table = 'farms';

    /**
     * Constructor taking db as params
     */
    public function __construct($a_database_connection)
    {
        $this->database_connection = $a_database_connection;
    }

    public function deleteFarm($farmid) {
        try {
            // Create query
            $query = 'DELETE FROM farms 
                WHERE
                id = ?
            ';

            // Prepare statement
            $query_statement = $this->database_connection->prepare($query);

            // Ensure safe data
            $i = htmlspecialchars(strip_tags($farmid));

            // Bind parameters to prepared stmt
            $query_statement->bindParam(1, $i);

            // Execute query statement
            if ($query_statement->execute()) {
                return true;
            } else {
                return false;
            }
        } catch (\Throwable $err) {
            //throw $err;
            file_put_contents('php://stderr', print_r('ERROR in deleteFarm(): ' . $err->getMessage() . "\n", TRUE));
            return false; // $err->getMessage(); 
        }
    }

    public function fakeDeleteFarm($farmid) {
        try {
            // Create query
            $query = 'UPDATE ' . $this->table . ' 
                SET 
                deleted = true
                WHERE
                id = ?
            ';

            // Prepare statement
            $query_statement = $this->database_connection->prepare($query);

            // Ensure safe data
            $i = htmlspecialchars(strip_tags($farmid));

            // Bind parameters to prepared stmt
            $query_statement->bindParam(1, $i);

            // Execute query statement
            if ($query_statement->execute()) {
                return true;
            } else {
                return false;
            }
        } catch (\Throwable $err) {
            //throw $err;
            file_put_contents('php://stderr', print_r('ERROR in fakeDeleteFarm(): ' . $err->getMessage() . "\n", TRUE));
            return false; // $err->getMessage(); 
        }
    }

    // Create new order, an entry. DONE?
    public function createFarm($challengesfaced, $farmcitytownlocation, $farmcountylocation, $farmeditems, $haveinsurance, $insurer, $numberofemployees, $otherchallengesfaced, $otherfarmeditems, $yearsfarming, $farmerid, $multiplechickenhouses) {
        $query = 'INSERT INTO ' . $this->table . '
            SET
            challengesfaced = :challengesfaced,
            farmcitytownlocation = :farmcitytownlocation,
            farmcountylocation = :farmcountylocation,
            farmeditems = :farmeditems,
            haveinsurance = :haveinsurance,
            insurer = :insurer,
            numberofemployees = :numberofemployees,
            otherchallengesfaced = :otherchallengesfaced,
            otherfarmeditems = :otherfarmeditems,
            yearsfarming = :yearsfarming,
            farmerid = :farmerid,
            multiplechickenhouses = :multiplechickenhouses
        ';

        // Prepare the query statement
        $stmt = $this->database_connection->prepare($query);

        // Ensure safe data
        $cf = ''; // convert the array to strings
        $fi = '';
        if (is_array($farmeditems)) {
            $fi = htmlspecialchars(strip_tags(implode(",", $farmeditems)));
        } else { // they are strings
            $fi = htmlspecialchars(strip_tags($farmeditems));
        }

        if (is_array($challengesfaced)) {
            $cf = htmlspecialchars(strip_tags(implode(",", $challengesfaced))); // convert the array to strings
        } else { // they are strings
            $cf = htmlspecialchars(strip_tags($challengesfaced));
        }
        
        $fctl = htmlspecialchars(strip_tags($farmcitytownlocation));
        $fcl = htmlspecialchars(strip_tags($farmcountylocation));
        $hin = htmlspecialchars(strip_tags($haveinsurance));
        $in = htmlspecialchars(strip_tags($insurer));
        $noe = htmlspecialchars(strip_tags($numberofemployees));
        $ocf = htmlspecialchars(strip_tags($otherchallengesfaced));
        $ofi = htmlspecialchars(strip_tags($otherfarmeditems));
        $yf = htmlspecialchars(strip_tags($yearsfarming));
        $mch = htmlspecialchars(strip_tags($multiplechickenhouses));
        $fid = htmlspecialchars(strip_tags($farmerid));

        // Bind parameters to prepared stmt
        $stmt->bindParam(':challengesfaced', $cf);
        $stmt->bindParam(':farmcitytownlocation', $fctl);
        $stmt->bindParam(':farmeditems', $fi);
        $stmt->bindParam(':insurer', $in);
        $stmt->bindParam(':haveinsurance', $hin);
        $stmt->bindParam(':numberofemployees', $noe);
        $stmt->bindParam(':farmcountylocation', $fcl);
        $stmt->bindParam(':otherchallengesfaced', $ocf);
        $stmt->bindParam(':yearsfarming', $yf);
        $stmt->bindParam(':otherfarmeditems', $ofi);
        $stmt->bindParam(':multiplechickenhouses', $mch);
        $stmt->bindParam(':farmerid', $fid);

        try {
            $r = $stmt->execute(); // returns true/false
            if ($r) {
                file_put_contents('php://stderr', print_r("\n\n\n\n\n\n" . 'last insert id ' . $this->database_connection->lastInsertId() . "\n", TRUE));
                return $this->database_connection->lastInsertId();
                // return $this.getSingleOrderByID($this->database_connection->lastInsertId());
            } else {
                file_put_contents('php://stderr', print_r('Farm.php->createFarm error: ' . $this->database_connection->errorInfo() . "\n", TRUE));
                // echo $this->database_connection->errorInfo();
                return $this->database_connection->errorInfo(); // false;
            }
        } catch (\PDOException $err) {
            file_put_contents('php://stderr', print_r('Farm.php->createFarm error: ' . $err->getMessage() . "\n", TRUE));
            return false; // $err->getMessage();
            // throw $th;
        }

        
    }

    public function updateFarmChickenHouses($chickenhousename, $farmid, $startdate, $chickenhouseid)
    {
        try {
            // Create query
            $query = 'UPDATE farm_chicken_houses 
                SET 
                name = :name,
                farmid = :farmid,
                startdate = :startdate
                WHERE
                id = :id
            ';

            // Prepare statement
            $stmt = $this->database_connection->prepare($query);

            // Ensure safe data
            $date1 = new DateTime($startdate); // Seems this isn't doing timezone conversion and is not accurate
            $sd = htmlspecialchars(strip_tags($date1->format('Y-m-d H:i:s')));
            
            $chn = htmlspecialchars(strip_tags($chickenhousename));
            $fid = htmlspecialchars(strip_tags($farmid));
            $_id = htmlspecialchars(strip_tags($chickenhouseid));

            // Bind parameters to prepared stmt
            $stmt->bindParam(':name', $chn);
            $stmt->bindParam(':farmid', $fid);
            $stmt->bindParam(':startdate', $sd);
            $stmt->bindParam(':id', $_id);

            // Execute query statement
            if ($stmt->execute()) {
                file_put_contents('php://stderr', print_r('Executed farm update query' . "\n", TRUE));
                return true;
            } else {
                file_put_contents('php://stderr', print_r('Failed to Execute farm update query' . "\n", TRUE));
                return false;
            }
        } catch (\Throwable $err) {
            // throw $err; $err->getMessage()
            file_put_contents('php://stderr', print_r('Farm.php->updateFarmByID error: ' . $err->getMessage() . "\n", TRUE));
            return false;
        }
    }

    public function addFarmChickenHouses($chickenhousename, $farmid, $startdate)
    {
        try {
            // Create query

            $query = 'INSERT INTO farm_chicken_houses
                SET
                name = :name,
                farmid = :farmid,
                startdate = :startdate
            ';

            // Prepare statement
            $stmt = $this->database_connection->prepare($query);

            // Ensure safe data
            $date1 = new DateTime($startdate); // Seems this isn't doing timezone conversion and is not accurate
            $sd = htmlspecialchars(strip_tags($date1->format('Y-m-d H:i:s')));
            
            $chn = htmlspecialchars(strip_tags($chickenhousename));
            $fid = htmlspecialchars(strip_tags($farmid));

            // Bind parameters to prepared stmt
            $stmt->bindParam(':name', $chn);
            $stmt->bindParam(':farmid', $fid);
            $stmt->bindParam(':startdate', $sd);

            // Execute query statement
            if ($stmt->execute()) {
                file_put_contents('php://stderr', print_r('Executed farm insert query' . "\n", TRUE));
                return true;
            } else {
                file_put_contents('php://stderr', print_r('Failed to Execute addFarmChickenHouses query' . "\n", TRUE));
                return false;
            }
        } catch (\Throwable $err) {
            // throw $err; $err->getMessage()
            file_put_contents('php://stderr', print_r('Farm.php->updateFarmByID error: ' . $err->getMessage() . "\n", TRUE));
            return false;
        }
    }

    public function getAllFarmChickenHousesByFarmID($farmid)
    {
        $query = 'SELECT * FROM farm_chicken_houses WHERE farmid = ? AND deleted = false';

        // Prepare statement
        $query_statement = $this->database_connection->prepare($query);

        // Execute query statement
        $query_statement->bindParam(1, $farmid);

        // Execute query statement
        $query_statement->execute();

        return $query_statement;
    }

    public function getFarmChickenHouseByID($chickenhouseid)
    {
        $query = 'SELECT * FROM farm_chicken_houses WHERE id = ? AND deleted = false';

        // Prepare statement
        $query_statement = $this->database_connection->prepare($query);

        // Execute query statement
        $query_statement->bindParam(1, $chickenhouseid);

        // Execute query statement
        $query_statement->execute();

        return $query_statement;
    }

    public function fakeDeleteFarmChickenHouseByID($chickenhouseid) {
        try {
            // Create query
            $query = 'UPDATE farm_chicken_houses 
                SET 
                deleted = true
                WHERE
                id = ?
            ';

            // Prepare statement
            $query_statement = $this->database_connection->prepare($query);

            // Ensure safe data
            $i = htmlspecialchars(strip_tags($chickenhouseid));

            // Bind parameters to prepared stmt
            $query_statement->bindParam(1, $i);

            // Execute query statement
            if ($query_statement->execute()) {
                return true;
            } else {
                return false;
            }
        } catch (\Throwable $err) {
            //throw $err;
            file_put_contents('php://stderr', print_r('ERROR in fakeDeleteFarm(): ' . $err->getMessage() . "\n", TRUE));
            return false; // $err->getMessage(); 
        }
    }

    // getSingleFarmByID
    public function getSingleFarmByID($id)
    {
        $query = 'SELECT * FROM ' . $this->table . ' WHERE id = ? AND deleted = false';

        // Prepare statement
        $query_statement = $this->database_connection->prepare($query);

        // Execute query statement
        $query_statement->bindParam(1, $id);

        // Execute query statement
        $query_statement->execute();

        return $query_statement;

    }

    // getAllFarmsByFarmerID
    public function getAllFarmsByFarmerID($id)
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
        $query = 'SELECT * FROM ' . $this->table . ' WHERE farmerid = ? AND deleted = false';

        // Prepare statement
        $query_statement = $this->database_connection->prepare($query);

        // Execute query statement
        $query_statement->bindParam(1, $id);

        // Execute query statement
        $query_statement->execute();

        return $query_statement;

    }

    // getSingleFarmerByID
    public function updateFarmByID($challengesfaced, $farmcitytownlocation, $farmcountylocation, $farmeditems, $haveinsurance, $insurer, $numberofemployees, $otherchallengesfaced, $otherfarmeditems, $yearsfarming, $id, $multiplechickenhouses)
    {
        try {
            // Create query
            $query = 'UPDATE ' . $this->table . ' 
                SET 
                challengesfaced = :challengesfaced,
                farmcitytownlocation = :farmcitytownlocation,
                farmcountylocation = :farmcountylocation,
                farmeditems = :farmeditems,
                haveinsurance = :haveinsurance,
                insurer = :insurer,
                numberofemployees = :numberofemployees,
                otherchallengesfaced = :otherchallengesfaced,
                otherfarmeditems = :otherfarmeditems,
                yearsfarming = :yearsfarming,
                multiplechickenhouses = :multiplechickenhouses
                WHERE
                id = :id
            ';

            // Prepare statement
            $stmt = $this->database_connection->prepare($query);

            // Ensure safe data
            $cf = ''; // convert the array to strings
            $fi = '';
            if (is_array($challengesfaced)) {
                $cf = htmlspecialchars(strip_tags(implode(",", $challengesfaced)));
            } else { // they are strings
                $cf = htmlspecialchars(strip_tags($challengesfaced));
            }

            if (is_array($farmeditems)) {
                $fi = htmlspecialchars(strip_tags(implode(",", $farmeditems)));
            } else { // they are strings
                $fi = htmlspecialchars(strip_tags($farmeditems));
            }
            
            $fctl = htmlspecialchars(strip_tags($farmcitytownlocation));
            $fcl = htmlspecialchars(strip_tags($farmcountylocation));
            $hin = htmlspecialchars(strip_tags($haveinsurance));
            $in = htmlspecialchars(strip_tags($insurer));
            $noe = htmlspecialchars(strip_tags($numberofemployees)); // no need for intval
            $ocf = htmlspecialchars(strip_tags($otherchallengesfaced));
            $ofi = htmlspecialchars(strip_tags($otherfarmeditems));
            $yf = htmlspecialchars(strip_tags($yearsfarming));
            $mch = htmlspecialchars(strip_tags($multiplechickenhouses));
            $_id = htmlspecialchars(strip_tags($id));

            // Bind parameters to prepared stmt
            $stmt->bindParam(':challengesfaced', $cf);
            $stmt->bindParam(':farmcitytownlocation', $fctl);
            $stmt->bindParam(':farmcountylocation', $fcl);
            $stmt->bindParam(':farmeditems', $fi);
            $stmt->bindParam(':haveinsurance', $hin);
            $stmt->bindParam(':insurer', $in);
            $stmt->bindParam(':numberofemployees', $noe);
            $stmt->bindParam(':otherchallengesfaced', $ocf);
            $stmt->bindParam(':otherfarmeditems', $ofi);
            $stmt->bindParam(':yearsfarming', $yf);
            $stmt->bindParam(':multiplechickenhouses', $mch);
            $stmt->bindParam(':id', $_id);

            // Execute query statement
            if ($stmt->execute()) {
                file_put_contents('php://stderr', print_r('Executed farm update query' . "\n", TRUE));
                return true;
            } else {
                file_put_contents('php://stderr', print_r('Failed to Execute farm update query' . "\n", TRUE));
                return false;
            }
        } catch (\Throwable $err) {
            // throw $err; $err->getMessage()
            file_put_contents('php://stderr', print_r('Farm.php->updateFarmByID error: ' . $err->getMessage() . "\n", TRUE));
            return false;
        }

    }

}