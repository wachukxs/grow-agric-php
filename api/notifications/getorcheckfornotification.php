<?php

// Headers
// https://stackoverflow.com/a/17098221
include_once '../../config/globals/header.php';

// Resources
include_once '../../config/Database.php';
include_once '../../model/Records.php';

// Instantiate Database to get a connection
$database_connection = new Database();
$a_database_connection = $database_connection->connect();

// Instantiate Farmer object
$records = new Records($a_database_connection);

// get data
$data = json_decode(file_get_contents('php://input'));

// will remove
class CleanWebPushData {

    public function __construct()
    {
        if ($this->subscription_data) {
            $this->subscription_data = htmlspecialchars_decode($this->subscription_data, ENT_QUOTES);
        }
    }
}

// we need to add more data points for if the farmer has other device
if ($_SERVER["REQUEST_METHOD"] == "GET") {
    if (
        isset($_GET["farmerid"])
        &&
        !empty($_GET["farmerid"])
    ) {
        $result = $records->getFarmerPushNotificationData($_GET["farmerid"]);
        
        // will remove
        $_r = $result->fetchAll(PDO::FETCH_CLASS, "CleanWebPushData"); // PDO::FETCH_GROUP


        file_put_contents('php://stderr', print_r('==== ++ ' . gettype($_r) . "\n\n" , TRUE));
        
        // file_put_contents('php://stderr', print_r($_r, TRUE));

        if (is_array($_r) && count($_r) > 0) {
            echo json_encode(
                array(
                    'message' => 'Good request, we have push data for farmer',
                    'response' => 'OK',
                    'response_code' => http_response_code(),
                    // 'results' => $_r, // will remove
                )
            );
        } else {
            http_response_code(400);
            echo json_encode(
                array(
                    'message' => 'Good request, no push data saved',
                    'response' => 'NOT VERY OK',
                    'response_code' => http_response_code(),
                )
            );
        }
        
        
        

    } else {
        http_response_code(400);

        echo json_encode(
            array(
                'message' => 'Bad data provided',
                'response' => 'NOT OK',
                'response_code' => http_response_code()
            )
        );
    }
    
} else {
    
}
