<?php

// Headers
// https://stackoverflow.com/a/17098221
include_once '../../../config/globals/header.php';

// Resources
include_once '../../../config/Database.php';
include_once '../../../model/Admin.php';

// Instantiate Database to get a connection
$database_connection = new Database();
$a_database_connection = $database_connection->connect();

// Instantiate Course object
$admin = new Admin($a_database_connection);

// WE should def do some authentication
if ($_SERVER["REQUEST_METHOD"] == "GET") {
    
    try {

        $row = array();

        $result1 = $admin->getReviewInfo();
        $row["summary"] = $result1->fetch(PDO::FETCH_ASSOC); // not fetchAll

        $result2 = $admin->getAllFinanceApplications();
        $row["finance_applications"] = $result2->fetchAll(PDO::FETCH_ASSOC);

        $result3 = $admin->getAllFarms();
        $row["farms"] = $result3->fetchAll(PDO::FETCH_ASSOC);

        $result4 = $admin->getAllFarmers();
        $row["farmers"] = $result4->fetchAll(PDO::FETCH_ASSOC);

        http_response_code();
        echo json_encode($row);
        
    } catch (\Throwable $err) {
        //throw $th;
        $result = array();

        file_put_contents('php://stderr', "ERR getting overview: " . $err->getMessage() . "\n" . "\n", FILE_APPEND | LOCK_EX);

        http_response_code(400);
        $result = array();
        $result["status"] = 0;
        $result["message"] = $err->getMessage();
    }
}