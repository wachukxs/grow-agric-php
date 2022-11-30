<?php
// Headers
include_once '../../config/globals/header.php';

// Resources
include_once '../../config/Database.php';
include_once '../../model/Farmer.php';
include_once '../../utilities/ICustom.php';
include_once '../../model/Admin.php';



// Instantiate farmer object
$farmer = new Farmer($a_database_connection);
$admin = new Admin($a_database_connection);

// get data
$data = json_decode(file_get_contents('php://input'));

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($data->email, $data->requestid)
        &&
        !empty($data->email)
        &&
        !empty($data->requestid)
    ) { // if good data was provided
        // Verify the farmer [email]
        $result = $farmer->getAllFarmerPasswordResetRequest($data->email);

        if ($result) {
            $row = $result->fetchAll(PDO::FETCH_ASSOC);

            file_put_contents('php://stderr', print_r("what is row all password reset request: " . "\n\n", TRUE));

            file_put_contents('php://stderr', print_r($row, TRUE));

            if (is_array($row) && count($row) > 0) {

                /**
                 * Array
(
    [0] => Array
        (
            [request_id] => 1
            [time_created] => 2022-03-22 11:10:45
            [used] => 0
        )

    [1] => Array
        (
            [request_id] => 2
            [time_created] => 2022-03-22 11:11:54
            [used] => 0
        )

)
                 */

                $it = NULL;

                // loop through // once done, mark as used
                foreach ($row as $key => $value) {
                    file_put_contents('php://stderr', print_r($value, TRUE));
                    if (hash(hash_algos()[29], $value['request_id']) == $data->requestid) {
                        $it = $value;
                        break;
                    }
                }
       
                echo json_encode(
                    array(
                        'message' => 'Verified returned true',
                        'response' => 'OK',
                        'response_code' => http_response_code(),
                        'result' => $it
                    )
                );
            } else {
                echo json_encode(
                    array(
                        'message' => 'Verification returned false',
                        'response' => 'NOT OK',
                        'response_code' => http_response_code()
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