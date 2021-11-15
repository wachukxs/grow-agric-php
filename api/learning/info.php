<?php
// Headers
// https://stackoverflow.com/a/17098221
$origin = isset($_SERVER['HTTP_ORIGIN']) ? $_SERVER['HTTP_ORIGIN'] : NULL;
$allowed_domains = [
    'https://farmers.growagric.com',
    'https://grow-agric.netlify.app',
    'http://localhost:4005',
];
// output to debug console/output
file_put_contents('php://stderr', print_r('Checking origin ' . $origin . ' for CORS access' . "\n", TRUE)); // or var_export($foo, true)

if (isset($origin) && in_array($origin, $allowed_domains)) {
    file_put_contents('php://stderr', print_r('Valid CORS access for ' . $origin . "\n", TRUE));
    header('Access-Control-Allow-Origin: ' . $origin);
} else {
    file_put_contents('php://stderr', print_r('Invalid CORS access for ' . $origin . ". Trying fallback\n", TRUE));
    header('Access-Control-Allow-Origin: *');
}
// header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Headers: *');
header('Content-Type: application/json');
header('Content-Control-Allow-Methods: GET'); // does this work ?
header('Content-Control-Allow-Headers: Content-Control-Allow-Methods, Content-Type, Content-Control-Allow-Headers, Authorization, X-Requested-With');

// Resources
include_once '../../config/Database.php';
include_once '../../model/Farmer.php';

// Instantiate Database to get a connection
$database_connection = new Database();
$a_database_connection = $database_connection->connect();

// Instantiate Farmer object
$farmer = new Farmer($a_database_connection);

// get data
$data = json_decode(file_get_contents('php://input'));

/**
 * check if $_GET["id"] is set
 * also check that that module id exist in db
 */
// echo $_GET["farmerid"];
// farmer id
if (isset($_GET["farmerid"])) {
    // Get the course [details]

    $course_result = $farmer->getLearningOverviewInfo($_GET["farmerid"]);
    $row1 = $course_result->fetch(PDO::FETCH_ASSOC);

    $course_completion = $farmer->getLearningProgressInfo($_GET["farmerid"]);
    $row2 = $course_completion->fetch(PDO::FETCH_ASSOC);

    $course_chart_data = $farmer->getLearningChartDataInfo($_GET["farmerid"]);
    $row2["learning_timeline"] = $course_chart_data->fetchAll(PDO::FETCH_ASSOC);

    $result = array_merge($row1, $row2);

    echo json_encode($result);
} else {
    
}

?>