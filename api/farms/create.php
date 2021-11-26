<?php

// Headers
// https://stackoverflow.com/a/17098221
$origin = $origin = isset($_SERVER['HTTP_ORIGIN']) ? $_SERVER['HTTP_ORIGIN'] : NULL;
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
include_once '../../model/Farm.php';

// Instantiate Database to get a connection
$database_connection = new Database();
$a_database_connection = $database_connection->connect();

// Instantiate new farm object
$farm = new Farm($a_database_connection);

// get data
$data = json_decode(file_get_contents('php://input'));

// echo log tracing => look into loggin in php
file_put_contents('php://stderr', print_r('Trying to add/update farm' . "\n", TRUE));

try {
    $result_array = array();

    foreach ($data as &$value) {

        $result;
        if (isset($value->id)) { // this if block should come before the for loop
            // Update the farm [details]
            $result = $farm->updateFarmByID($value->challengesfaced, $value->farmcitytownlocation, $value->farmcountylocation, $value->farmeditems, $value->haveinsurance, $value->insurer, $value->numberofemployees, $value->otherchallengesfaced, $value->otherfarmeditems, $value->yearsfarming, $value->id);
        } else { // create a new farm entry
            // $value;
            file_put_contents('../../logs/api.log', print_r($value, TRUE));
            $result = $farm->createFarm($value->challengesfaced, $value->farmcitytownlocation, $value->farmcountylocation, $value->farmeditems, $value->haveinsurance, $value->insurer, $value->numberofemployees, $value->otherchallengesfaced, $value->otherfarmeditems, $value->yearsfarming, $value->farmerid);
        }
        if ($result) {
            // $result is 1 if we're good
            file_put_contents('php://stderr', print_r('[]][==== ++' . gettype($result), TRUE));

            file_put_contents('php://stderr', print_r("\n\n[]" . $result, TRUE));
            file_put_contents('php://stderr', print_r('Updated/created new farm record: ' . $result . "\n", TRUE));

            array_push($result_array, $result);
        } else {
            file_put_contents('php://stderr', print_r('Did NOT Update/create new farm record: ' . $result . "\n", TRUE));
            // break from for loop, and set http_response_header // alternative, populate $result with true|false, and check after the loop
        }
    }


    if ($result_array) { // check that $result is an int
        // Get the farm [details]
        $farms_result = $farm->getAllFarmsByFarmerID($data[0]->farmerid);

        // returns an array, $row is an array
        $row = $farms_result->fetchAll(PDO::FETCH_ASSOC);

        if (is_array($row)) { // gettype($row) == "array"
            // check if $row is array (means transaction was successful)
            // extract($row);
            // we should return all the farms of the farmer

            // Create array
            // $farm_details_arr = array(
            //     'firstname' => $firstname,
            //     'lastname' => $lastname,
            //     'email' => $email,
            //     'phonenumber' => $phonenumber,
            //     'id' => $id,
            //     'image' => 'https://' .  $_SERVER['HTTP_HOST'] . '/chuks/food_delivery/assets/images/' . rawurlencode($image), // https://www.php.net/manual/en/function.urlencode.php#56426
            //     'time_of_order' => $time_of_order,
            //     'total' => $total,
            //     'name' => $name
            // );

            echo json_encode(
                array(
                    'message' => 'Farm created/updated',
                    'response' => 'OK',
                    'response_code' => http_response_code(),
                    'farms' => $row
                )
            );
        }
    } else {
        // http_response_code(400);
        echo json_encode(
            array(
                'message' => 'Farm details not created/updated',
                'response' => 'NOT OK',
                'response_code' => http_response_code(),
                'message_details' => $result, //
            )
        );
    }
} catch (\Throwable $err) {
    file_put_contents('php://stderr', print_r('Error while trying to add/update farm: ' . $err->getMessage() . "\n", TRUE));
    // http_response_code(400);
    echo json_encode(
        array(
            'message' => 'Farm not created/updated',
            'response' => 'NOT OK',
            'response_code' => http_response_code(),
            'message_details' => $err, //
        )
    );
}
