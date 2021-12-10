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

// Instantiate new farmer object
$farmer = new Farmer($a_database_connection);

// get data
$data = json_decode(file_get_contents('php://input'));

// every api logic should be enclosed in a try catch block

// echo log tracing => look into loggin in php
file_put_contents('php://stderr', print_r('Trying to update farmer details1' . "\n", TRUE));
file_put_contents('php://stderr', print_r('farmer data is ' . $data->lastname . '|' . $data->firstname . '|' . $data->email . '|' . $data->phonenumber . '|' . $data->age . '|' . $data->maritalstatus . '|' . $data->highesteducationallevel . '|' . $data->id . "\n", TRUE));

if (isset($data->lastname, $data->firstname, $data->email, $data->phonenumber, $data->age, $data->maritalstatus, $data->highesteducationallevel, $data->yearsofexperience, $data->id)
    &&
    !empty($data->lastname)
    &&
    !empty($data->firstname)
    &&
    !empty($data->email)
    &&
    !empty($data->phonenumber)
    &&
    !empty($data->age)
    &&
    !empty($data->maritalstatus)
    &&
    !empty($data->highesteducationallevel)
    &&
    !empty($data->yearsofexperience)
    &&
    !empty($data->id)
) { // if good data was provided
    try {
        // Update the farmer [details]
        file_put_contents('php://stderr', print_r('Good data was provided' . "\n", TRUE));
        $result = $farmer->updateFarmerProfile1ByID($data->firstname, $data->lastname, $data->email, $data->phonenumber, $data->age, $data->maritalstatus, $data->highesteducationallevel, $data->yearsofexperience, $data->id);
        if ($result) { // check that $result is an int
            // Get the farmer [details]
            file_put_contents('php://stderr', print_r('Good result too' . "\n", TRUE));
            $order_result = $farmer->getSingleFarmerByID($result);
            // ??
            // returns an array, $row is an array
            $row = $order_result->fetch(PDO::FETCH_ASSOC);

            extract($row);

            file_put_contents('php://stderr', print_r('Farmer updated' . "\n", TRUE));

            // Create array
            $farmer_details_arr = array(
                'firstname' => $firstname,
                'lastname' => $lastname,
                'email' => $email,
                'phonenumber' => $phonenumber,
                'id' => $id,
                // 'image' => 'https://' .  $_SERVER['HTTP_HOST'] . '/chuks/food_delivery/assets/images/' . rawurlencode($image), // https://www.php.net/manual/en/function.urlencode.php#56426
                'yearsofexperience' => $yearsofexperience,
                'maritalstatus' => $maritalstatus,
                'highesteducationallevel' => $highesteducationallevel
            );

            if (is_array($row)) { // gettype($row) == "array"
                echo json_encode(
                    array(
                        'message' => 'Farmer details updated',
                        'response' => 'OK',
                        'response_code' => http_response_code(),
                        // 'farmer_details' => $farmer_details_arr // we could just set on the front end. less network load
                    )
                );
            } else { // $row is bool
                echo json_encode(
                    array(
                        'message' => 'Farmer details not updated',
                        'response' => 'NOT OK',
                        'response_code' => http_response_code(301),
                        'message_details' => $result
                    )
                );
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
        file_put_contents('php://stderr', print_r('updatedetails1 Error: ' . $err->getMessage() . "\n", TRUE));
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
    file_put_contents('php://stderr', print_r('Sending ' . http_response_code() . "\n", TRUE));
    echo json_encode(
        array(
            'message' => 'Bad data provided',
            'response' => 'NOT OK',
            'response_code' => http_response_code()
        )
    );
}

?>
