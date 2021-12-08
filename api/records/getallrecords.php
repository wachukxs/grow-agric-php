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
header('Content-Control-Allow-Methods: GET');
header('Content-Control-Allow-Headers: Content-Control-Allow-Methods, Content-Type, Content-Control-Allow-Headers, Authorization, X-Requested-With');

// Resources
include_once '../../config/Database.php';
include_once '../../model/Records.php';

// Instantiate Database to get a connection
$database_connection = new Database();
$a_database_connection = $database_connection->connect();

// Instantiate Course object
$records = new Records($a_database_connection);

// get data
$data = json_decode(file_get_contents('php://input'));

file_put_contents('php://stderr', print_r('829239\\n', TRUE));
/**
 * check if $_GET["id"] is set
 * also check that that module id exist in db
 */
// echo $_GET["id"];
if (isset($_GET["farmerid"])) {
    // Get the [details]

    $sales_result = $records->getAllFarmerSalesInputRecords($_GET["farmerid"]);
    $row1["sale_records"] = $sales_result->fetchAll(PDO::FETCH_ASSOC);

    $diseases_result = $records->getAllFarmerDiseasesInputRecords($_GET["farmerid"]);
    $row1["disease_records"] = $diseases_result->fetchAll(PDO::FETCH_ASSOC);

    $mortality_result = $records->getAllFarmerMortalitiesInputRecords($_GET["farmerid"]);
    $row1["mortality_records"] = $mortality_result->fetchAll(PDO::FETCH_ASSOC);

    $brooding_result = $records->getAllFarmerBroodingInputRecords($_GET["farmerid"]);
    $row1["brooding_records"] = $brooding_result->fetchAll(PDO::FETCH_ASSOC);

    $chicken_result = $records->getAllChickenInputRecords($_GET["farmerid"]);
    $row1["chicken_inputs"] = $chicken_result->fetchAll(PDO::FETCH_ASSOC);

    $feeds_result = $records->getAllFeedsInputRecords($_GET["farmerid"]);
    $row1["feeds_inputs"] = $feeds_result->fetchAll(PDO::FETCH_ASSOC);

    $labour_result = $records->getAllFarmerLabourRecords($_GET["farmerid"]);
    $row1["labour_records"] = $labour_result->fetchAll(PDO::FETCH_ASSOC);

    $medicine_result = $records->getAllFarmerMedicineInputRecords($_GET["farmerid"]);
    $row1["medicine_records"] = $medicine_result->fetchAll(PDO::FETCH_ASSOC);

    $income_expense_result = $records->getAllFarmerOtherIncomeOrExpenseInputRecords($_GET["farmerid"]);
    $row1["income_expense_records"] = $income_expense_result->fetchAll(PDO::FETCH_ASSOC);

    $customers_result = $records->getAllFarmerCustomers($_GET["farmerid"]);
    $row1["customer_records"] = $customers_result->fetchAll(PDO::FETCH_ASSOC);

    $employees_result = $records->getAllFarmerEmployees($_GET["farmerid"]);
    $row1["employee_records"] = $employees_result->fetchAll(PDO::FETCH_ASSOC);


    

    echo json_encode($row1);
} else {
    file_put_contents('php://stderr', print_r(dirname(__FILE__) . ' NOOOO Farmer id \\n', TRUE));
}