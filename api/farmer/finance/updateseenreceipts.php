<?php
// SEEMS This should be under api/farmer/
// Headers
// https://stackoverflow.com/a/17098221
include_once '../../../config/globals/header.php';

// Resources
include_once '../../../config/Database.php';
include_once '../../../model/Farmer.php';

$database_connection = new Database();
$a_database_connection = $database_connection->connect();

// Instantiate new farmer object
$farmer = new Farmer($a_database_connection);

// get data
$data = json_decode(file_get_contents('php://input'));

file_put_contents('php://stderr', print_r('Trying to create and send message' . "\n", TRUE));

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (
        isset($data->farmerid, $data->timeread, $data->financeapplicationid)
        &&
        !empty($data->farmerid)
        &&
        !empty($data->timeread)
        &&
        !empty($data->financeapplicationid)
    ) {

        $result = $farmer->updateFinanceApplicationSeenReceipts($data->financeapplicationid, $data->farmerid, $data->timeread);

        file_put_contents('php://stderr', print_r("\n\n" . "EOHHHHH $result" . "\n", TRUE));


        if ($result) {
            echo json_encode(
                array(
                    'message' => 'you will get a response message.',
                    'response' => 'OK',
                    'response_code' => http_response_code(),
                    'sent' => $result
                )
            );
        } else {
            http_response_code(400);

            echo json_encode(
                array(
                    'message' => 'sth went wrong on our end.',
                    'response' => 'NOT OK',
                    'response_code' => http_response_code()
                )
            );
        }
        
        
    } else {
        file_put_contents('php://stderr', print_r("\n\n" . 'ERR Trying to update message read receipts, Bad data provided' . "\n", TRUE));

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
