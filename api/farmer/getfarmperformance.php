<?php

// Headers
// https://stackoverflow.com/a/17098221
include_once '../../config/globals/header.php';

// Resources
include_once '../../config/Database.php';
include_once '../../model/Records.php';
include_once '../../model/Course.php';

// get data
$data = json_decode(file_get_contents('php://input'));

if ($_SERVER["REQUEST_METHOD"] == "GET") {
        // Instantiate Database to get a connection
        $database_connection = new Database();
        $a_database_connection = $database_connection->connect();
    
        // Instantiate new Records object
        $records = new Records($a_database_connection);
        // Instantiate new Course object
        $course = new Course($a_database_connection);

        file_put_contents('php://stderr', print_r('Trying to get farm performance' . "\n", TRUE));

        file_put_contents('php://stderr', print_r($data, TRUE));

        if (isset($_GET["farmerid"])) {
            $result1 = $records->getMinDateOfRecords($_GET["farmerid"]);
            $row1["mindate"] = $result1->fetch(PDO::FETCH_ASSOC);
    
            // number of the total records saved by farmer
            $result2 = $records->sumsOfRecords($_GET["farmerid"]);
            $row1["recordssum"] = $result2->fetch(PDO::FETCH_ASSOC);
    
            // array of all the records farmer have saved
            $result3 = $records->totalRecordsOfFarmers($_GET["farmerid"]);
            $row1["totalrecordssaved"] = $result3->fetchAll(PDO::FETCH_ASSOC);

            $result4 = $records->profitAndLoss($_GET["farmerid"]);
            $row1["incomeandexpense"] = $result4->fetchAll(PDO::FETCH_ASSOC);
    
            $result5 = $records->getMaxDateOfRecord($_GET["farmerid"]);
            $row1["maxdate"] = $result5->fetch(PDO::FETCH_ASSOC);

            $result6 = $records->totalFarmerFarms($_GET["farmerid"]);
            $row1["farms"] = $result6->fetch(PDO::FETCH_ASSOC);

            $result7 = $records->totalRecordedFarmerEmployees($_GET["farmerid"]);
            $row1["employees"] = $result7->fetch(PDO::FETCH_ASSOC);

            $result8 = $records->totalSalaryPaidByFarmer($_GET["farmerid"]);
            $row1["salary"] = $result8->fetch(PDO::FETCH_ASSOC);

            $result9 = $records->getAllSalesTotalByFarmer($_GET["farmerid"]);
            $row1["salessum"] = $result9->fetch(PDO::FETCH_ASSOC);

            // employees the farmer hasn't recorded ...
            $result10 = $records->getFarmerTotalNumberOfEmployees($_GET["farmerid"]);
            $row1["totalemployess"] = $result10->fetch(PDO::FETCH_ASSOC);

            $result11 = $course->getAllFarmerCompletedCourses($_GET["farmerid"]);
            $row1["completedcourses"] = $result11->fetchAll(PDO::FETCH_ASSOC);

            $result12 = $records->selectFarmerFeedsSupplier($_GET["farmerid"]);
            $row1["feedssuppliers"] = $result12->fetchAll(PDO::FETCH_ASSOC);

            $result13 = $records->selectFarmerChickenSupplier($_GET["farmerid"]);
            $row1["chickensuppliers"] = $result13->fetchAll(PDO::FETCH_ASSOC);

            $result14 = $records->getAllFarmerCustomers($_GET["farmerid"]);
            $row1["allfarmercustomer"] = $result14->fetchAll(PDO::FETCH_ASSOC);
    
            $result15 = $records->calculateTotalInputsCost($_GET["farmerid"]);
            $row1["totalinputscost"] = $result15->fetch(PDO::FETCH_ASSOC);
    
            // get total income & expense
    
            echo json_encode($row1);
        } else {
            
        }
}