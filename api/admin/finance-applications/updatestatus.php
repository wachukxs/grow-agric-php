<?php


// Headers
// https://stackoverflow.com/a/17098221
include_once '../../../config/globals/header.php';
include_once '../../../utilities/Auth.php';

// Resources
include_once '../../../config/Database.php';
include_once '../../../model/Admin.php';
include_once '../../../model/Finance.php';

// Instantiate Admin n Finance object
$admin = new Admin($a_database_connection);
$finance = new Finance($a_database_connection);

// get data
$data = json_decode(file_get_contents('php://input'));

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    try { // an if statement to check for 


        if (
            isset($data->lastupdateby, $data->status, $data->finance_application_id)
            &&
            !empty($data->lastupdateby)
            &&
            !empty($data->status)
            &&
            !empty($data->finance_application_id)
            && 
            (
                ($data->status == "APPROVED")
                XOR
                ($data->status == "DECLINED" && isset($data->reason) && !empty($data->reason))
            )
        ) {
            // tiny or hot fix:
            if ($data->status == "APPROVED") {
                $data->reason = NULL;
            }
            
            $result = $finance->updateFinanceRegistrationStatus($data->lastupdateby, $data->status, $data->finance_application_id, $data->reason);

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
        } else {
            file_put_contents('php://stderr', print_r("\n\n" . 'Bad data provided in updatestatus.php' . "\n", TRUE));

            http_response_code(400);
            echo json_encode(
                array(
                    'message' => 'Bad data provided',
                    'response' => 'NOT OK',
                    'response_code' => http_response_code()
                )
            );
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