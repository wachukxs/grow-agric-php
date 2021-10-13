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

    // Create new order, an entry. DONE?
    public function createFarm($challengesfaced, $farmcitytownlocation, $farmcountylocation, $farmeditems, $haveinsurance, $insurer, $numberofemployees, $otherchallengesfaced, $otherfarmeditems, $yearsfarming, $farmerid) {
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
            farmerid = :farmerid
        ';

        // Prepare the query statement
        $stmt = $this->database_connection->prepare($query);

        // Ensure safe data
        $cf = htmlspecialchars(strip_tags(implode(", ", $challengesfaced))); // conver the array to strings
        $fi = htmlspecialchars(strip_tags(implode(", ", $farmeditems)));
        $fctl = htmlspecialchars(strip_tags($farmcitytownlocation));
        $fcl = htmlspecialchars(strip_tags($farmcountylocation));
        $hin = htmlspecialchars(strip_tags($haveinsurance));
        $in = htmlspecialchars(strip_tags($insurer));
        $noe = htmlspecialchars(strip_tags($numberofemployees));
        $ocf = htmlspecialchars(strip_tags($otherchallengesfaced));
        $ofi = htmlspecialchars(strip_tags($otherfarmeditems));
        $yf = htmlspecialchars(strip_tags($yearsfarming));
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
        $stmt->bindParam(':farmerid', $fid);

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
            file_put_contents('php://stderr', print_r('Farm.php->createFarm error: ' . $err->getMessage() . "\n", TRUE));
            return false; // $err->getMessage();
            // throw $th;
        }

        
    }

    // getSingleFarmByID
    public function getSingleFarmByID($id)
    {
        $query = 'SELECT * FROM ' . $this->table . ' WHERE id = ?';

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
    public function updateFarmByID($challengesfaced, $farmcitytownlocation, $farmcountylocation, $farmeditems, $haveinsurance, $insurer, $numberofemployees, $otherchallengesfaced, $otherfarmeditems, $yearsfarming, $id)
    {
        try {
            // Create query
            $query = 'UPDATE ' . $this->table . ' 
                SET 
                challengesfaced = :challengesfaced,
                farmcitytownlocation = :farmcitytownlocation,
                farmcountylocation = :farmcountylocation,
                farmeditems = :farmeditems,
                haveinsurance = :haveinsurance
                insurer = :insurer,
                numberofemployees = :numberofemployees,
                otherchallengesfaced = :otherchallengesfaced,
                otherfarmeditems = :otherfarmeditems,
                yearsfarming = :yearsfarming
                WHERE
                id = :id
            ';

            // Prepare statement
            $stmt = $this->database_connection->prepare($query);

            // Ensure safe data
            $cf = htmlspecialchars(strip_tags($challengesfaced));
            $fctl = htmlspecialchars(strip_tags($farmcitytownlocation));
            $fcl = htmlspecialchars(strip_tags($farmcountylocation));
            $fi = htmlspecialchars(strip_tags($farmeditems));
            $hin = htmlspecialchars(strip_tags($haveinsurance));
            $in = htmlspecialchars(strip_tags($insurer));
            $noe = htmlspecialchars(strip_tags($numberofemployees));
            $ocf = htmlspecialchars(strip_tags($otherchallengesfaced));
            $ofi = htmlspecialchars(strip_tags($otherfarmeditems));
            $yf = htmlspecialchars(strip_tags($yearsfarming));
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
        }

    }

}