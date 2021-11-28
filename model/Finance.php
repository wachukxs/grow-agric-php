<?php

class Finance {
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

    public function newFinanceRegisteration($farmerid, $farmid, $farmbirdcapacity, $currentfarmproduction, $averagemortalityrate, 
    $numberofchickensmoneyisfor, $numberofstaff, $preferredchickssupplier, $preferredfeedsssupplier, 
    $otherpreferredchickssupplier,
    $otherpreferredfeedsssupplier, $howmuchrequired, $chickscost, $feedscost, $broodingcost, 
    $vaccinesused, $medicinesused, $projectedsales)
    {

        // Create query

        $query = 'INSERT INTO ' . $this->table . '
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
            vaccinesused = :vaccinesused,
            medicinesused = :medicinesused,
            projectedsales = :projectedsales
        ';

        // Prepare statement
        $stmt = $this->database_connection->prepare($query);

        // Ensure safe data
        $fi = htmlspecialchars(strip_tags($farmerid));
        $fid = htmlspecialchars(strip_tags($farmid));
        $fbc = htmlspecialchars(strip_tags($farmbirdcapacity));
        $cfp = htmlspecialchars(strip_tags($currentfarmproduction));
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
        $vu = htmlspecialchars(strip_tags($vaccinesused));
        $mu = htmlspecialchars(strip_tags($medicinesused));
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
        $stmt->bindParam(':otherpreferredchickssupplier', $opcs);
        $stmt->bindParam(':otherpreferredfeedsssupplier', $opfs);
        $stmt->bindParam(':howmuchrequired', $hmr);
        $stmt->bindParam(':chickscost', $cc);
        $stmt->bindParam(':feedscost', $fc);
        $stmt->bindParam(':broodingcost', $bc);
        $stmt->bindParam(':vaccinesused', $vu);
        $stmt->bindParam(':medicinesused', $mu);
        $stmt->bindParam(':projectedsales', $ps);

        // Execute query statement
        if ($stmt->execute()) {
            return $this->database_connection->lastInsertId();
            // return true;
        } else {
            return false;
        }

    }
}