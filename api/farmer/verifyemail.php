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

            /**
             * Array
                (
                    [id] => 1
                    [firstname] => Nwachukwu
                    [lastname] => Ossai
                    [middlename] => 
                    [email] => nwachukwuossai@gmail.com
                    [phonenumber] => 0115335593
                    [password] => pass
                    [timejoined] => 2021-09-30 19:54:15
                    [highesteducationallevel] => Secondary school
                    [maritalstatus] => Widowed
                    [age] => 20
                    [yearsofexperience] => 1
                )
             */

            file_put_contents('php://stderr', print_r("what is row farmerid: " . $row['id'] . "\n\n", TRUE));

            file_put_contents('php://stderr', print_r($row, TRUE));

            if ($row) {
                
                // create reset password request, then send reset password email
                $requestid = $farmer->createNewFarmerPasswordResetRequest($row['id']);

                // get hash of request id, hash(HASHING_ALGORITHM, 4), if hash is ...
                // email/hash


                // ger cta link, in this case, password reset link
                $cta_link = getenv("PROD_BASE_URL") . "/" . "password-reset" . "/" . $row['email'] . "/" . hash(getenv("HASHING_ALGORITHM"), $requestid);

                // sending email
                $admin->sendMail($row['firstname'], Emailing::PASSWORD_RESET, $row['email'], NULL, NULL, NULL, $cta_link);

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