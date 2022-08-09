<?php

// Headers
// https://stackoverflow.com/a/17098221
include_once '../../config/globals/header.php';

// Resources
include_once '../../config/Database.php';
include_once '../../model/Farmer.php';
include_once '../../model/Farm.php';
include_once '../../model/Admin.php';

include_once '../../utilities/ICustom.php'; // re-dundant

// get data
$data = json_decode(file_get_contents('php://input'));

if ($_SERVER["REQUEST_METHOD"] == "POST") {
        // Instantiate Database to get a connection
        $database_connection = new Database();
        $a_database_connection = $database_connection->connect();
    
        // Instantiate new farmer object
        $admin = new Admin($a_database_connection);

        file_put_contents('php://stderr', print_r('Trying to invite farmer' . "\n", TRUE));

        file_put_contents('php://stderr', print_r($data, TRUE));

        if (count($data) > 0) {
            foreach($data as $key => $invite) { // key is 0, 1, etc.
                
                file_put_contents('php://stderr', print_r($invite->invitedfullname, TRUE));

                if (getenv("CURR_ENV") == "production") {
                    $admin->sendMail(NULL, Emailing::INVITE, $invite->invitedemail, $invite->invitedbyfarmerfullname, NULL, $invite->invitedfullname);
                }
            }
        } else {
            
        }

        echo json_encode(
            array(
                'message' => 'Farmer invited',
                'response' => 'OK',
                'response_code' => http_response_code()
            )
        );
}