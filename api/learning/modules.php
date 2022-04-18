<?php
// Headers
// https://stackoverflow.com/a/17098221
include_once '../../config/globals/header.php';


// Resources
include_once '../../config/Database.php';
include_once '../../model/Module.php';
include_once '../../model/Course.php';
include_once '../../model/Farmer.php';

// Instantiate Database to get a connection
$database_connection = new Database();
$a_database_connection = $database_connection->connect();

// Instantiate food delivery Farmer object
$module = new Module($a_database_connection);
$course = new Course($a_database_connection);
$farmer = new Farmer($a_database_connection);
// get data
$data = json_decode(file_get_contents('php://input'));

/**
 * TODO: check if $_GET["id"] is set
 * also check that that module id exist in db
 */
// echo $_GET["id"];

try { 
    $course_result = $farmer->getLearningOverviewInfo($_GET["farmerid"]);
    $row1 = $course_result->fetch(PDO::FETCH_ASSOC);

    $modules_result = $module->getAllModules();
    $row1["modules"] = $modules_result->fetchAll(PDO::FETCH_ASSOC);

    $course_completion = $farmer->getLearningProgressInfo($_GET["farmerid"]);
    $row2 = $course_completion->fetch(PDO::FETCH_ASSOC);

    $saved_courses = $farmer->getSavedCoursesForFarmer($_GET["farmerid"]);
    $row1["saved_courses"] = $saved_courses->fetchAll(PDO::FETCH_ASSOC);

    $result = array_merge($row1, $row2);
    echo json_encode($result);
} catch (\Throwable $err) {
    // throw $th;
    // echo "Error";

    // hot fix
    // not sure if setting a 400 status is right giving this is a resolver endpoint
    $result = array(
        "completed_learning"=> "0",
        "detailed_total_learning_hours"=> "0",
        "in_progress_learning"=> "0",
        "modules"=> array(),
        "not_started_learning"=> "0",
        "saved_courses"=> array(),
        "total_learning_hours"=> "0",
        "total_learning_minutes"=> "0",
    );

    echo json_encode($result);

}

?>