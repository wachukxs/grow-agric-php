<?php

// Headers
// https://stackoverflow.com/a/17098221
include_once '../../../config/globals/header.php';
include_once '../../../utilities/Auth.php';

// Resources
include_once '../../../config/Database.php';
include_once '../../../model/Admin.php';
include_once '../../../model/Finance.php';
include_once '../../../model/Farmer.php';
include_once '../../../model/Course.php';

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

        $all_courses_result = $course->getAllCourses();
        $row3 = $all_courses_result->fetchAll(PDO::FETCH_ASSOC);

        $response["personalInfo"] = $row1;

        $response["incompletedcompletedcourses"] = $row2;
        $response["courses"] = $row3;

        // add farms, finance applications, and farmers too
        $result2 = $admin->getAllFinanceApplications();
        $response["finance_applications"] = $result2->fetchAll(PDO::FETCH_ASSOC);

        $result3 = $admin->getAllFarms();
        $response["farms"] = $result3->fetchAll(PDO::FETCH_ASSOC);

        $result4 = $admin->getAllFarmers();
        $response["farmers"] = $result4->fetchAll(PDO::FETCH_ASSOC);

        $result5 = $admin->allTotalRecordsOfFarmers();
        $response["learning_data"] = $result5->fetchAll(PDO::FETCH_ASSOC);

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