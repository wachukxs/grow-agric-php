<?php
// Headers
// https://stackoverflow.com/a/17098221
include_once '../../../config/globals/header.php';

// Resources
include_once '../../../config/Database.php';
include_once '../../../model/Admin.php';

// Instantiate Database to get a connection
$database_connection = new Database();
$a_database_connection = $database_connection->connect();

// Instantiate new farmer object
$admin = new Admin($a_database_connection);

// get data
$data = json_decode(file_get_contents('php://input'));

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    file_put_contents('php://stderr', print_r("\n\n" . 'Trying to send email' . "\n", TRUE));
    $sent = $admin->sendMail();

    if ($sent) {

            echo json_encode(
                array(
                    'message' => 'GOOd response. message sent.',
                    'response' => 'OK',
                    'response_code' => http_response_code()
                )
            );
    } else {
        http_response_code(400);

            echo json_encode(
                array(
                    'message' => 'BAD response. message NOT sent.',
                    'response' => 'NOT OK',
                    'response_code' => http_response_code()
                )
            );
    }
    
} else {
    
}

