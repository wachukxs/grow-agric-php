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

// Instantiate Course object
$records = new Records($a_database_connection);

// get data
$data = json_decode(file_get_contents('php://input'));

// record_type ==> "Feeds"

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (
        $data
        &&
        isset($data[0])
        &&
        !empty($data)
    ) {
    

        $result_array = array();
        foreach ($data as &$value) {
            // insert the record [details]
            $result = $records->addFarmerMortalityInputRecord($value->reason, $value->date, $value->openingbalance, $value->numberofdeaths, $value->closingbalance, $value->farmid, $value->farmerid);
        
            // returns an int [last insert id], $result is an int

            file_put_contents('php://stderr', print_r(dirname(__FILE__) . gettype($result), TRUE));
            
            file_put_contents('php://stderr', print_r("\n\n[]" . $result, TRUE));

            array_push($result_array, $result);
            
        }
        
        echo json_encode(
            array(
                'message' => 'Good request, no errors',
                'response' => 'OK',
                'response_code' => http_response_code(),
                'save_details' => $result_array
            )
        );
    
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
    file_put_contents('php://stderr', print_r('Woow 3', TRUE));
}