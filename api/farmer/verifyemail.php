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
            // delete password
            unset($row["password"]);


            if ($row) {
                
                file_put_contents('php://stderr', print_r("what is row farmerid: " . $row['id'] . "\n\n", TRUE));

                file_put_contents('php://stderr', print_r($row, TRUE));
                
                // create reset password request, then send reset password email
                $requestid = $farmer->createNewFarmerPasswordResetRequest($row['id']);

                // get hash of request id, hash(HASHING_ALGORITHM, 4), if hash is ...
                // email/hash


                // ger cta link, in this case, password reset link
                $cta_link = getenv("PROD_BASE_URL") . "/" . "password-reset" . "/" . $row['email'] . "/" . hash(hash_algos()[29], $requestid);

                // sending email
                if (getenv("CURR_ENV") == "production") {
                    $admin->sendMail($row['firstname'], Emailing::PASSWORD_RESET, $row['email'], NULL, NULL, NULL, NULL, $cta_link, $row['id']);
                }

                echo json_encode(
                    array(
                        'message' => 'Verified returned true',
                        'response' => 'OK',
                        'response_code' => http_response_code(),
                        // 'result' => $row
                    )
                );
            } else {
                http_response_code(400);
                echo json_encode(
                    array(
                        'message' => 'Verification failed',
                        'response' => 'NOT OK',
                        'response_code' => http_response_code()
                    )
                );
            }
        } else {
            http_response_code(400);
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