<?php

// this should be in farmer

// Headers
// https://stackoverflow.com/a/17098221
include_once '../../../config/globals/header.php';

// Resources
include_once '../../../config/Database.php';
include_once '../../../model/Admin.php';


// Instantiate Course object
$admin = new Admin($a_database_connection);

// WE should def do some authentication
if ($_SERVER["REQUEST_METHOD"] == "GET") {
    
    try {

        $result2 = $admin->getAllFinanceApplications();
        $row["finance_applications"] = $result2->fetchAll(PDO::FETCH_ASSOC);

        http_response_code();
        echo json_encode($row);
        
        
    } catch (\Throwable $err) {
        //throw $th;
        $result = array();

        file_put_contents('php://stderr', "ERR getting all finance applications: " . $err->getMessage() . "\n" . "\n", FILE_APPEND | LOCK_EX);

        http_response_code(400);
        $result = array();
        $result["status"] = http_response_code(); // do we wanna be including this?
        $result["message"] = $err->getMessage();

        echo json_encode($result);
    }
}