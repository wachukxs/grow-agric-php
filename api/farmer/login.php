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
include_once '../../model/Farmer.php';
include_once '../../model/Farm.php';
include_once '../../model/Records.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") { // hot fix for handling pre-flight request
    // Instantiate Database to get a connection
    $database_connection = new Database();
    $a_database_connection = $database_connection->connect();

    // Instantiate new farmer object
    $farmer = new Farmer($a_database_connection);

    $farm = new Farm($a_database_connection);

    $records = new Records($a_database_connection);

    // get data
    $data = json_decode(file_get_contents('php://input'));

    file_put_contents('php://stderr', print_r('Trying to log in farmer' . "\n", TRUE));

    if (
        isset($data->email, $data->password)
        &&
        !empty($data->email)
        &&
        !empty($data->password)
    ) {
        // try to check their credentials
        $result1 = $farmer->getFarmerByEmail($data->email);
        file_put_contents('php://stderr', print_r($result1, TRUE));
        file_put_contents('php://stderr', print_r(gettype($result1), TRUE));

        // returns an array, $row1 is an array
        $row1 = $result1->fetch(PDO::FETCH_ASSOC);

        if (is_array($row1)) { // gettype($row1) == "array" // check if $row1 is array (means transaction was successful)
            if ($row1["password"] === $data->password) {
                // delete password
                unset($row1["password"]);

                file_put_contents('php://stderr', print_r($row1, TRUE));

                // fetch the farms associated with the farmer
                $result2 = $farm->getAllFarmsByFarmerID($row1["id"]);
                $row2 = $result2->fetchAll(PDO::FETCH_ASSOC); // should check if $row2 is an array too, or some form of validation

                $result3 = $records->getAllFarmerEmployees($row1["id"]);
                $row3 = $result3->fetchAll(PDO::FETCH_ASSOC);

                $result4 = $records->getAllFarmerCustomers($row1["id"]);
                $row4 = $result4->fetchAll(PDO::FETCH_ASSOC);

                $farmer_details_arr["personalInfo"] = $row1;
                $farmer_details_arr["farms"] = $row2;
                $farmer_details_arr["employees"] = $row3;
                $farmer_details_arr["customers"] = $row4;

                echo json_encode(
                    array(
                        'message' => 'Farmer logged in',
                        'response' => 'OK',
                        'response_code' => http_response_code(),
                        'farmer_details' => $farmer_details_arr
                    )
                );
            } else {
                echo json_encode(
                    array(
                        'message' => 'Farmer not logged',
                        'response' => 'NOT OK',
                        'response_code' => http_response_code(403),
                        'message_details' => 'Incorrect password'
                    )
                );
            }
        } else { // $row1 is bool
            echo json_encode(
                array(
                    'message' => 'Farmer not logged',
                    'response' => 'NOT OK',
                    'response_code' => http_response_code(401),
                    'message_details' => 'Account not found'
                )
            );
        }
    } else { // if bad or empty data was provided

        file_put_contents('php://stderr', print_r('Trying to log in farmer, Bad data provided' . "\n", TRUE));

        echo json_encode(
            array(
                'message' => 'Bad data provided',
                'response' => 'NOT OK',
                'response_code' => http_response_code(400)
            )
        );
    }
}
