<?php

// Headers
// https://stackoverflow.com/a/17098221
include_once '../../../config/globals/header.php';
include_once '../../../utilities/Auth.php';

// Resources
include_once '../../../config/Database.php';
include_once '../../../model/Admin.php';

class FarmVisitsss {
    
    public function __construct()
    {
        if ($this->otherfarmedanimals) {            
            
            // $this->otherfarmedanimals = unserialize($this->otherfarmedanimals);

            // removed special chars from the string
            $this->otherfarmedanimals = htmlspecialchars_decode($this->otherfarmedanimals, ENT_QUOTES);

            // convert the stringed string to json
            $this->otherfarmedanimals = json_decode($this->otherfarmedanimals, true);


            // removed special chars from the string
            // $this->otherfarmedanimals = explode(",", $this->otherfarmedanimals);
        }
    }
}

// Instantiate Course object
$admin = new Admin($a_database_connection);

// WE should def do some authentication
if ($_SERVER["REQUEST_METHOD"] == "GET") {
    
    try {

        $row = array();

        $result1 = $admin->getAllFieldAgentFarmVisits();
        $row["farm_visits"] = $result1->fetchAll(PDO::FETCH_CLASS, "FarmVisitsss");

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