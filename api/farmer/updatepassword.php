<?php

// Headers
// https://stackoverflow.com/a/17098221
include_once '../../config/globals/header.php';

// Resources
include_once '../../config/Database.php';
include_once '../../model/Farmer.php';



// Instantiate new farmer object
$farmer = new Farmer($a_database_connection);

// get data
$data = json_decode(file_get_contents('php://input'));

// every api logic should be enclosed in a try catch block

// echo log tracing => look into loggin in php
file_put_contents('php://stderr', print_r('Trying to update farmer details1' . "\n", TRUE));

if (isset($data->password, $data->farmerid, $data->request_id)
    &&
    !empty($data->password)
    &&
    !empty($data->farmerid)
    &&
    !empty($data->request_id)

) { // if good data was provided
    try {

        // Update the farmer [details]
        file_put_contents('php://stderr', print_r('Good data was provided' . "\n", TRUE));
        $result = $farmer->updateFarmerPassword($data->farmerid, $data->password);
        
        if ($result) { // check that $result is an int

            $result2 = $result = $farmer->updatePasswordResetStatus($data->request_id);
            if ($result2) {
                echo json_encode(
                    array(
                        'message' => 'Farmer updated ' . gettype($result),
                        'response' => 'OK',
                        'response_code' => http_response_code(),
                        // 'message_details' => $result, // "SQLSTATE[23000]: Integrity constraint violation: 1062 Duplicate entry '0115335593' for key 'phonenumber'"
                    )
                );
            } else {
                
            }
            
            
        } else {
            file_put_contents('php://stderr', print_r('Farmer not updated' . "\n", TRUE));
            http_response_code(400);
            /**
             * $farmer->getSingleFarmerByID($result)->fetch(PDO::FETCH_ASSOC) is false if there was an error
             */
            echo json_encode(
                array(
                    'message' => 'Farmer not updated ' . gettype($result),
                    'response' => 'NOT OK',
                    'response_code' => http_response_code(),
                    'message_details' => $result, // "SQLSTATE[23000]: Integrity constraint violation: 1062 Duplicate entry '0115335593' for key 'phonenumber'"
                )
            );
        }
    } catch (\Throwable $err) {
        //throw $err;
        file_put_contents('php://stderr', print_r('update password Error: ' . $err->getMessage() . "\n", TRUE));
        echo json_encode(
            array(
                'message' => 'Farmer not updated ' . gettype($result),
                'response' => 'NOT OK',
                'response_code' => http_response_code()
            )
        );
    }
} else { // if bad or empty data was provided
    file_put_contents('php://stderr', print_r('Bad data provided' . "\n", TRUE));
    // header("HTTP/1.0 400 Bad Request"); // http_response_code(501); // this line casues errors (basically setting http headers) even when there isn't any. How can we send appropriate http status codes so the front end get them.
    file_put_contents('php://stderr', print_r('Sending http status code ' . http_response_code() . "\n", TRUE));
    echo json_encode(
        array(
            'message' => 'Bad data provided',
            'response' => 'NOT OK',
            'response_code' => http_response_code()
        )
    );
}

?>
