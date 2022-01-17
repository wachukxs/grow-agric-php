<?php

// Headers
// https://stackoverflow.com/a/17098221
include_once '../../config/globals/header.php';

// Resources
include_once '../../config/Database.php';
include_once '../../model/Farmer.php';
include_once '../../model/Farm.php';
include_once '../../model/Records.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") { // hot fix for handling pre-flight request
    // Instantiate Database to get a connection
    $database_connection = new Database();
    $a_database_connection = $database_connection->connect();

    // Instantiate new farmer object
    $farmer = new Farmer($a_database_connection);

    $farm = new Farm($a_database_connection);

    $records = new Records($a_database_connection);

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
        $result1 = $farmer->getFarmerByEmail($data->email);
        file_put_contents('php://stderr', print_r($result1, TRUE));
        file_put_contents('php://stderr', print_r(gettype($result1), TRUE));

        // returns an array, $row1 is an array
        $row1 = $result1->fetch(PDO::FETCH_ASSOC);

        if (is_array($row1)) { // gettype($row1) == "array" // check if $row1 is array (means transaction was successful)
            if ($row1["password"] === $data->password) {
                // delete password [& middlename, middlename is causing ish]
                unset($row1["password"]);
                unset($row1["middlename"]);

                file_put_contents('php://stderr', print_r($row1, TRUE));

                // fetch the farms associated with the farmer
                $result2 = $farm->getAllFarmsByFarmerID($row1["id"]);
                $row2 = $result2->fetchAll(PDO::FETCH_ASSOC); // should check if $row2 is an array too, or some form of validation

                $result3 = $records->getAllFarmerEmployees($row1["id"]);
                $row3 = $result3->fetchAll(PDO::FETCH_ASSOC);

                $result4 = $records->getAllFarmerCustomers($row1["id"]);
                $row4 = $result4->fetchAll(PDO::FETCH_ASSOC);

                $result5 = $records->getFinanceApplicationStatus($row1["id"]);
                $row5 = $result5->fetchAll(PDO::FETCH_ASSOC);

                $farmer_details_arr["personalInfo"] = $row1;
                
                // add chickenhouses to farms
                foreach ($row2 as $key => $_farm) {
                    $row2[$key]["chickenhouses"] = array();
                    $r = $farm->getAllFarmChickenHousesByFarmID($_farm["id"]);
                    $row2[$key]["chickenhouses"] = $r->fetchAll(PDO::FETCH_ASSOC);
   
                }

                $farmer_details_arr["farms"] = $row2;
                
                $farmer_details_arr["employees"] = $row3;
                $farmer_details_arr["customers"] = $row4;
                $farmer_details_arr["financeApplications"] = $row5;

                echo json_encode(
                    array(
                        'message' => 'Farmer logged in',
                        'response' => 'OK',
                        'response_code' => http_response_code(),
                        'farmer_details' => $farmer_details_arr
                    )
                );
            } else {
                echo json_encode(
                    array(
                        'message' => 'Farmer not logged',
                        'response' => 'NOT OK',
                        'response_code' => http_response_code(403),
                        'message_details' => 'Incorrect password'
                    )
                );
            }
        } else { // $row1 is bool
            echo json_encode(
                array(
                    'message' => 'Farmer not logged',
                    'response' => 'NOT OK',
                    'response_code' => http_response_code(401),
                    'message_details' => 'Account not found'
                )
            );
        }
    } else { // if bad or empty data was provided

        file_put_contents('php://stderr', print_r('Trying to log in farmer, Bad data provided' . "\n", TRUE));

        echo json_encode(
            array(
                'message' => 'Bad data provided',
                'response' => 'NOT OK',
                'response_code' => http_response_code(400)
            )
        );
    }
}
