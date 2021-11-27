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


if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // echo log tracing => look into loggin in php
    file_put_contents('php://stderr', print_r('Trying to delete farm with id: ' . $data->id . "\n", TRUE));
    file_put_contents('php://stderr', print_r($data, TRUE));

    try {

        $result;
        if (isset($data->id)) { // this if block should come before the for loop
            // Update the farm [details]
            $result = $farm->deleteFarm($data->id);
            // file_put_contents('../../logs/api.log', print_r("we are deleting with deleteFarm() \n", TRUE));
            // file_put_contents('../../logs/api.log', print_r('result: ' . $result, TRUE));

            if ($result) {
                // Get the farm [details]
                echo json_encode(
                    array(
                        'message' => 'Farm deleted',
                        'response' => 'OK',
                        'response_code' => http_response_code(),
                        'deleted_farm_id' => $result
                    )
                );
            } else {
                http_response_code(400);
                echo json_encode(
                    array(
                        'message' => 'Farm details not deleted',
                        'response' => 'NOT OK',
                        'response_code' => http_response_code(),
                        'message_details' => $result, //
                    )
                );
            }
        } else { // create a new farm entry
            http_response_code(400);
            echo json_encode(
                array(
                    'message' => 'Farm details not delete, no id provided',
                    'response' => 'NOT OK',
                    'response_code' => http_response_code(),
                    'message_details' => $result, //
                )
            );
        }

    } catch (\Throwable $err) {
        file_put_contents('php://stderr', print_r('Error while trying to add/update farm: ' . $err->getMessage() . "\n", TRUE));
        http_response_code(400);
        echo json_encode(
            array(
                'message' => 'Farm not deleted',
                'response' => 'NOT OK',
                'response_code' => http_response_code(),
                'message_details' => $err, //
            )
        );
    }
}
