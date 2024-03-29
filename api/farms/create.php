<?php

// Headers
// https://stackoverflow.com/a/17098221
include_once '../../config/globals/header.php';

// Resources
include_once '../../config/Database.php';
include_once '../../model/Farm.php';



// Instantiate new farm object
$farm = new Farm($a_database_connection);

// get data
$data = json_decode(file_get_contents('php://input'));


if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // echo log tracing => look into loggin in php
    file_put_contents('php://stderr', print_r( (isset($value->id) ? 'Trying to update farm with id: ' . $data->id : 'Tying to create new farm') . "\n\n", TRUE));
    // file_put_contents('php://stderr', print_r($data, TRUE));

    try {

        if (is_array($data)) {
            $result_array = array();

            foreach ($data as $key => $value) {
                $result;
                if (isset($value->id) && !empty($value->id)) { // this if block should come before the for loop
                    // Update the farm [details]
                    file_put_contents('php://stderr', print_r("\n\nUpdating the farm [details] " . " " . "\n\n", TRUE));
                    // file_put_contents('php://stderr', print_r($value, TRUE));
                    $result = $farm->updateFarmByID($value->challengesfaced, $value->farmwardlocation, $value->farmsubcountylocation, $value->farmcountylocation, $value->farmeditems, $value->haveinsurance, $value->insurer, $value->numberofemployees, $value->otherchallengesfaced, $value->otherfarmeditems, $value->yearsfarming, $value->id);

                    // chickenhouses
                    if (!empty($value->chickenhouses)) {
                        foreach ($value->chickenhouses as $chickenhouse) {
                            if (isset($chickenhouse->id) && !empty($chickenhouse->id)) { // not in_array()
                                $farm->updateFarmChickenHouses($chickenhouse->name, $chickenhouse->farmid, $chickenhouse->id);
                            } else {
                                $farm->addFarmChickenHouses($chickenhouse->name, $chickenhouse->farmid);
                            }
                            
                        }
                    }
                } else { // create a new farm entry
                    // $value;
                    file_put_contents('php://stderr', print_r("\n\nwe are saving with createFarm() " . $value->farmsubcountylocation . " \n", TRUE));
                    file_put_contents('php://stderr', print_r($value, TRUE));
                    $result = $farm->createFarm($value->challengesfaced, $value->farmwardlocation, $value->farmsubcountylocation, $value->farmcountylocation, $value->farmeditems, $value->haveinsurance, $value->insurer, $value->numberofemployees, $value->otherchallengesfaced, $value->otherfarmeditems, $value->yearsfarming, $value->farmerid);
                    
                    // chickenhouses
                    if (!empty($value->chickenhouses)) {
                        foreach ($value->chickenhouses as $chickenhouse) {
                            if (isset($chickenhouse->id) && !empty($chickenhouse->id)) { // not in_array()
                                $farm->updateFarmChickenHouses($chickenhouse->name, $chickenhouse->farmid, $chickenhouse->id);
                            } else {
                                $farm->addFarmChickenHouses($chickenhouse->name, $result);
                            }
                            
                        }
                    }
                }

                if ($result) {
                    // $result is 1 if we're good
                    file_put_contents('php://stderr', print_r(dirname(__FILE__) . gettype($result) . "\n\n", TRUE));

                    file_put_contents('php://stderr', print_r("\n\n[last insert id for farm] " . $result . "\n\n", TRUE));
                    file_put_contents('php://stderr', print_r('Updated/created new farm record: ' . $result . "\n", TRUE));

                    array_push($result_array, $result);
                } else {
                    file_put_contents('php://stderr', print_r('Did NOT Update/create new farm record: ' . $result . "\n", TRUE));
                    // break from for loop, and set http_response_header // alternative, populate $result with true|false, and check after the loop
                }
            }


            if (!empty($result_array)) { // check that every element is true
                // Get the farm [details]
                file_put_contents('php://stderr', print_r('Gtting farm record for farmerid: ' . $data[0]->farmerid . "\n", TRUE));
                $farms_result = $farm->getAllFarmsByFarmerID($data[0]->farmerid);

                // returns an array, $row is an array
                $row = $farms_result->fetchAll(PDO::FETCH_ASSOC);
                
                foreach ($row as $key => $_farm) {

                    // fix, don't wanna see htmls special chars
                    $row[$key]["farmcountylocation"] = htmlspecialchars_decode($row[$key]["farmcountylocation"], ENT_QUOTES);
                    $row[$key]["farmsubcountylocation"] = htmlspecialchars_decode($row[$key]["farmsubcountylocation"], ENT_QUOTES);
                    $row[$key]["farmwardlocation"] = htmlspecialchars_decode($row[$key]["farmwardlocation"], ENT_QUOTES);


                    $row[$key]["chickenhouses"] = array();
                    $r = $farm->getAllFarmChickenHousesByFarmID($_farm["id"]);
                    $row[$key]["chickenhouses"] = $r->fetchAll(PDO::FETCH_ASSOC);
   
                }

                echo json_encode(
                    array(
                        'message' => 'Farm created/updated',
                        'response' => 'OK',
                        'response_code' => http_response_code(),
                        'farms' => $row
                    )
                );
            } else {
                http_response_code(400);
                echo json_encode(
                    array(
                        'message' => 'Farm details not created/updated',
                        'response' => 'NOT OK',
                        'response_code' => http_response_code(),
                        'message_details' => "Empty data provided", //
                    )
                );
            }
        } else { // not array, just pick farm details
            $result;
            if (isset($data->id) && !empty($data->id)) { // this if block should come before the for loop
                // Update the farm [details]
                
                $result = $farm->updateFarmByID($data->challengesfaced, $value->farmwardlocation, $value->farmsubcountylocation, $data->farmcountylocation, $data->farmeditems, $data->haveinsurance, $data->insurer, $data->numberofemployees, $data->otherchallengesfaced, $data->otherfarmeditems, $data->yearsfarming, $data->id);

                // chickenhouses
                if (!empty($value->chickenhouses)) {
                    foreach ($value->chickenhouses as $chickenhouse) {
                        if (isset($chickenhouse->id) && !empty($chickenhouse->id)) { // not in_array()
                            $farm->updateFarmChickenHouses($chickenhouse->name, $chickenhouse->farmid, $chickenhouse->id);
                        } else {
                            $farm->addFarmChickenHouses($chickenhouse->name, $chickenhouse->farmid);
                        }
                        
                    }
                }

                file_put_contents('php://stderr', print_r("\n\n\n\n\n\n running updateFarmByID:" . $result, TRUE));
            } else { // create a new farm entry
                // $value;
                // file_put_contents('../../logs/api.log', print_r("we are saving with createFarm() \n", TRUE));
                
                $result = $farm->createFarm($data->challengesfaced, $value->farmwardlocation, $value->farmsubcountylocation, $data->farmcountylocation, $data->farmeditems, $data->haveinsurance, $data->insurer, $data->numberofemployees, $data->otherchallengesfaced, $data->otherfarmeditems, $data->yearsfarming, $data->farmerid);

                // chickenhouses
                if (!empty($value->chickenhouses)) {
                    foreach ($value->chickenhouses as $chickenhouse) {
                        if (isset($chickenhouse->id) && !empty($chickenhouse->id)) { // not in_array()
                            $farm->updateFarmChickenHouses($chickenhouse->name, $chickenhouse->farmid, $chickenhouse->id);
                        } else {
                            $farm->addFarmChickenHouses($chickenhouse->name, $chickenhouse->farmid);
                        }
                        
                    }
                }

                file_put_contents('php://stderr', print_r("\n\n\n\n else createFarm: " . $result, TRUE));
            }
            file_put_contents('php://stderr', print_r("\n\n\n\n\n\n result:" . $result, TRUE));
            
            if ($result) { // check that $result is an int
                // Get the farm [details]
                
                $farm_result = $farm->getSingleFarmByID( isset($data->id) && !empty($data->id) ? $data->id : $result); // we need to check if there was an id, so we don't use $result which will be true|1 if there was an id, and that would select what we don't want.

                // returns an array, $row is an array
                $row = $farm_result->fetch(PDO::FETCH_ASSOC);

                // fix, don't wanna see htmls special chars
                $row["farmcountylocation"] = htmlspecialchars_decode($row["farmcountylocation"], ENT_QUOTES);
                $row["farmsubcountylocation"] = htmlspecialchars_decode($row["farmsubcountylocation"], ENT_QUOTES);
                $row["farmwardlocation"] = htmlspecialchars_decode($row["farmwardlocation"], ENT_QUOTES);
                
                $row["chickenhouses"] = $farm->getAllFarmChickenHousesByFarmID($row->id);

                if (is_array($row)) { // gettype($row) == "array"
                    // check if $row is array (means transaction was successful)

                    echo json_encode(
                        array(
                            'message' => 'Farm created/updated',
                            'response' => 'OK',
                            'response_code' => http_response_code(),
                            'farm' => $row
                        )
                    );
                }
            } else {
                // http_response_code(400);
                echo json_encode(
                    array(
                        'message' => 'Farm details not created/updated',
                        'response' => 'NOT OK',
                        'response_code' => http_response_code(),
                        'message_details' => $result, //
                    )
                );
            }
        }
    } catch (\Throwable $err) {
        file_put_contents('php://stderr', print_r('Error while trying to add/update farm: ' . $err->getMessage() . "\n", TRUE));
        
        http_response_code(400);
        echo json_encode(
            array(
                'message' => 'Farm not created/updated',
                'response' => 'NOT OK',
                'response_code' => http_response_code(),
                'message_details' => $err, //
            )
        );
    }
}
