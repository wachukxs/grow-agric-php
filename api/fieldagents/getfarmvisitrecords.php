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
$field_agents = new FieldAgents($a_database_connection);

// get data
$data = json_decode(file_get_contents('php://input'));
/*


*/
file_put_contents('php://stderr', print_r("\n\n[===>] \n", TRUE));
file_put_contents('php://stderr', print_r($data, TRUE));
file_put_contents('php://stderr', print_r("\n\n[<===] \n", TRUE));

if ($_SERVER["REQUEST_METHOD"] == "GET") {
    if (isset($_GET["fieldagentid"])) {

        $result1 = $field_agents->getAllFieldAgentFarmVisitRecords($_GET["farmerid"]);
        $row1["visits"] = $result1->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode($row1);
    } else {
        file_put_contents('php://stderr', print_r("\n\n" . 'ERR: Trying to fetch previous farm visits for field agents, Bad data provided' . "\n", TRUE));

        http_response_code(400);
        echo json_encode(
            array(
                'message' => 'Bad data provided',
                'response' => 'NOT OK',
                'response_code' => http_response_code()
            )
        );
    }
    

}