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
include_once '../../../config/Database.php';
include_once '../../../model/Finance.php';


 // get data
 $data = json_decode(file_get_contents('php://input'));

 file_put_contents('php://stderr', print_r('Trying to register for finance' . "\n", TRUE));


if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $database_connection = new Database();
    $a_database_connection = $database_connection->connect();

    // Instantiate new finance object
    $finance = new Finance($a_database_connection);

    if (isset($data->farmerid, $data->farmid)
    &&
    !empty($data->farmerid)
    &&
    !empty($data->farmid)) {


        
        $result =  $finance->newFinanceRegisteration($data->farmerid, $data->farmid, $data->farmbirdcapacity, 
        $data->currentfarmproduction, $data->averagemortalityrate, 
        $data->numberofchickensmoneyisfor, $data->numberofstaff, $data->preferredchickssupplier, 
        $data->preferredfeedsssupplier, 
        $data->otherpreferredchickssupplier,
        $data->otherpreferredfeedsssupplier, $data->howmuchrequired, $data->chickscost, $data->feedscost, 
        $data->broodingcost, 
        $data->vaccinesused, $data->medicinesused, $data->projectedsales);

        if ($result instanceof Throwable) {
            
            http_response_code(400);
            echo json_encode(
                array(
                    'message' => 'Badd request, there are errors',
                    'response' => 'OK',
                    'response_code' => http_response_code(),
                    'message_details' => $result->getMessage()
                )
            );
        } else {
            echo json_encode(
                array(
                    'message' => 'Good request, no errors',
                    'response' => 'OK',
                    'response_code' => http_response_code(),
                    'order_details' => $result
                )
            );
        }
        

    } else {
        http_response_code(400);
            echo json_encode(
                array(
                    'message' => 'Badd request, there are errors',
                    'response' => 'OK',
                    'response_code' => http_response_code(),
                    'order_details' => $data
                )
            );
    }
    

    
}