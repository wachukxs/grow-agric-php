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
include_once '../../model/Farm.php';

// Instantiate Database to get a connection
$database_connection = new Database();
$a_database_connection = $database_connection->connect();

// Instantiate new farmer object
$farmer = new Farmer($a_database_connection);

$farm = new Farm($a_database_connection);

// get data
$data = json_decode(file_get_contents('php://input'));

file_put_contents('php://stderr', print_r('Trying to log in farmer' . "\n", TRUE));

if (isset($data->email, $data->password)
    &&
    !empty($data->email)
    &&
    !empty($data->password)
) { 
    // try to check their credentials
    $result1 = $farmer->loginFarmerByEmailAndPassword($data->email, $data->password);

    if ($result1) { // check that $result1 is an int

        // returns an array, $row1 is an array
        $row1 = $result1->fetch(PDO::FETCH_ASSOC);

        if (is_array($row1)) { // gettype($row1) == "array"
            // check if $row1 is array (means transaction was successful)
            // extract($row1); // uhmm, no need to extract
            file_put_contents('php://stderr', print_r($row1, TRUE));

            // fetch the farms associated with the farmer
            $result2 = $farm->getAllFarmsByFarmerID($row1["id"]);
            $row2 = $result2->fetchAll(PDO::FETCH_ASSOC); // should check if $row2 is an array too, or some form of validation

            // Create array, if we extract
            // $farmer_details_arr = array(
            //     'firstname' => $row1["firstname"],
            //     'lastname' => $row1["lastname"],
            //     'email' => $row1["email"],
            //     'phonenumber' => $row1["phonenumber"],
            //     'id' => $row1["id"],
            //     'farms' => $row2
            // );

            $farmer_details_arr = $row1;
            $farmer_details_arr["farms"] = $row2;

            echo json_encode(
                array(
                    'message' => 'Farmer logged in',
                    'response' => 'OK',
                    'response_code' => http_response_code(),
                    'farmer_details' => $farmer_details_arr
                )
            );
        } else { // $row1 is bool
            echo json_encode(
                array(
                    'message' => 'Farmer not logged',
                    'response' => 'NOT OK',
                    'response_code' => http_response_code(301),
                    'message_details' => $result1
                )
            );
        }
        
    } else {
        /**
         * $farmer->getSingleFarmerByID($result1)->fetch(PDO::FETCH_ASSOC) is false if there was an error
         */
        echo json_encode(
            array(
                'message' => 'Farmer not logged in ' . gettype($result1),
                'response' => 'OK',
                'response_code' => http_response_code(),
                'message_details' => $result1, // "SQLSTATE[23000]: Integrity constraint violation: 1062 Duplicate entry '0115335593' for key 'phonenumber'"
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