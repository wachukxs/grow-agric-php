<?php

// Headers
// https://stackoverflow.com/a/17098221
include_once '../../../../config/globals/header.php';

// Resources
include_once '../../../../config/Database.php';
include_once '../../../../model/Records.php';



// Instantiate Course object
$records = new Records($a_database_connection);

// get data
$data = json_decode(file_get_contents('php://input'));

file_put_contents('php://stderr', print_r('829239\\n', TRUE));
/**
 * check if $_GET["id"] is set
 * also check that that module id exist in db
 */
// echo $_GET["id"];
if (isset($_GET["farmerid"])) {
    // Get the course [details]

    $labour_result = $records->getAllFarmerLabourRecords($_GET["farmerid"]);
    $row1["labour_records"] = $labour_result->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($row1);
} else {
    file_put_contents('php://stderr', print_r(dirname(__FILE__) . ' NOOOO Farmer id \\n', TRUE));
}

?>