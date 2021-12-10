<?php
// Headers
// https://stackoverflow.com/a/17098221
include_once '../../config/globals/header.php';

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