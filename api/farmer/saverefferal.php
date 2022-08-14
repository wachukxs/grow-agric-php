<?php

// Headers
// https://stackoverflow.com/a/17098221
include_once '../../config/globals/header.php';

// Resources
include_once '../../config/Database.php';
include_once '../../model/Farmer.php';
include_once '../../model/Records.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") { // hot fix for handling pre-flight request
    // Instantiate Database to get a connection
    $database_connection = new Database();
    $a_database_connection = $database_connection->connect();

    // Instantiate new farmer object
    $farmer = new Farmer($a_database_connection);
    $records = new Records($a_database_connection);

    // get data
    $data = json_decode(file_get_contents('php://input'));

    file_put_contents('php://stderr', print_r('Trying to save refferal' . "\n", TRUE));

    if (
        isset($data->farmerid, $data->howfarmerheardaboutus)
        &&
        !empty($data->howfarmerheardaboutus)
        &&
        !empty($data->farmerid)
    ) {

        $result = $farmer->saveRefferal($data->farmerid, $data->howfarmerheardaboutus, $data->whoreferredfarmer, $data->othersourcefarmerheardusfrom);

        if ($result) {

            $reffered_result = $records->getFarmerRefferalByID($result);
            $_row = $reffered_result->fetch(PDO::FETCH_ASSOC);

            echo json_encode(
                array(
                    'message' => 'Farmer reffered record saved',
                    'response' => 'OK',
                    'response_code' => http_response_code(),
                    'message_details' => $_row
                )
            );
        } else {
            echo json_encode(
                array(
                    'message' => 'Farmer reffered record NOT saved',
                    'response' => 'NOT OK',
                    'response_code' => http_response_code(403),
                    'message_details' => 'We did not get that... it is us'
                )
            );
        }
        
    } else { // if bad or empty data was provided

        file_put_contents('php://stderr', print_r('Trying to save farmer refferal, Bad data provided' . "\n", TRUE));

        http_response_code(400);

        echo json_encode(
            array(
                'message' => 'Bad data provided',
                'response' => 'NOT OK',
                'response_code' => http_response_code()
            )
        );
    }
}
