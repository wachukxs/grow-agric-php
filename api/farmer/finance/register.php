<?php

// Headers
// https://stackoverflow.com/a/17098221
include_once '../../../config/globals/header.php';

// Resources
include_once '../../../config/Database.php';
include_once '../../../model/Finance.php';
include_once '../../../model/Admin.php';

// get data
$data = json_decode(file_get_contents('php://input'));

file_put_contents('php://stderr', print_r('Trying to register for finance' . "\n", TRUE));


if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $database_connection = new Database();
    $a_database_connection = $database_connection->connect();

    // Instantiate new Finance and Admin object
    $finance = new Finance($a_database_connection);
    $admin = new Admin($a_database_connection);

    if (isset($data->farmerid, $data->farmid)
    &&
    !empty($data->farmerid)
    &&
    !empty($data->farmid)) {

        $result =  $finance->newFinanceRegisteration($data->farmerid, $data->farmid, $data->farmbirdcapacity, 
        $data->currentfarmproduction, $data->averagemortalityrate, 
        $data->numberofchickensmoneyisfor, $data->numberofstaff, $data->preferredchickssupplier, 
        $data->preferredfeedsssupplier, 
        $data->otherpreferredchickssupplier,
        $data->otherpreferredfeedsssupplier, $data->howmuchrequired, $data->chickscost, $data->feedscost, 
        $data->broodingcost, 
        $data->dateneeded, $data->medicinesandvaccinescost, // no longer collected
        $data->projectedsales);

        if ($result instanceof Throwable || $result) {
            
            http_response_code(400);
            echo json_encode(
                array(
                    'message' => 'Badd request, there are errors',
                    'response' => 'NOT OK',
                    'response_code' => http_response_code(),
                    'message_details' => $result->getMessage()
                )
            );
        } else if (!$result) { // false
            
            http_response_code(400);
            echo json_encode(
                array(
                    'message' => 'Badd request, there are errors',
                    'response' => 'NOT OK',
                    'response_code' => http_response_code(),
                    'message_details' => $result
                )
            );
        } else {

            $finance_result = $finance->getSingleFarmerFinanceApplicationByID($result);
            $_row = $finance_result->fetch(PDO::FETCH_ASSOC);

            $result2 = $finance->getFarmerEmailAndFirstnameFromFinanceApplicationID($result);
            $farmerRow = $result2->fetch(PDO::FETCH_ASSOC);

            file_put_contents('php://stderr', "\ndate of creation " . $farmerRow['created_on'] . "\n" . "\n", FILE_APPEND | LOCK_EX);


            // send email
            if (getenv("CURR_ENV") == "production") {
                $admin->sendMail($farmerRow['firstname'], Emailing::FINANCE_APPLICATION_SUBMISSION, $farmerRow['email'], NULL, NULL, NULL, $farmerRow['created_on']);
            }

            echo json_encode(
                array(
                    'message' => 'Good request, no errors',
                    'response' => 'OK',
                    'response_code' => http_response_code(),
                    'finance_details' => $_row
                )
            );
        }
        

    } else {
        http_response_code(400);
        echo json_encode(
            array(
                'message' => 'Badd request, there are errors',
                'response' => 'NOT OK',
                'response_code' => http_response_code(),
                'order_details' => $data
            )
        );
    }
    

    
}