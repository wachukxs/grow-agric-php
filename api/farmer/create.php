<?php

// Headers
// https://stackoverflow.com/a/17098221
$origin = $_SERVER['HTTP_ORIGIN'];
$allowed_domains = [
    'https://farmers.growagric.com',
    'https://grow-agric.netlify.app',
    'http://localhost:4005',
];
// output to debug console/output
file_put_contents('php://stderr', print_r('Checking origin:' . $origin . ' for CORS access', TRUE)); // or var_export($foo, true)

if (in_array($origin, $allowed_domains)) {
    header('Access-Control-Allow-Origin: ' . $origin);
}
// header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Headers: *');
header('Content-Type: application/json');
header('Content-Control-Allow-Methods: POST');
header('Content-Control-Allow-Headers: Content-Control-Allow-Methods, Content-Type, Content-Control-Allow-Headers, Authorization, X-Requested-With');

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

// echo log tracing => look into loggin in php
// echo 'Trying to sign up farmer' . "\n";

if (isset($data->lastname, $data->firstname, $data->email, $data->phonenumber, $data->password)
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
                'id' => $id,
                // 'image' => 'https://' .  $_SERVER['HTTP_HOST'] . '/chuks/food_delivery/assets/images/' . rawurlencode($image), // https://www.php.net/manual/en/function.urlencode.php#56426
                // 'time_of_order' => $time_of_order,
                // 'total' => $total,
                // 'name' => $name
            );

            echo json_encode(
                array(
                    'message' => 'Farmer created',
                    'response' => 'OK',
                    'response_code' => http_response_code(),
                    'farmer_details' => $farmer_details_arr
                )
            );
        } else { // $row is bool
            echo json_encode(
                array(
                    'message' => 'Farmer not created',
                    'response' => 'NOT OK',
                    'response_code' => http_response_code(301),
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
                'response' => 'OK',
                'response_code' => http_response_code(),
                'message_details' => $result, // "SQLSTATE[23000]: Integrity constraint violation: 1062 Duplicate entry '0115335593' for key 'phonenumber'"
            )
        );
    }
} else { // if bad or empty data was provided
    echo json_encode(
        array(
            'message' => 'Bad data provided',
            'response' => 'NOT OK',
            'response_code' => http_response_code()
        )
    );
}

?>
