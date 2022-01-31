<?php

include_once '../../config/globals/header.php';

// Resources
include_once '../../config/Database.php';
include_once '../../model/Records.php';

// Instantiate Database to get a connection
$database_connection = new Database();
$a_database_connection = $database_connection->connect();

// Instantiate green homes orders object
$records = new Records($a_database_connection);

if ($_SERVER["REQUEST_METHOD"] == "GET") {
    
    if (isset($_GET["farmerid"])) {
        $result1 = $records->getAllFarmerMortalitiesInputRecords($_GET["farmerid"]);
        $row1["mortalities"] = $result1->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode($row1);
    } else {
        
    }
    
}

?>