<?php
// whatttttttttttttttt?????? https://www.php.net/sockets
// Headers
// https://stackoverflow.com/a/17098221
include_once '../../../config/globals/header.php';

// Resources
include_once '../../../config/Database.php';
include_once '../../../model/Admin.php';
include_once '../../../model/Farmer.php';

$database_connection = new Database();
$a_database_connection = $database_connection->connect();

// Instantiate new farmer object
$admin = new Admin($a_database_connection);
$farmer = new Farmer($a_database_connection);

// get data
$data = json_decode(file_get_contents('php://input'));

file_put_contents('php://stderr', print_r('Trying to create and send message' . "\n", TRUE));

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (
        isset($data->farmerid, $data->message, $data->to, $data->from, $data->subject)
        &&
        !empty($data->message)
        &&
        !empty($data->to)
        &&
        !empty($data->from)
        &&
        !empty($data->subject)
        &&
        !empty($data->farmerid)
    ) {

        // try to check their credentials
        $result = $admin->sendMessage($data->message, $data->timesent, $data->from, $data->to, $data->farmerid, $data->subject);

        $result2 = $farmer->getAllFarmersPersonalInfo($data->farmerid);
        $farmerRow = $result2->fetch(PDO::FETCH_ASSOC);

        file_put_contents('php://stderr', "\nfarmer first name" . $farmerRow['firstname'] . "\n" . "\n", FILE_APPEND | LOCK_EX);


        if ($result) {
            
            echo json_encode(
                array(
                    'message' => 'you will get a response message.',
                    'response' => 'OK',
                    'response_code' => http_response_code(),
                    'sent' => $result
                )
            );


        // afterwards we send email
        // only use this if block after testing in local
        if (getenv("CURR_ENV") == "production") {
            file_put_contents('php://stderr', print_r('Sending message email update cause we\'re in prod.' . "\n", TRUE));
            $admin->sendMail($farmerRow['firstname'], Emailing::NEW_MESSAGE_UPDATE, $farmerRow['email']);
        }  


        } else {
            http_response_code(400);

            echo json_encode(
                array(
                    'message' => 'you will NOT get a response message.',
                    'response' => 'NOT OK',
                    'response_code' => http_response_code()
                )
            );
        }

        
        
    } else {
        file_put_contents('php://stderr', print_r("\n\n" . 'ERR Trying to send message, Bad data provided' . "\n", TRUE));

        http_response_code(400);
        echo json_encode(
            array(
                'message' => 'Bad data provided',
                'response' => 'NOT OK',
                'response_code' => http_response_code()
            )
        );
    }
    
} else { // ? what about options calls ??
    file_put_contents('php://stderr', print_r("\n\n" . 'Ignoring wrong http method call' . "\n", TRUE));

}
