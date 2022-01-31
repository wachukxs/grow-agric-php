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


if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($data->fullname, $data->email, $data->farmeditems)
    &&
    !empty($data->fullname)
    &&
    !empty($data->farmeditems)
    &&
    !empty($data->email)
) {
    $result = $farmer->addToWaitingList($data->fullname, $data->email, $data->farmeditems);
    echo json_encode(
        array(
            'message' => 'Farmer added to wait list',
            'response' => 'OK',
            'response_code' => http_response_code(),
            'message_details' => $result
        )
    );
} else {
    # code...
}
}

?>