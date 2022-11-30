<?php
// Headers
// https://stackoverflow.com/a/17098221
include_once '../../config/globals/header.php';

// Resources
include_once '../../config/Database.php';
include_once '../../model/Farmer.php';



// Instantiate new farmer object
$farmer = new Farmer($a_database_connection);
// get data
$data = json_decode(file_get_contents('php://input'));

file_put_contents('php://stderr', print_r("Trying to add farmer learning data\n", TRUE));

if ($_SERVER["REQUEST_METHOD"] == "POST") {
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
    // http_response_code(400);

    echo json_encode(
        array(
            'message' => 'Farmer learning NOT info updated',
            'response' => 'NOT OK',
            'response_code' => http_response_code(),
            'message_details' => NULL
        )
    );
}
}

?>