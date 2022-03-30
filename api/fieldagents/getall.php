<?php
// Headers
// https://stackoverflow.com/a/17098221
include_once '../../config/globals/header.php';

// Resources
include_once '../../config/Database.php';
include_once '../../model/Records.php';
include_once '../../model/Farmer.php';
include_once '../../model/Farm.php';

include_once '../../model/FieldAgents.php';

// Instantiate Database to get a connection
$database_connection = new Database();
$a_database_connection = $database_connection->connect();

// Instantiate Records object
$records = new Records($a_database_connection);
$farmer = new Farmer($a_database_connection);
$farm = new Farm($a_database_connection);
$field_agents = new FieldAgents($a_database_connection);

// get data
$data = json_decode(file_get_contents('php://input'));
/*


*/
file_put_contents('php://stderr', print_r("\n\n[===>]fieldagentid: \n", TRUE));
file_put_contents('php://stderr', print_r($_GET["fieldagentid"], TRUE));
file_put_contents('php://stderr', print_r("\n\n[<===] \n", TRUE));


// from https://www.php.net/manual/en/function.in-array.php#105251
function in_array_field($needle, $needle_field, $haystack, $strict = false) {

    if ($strict) {
        foreach ($haystack as $item)

            // file_put_contents('php://stderr', print_r("\n\n[<=== comparing{{{] \n" . $needle . "::::" . $item[$needle_field] , TRUE));
            if (isset($item->$needle_field) && stristr($needle, $item->$needle_field)) // && $item->$needle_field === $needle
                return true;
    }
    else {
        foreach ($haystack as $item)

            // file_put_contents('php://stderr', print_r("\n\n[<=== comparing{{{] \n" . $needle . ":::" . $item[$needle_field] , TRUE));
            if (isset($item[$needle_field]) && stristr($needle, $item[$needle_field])) // &&  $item->$needle_field == $needle
                return true;
    }
    return false;
}


if ($_SERVER["REQUEST_METHOD"] == "GET") {
    try {
        $result1 = $farmer->getAllFarmersPersonalInfo();

        $result2 = $farm->getAllFarms();

        // returns an array, $row is an array
        $farmers = $result1->fetchAll(PDO::FETCH_ASSOC); // farmers
        $farms = $result2->fetchAll(PDO::FETCH_ASSOC); // farms

        // isset($_GET["fieldagentid"])
        $result3 = $field_agents->getFieldAgentAssignedSubCounties($_GET["fieldagentid"]);
        $fieldAgentAssignedSubCounties = $result3->fetch(PDO::FETCH_ASSOC);


        for ($i = 0; $i < count($farmers); $i++) {

            $_farmer_id = $farmers[$i]['id'];

            $farmers[$i]['farms'] = array_values(array_filter($farms, function($_farm) use ($_farmer_id)
            {
                return $_farm['farmerid'] == $_farmer_id;
            }));
        }

        // later include farmers without farms

        $farmers = array_values(array_filter($farmers, function ($_farmer) use ($fieldAgentAssignedSubCounties) {
            if (count($_farmer['farms']) > 0) {
                // $_farmer['farms']->farmsubcountylocation
                // file_put_contents('php://stderr', print_r("\n\n[<=== mayyybeeee checking{{{] \n" , TRUE));
                // file_put_contents('php://stderr', print_r($fieldAgentAssignedSubCounties['assignedsubcounties'], TRUE));
                
                return in_array_field($fieldAgentAssignedSubCounties['assignedsubcounties'], 'farmsubcountylocation', $_farmer['farms']);

                // return stristr($fieldAgentAssignedSubCounties, $_farmer['farms']->farmsubcountylocation);

            } else {
                return false;
            }
            
        }));


        // get all field visits
        $result4 = $field_agents->getAllFarmVisitsByFieldAgent($_GET["fieldagentid"]);
        $fieldAgentFarmVisits = $result4->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode(
            array(
                'message' => 'Good request, no errors',
                'response' => 'OK',
                'response_code' => http_response_code(),
                'farmers_info' => $farmers,
                'farm_visits' => $fieldAgentFarmVisits
            )
        );
    } catch (\Throwable $err) {
        http_response_code(400);

        echo json_encode(
            array(
                'message' => 'BAD request, errors',
                'response' => 'NOT OK',
                'response_code' => http_response_code()
            )
        );
    }
}

