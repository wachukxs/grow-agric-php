<?php
// Headers
// https://stackoverflow.com/a/17098221
include_once '../../config/globals/header.php';

// Resources
include_once '../../config/Database.php';
include_once '../../model/Farmer.php';

// Instantiate Database to get a connection
$database_connection = new Database();
$a_database_connection = $database_connection->connect();

// Instantiate Course object
$farmer = new Farmer($a_database_connection);

// get data
$data = json_decode(file_get_contents('php://input'));


if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (
        isset($data->courseid, $data->farmerid)
        &&
        !empty($data->courseid)
        &&
        !empty($data->farmerid)
    ) {
        // Get the order [details]
        $result = $farmer->deleteSavedCourseForFarmer($data->courseid, $data->farmerid);

        // returns an array, $row is an array
        // $row = $result->fetch(PDO::FETCH_ASSOC);

        echo json_encode(
            array(
                'message' => 'Good request, no errors',
                'response' => 'OK',
                'response_code' => http_response_code(),
                'delete_details' => $result
            )
        );
    } else {
        http_response_code(400);

        echo json_encode(
            array(
                'message' => 'Bad data provided',
                'response' => 'NOT OK',
                'response_code' => http_response_code()
            )
        );
    }
} else {
    file_put_contents('php://stderr', print_r('Woow 44', TRUE));
}
