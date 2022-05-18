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

class WaitListing {
    
    public function __construct()
    {
        if ($this->farmeditems) {
            // removed special chars from the string
            $this->farmeditems = htmlspecialchars_decode($this->farmeditems, ENT_QUOTES);

            // convert the stringed json to json
            $this->farmeditems = json_decode($this->farmeditems, true);

            // filter out the farmeditems not farmed
            $this->farmeditems = array_filter($this->farmeditems, function ($value) {
                return $value == true;
            });

        }
    }
}

// WE should def do some authentication
if ($_SERVER["REQUEST_METHOD"] == "GET") {
    
    try {

        $row = array();

        $result1 = $admin->getAllWaitingList();
        $row["wait_list"] = $result1->fetchAll(PDO::FETCH_CLASS, "WaitListing"); // not fetchAll

        // $row["wait_list"]["farmeditems"] = htmlspecialchars_decode($row["wait_list"]["farmeditems"]);
        
        http_response_code();
        echo json_encode($row);
        
    } catch (\Throwable $err) {
        //throw $th;
        $result = array();

        file_put_contents('php://stderr', "ERR getting wait list: " . $err->getMessage() . "\n" . "\n", FILE_APPEND | LOCK_EX);

        http_response_code(400);
        $result = array();
        $result["status"] = http_response_code();
        $result["message"] = $err->getMessage();

        echo json_encode($result);
    }
}