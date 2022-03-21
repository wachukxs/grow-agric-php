<?php
// Headers
include_once '../../config/globals/header.php';

// Resources
include_once '../../config/Database.php';
include_once '../../model/Farmer.php';
include_once '../../utilities/Emailing.php';
include_once '../../model/Admin.php';

// Instantiate Database to get a connection
$database_connection = new Database();
$a_database_connection = $database_connection->connect();

// Instantiate farmer object
$farmer = new Farmer($a_database_connection);
$admin = new Admin($a_database_connection);

// get data
$data = json_decode(file_get_contents('php://input'));

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($data->email)
        &&
        !empty($data->email)
    ) { // if good data was provided
        // Verify the farmer [email]
        $result = $farmer->getFarmerByEmail($data->email);
        if ($result) {
            $row = $result->fetch(PDO::FETCH_ASSOC);
            if ($row) {
                
                // create reset password request, then send reset password email
                $requestid = $farmer->createNewFarmerPasswordResetRequest($row['farmerid']);

                // sending email
                $admin->sendMail($firstname, Emailing::PASSWORD_RESET, $email);
                


                echo json_encode(
                    array(
                        'message' => 'Verified returned true',
                        'response' => 'OK',
                        'response_code' => http_response_code(),
                        // 'result' => $row
                    )
                );
            }
        } else {
            echo json_encode(
                array(
                    'message' => 'Verification returned false',
                    'response' => 'NOT OK',
                    'response_code' => http_response_code()
                )
            );
        }
    } else { // if bad or empty data was provided
        http_response_code(400);
        echo json_encode(
            array(
                'message' => 'Bad or empty data provided',
                'response' => 'NOT OK',
                'response_code' => http_response_code()
            )
        );
    }
}
?>