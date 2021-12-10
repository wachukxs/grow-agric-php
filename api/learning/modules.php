<?php
// Headers
// https://stackoverflow.com/a/17098221
include_once '../../config/globals/header.php';


// Resources
include_once '../../config/Database.php';
include_once '../../model/Module.php';
include_once '../../model/Course.php';

// Instantiate Database to get a connection
$database_connection = new Database();
$a_database_connection = $database_connection->connect();

// Instantiate food delivery Farmer object
$module = new Module($a_database_connection);
$course = new Course($a_database_connection);

// get data
$data = json_decode(file_get_contents('php://input'));

/**
 * check if $_GET["id"] is set
 * also check that that module id exist in db
 */
// echo $_GET["id"];

try {
    $modules_result = $module->getAllModules();
    $row1 = $modules_result->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($row1);
} catch (\Throwable $err) {
    //throw $th;
    echo "Error";
}

?>