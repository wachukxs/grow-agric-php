<?php

// Headers
// https://stackoverflow.com/a/17098221
include_once '../../../config/globals/header.php';

// Resources
include_once '../../../config/Database.php';
include_once '../../../model/Admin.php';
include_once '../../../model/Finance.php';
include_once '../../../model/Farmer.php';
include_once '../../../model/Course.php';

// Instantiate Database to get a connection
$database_connection = new Database();
$a_database_connection = $database_connection->connect();

// Instantiate Course, Admin, Finance, and Farmer object
$admin = new Admin($a_database_connection);
$finance = new Finance($a_database_connection);
$course = new Course($a_database_connection);
$farmer = new Farmer($a_database_connection);

// get data
$data = json_decode(file_get_contents('php://input'));

if ($_SERVER["REQUEST_METHOD"] == "GET") {
    try {
        $response = array();

        $result = $farmer->getAllFarmersPersonalInfo();

        // returns an array, $row is an array
        $row1 = $result->fetchAll(PDO::FETCH_ASSOC);

        $farmers_courses_statuses_result = $course->getAllCompletedAndIncompletedCourses();
        $row2 = $farmers_courses_statuses_result->fetchAll(PDO::FETCH_ASSOC);

        $incompleted_courses_result = $course->getAllCourses();
        $row3 = $incompleted_courses_result->fetchAll(PDO::FETCH_ASSOC);

        $response["personalInfo"] = $row1;

        $response["incompletedcompletedcourses"] = $row2;
        $response["courses"] = $row3;

        echo json_encode(
            array(
                'message' => 'Good request, no errors',
                'response' => 'OK',
                'response_code' => http_response_code(),
                'farmers_info' => $response
            )
        );
        
    } catch (\Throwable $err) {
        file_put_contents('php://stderr', print_r('Error while get all farmers info: ' . $err->getMessage() . "\n", TRUE));
        
        http_response_code(400);
        echo json_encode(
            array(
                'message' => 'ERROR',
                'response' => 'NOT OK',
                'response_code' => http_response_code(),
                'message_details' => $err, //
            )
        );
    }
}