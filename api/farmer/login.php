<?php

// Headers
// https://stackoverflow.com/a/17098221
include_once '../../config/globals/header.php';

// Resources
include_once '../../config/Database.php';
include_once '../../model/Farmer.php';
include_once '../../model/Farm.php';
include_once '../../model/Records.php';

class Farmm {
    // can do withour these
    // public $farmcountylocation;
    // public $farmsubcountylocation;
    // public $farmwardlocation;
    // public $yearsfarming;
    // public $numberofemployees;
    // public $haveinsurance;
    // public $insurer;
    // public $farmeditems;
    // public $otherfarmeditems;
    // public $challengesfaced;
    // public $otherchallengesfaced;
    // public $multiplechickenhouses;
    // public $id;
    // public $farmerid;
    // public $deleted;
    
    public function __construct()
    {
        if ($this->farmcountylocation) {
            $this->farmcountylocation = htmlspecialchars_decode($this->farmcountylocation, ENT_QUOTES);
        }
        if ($this->farmsubcountylocation) {
            $this->farmsubcountylocation = htmlspecialchars_decode($this->farmsubcountylocation, ENT_QUOTES);
        }
        if ($this->farmwardlocation) {
            $this->farmwardlocation = htmlspecialchars_decode($this->farmwardlocation, ENT_QUOTES);
        }
    }
}



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
        
        $result1 = $farmer->getFarmerWithPasswordByEmail($data->email);
        file_put_contents('php://stderr', print_r($result1, TRUE));
        file_put_contents('php://stderr', print_r(gettype($result1), TRUE));

        if (is_bool($result1)) {
            file_put_contents('php://stderr', print_r('Failed to log in farmer, Probably DB error' . "\n", TRUE));

            echo json_encode(
                array(
                    'message' => 'It\'s US, not You.',
                    'response' => 'NOT OK',
                    'response_code' => http_response_code(400)
                )
            );
        } else {
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
                    $row2 = $result2->fetchAll(PDO::FETCH_CLASS, "Farmm"); // should check if $row2 is an array too, or some form of validation

                    $result3 = $records->getAllFarmerEmployees($row1["id"]);
                    $row3 = $result3->fetchAll(PDO::FETCH_ASSOC);

                    $result4 = $records->getAllFarmerCustomers($row1["id"]);
                    $row4 = $result4->fetchAll(PDO::FETCH_ASSOC);

                    $result5 = $records->getAllFarmerFinanceApplicationStatusByFarmerID($row1["id"]);
                    $row5 = $result5->fetchAll(PDO::FETCH_ASSOC);

                    $result6 = $records->getAllFarmerUploadedDocuments($row1["id"]);
                    $row6 = $result6->fetchAll(PDO::FETCH_ASSOC);

                    $result7 = $records->getFarmerRefferals($row1["id"]);
                    $row7 = $result7->fetchAll(PDO::FETCH_ASSOC);

                    $farmer_details_arr["personalInfo"] = $row1;
                    
                    // add chickenhouses to farms
                    foreach ($row2 as $key => $_farm) {

                        $row2[$key]->chickenhouses = array();
                        $r = $farm->getAllFarmChickenHousesByFarmID($_farm->id);
                        $row2[$key]->chickenhouses = $r->fetchAll(PDO::FETCH_ASSOC);

                        // uncommenting since we started useing pdo::fetch_func
                        // $row2[$key]["chickenhouses"] = array();
                        // $r = $farm->getAllFarmChickenHousesByFarmID($_farm["id"]);
                        // $row2[$key]["chickenhouses"] = $r->fetchAll(PDO::FETCH_ASSOC);
                    }

                    $farmer_details_arr["farms"] = $row2;
                    
                    $farmer_details_arr["employees"] = $row3;
                    $farmer_details_arr["customers"] = $row4;
                    $farmer_details_arr["financeApplications"] = $row5;

                    $farmer_details_arr["uploadedDocuments"] = $row6;

                    $farmer_details_arr["reffered"] = $row7;

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
