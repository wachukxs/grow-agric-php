<?php

include_once '../../../config/globals/header.php';

// Resources
include_once '../../../config/Database.php';
include_once '../../../model/Records.php';

// Instantiate Database to get a connection
$database_connection = new Database();
$a_database_connection = $database_connection->connect();

// Instantiate green homes orders object
$records = new Records($a_database_connection);

// get data
$data = json_decode(file_get_contents('php://input'));

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (
        isset($data->id, $data->pricepurchasedfor, $data->datepurchased, $data->quantitybought)
        &&
        !empty($data->datepurchased)
        &&
        !empty($data->pricepurchasedfor)
        &&
        !empty($data->quantitybought)
        &&
        !empty($data->id)
    ) {
        $result = $records->editPurchaseDetailsForProducePurchase($data->id, $data->pricepurchasedfor, $data->datepurchased, $data->quantitybought);
            
            if ($result) {
                echo json_encode(
                    array(
                        'message' => 'Edited purchase details',
                        'response' => 'OK',
                        'response_code' => http_response_code(),
                        'details' => $result
                    )
                );
            } else {
                http_response_code(400);
                echo json_encode(
                    array(
                        'message' => 'Ouch, there are errors',
                        'response' => 'NOT OK',
                        'response_code' => http_response_code(),
                        'details' => $result,
                    )
                );
            }
    } else {
        file_put_contents('php://stderr', print_r('Omo' . "\n", TRUE));
        file_put_contents('php://stderr', print_r('bad data provided for setdetailsforproducebought' . "\n", TRUE));
        
        http_response_code(400);
        echo json_encode(
            array(
                'message' => 'Bad data provided',
                'response' => 'NOT OK',
                'response_code' => http_response_code(), // 400 // setting http code here causes error
            )
        );
    }
    
}