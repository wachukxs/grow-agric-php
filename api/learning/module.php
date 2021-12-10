<?php
// Headers
// https://stackoverflow.com/a/17098221
include_once '../../config/globals/header.php';

// Resources
include_once '../../config/Database.php';
include_once '../../model/Module.php';
include_once '../../model/Course.php';

// Instantiate Database to get a connection
$database_connection = new Database();
$a_database_connection = $database_connection->connect();

// Instantiate Module and Course object
$module = new Module($a_database_connection);
$course = new Course($a_database_connection);

// get data
$data = json_decode(file_get_contents('php://input'));

/**
 * check if $_GET["id"] is set
 * also check that that module id exist in db
 */
// echo $_GET["id"];

if (isset($_GET["id"])) {
    // Get the module [details]
    $module_result = $module->getSingleModuleByID($_GET["id"]);
    $row1 = $module_result->fetch(PDO::FETCH_ASSOC);

    $courses_result = $course->getAllCoursesInModuleByModuleID($_GET["id"]);
    $row2 = $courses_result->fetchAll(PDO::FETCH_ASSOC);

    $row1["courses"] = $row2;
    $row1["numberofcourses"] = count($row2);
    echo json_encode($row1);
} else {
    
}

?>