<?php

// Headers
// https://stackoverflow.com/a/17098221
include_once '../../../config/globals/header.php';

// Resources
include_once '../../../config/Database.php';
include_once '../../../model/Farm.php';



// Instantiate new farm object
$farm = new Farm($a_database_connection);

// get data
$data = json_decode(file_get_contents('php://input'));

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    file_put_contents('php://stderr', print_r('Trying to delete farm with id: ' . $data->chickenhouseid . "\n", TRUE));
    file_put_contents('php://stderr', print_r($data, TRUE));

    try {

        $result;
        if (isset($data->chickenhouseid)) { // this if block should come before the for loop
            // Update the farm [details]
            $result = $farm->fakeDeleteFarmChickenHouseByID($data->chickenhouseid);
            // file_put_contents('../../logs/api.log', print_r("we are deleting with fakeDeleteFarm() \n", TRUE));
            // file_put_contents('../../logs/api.log', print_r('result: ' . $result, TRUE));

            if ($result) {
                // Get the farm [details]
                echo json_encode(
                    array(
                        'message' => 'Farm chicken house deleted',
                        'response' => 'OK',
                        'response_code' => http_response_code(),
                        'deleted_farm_chicken_house_id' => $result
                    )
                );
            } else {
                http_response_code(400);
                echo json_encode(
                    array(
                        'message' => 'Farm chicken house details not deleted',
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
                    'message' => 'Farm chicken house details not delete, no id provided',
                    'response' => 'NOT OK',
                    'response_code' => http_response_code(),
                    'message_details' => $result, //
                )
            );
        }

    } catch (\Throwable $err) {
        file_put_contents('php://stderr', print_r('Error while trying to delete farm chicken house: ' . $err->getMessage() . "\n", TRUE));
        http_response_code(400);
        echo json_encode(
            array(
                'message' => 'Farm chicken house not deleted',
                'response' => 'NOT OK',
                'response_code' => http_response_code(),
                'message_details' => $err, //
            )
        );
    }
}