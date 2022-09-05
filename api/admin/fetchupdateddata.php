<?php
// Headers
// https://stackoverflow.com/a/17098221
include_once '../../config/globals/header.php';

// Resources
include_once '../../config/Database.php';
include_once '../../model/Admin.php';

// get data
$data = json_decode(file_get_contents('php://input'));

if ($_SERVER["REQUEST_METHOD"] == "GET") {
    // Instantiate Database to get a connection

    $database_connection = new Database();
    $a_database_connection = $database_connection->connect();

    // Instantiate new farmer object
    $admin = new Admin($a_database_connection);

    // this api ... isn't very okay

    file_put_contents('php://stderr', print_r("we are updating admin" . "\n", TRUE));

    $row = array();

    $result1 = $admin->getReviewInfo();
    $row["summary"] = $result1->fetch(PDO::FETCH_ASSOC); // not fetchAll

    $result2 = $admin->getAllFinanceApplications();
    $row["finance_applications"] = $result2->fetchAll(PDO::FETCH_ASSOC);

    $result3 = $admin->getAllFarms();
    $row["farms"] = $result3->fetchAll(PDO::FETCH_ASSOC);

    $result4 = $admin->getAllFarmers();
    $row["farmers"] = $result4->fetchAll(PDO::FETCH_ASSOC);

    $result5 = $admin->getAllCourses();
    $row["courses"] = $result5->fetchAll(PDO::FETCH_ASSOC);

    $result6 = $admin->getAllModules();
    $row["modules"] = $result6->fetchAll(PDO::FETCH_ASSOC);

    http_response_code();

    echo json_encode(
        array(
            'message' => 'Admin data updated',
            'response' => 'OK',
            'response_code' => http_response_code(),
            'admin_details' => $row
        )
    );
}
