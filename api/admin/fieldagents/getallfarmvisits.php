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

        $result1 = $admin->getAllFieldAgentFarmVisits();
        $row["farm_visits"] = $result1->fetchAll(PDO::FETCH_ASSOC);

        // $row["wait_list"]["farmeditems"] = htmlspecialchars_decode($row["wait_list"]["farmeditems"]);
        
        http_response_code();
        echo json_encode($row);
        
    } catch (\Throwable $err) {
        //throw $th;
        $result = array();

        file_put_contents('php://stderr', "ERR getting field agent visits: " . $err->getMessage() . "\n" . "\n", FILE_APPEND | LOCK_EX);

        http_response_code(400);
        $result = array();
        $result["status"] = http_response_code();
        $result["message"] = $err->getMessage();

        echo json_encode($result);
    }
}