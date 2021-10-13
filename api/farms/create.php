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
        for ($i=0; $i < count($data->farms); $i++) {
            // foreach ($arr as $key => $value) {
            //     // $arr[3] will be updated with each value from $arr...
            //     echo "{$key} => {$value} ";
            //     print_r($arr);
            // }
            $result;
            if (isset($data->farms[$i]->id)) { // this if block should come before the for loop
                // Update the farm [details]
                $result = $farm->updateFarmByID($data->farms[$i]->challengesfaced, $farmcitytownlocation, $farmcountylocation, $farmeditems, $haveinsurance, $insurer, $numberofemployees, $otherchallengesfaced, $otherfarmeditems, $yearsfarming, $id);
            } else { // create a new farm entry
                // $data->farms[$i];
                $result = $farm->createFarm($data->farms[$i]->challengesfaced, $data->farms[$i]->farmcitytownlocation, $data->farms[$i]->farmcountylocation, $data->farms[$i]->farmeditems, $data->farms[$i]->haveinsurance, $data->farms[$i]->insurer, $data->farms[$i]->numberofemployees, $data->farms[$i]->otherchallengesfaced, $data->farms[$i]->otherfarmeditems, $data->farms[$i]->yearsfarming, $data->farmerid);
            }
            if ($result) {
                // $result is 1 if we're good
                file_put_contents('php://stderr', print_r('Updated/created new farm record: ' . $result . "\n", TRUE));
            } else {
                file_put_contents('php://stderr', print_r('Did NOT Update/create new farm record: ' . $result . "\n", TRUE));
                // break from for loop, and set http_response_header // alternative, populate $result with true|false, and check after the loop
            }
            
        } // once we can finish this loop without any errors, we're good

        if ($result) { // check that $result is an int
            // Get the farm [details]
            $farms_result = $farm->getAllFarmsByFarmerID($data->farmerid);
    
            // returns an array, $row is an array
            $row = $farms_result->fetch(PDO::FETCH_ASSOC);
    
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
                        'farm_details' => $row // $farm_details_arr // will fail now cause of "global varialbe"
                    )
                );
    
                
            }
            
        } else {
            // http_response_code(400);
            echo json_encode(
                array(
                    'message' => 'Farmer not created',
                    'response' => 'NOT OK',
                    'response_code' => http_response_code(),
                    'message_details' => $result, // "SQLSTATE[23000]: Integrity constraint violation: 1062 Duplicate entry '0115335593' for key 'phonenumber'"
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
                'message_details' => $result, // "SQLSTATE[23000]: Integrity constraint violation: 1062 Duplicate entry '0115335593' for key 'phonenumber'"
            )
        );
    }


?>
