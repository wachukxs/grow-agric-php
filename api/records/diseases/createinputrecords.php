<?php

// Headers
// https://stackoverflow.com/a/17098221
include_once '../../../config/globals/header.php';

// Resources
include_once '../../../config/Database.php';
include_once '../../../model/Records.php';

// Instantiate Database to get a connection
$database_connection = new Database();
$a_database_connection = $database_connection->connect();

// Instantiate Records object
$records = new Records($a_database_connection);

// get data
$data = json_decode(file_get_contents('php://input'));
/*

data is an array
Array
(
    [0] => stdClass Object
        (
            [date] => 2022-01-18T21:00:00.000Z
            [diagonsis] => Thelol
            [disease] => Shared
            [vet_name] => C'mon
            [documents] => 
            [notes] => 
            [farmerid] => 147
            [farmid] => 43
        )

)

*/
file_put_contents('php://stderr', print_r("\n\n[===>] \n", TRUE));
file_put_contents('php://stderr', print_r($data, TRUE));
file_put_contents('php://stderr', print_r("\n\n[<===] \n", TRUE));
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (
        !empty($data)
        &&
        count($data) > 0
    ) {
    
        $result_array = array();
        foreach ($data as &$value) {
            // insert the record [details]
            // $notes, $_date, $diagonsis, $disease, $vet_name, $farmid, $farmerid
            $result = $records->addFarmerDiseasesInputRecord($value->notes, $value->date, $value->diagonsis, $value->disease, $value->vet_name, $value->farmid, $value->farmerid, $value->documents);
        
            // returns an int [last insert id], $result is an int

            file_put_contents('php://stderr', print_r(dirname(__FILE__) . ' type of result ' .  gettype($result), TRUE));
            
            file_put_contents('php://stderr', print_r("\n\n[result of adding disease record] " . $result . "\n\n", TRUE));

            array_push($result_array, $result);
            
        }
        
        if (in_array(false, $result_array)) {
            http_response_code(400);
            echo json_encode(
                array(
                    'message' => 'Operation failed',
                    'response' => 'NOT OK',
                    'response_code' => http_response_code()
                )
            );
        } else {
            echo json_encode(
                array(
                    'message' => 'Good request, no errors',
                    'response' => 'OK',
                    'response_code' => http_response_code(),
                    'save_details' => $result_array
                )
            );
        }
    
    } else {
        echo json_encode(
            array(
                'message' => 'Bad data provided',
                'response' => 'NOT OK',
                'response_code' => http_response_code(400)
            )
        );
    }
} else {
    file_put_contents('php://stderr', print_r("Woow 3\n", TRUE));
}