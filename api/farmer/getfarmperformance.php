<?php

// Headers
// https://stackoverflow.com/a/17098221
include_once '../../config/globals/header.php';

// Resources
include_once '../../config/Database.php';
include_once '../../model/Records.php';

// get data
$data = json_decode(file_get_contents('php://input'));

if ($_SERVER["REQUEST_METHOD"] == "GET") {
        // Instantiate Database to get a connection
        $database_connection = new Database();
        $a_database_connection = $database_connection->connect();
    
        // Instantiate new farmer object
        $records = new Records($a_database_connection);

        file_put_contents('php://stderr', print_r('Trying to get farm performance' . "\n", TRUE));

        file_put_contents('php://stderr', print_r($data, TRUE));

        if (isset($_GET["farmerid"])) {
            $result1 = $records->getMinDateOfRecords($_GET["farmerid"]);
            $row1["mindate"] = $result1->fetch(PDO::FETCH_ASSOC);
    
            $result2 = $records->sumsOfRecords($_GET["farmerid"]);
            $row1["recordssum"] = $result2->fetch(PDO::FETCH_ASSOC);
    
            $result3 = $records->totalRecordsOfFarmers($_GET["farmerid"]);
            $row1["totalrecordssaved"] = $result3->fetchAll(PDO::FETCH_ASSOC);

            $result4 = $records->profitAndLoss($_GET["farmerid"]);
            $row1["incomeandexpense"] = $result4->fetchAll(PDO::FETCH_ASSOC);
    
            $result5 = $records->getMaxDateOfRecord($_GET["farmerid"]);
            $row1["maxdate"] = $result5->fetch(PDO::FETCH_ASSOC);

            $result6 = $records->totalFarmerFarms($_GET["farmerid"]);
            $row1["farms"] = $result6->fetch(PDO::FETCH_ASSOC);

            
    
            // get total income & expense
    
            echo json_encode($row1);
        } else {
            
        }
}