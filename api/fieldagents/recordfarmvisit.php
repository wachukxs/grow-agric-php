<?php
// Headers
// https://stackoverflow.com/a/17098221
include_once '../../config/globals/header.php';

// Resources
include_once '../../config/Database.php';
include_once '../../model/Records.php';

// Instantiate Database to get a connection
$database_connection = new Database();
$a_database_connection = $database_connection->connect();

// Instantiate Records object
$records = new Records($a_database_connection);

// get data
$data = json_decode(file_get_contents('php://input'));
/*


*/
file_put_contents('php://stderr', print_r("\n\n[7287248===>] \n", TRUE));
file_put_contents('php://stderr', print_r($data, TRUE));
file_put_contents('php://stderr', print_r("\n\n[37835686<===] \n", TRUE));


if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (!empty($data)) {


        /**
         *
         * ageoffarmer, // skipped:
         * maritalstatusoffarmer, // skipped, 
         * farmereducationallevel, // skipped
         */
        $result = $records->saveFieldAgentFarmVisit(
            $data->fieldagentid,
            $data->farmer->id,
            $data->dateofvisit,
            $data->farmid,
            $data->farmvisittype,
            $data->didchickengainnecessaryrequiredweight,
            $data->numberofdeadchickensincelastvisit,
            $data->totalmortalitytodate,
            $data->additionalobservations,
            $data->advicegiventofarmer,
            $data->dateofnextvisit,
            $data->numberofchickenthatcanfitthecurrentchickenhouse,

            $data->farmerhousebuildingmaterial,
            $data->numberoffinancedchicken,

            $data->farmernumberofchildren,
            $data->farmernumberofchildrenlessthan18,
            $data->farmernumberofoccupants,

            $data->numberofpeopleworkingonfarm,
            $data->farmermobiledevicetype,

            $data->numberofchickenaddedbysupplierondelivery,
            $data->numberofdeadchicksondayofdelivery,

            $data->nameofinsurer,
            $data->datefarmercanstartfarmingwithus,
            $data->otherfarmedanimals,
            $data->opinionofhowmanychickenweshouldfinancefarmerfor,
            $data->howmuchfinancingisthefarmerseeking,
            $data->isfarmingontrack,
            $data->doesfarmerhavepreviousfarmingrecords,
            $data->takencopiesorphotosoffarmerpreviousfarmingrecords,
            $data->farmerchickenhousebuildingmaterial,
            $data->doesfarmerhaveexistinginsurance,
            $data->seenevidenceofexistinginsurance,
            $data->didfarmerfillcicinsuranceformcorrectly,
            $data->hasfarmerobtainedstampedvetreportwithvetregistrationnumber,
            $data->takencopiesoffarmeridsordocumentsandphonenumber,
            $data->doesfarmerkeeplayers,
            $data->seenproofthatfarmerhasbuyers
        );

        if ($result) {
            echo json_encode(
                array(
                    'message' => 'Good request, no errors',
                    'response' => 'OK',
                    'response_code' => http_response_code(),
                    'save_details' => $result
                )
            );

            
        } else {
            http_response_code(400);
            echo json_encode(
                array(
                    'message' => 'Operation failed',
                    'response' => 'NOT OK',
                    'response_code' => http_response_code()
                )
            );
        }
    } else {
        
    }
}
