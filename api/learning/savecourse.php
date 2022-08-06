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

// Instantiate Farmer object
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
    
            
        //
        $result = $farmer->saveCourseForFarmer($data->courseid, $data->farmerid);
        
        // returns an array, $row is an array
        // $row = $result->fetch(PDO::FETCH_ASSOC);

        file_put_contents('php://stderr', print_r('==== ++ ' . gettype($result), TRUE));
        
        file_put_contents('php://stderr', print_r("\n\n" . $result, TRUE));

        // we should check the type of $result
        if (gettype($result) == "string" && is_string($result)) {
            echo json_encode(
                array(
                    'message' => 'Good request, no errors',
                    'response' => 'OK',
                    'response_code' => http_response_code(),
                    'save_details' => $result
                )
            );
        } else { // it must've been boolean
            echo json_encode(
                array(
                    'message' => 'Bad request, we could not save course',
                    'response' => 'NOT OK',
                    'response_code' => http_response_code(400),
                )
            );
        }
        
    
    } else {
        echo json_encode(
            array(
                'message' => 'Bad data provided',
                'response' => 'NOT OK',
                'response_code' => http_response_code(400)
            )
        );
    }
} else {
    file_put_contents('php://stderr', print_r('NOT A POST REQUEST: ' . $_SERVER["REQUEST_METHOD"] . ' method in ' . $_SERVER['SCRIPT_FILENAME'], TRUE));
}
