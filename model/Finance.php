<?php

class Finance
{
    // DB stuff
    public $database_connection;
    private $table = 'finance_applications';

    /**
     * Constructor taking db as params
     */
    public function __construct($a_database_connection)
    {
        $this->database_connection = $a_database_connection;
    }

    public function getSingleFarmerFinanceApplicationByID($id)
    {
        $query = 'SELECT 
        fa.`farmerid`, 
        fa.`farmid`, 
        fa.`id`, 
        fa.created_on, 
        fas.status 
        FROM 
        `finance_applications` fa
        LEFT JOIN 
        finance_application_statuses fas
        ON 
        fa.id = fas.finance_application_id 
        WHERE fa.id = ?';

        // if fas.status is null, replace with 'NULL'

        // Prepare statement
        $query_statement = $this->database_connection->prepare($query);

        // Execute query statement
        $query_statement->bindParam(1, $id);

        // Execute query statement
        $query_statement->execute();

        return $query_statement;
    }

    public function newFinanceRegisteration(
        $farmerid,
        $farmid,
        $farmbirdcapacity,
        $currentfarmproduction,
        $averagemortalityrate,
        $numberofchickensmoneyisfor,
        $numberofstaff,
        $preferredchickssupplier,
        $preferredfeedsssupplier,
        $otherpreferredchickssupplier,
        $otherpreferredfeedsssupplier,
        $howmuchrequired,
        $chickscost,
        $feedscost,
        $broodingcost,
        $dateneeded,
        $medicinesandvaccinescost,
        $projectedsales
    ) {

        try {
            // Create query

            $query = 'INSERT INTO `finance_applications`
                SET
                farmerid = :farmerid, 
                farmid = :farmid,
                farmbirdcapacity = :farmbirdcapacity,
                currentfarmproduction = :currentfarmproduction,
                averagemortalityrate = :averagemortalityrate,
                numberofchickensmoneyisfor = :numberofchickensmoneyisfor,
                numberofstaff = :numberofstaff,
                preferredchickssupplier = :preferredchickssupplier,
                preferredfeedsssupplier = :preferredfeedsssupplier,
                otherpreferredchickssupplier = :otherpreferredchickssupplier,
                otherpreferredfeedsssupplier = :otherpreferredfeedsssupplier,
                howmuchrequired = :howmuchrequired,
                chickscost = :chickscost,
                feedscost = :feedscost,
                broodingcost = :broodingcost,
                dateneeded = :dateneeded,
                medicinesandvaccinescost = :medicinesandvaccinescost,
                projectedsales = :projectedsales
            '; 

            // ,
            // created_on = :created_on,
            // id = :id
            
            // NOW() or UNIX_TIMESTAMP() // https://stackoverflow.com/a/14849994/9259701

            // Prepare statement
            $stmt = $this->database_connection->prepare($query);

            // Ensure safe data
            $fi = htmlspecialchars(strip_tags($farmerid));
            $fid = htmlspecialchars(strip_tags($farmid));
            $fbc = htmlspecialchars(strip_tags(str_replace(',', '', $farmbirdcapacity)));
            $cfp = htmlspecialchars(strip_tags(str_replace(',', '', $currentfarmproduction)));
            $amr = htmlspecialchars(strip_tags($averagemortalityrate));
            $nocmif = htmlspecialchars(strip_tags(str_replace(',', '', $numberofchickensmoneyisfor)));
            $nos = htmlspecialchars(strip_tags(str_replace(',', '', $numberofstaff)));
            $pcs = htmlspecialchars(strip_tags($preferredchickssupplier));
            $pfs = htmlspecialchars(strip_tags($preferredfeedsssupplier));
            $opcs = htmlspecialchars(strip_tags($otherpreferredchickssupplier));
            $opfs = htmlspecialchars(strip_tags($otherpreferredfeedsssupplier));
            $hmr = htmlspecialchars(strip_tags(str_replace(',', '', $howmuchrequired)));
            $cc = htmlspecialchars(strip_tags(str_replace(',', '', $chickscost)));
            $fc = htmlspecialchars(strip_tags(str_replace(',', '', $feedscost)));
            $bc = htmlspecialchars(strip_tags(str_replace(',', '', $broodingcost)));
            $mavc = htmlspecialchars(strip_tags(str_replace(',', '', $medicinesandvaccinescost)));

            $date1 = new DateTime($dateneeded);
            $dn = htmlspecialchars(strip_tags($date1->format('Y-m-d H:i:s')));

            file_put_contents('php://stderr', print_r( "dn is $dn", TRUE));
            
            $ps = htmlspecialchars(strip_tags(str_replace(',', '', $projectedsales)));

            // Bind parameters to prepared stmt
            $stmt->bindParam(':farmerid', $fi);
            $stmt->bindParam(':farmid', $fid);
            $stmt->bindParam(':farmbirdcapacity', $fbc);
            $stmt->bindParam(':currentfarmproduction', $cfp);
            $stmt->bindParam(':averagemortalityrate', $amr);
            $stmt->bindParam(':numberofchickensmoneyisfor', $nocmif);
            $stmt->bindParam(':numberofstaff', $nos);
            $stmt->bindParam(':preferredchickssupplier', $pcs);
            $stmt->bindParam(':preferredfeedsssupplier', $pfs);
            if ($opcs) {
                $stmt->bindParam(':otherpreferredchickssupplier', $opcs);
            } else {
                $stmt->bindValue(':otherpreferredchickssupplier', NULL);
            }
            if ($opfs) {
                $stmt->bindParam(':otherpreferredfeedsssupplier', $opfs);
            } else {
                $stmt->bindValue(':otherpreferredfeedsssupplier', NULL);
            }
            
            $stmt->bindParam(':howmuchrequired', $hmr);
            $stmt->bindParam(':chickscost', $cc);
            $stmt->bindParam(':feedscost', $fc);
            $stmt->bindParam(':broodingcost', $bc);
            $stmt->bindParam(':medicinesandvaccinescost', $mavc);

            $stmt->bindParam(':dateneeded', $dn);
            $stmt->bindParam(':projectedsales', $ps);

            // $_idd = 2242; $rrr = NULL;
            // $stmt->bindParam(':created_on', $rrr);
            // $stmt->bindParam(':id', $_idd);




            // file_put_contents('php://stderr', print_r( $stmt->queryString, TRUE));
            // file_put_contents('php://stderr', print_r( $stmt->debugDumpParams(), TRUE));
            

            // Execute query statement
            if ($stmt->execute()) {
                return $this->database_connection->lastInsertId();
                // return true;
            } else {
                return false;
            }
        } catch (\Throwable $err) {

            file_put_contents('php://stderr', print_r( "ERRRRRSSS" . "\n\n\n", TRUE));

            file_put_contents('php://stderr', print_r( $err, TRUE));
            
            file_put_contents('php://stderr', print_r('ERROR Trying to do finance registration for farmer: ' . $err->getMessage() . "\n", TRUE));
            return $err;// ->getMessage();
        }
    }

