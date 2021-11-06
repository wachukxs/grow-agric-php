<?php
// Headers
// https://stackoverflow.com/a/17098221
$origin = isset($_SERVER['HTTP_ORIGIN']) ? $_SERVER['HTTP_ORIGIN'] : NULL;
$allowed_domains = [
    'https://farmers.growagric.com',
    'https://grow-agric.netlify.app',
    'http://localhost:4005',
];
// output to debug console/output
file_put_contents('php://stderr', print_r('Checking origin ' . $origin . ' for CORS access' . "\n", TRUE)); // or var_export($foo, true)

if (isset($origin) && in_array($origin, $allowed_domains)) {
    file_put_contents('php://stderr', print_r('Valid CORS access for ' . $origin . "\n", TRUE));
    header('Access-Control-Allow-Origin: ' . $origin);
} else {
    file_put_contents('php://stderr', print_r('Invalid CORS access for ' . $origin . ". Trying fallback\n", TRUE));
    header('Access-Control-Allow-Origin: *');
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

file_put_contents('php://stderr', print_r("Trying to add farmer to wait list\n", TRUE));

if (isset($data->courseid, $data->currentpage, $data->readendtime, $data->readstarttime, $data->totalpages, $data->farmerid)
    &&
    !empty($data->courseid)
    &&
    !empty($data->currentpage)
    &&
    !empty($data->readendtime)
    &&
    !empty($data->readstarttime)
    &&
    !empty($data->totalpages)
    &&
    !empty($data->farmerid)
) {
    $result = $farmer->addLearningData($data->courseid, $data->currentpage, $data->readendtime, $data->readstarttime, $data->totalpages, $data->farmerid);

    echo json_encode(
        array(
            'message' => 'Farmer learning info updated',
            'response' => 'OK',
            'response_code' => http_response_code(),
            'message_details' => $result
        )
    );
} else {
    echo json_encode(
        array(
            'message' => 'Farmer learning NOT info updated',
            'response' => 'NOT OK',
            'response_code' => http_response_code(),
            'message_details' => NULL
        )
    );
}
?>