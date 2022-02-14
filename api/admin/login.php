<?php


// Headers
// https://stackoverflow.com/a/17098221
include_once '../../config/globals/header.php';

// Resources
include_once '../../config/Database.php';
include_once '../../model/Admin.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Instantiate Database to get a connection
    $database_connection = new Database();
    $a_database_connection = $database_connection->connect();

    // Instantiate new farmer object
    $admin = new Admin($a_database_connection);

    // get data
    $data = json_decode(file_get_contents('php://input'));

    file_put_contents('php://stderr', print_r('Trying to log in farmer' . "\n", TRUE));

    if (
        isset($data->email, $data->password)
        &&
        !empty($data->email)
        &&
        !empty($data->password)
    ) {
        // try to check their credentials
        $result1 = $admin->getAdminByEmail($data->email);
        file_put_contents('php://stderr', print_r($result1, TRUE));
        file_put_contents('php://stderr', print_r(gettype($result1), TRUE));

        // returns an array, $row1 is an array
        $row1 = $result1->fetch(PDO::FETCH_ASSOC);

        if (is_array($row1)) {
            if ($row1["password"] === $data->password) {
                // delete password
                unset($row1["password"]);

                file_put_contents('php://stderr', print_r($row1, TRUE));

                echo json_encode(
                    array(
                        'message' => 'Admin logged in',
                        'response' => 'OK',
                        'response_code' => http_response_code(),
                        'admin_details' => $row1
                    )
                );
            } else {
                echo json_encode(
                    array(
                        'message' => 'Admin not logged',
                        'response' => 'NOT OK',
                        'response_code' => http_response_code(403),
                        'message_details' => 'Incorrect password'
                    )
                );
            }
        } else {
            echo json_encode(
                array(
                    'message' => 'Admin not logged',
                    'response' => 'NOT OK',
                    'response_code' => http_response_code(401),
                    'message_details' => 'Account not found'
                )
            );
        }
    } else {
        file_put_contents('php://stderr', print_r("\n\n" . 'ERR Trying to log in admin, Bad data provided' . "\n", TRUE));

        http_response_code(400);
        echo json_encode(
            array(
                'message' => 'Bad data provided',
                'response' => 'NOT OK',
                'response_code' => http_response_code()
            )
        );
    }
}
