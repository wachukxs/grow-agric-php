<?php
// Headers
// https://stackoverflow.com/a/17098221
include_once '../../config/globals/header.php';

// Resources
include_once '../../config/Database.php';
include_once '../../model/Admin.php';

class FieldAgentsss {
    
    public function __construct()
    {
        if ($this->assignedsubcounties) {
            // removed special chars from the string
            $this->assignedsubcounties = explode(",", $this->assignedsubcounties);
        }
    }
}

// get data
$data = json_decode(file_get_contents('php://input'));

file_put_contents('php://stderr', print_r('Trying to log in admin' . "\n", TRUE));

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Instantiate Database to get a connection

    $database_connection = new Database();
    $a_database_connection = $database_connection->connect();

    // Instantiate new farmer object
    $admin = new Admin($a_database_connection);

    
    if (
        isset($data->email, $data->password)
        &&
        !empty($data->email)
        &&
        !empty($data->password)
    ) {
        // try to check their credentials
        $result0 = $admin->getAdminByEmail($data->email);
        // file_put_contents('php://stderr', print_r($result0, TRUE)); // result0 is a PDOStatement Object (...)
        // file_put_contents('php://stderr', print_r(gettype($result0), TRUE)); // should be ==> objectArray

        // returns an array, $row1 is an array
        $row0 = $result0->fetch(PDO::FETCH_ASSOC);

        if (is_array($row0)) {
            if ($row0["password"] === $data->password) {
                // delete password
                unset($row0["password"]);

                file_put_contents('php://stderr', print_r($row0, TRUE));

                $row = array();
                $row['personalInfo'] = $row0;

                $result1 = $admin->getReviewInfo();
                $row["summary"] = $result1->fetch(PDO::FETCH_ASSOC); // not fetchAll

                $result2 = $admin->getAllFinanceApplications();
                $row["finance_applications"] = $result2->fetchAll(PDO::FETCH_ASSOC);

                $result3 = $admin->getAllFarms();
                $row["farms"] = $result3->fetchAll(PDO::FETCH_ASSOC);

                $result4 = $admin->getAllFarmers();
                $row["farmers"] = $result4->fetchAll(PDO::FETCH_ASSOC);

                $result5 = $admin->getAllCourses();
                $row["courses"] = $result5->fetchAll(PDO::FETCH_ASSOC);

                $result6 = $admin->getAllModules();
                $row["modules"] = $result6->fetchAll(PDO::FETCH_ASSOC);

                $result7 = $admin->getAllFieldAgents();
                $row["fieldagents"] = $result7->fetchAll(PDO::FETCH_CLASS, "FieldAgentsss");
        
                http_response_code();

                echo json_encode(
                    array(
                        'message' => 'Admin logged in',
                        'response' => 'OK',
                        'response_code' => http_response_code(),
                        'admin_details' => $row
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
