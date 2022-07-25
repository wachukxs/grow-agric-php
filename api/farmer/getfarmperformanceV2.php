<?php

// Headers
// https://stackoverflow.com/a/17098221
include_once '../../config/globals/header.php';

// Resources
include_once '../../config/Database.php';
include_once '../../model/Records.php';
include_once '../../model/Course.php';

// get data
$data = json_decode(file_get_contents('php://input'));

if ($_SERVER["REQUEST_METHOD"] == "GET") {
        // Instantiate Database to get a connection
        $database_connection = new Database();
        $a_database_connection = $database_connection->connect();
    
        // Instantiate new Records object
        $records = new Records($a_database_connection);
        // Instantiate new Course object
        $course = new Course($a_database_connection);

        file_put_contents('php://stderr', print_r('Trying to get farm performance' . "\n", TRUE));

        file_put_contents('php://stderr', print_r($data, TRUE));

        if (isset($_GET["farmerid"])) {
            $row1;

            if (isset($_GET["duration"])) {
                $result1 = $records->getFarmPerformanceV2Inputs($_GET["farmerid"], $_GET["duration"]);
                $row1["inputs"] = $result1->fetchAll(PDO::FETCH_ASSOC);
                
                // TODO: include the customers from these sales
                $result2 = $records->getFarmPerformanceV2Sales($_GET["farmerid"], $_GET["duration"]);
                $row1["sales"] = $result2->fetchAll(PDO::FETCH_ASSOC);
    
                $result3 = $records->fetFarmPerformanceV2Mortalities($_GET["farmerid"], $_GET["duration"]);
                $row1["mortalities"] = $result3->fetchAll(PDO::FETCH_ASSOC);
                
                $result4 = $records->fetFarmPerformanceV2IncomeAndExpense($_GET["farmerid"], $_GET["duration"]);
                $row1["incomeandexpense"] = $result4->fetchAll(PDO::FETCH_ASSOC);


                file_put_contents('php://stderr', print_r('Trying to get farm performance::: ' . $_GET["duration"] . "\n", TRUE));

            } else {
                $result1 = $records->getFarmPerformanceV2Inputs($_GET["farmerid"]);
                $row1["inputs"] = $result1->fetchAll(PDO::FETCH_ASSOC);
                
                // TODO: include the customers from these sales
                $result2 = $records->getFarmPerformanceV2Sales($_GET["farmerid"]);
                $row1["sales"] = $result2->fetchAll(PDO::FETCH_ASSOC);
    
                $result3 = $records->fetFarmPerformanceV2Mortalities($_GET["farmerid"]);
                $row1["mortalities"] = $result3->fetchAll(PDO::FETCH_ASSOC);
                
                $result4 = $records->fetFarmPerformanceV2IncomeAndExpense($_GET["farmerid"]);
                $row1["incomeandexpense"] = $result4->fetchAll(PDO::FETCH_ASSOC);
        
            }
            
    
            // get total income & expense
    
            echo json_encode($row1);
        } else {
            
        }
}


/**8
 * 
 * -- total price/amount
                    -- total quantiry
                    -- name
                    
                    SELECT -- this has to be different
                    NULL as 'sum_price',
                    SUM(`input_records_mortalities`.`numberofdeaths`) AS 'total_no_deaths',
                    'mortalities' as name 
                    FROM `input_records_mortalities` WHERE `input_records_mortalities`.`farmerid` = 1

                    
                    UNION
                    SELECT 
                    COUNT(`input_records_medicines`.`id`) as sum1, 
                    SUM(`input_records_medicines`.`price`) AS 'total_price',
                    'medicines' as name 
                    FROM `input_records_medicines` WHERE `input_records_medicines`.`farmerid` = 1
                    
                    
                    UNION
                    SELECT 
                    COUNT(`input_records_labour`.`id`) as sum2, 
                    SUM(`input_records_labour`.`salary`) AS 'total_salary_paid',
                    'labour' as name  
                    FROM `input_records_labour` WHERE `input_records_labour`.`farmerid` = 1
                    
                    
                    UNION
                    SELECT NULL as sum3,
                    SUM(`input_records_income_expenses`.`amount`) AS 'total_income_expense_amt',
                    'income and expenses' as name  
                    FROM `input_records_income_expenses` WHERE `input_records_income_expenses`.`farmerid` = 1
                    
                    
                    UNION
                    SELECT 
                    SUM(`inputs_records_chicken`.`price`) as 'sum_chicken',
                    SUM(`inputs_records_chicken`.`quantity`) as 'total_chick_quantity', 
                    'chicken' as name  
                    FROM `inputs_records_chicken` WHERE `inputs_records_chicken`.`farmerid` = 1
                    
                    
                    UNION
                    SELECT 
                    NULL as 'sum_disease', 
                    NULL AS 'total',
                    'diseases' as name 
                    FROM `input_records_diseases` WHERE `input_records_diseases`.`farmerid` = 1
                    
                    
                    UNION
                    SELECT 
                    COUNT(`input_records_brooding`.`id`) as 'times_brooding_done', 
                    SUM(`input_records_brooding`.`amount_spent`) AS 'total_brooding_spent',
                    'brooding' as name 
                    FROM `input_records_brooding` WHERE `input_records_brooding`.`farmerid` = 1
                    
                    
                    UNION
                    SELECT 
                    COUNT(`inputs_records_feeds`.`quantity`) as sum7, 
                    SUM(`inputs_records_feeds`.`price`) as 'total_spent_feeds',
                    'feeds' as name 
                    FROM `inputs_records_feeds` WHERE `inputs_records_feeds`.`farmerid` = 1
 * 
 */