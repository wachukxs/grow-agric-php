<?php
// Headers
// https://stackoverflow.com/a/17098221
include_once '../../config/globals/header.php';

// Resources
include_once '../../config/Database.php';
include_once '../../model/Records.php';
include_once '../../model/Farmer.php';
include_once '../../model/Farm.php';

// Instantiate Database to get a connection
$database_connection = new Database();
$a_database_connection = $database_connection->connect();

// Instantiate Records object
$records = new Records($a_database_connection);
$farmer = new Farmer($a_database_connection);
$farm = new Farm($a_database_connection);

// get data
$data = json_decode(file_get_contents('php://input'));
/*


*/
file_put_contents('php://stderr', print_r("\n\n[===>] \n", TRUE));
file_put_contents('php://stderr', print_r($data, TRUE));
file_put_contents('php://stderr', print_r("\n\n[<===] \n", TRUE));


if ($_SERVER["REQUEST_METHOD"] == "GET") {
    try {
        $result1 = $farmer->getAllFarmersPersonalInfo();

        $result2 = $farm->getAllFarms();

        // returns an array, $row is an array
        $row1 = $result1->fetchAll(PDO::FETCH_ASSOC); // farmers
        $row2 = $result2->fetchAll(PDO::FETCH_ASSOC); // farms

        for ($i = 0; $i < count($row1); $i++) {
            $_farmer_id = $row1[$i]['id'];

            $row1[$i]['farms'] = array_values(array_filter($row2, function($_farm) use ($_farmer_id)
            {
                return $_farm['farmerid'] == $_farmer_id;
            }));
        }

        echo json_encode(
            array(
                'message' => 'Good request, no errors',
                'response' => 'OK',
                'response_code' => http_response_code(),
                'farmers_info' => $row1,
                // 'farms' => $row2
            )
        );
    } catch (\Throwable $err) {
        http_response_code(400);

        echo json_encode(
            array(
                'message' => 'BAD request, errors',
                'response' => 'NOT OK',
                'response_code' => http_response_code()
            )
        );
    }
}

