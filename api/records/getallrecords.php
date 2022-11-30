<?php

// Headers
// https://stackoverflow.com/a/17098221
include_once '../../config/globals/header.php';

// Resources
include_once '../../config/Database.php';
include_once '../../model/Records.php';



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