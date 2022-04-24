<?php


// Headers
// https://stackoverflow.com/a/17098221
include_once '../../../config/globals/header.php';

// Resources
include_once '../../../config/Database.php';
include_once '../../../model/Admin.php';
include_once '../../../model/Finance.php';

// Instantiate Database to get a connection
$database_connection = new Database();
$a_database_connection = $database_connection->connect();

// Instantiate Admin n Finance object
$admin = new Admin($a_database_connection);
$finance = new Finance($a_database_connection);

// get data
$data = json_decode(file_get_contents('php://input'));

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    try { // an if statement to check for 

        $result = $finance->updateFinanceRegistrationStatus($data->lastupdateby, $data->status, $data->finance_application_id);

        if ($result) {
            $result1 = $finance->selectSingleFinanceRegistrationStatusByID($data->finance_application_id);

            $row["updated_data"] = $result1->fetch(PDO::FETCH_ASSOC);

            $result2 = $finance->getFarmerEmailAndFirstnameFromFinanceApplicationID($data->finance_application_id);
            $farmerRow = $result2->fetch(PDO::FETCH_ASSOC);

            file_put_contents('php://stderr', "\ndate of creation " . $farmerRow['created_on'] . "\n" . "\n", FILE_APPEND | LOCK_EX);

            // send email
            if (getenv("CURR_ENV") == "production") {
                $admin->sendMail($farmerRow['firstname'], Emailing::FINANCE_APPLICATION_UPDATE, $farmerRow['email'], NULL, NULL, NULL, $farmerRow['created_on']);    
            }
            
            http_response_code();
            echo json_encode($row);
        } else {
            http_response_code(400);
        }
        

    } catch (\Throwable $err) {
        $result = array();

        file_put_contents('php://stderr', "ERR updatting status: " . $err->getMessage() . "\n" . "\n", FILE_APPEND | LOCK_EX);

        http_response_code(400);
        $result = array();
        $result["status"] = 0;
        $result["message"] = $err->getMessage();
    }
}