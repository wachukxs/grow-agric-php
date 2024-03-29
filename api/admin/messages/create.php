<?php
// whatttttttttttttttt?????? https://www.php.net/sockets
// Headers
// https://stackoverflow.com/a/17098221
include_once '../../../config/globals/header.php';
include_once '../../../utilities/Auth.php';

// Resources
include_once '../../../config/Database.php';
include_once '../../../model/Admin.php';
include_once '../../../model/Farmer.php';

include_once '../../../utilities/WebPushNotifications.php';

// Instantiate new farmer object
$admin = new Admin($a_database_connection);
$farmer = new Farmer($a_database_connection);

// get data
$data = json_decode(file_get_contents('php://input'));

file_put_contents('php://stderr', print_r('Trying to create and send message' . "\n", TRUE));

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (
        isset($data->farmerid, $data->the_message, $data->_to, $data->_from, $data->subject)
        &&
        !empty($data->the_message)
        &&
        !empty($data->_to)
        &&
        !empty($data->_from)
        &&
        !empty($data->subject)
        &&
        !empty($data->farmerid)
    ) {

        // try to check their credentials
        $result = $admin->sendMessage($data->the_message, $data->time_sent, $data->_from, $data->_to, $data->farmerid, $data->subject);

        file_put_contents('php://stderr', "\n\n" . " getting all farmer personal info:::: for id " . $data->farmerid . "\n" . "\n", FILE_APPEND | LOCK_EX);

        $result2 = $farmer->getSingleFarmerByID($data->farmerid);
        $farmerRow = $result2->fetch(PDO::FETCH_ASSOC);

        // file_put_contents('php://stderr', "\n\n", FILE_APPEND | LOCK_EX);

        // file_put_contents('php://stderr', "\nfarmer first name " . $farmerRow['firstname'] . "\n" . "\n", FILE_APPEND | LOCK_EX);


        if ($result) {

            // remove special char fix.
            $result['the_message'] = htmlspecialchars_decode($result['the_message'], ENT_QUOTES);
            
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
            // if it is sent by admin
            if (strpos($data->_from, 'growagric.com')) {
                file_put_contents('php://stderr', "\nwho sent the message:::: " . $data->_from . "\n" . "\n", FILE_APPEND | LOCK_EX);
                file_put_contents('php://stderr', "\nmessage to:::: " . $farmerRow['id'] . "\n" . "\n", FILE_APPEND | LOCK_EX);

                // we should only send if the message was from admin, and if they haven't been sent an email in 6 hours
                // don't send for the mean time.
                $admin->sendMail($farmerRow['firstname'], Emailing::NEW_MESSAGE_UPDATE, $farmerRow['email']);

                // will complete later
                sendNewMessageNotification($farmerRow['id']);
            } else {
                file_put_contents('php://stderr', "\nwho sent the message:::: " . $data->_from . " well not adminnn\n" . "\n", FILE_APPEND | LOCK_EX);
            }
          
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
