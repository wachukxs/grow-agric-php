<?php

include_once '../../../config/globals/header.php';

// Resources
include_once '../../../config/Database.php';
include_once '../../../model/Records.php';

class CleanQuotes {
    // can do withour these
    // public $farmcountylocation;
    // public $farmsubcountylocation;
    // public $farmwardlocation;
    // public $yearsfarming;
    // public $numberofemployees;
    // public $haveinsurance;
    // public $insurer;
    // public $farmeditems;
    // public $otherfarmeditems;
    // public $challengesfaced;
    // public $otherchallengesfaced;
    // public $multiplechickenhouses;
    // public $id;
    // public $farmerid;
    // public $deleted;
    
    public function __construct()
    {
        if ($this->farmcountylocation) {
            $this->farmcountylocation = htmlspecialchars_decode($this->farmcountylocation, ENT_QUOTES);
        }
        if ($this->farmsubcountylocation) {
            $this->farmsubcountylocation = htmlspecialchars_decode($this->farmsubcountylocation, ENT_QUOTES);
        }
        if ($this->farmwardlocation) {
            $this->farmwardlocation = htmlspecialchars_decode($this->farmwardlocation, ENT_QUOTES);
        }
    }
}

// Instantiate Database to get a connection
$database_connection = new Database();
$a_database_connection = $database_connection->connect();

// Instantiate green homes orders object
$records = new Records($a_database_connection);

if ($_SERVER["REQUEST_METHOD"] == "GET") {
    
    if (isset($_GET["farmerid"])) {
        $result1 = $records->getAllAvailableChicksForSaleByFarmer($_GET["farmerid"]);
        // $row1["chicks"] = $result1->fetchAll(PDO::FETCH_ASSOC);
        $row1["chicks"] = $result1->fetchAll(PDO::FETCH_CLASS, "CleanQuotes");

        echo json_encode($row1);
    } else {
        $result1 = $records->getAllAvailableChicksForSale();
        // $row1["chicks"] = $result1->fetchAll(PDO::FETCH_ASSOC);
        $row1["chicks"] = $result1->fetchAll(PDO::FETCH_CLASS, "CleanQuotes");

        echo json_encode($row1);
    }
    
}

?>