    public function updateFinanceRegistrationStatus($lastupdateby, $status, $finance_application_id, $reason) {
        try {
            // Create query
            $query = 'UPDATE finance_application_statuses 
                SET 
                lastupdateby = :lastupdateby,
                status = :status,
                reason = :reason
                WHERE
                finance_application_id = :finance_application_id
            ';

            // Prepare statement
            $stmt = $this->database_connection->prepare($query);

            // Ensure safe data
            $lub = htmlspecialchars(strip_tags($lastupdateby));
            $s = htmlspecialchars(strip_tags($status));
            $faid = htmlspecialchars(strip_tags($finance_application_id));
            $r = htmlspecialchars(strip_tags($reason));

            // Bind parameters to prepared stmt
            $stmt->bindParam(':lastupdateby', $lub);
            $stmt->bindParam(':status', $s);
            $stmt->bindParam(':finance_application_id', $faid);
            $stmt->bindParam(':reason', $r);

            // Execute query statement
            if ($stmt->execute()) {
                file_put_contents('php://stderr', print_r('Executed updateFinanceRegistrationStatus query' . "\n", TRUE));
                return true;
            } else {
                return false;
            }
        } catch (\Throwable $err) {
            
        }
    }

    public function selectSingleFinanceRegistrationStatusByID($finance_application_id) {
        try {
            // Create query
        $query = 'SELECT * 
            FROM finance_applications 
            LEFT JOIN 
            finance_application_statuses 
            ON 
            finance_applications.id = finance_application_statuses.finance_application_id
            WHERE
            finance_applications.id = ?
            ';

        // Prepare statement
        $query_statement = $this->database_connection->prepare($query);

        // Execute query statement
        $query_statement->bindParam(1, $finance_application_id);

        // Execute query statement
        $query_statement->execute();

        return $query_statement;
        } catch (\Throwable $err) {
            file_put_contents('php://stderr', print_r('ERROR running Finance.php -> selectSingleFinanceRegistrationStatusByID(): ' . $err->getMessage() . "\n", TRUE));
            return $err;
        }
    }

    public function getFarmerEmailAndFirstnameFromFinanceApplicationID($fin_app_id)
    {
        try {
            $query = "SELECT farmers.firstname, farmers.email, DATE_FORMAT(finance_applications.created_on, '%W the %D of %M %Y') AS created_on FROM `finance_applications`
                        LEFT JOIN farmers
                        ON farmers.id = finance_applications.farmerid
                        WHERE finance_applications.id = ?
            ";

            // Prepare statement
            $query_statement = $this->database_connection->prepare($query);

            // Execute query statement
            $query_statement->bindParam(1, $fin_app_id);

            // Execute query statement
            $query_statement->execute();

            return $query_statement;
        } catch (\Throwable $th) {
            //throw $th;
        }

    }
}
