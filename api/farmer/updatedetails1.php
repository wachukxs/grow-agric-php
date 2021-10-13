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
file_put_contents('php://stderr', print_r('Checking origin ' . $origin . ' for CORS access' . "\n", TRUE)); // or var_export($foo, true)

if (in_array($origin, $allowed_domains)) {
    file_put_contents('php://stderr', print_r('Valid CORS access for ' . $origin . "\n", TRUE));
    header('Access-Control-Allow-Origin: ' . $origin);
} else {
    file_put_contents('php://stderr', print_r('Invalid CORS access for ' . $origin . "\n", TRUE));
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
file_put_contents('php://stderr', print_r('Trying to update farmer details1' . "\n", TRUE));
file_put_contents('php://stderr', print_r('farmer id is ' . $data->id . "\n", TRUE));

if (isset($data->lastname, $data->firstname, $data->email, $data->phonenumber, $data->age, $data->maritalstatus, $data->highesteducationallevel, $data->id)
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
    !empty($data->id)
) { // if good data was provided
    // Create the farmer [details]
    $result = $farmer->updateFarmerProfile1ByID($data->firstname, $data->lastname, $data->email, $data->phonenumber, $data->age, $data->maritalstatus, $data->highesteducationallevel, $data->id);
    if ($result) { // check that $result is an int
        // Get the farmer [details]
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
            // 'time_of_order' => $time_of_order,
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
} else { // if bad or empty data was provided
    file_put_contents('php://stderr', print_r('Bad data provided' . "\n", TRUE));
    header("HTTP/1.0 400 Bad Request"); // http_response_code(501); // 
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
