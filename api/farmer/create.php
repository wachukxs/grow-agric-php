<?php

// I FIXED A BUG HERE, FOR SOME REASON, THE REQUEST IS MADE TWICE!!! [Caused by pre-flight request]


// $_SERVER['HTTP_X_REQUESTED_WITH']

// foreach ($_SERVER as $key => $value) {
//     file_put_contents('../../logs/api.log', '$_SERVER["' . $key . '"] = ' . $value . "\n" . "\n", FILE_APPEND | LOCK_EX);
// }

// Headers
// https://stackoverflow.com/a/17098221
include_once '../../config/globals/header.php';

// Resources
include_once '../../config/Database.php';
include_once '../../model/Farmer.php';
include_once '../../model/Admin.php';
include_once '../../utilities/ICustom.php'; // re-dundant

if ($_SERVER["REQUEST_METHOD"] == "POST") { // hot fix for handling pre-flight request
    // Instantiate Database to get a connection
    $database_connection = new Database();
    $a_database_connection = $database_connection->connect();

    // Instantiate new farmer n admin object
    $farmer = new Farmer($a_database_connection);
    $admin = new Admin($a_database_connection);

    // get data
    $data = json_decode(file_get_contents('php://input'));

    // echo log tracing => look into loggin in php
    file_put_contents('php://stderr', print_r('Trying to sign up farmer' . "\n", TRUE));

    try {
        if (
            isset($data->lastname, $data->firstname, $data->email, $data->phonenumber, $data->password)
            &&
            !empty($data->lastname)
            &&
            !empty($data->firstname)
            &&
            !empty($data->email)
            &&
            !empty($data->phonenumber)
            &&
            !empty($data->password)
        ) { // if good data was provided
            // Create the farmer [details]
            $result = $farmer->createFarmer($data->firstname, $data->lastname, $data->email, $data->phonenumber, $data->password);
            if ($result) { // check that $result is an int
                // Get the farmer [details]
                $order_result = $farmer->getSingleFarmerByID($result);

                // returns an array, $row is an array
                $row = $order_result->fetch(PDO::FETCH_ASSOC);

                if (is_array($row)) { // gettype($row) == "array"
                    // check if $row is array (means transaction was successful)
                    extract($row);

                    // Create array
                    $farmer_details_arr = array(
                        'firstname' => $firstname,
                        'lastname' => $lastname,
                        'email' => $email,
                        'phonenumber' => $phonenumber,
                        'id' => $id, // should rename to farmer_id later
                        // 'image' => 'https://' .  $_SERVER['HTTP_HOST'] . '/chuks/food_delivery/assets/images/' . rawurlencode($image), // https://www.php.net/manual/en/function.urlencode.php#56426
                        // 'time_of_order' => $time_of_order,
                        // 'total' => $total,
                        // 'name' => $name
                    );

                    // send the farmer an email.
                    if (getenv("CURR_ENV") == "production") {
                        $admin->sendMail($firstname, Emailing::SIGNUP, $email);
                    }

                    echo json_encode(
                        array(
                            'message' => 'Farmer created',
                            'response' => 'OK',
                            'response_code' => http_response_code(),
                            'farmer_details' => $farmer_details_arr
                        )
                    );
                } else { // $row is string // need to make here better
                    echo json_encode(
                        array(
                            'message' => 'Farmer not created',
                            'response' => 'NOT OK',
                            'response_code' => http_response_code(409),
                            'message_details' => $result
                        )
                    );
                }
            } else {
                /**
                 * $farmer->getSingleFarmerByID($result)->fetch(PDO::FETCH_ASSOC) is false if there was an error
                 */
                echo json_encode(
                    array(
                        'message' => 'Farmer not created ' . gettype($result),
                        'response' => 'NOT OK',
                        'response_code' => http_response_code(),
                        'message_details' => $result, // "SQLSTATE[23000]: Integrity constraint violation: 1062 Duplicate entry '0115335593' for key 'phonenumber'"
                    )
                );
            }
        } else { // if bad or empty data was provided
            file_put_contents('php://stderr', print_r('Omo' . "\n", TRUE));
            file_put_contents('php://stderr', print_r('END OFFFFF sign up farmer' . "\n", TRUE));
            echo json_encode(
                array(
                    'message' => 'Bad data provided',
                    'response' => 'NOT OK',
                    'response_code' => http_response_code() // 400 // setting http code here causes error
                )
            );
        }
    } catch (\Throwable $err) {
        //throw $err;
        file_put_contents('php://stderr', print_r('WOAHHH, Error: sign up farmer' . $err->getMessage() . "\n", TRUE));
        
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
