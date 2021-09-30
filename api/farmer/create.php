<?php

// Headers
header('Access-Control-Allow-Origin: *');
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

// echo log tracing
echo 'Trying to sign up farmer';

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
    if ($result) {
        // Get the farmer [details]
        $order_result = $farmer->getSingleFarmerByID($result);
        // returns an array, $row is an array
        $row = $order_result->fetch(PDO::FETCH_ASSOC);

        extract($row);

        // Create array
        $farmer_details_arr = array(
            'firstname' => $firstname,
            'lastname' => $lastname,
            'email' => $email,
            'phonenumber' => $phonenumber,
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
                'order_details' => $farmer_details_arr
            )
        );
    } else {
        echo json_encode(
            array(
                'message' => 'Farmer not created',
                'response' => 'OK',
                'response_code' => http_response_code()
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
