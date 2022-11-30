<?php
// Headers
// https://stackoverflow.com/a/17098221
include_once '../../../config/globals/header.php';

// Resources
include_once '../../../config/Database.php';
include_once '../../../model/Admin.php';

include_once '../../../utilities/ICustom.php';



// Instantiate new farmer object
$admin = new Admin($a_database_connection);

// get data
$data = json_decode(file_get_contents('php://input'));

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    file_put_contents('php://stderr', print_r("\n\n" . 'Trying to send email' . "\n", TRUE));

    // $firstname, $emailtype, $invitedby = NULL, $lastname = NULL, $fullname = NULL
    $sent = false; // $admin->sendMail();

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

