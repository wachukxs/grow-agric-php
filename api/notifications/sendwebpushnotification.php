<?php

// Headers
// https://stackoverflow.com/a/17098221
include_once '../../config/globals/header.php';

// Resources
include_once '../../config/Database.php';
include_once '../../model/Records.php';

// Instantiate Database to get a connection
$database_connection = new Database();
$a_database_connection = $database_connection->connect();

// Instantiate Farmer object
$records = new Records($a_database_connection);

// get data
$data = json_decode(file_get_contents('php://input'));

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (
        isset($data->notificationtype, $data->role, $data->roleid)
        &&
        !empty($data->roleid)
        &&
        !empty($data->role)
        &&
        !empty($data->notificationtype)
    ) {
        
    } else {
        
    }
    
} else {

}
