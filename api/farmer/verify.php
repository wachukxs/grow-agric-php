<?php
// Headers
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');

// Resources
include_once '../../config/Database.php';
include_once '../../model/Orders.php';

// Instantiate Database to get a connection
$database_connection = new Database();
$a_database_connection = $database_connection->connect();

// Instantiate food delivery order object
$order = new Orders($a_database_connection);

// get data
$data = json_decode(file_get_contents('php://input'));

if (isset($data->id)
    &&
    !empty($data->id)
) { // if good data was provided
    // Verify the order [details]
    $result = $order->verifyOrder($data->id);
    if ($result) {
        echo json_encode(
            array(
                'message' => 'Verified returned true',
                'response' => 'OK',
                'response_code' => http_response_code()
            )
        );
    } else {
        echo json_encode(
            array(
                'message' => 'Verification returned false',
                'response' => 'OK',
                'response_code' => http_response_code()
            )
        );
    }
} else { // if bad or empty data was provided
    echo json_encode(
        array(
            'message' => 'Bad or empty data provided',
            'response' => 'NOT OK',
            'response_code' => http_response_code()
        )
    );
}
?>