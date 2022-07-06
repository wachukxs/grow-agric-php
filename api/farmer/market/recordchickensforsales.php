<?php

include_once '../../../config/globals/header.php';

// Resources
include_once '../../../config/Database.php';
include_once '../../../model/Records.php';


// get data
$data = json_decode(file_get_contents('php://input'));
if ($_SERVER["REQUEST_METHOD"] == "POST") {



    if (
        isset($data->farmerid, $data->dateavailable, $data->farmcountylocation
        , $data->farmwardlocation, $data->farmsubcountylocation
        , $data->numberofchickens, $data->averagekg
        )
        &&
        !empty($data->dateavailable)
        &&
        !empty($data->farmcountylocation)
        &&
        !empty($data->farmwardlocation)
        &&
        !empty($data->farmsubcountylocation)
        &&
        !empty($data->numberofchickens)
        &&
        !empty($data->averagekg)
        &&
        !empty($data->farmerid)
    ) {
            // Instantiate Database to get a connection
            $database_connection = new Database();
            $a_database_connection = $database_connection->connect();

            // Instantiate green homes orders object
            $records = new Records($a_database_connection);

            $result = $records->createFarmerAvailableSaleForChickens($data->dateavailable, $data->farmcountylocation, $data->farmwardlocation, $data->farmsubcountylocation, $data->numberofchickens, $data->averagekg, $data->farmerid);
            
            if ($result) {
                echo json_encode(
                    array(
                        'message' => 'Saved available sale',
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
                        // 'more_details' => array(
                        //     'dateavailable' => !empty($data->dateavailable),
                        //     'farmcountylocation' => !empty($data->farmcountylocation),
                        //     'farmwardlocation' => !empty($data->farmwardlocation),
                        //     'farmsubcountylocation' => !empty($data->farmsubcountylocation),
                        //     'numberofchickens' => !empty($data->numberofchickens),
                        //     'averagekg' => !empty($data->averagekg),
                        //     'farmerid' => !empty($data->farmerid)
                        // )
                    )
                );
            }
            
    } else {
        file_put_contents('php://stderr', print_r('Omo' . "\n", TRUE));
        file_put_contents('php://stderr', print_r('bad data provided for recordchickensforsales' . "\n", TRUE));
        
        http_response_code(400);
        echo json_encode(
            array(
                'message' => 'Bad data provided',
                'response' => 'NOT OK',
                'response_code' => http_response_code(), // 400 // setting http code here causes error
                // write a global function to find out what field is empty in $data, and just give details about the general error
                'more_details' => array(
                    'dateavailable' => !empty($data->dateavailable),
                    'farmcountylocation' => !empty($data->farmcountylocation),
                    'farmwardlocation' => !empty($data->farmwardlocation),
                    'farmsubcountylocation' => !empty($data->farmsubcountylocation),
                    'numberofchickens' => !empty($data->numberofchickens),
                    'averagekg' => !empty($data->averagekg),
                    'farmerid' => !empty($data->farmerid)
                )
            )
        );
    }

}