<?php

include_once '../../../config/globals/header.php';

// Resources
include_once '../../../config/Database.php';
include_once '../../../model/Records.php';



// Instantiate green homes orders object
$records = new Records($a_database_connection);

if ($_SERVER["REQUEST_METHOD"] == "GET") {
    
    if (isset($_GET["farmerid"])) {
        $result1 = $records->getAllFarmerFinanceApplicationStatusByFarmerID($_GET["farmerid"]);
        $row1["farmerfinanceapplications"] = $result1->fetchAll(PDO::FETCH_ASSOC);

        // get total income & expense

        echo json_encode($row1);
    } else {
        
    }
    
}

?>