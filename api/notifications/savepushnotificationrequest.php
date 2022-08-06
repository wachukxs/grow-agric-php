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
        isset($data->roleid, $data->role, $data->webpushdata)
        &&
        !empty($data->roleid)
        &&
        !empty($data->role)
        &&
        !empty($data->webpushdata)
    ) {
        $result = $records->saveWebPushRequestData($data->roleid, $data->role, $data->webpushdata);
        
        file_put_contents('php://stderr', print_r('==== ++ ' . gettype($result), TRUE));
        
        file_put_contents('php://stderr', print_r("\n\n" . $result, TRUE));
        echo json_encode(
            array(
                'message' => 'Good request, no errors',
                'response' => 'OK',
                'response_code' => http_response_code(),
                'save_details' => $result
            )
        );

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
    
}
