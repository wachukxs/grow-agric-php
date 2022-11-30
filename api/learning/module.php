<?php
// Headers
// https://stackoverflow.com/a/17098221
include_once '../../config/globals/header.php';

// Resources
include_once '../../config/Database.php';
include_once '../../model/Module.php';
include_once '../../model/Course.php';



// Instantiate Module and Course object
$module = new Module($a_database_connection);
$course = new Course($a_database_connection);

// get data
$data = json_decode(file_get_contents('php://input'));

/**
 * check if $_GET["id"] is set
 * we also have $_GET["farmerid"]
 * also check that that module id exist in db
 */
// echo $_GET["id"];

if (isset($_GET["id"])) {
    // Get the module [details]
    $module_result = $module->getSingleModuleByID($_GET["id"]);
    $row1 = $module_result->fetch(PDO::FETCH_ASSOC);

    $courses_result = $course->getAllCoursesInModuleByModuleID($_GET["id"]);
    $row2 = $courses_result->fetchAll(PDO::FETCH_ASSOC);

    $completed_courses_result = $course->getAllFarmerCompletedCoursesInModule($_GET["farmerid"], $_GET["id"]);
    $row3 = $completed_courses_result->fetchAll(PDO::FETCH_ASSOC);

    $incompleted_courses_result = $course->getAllFarmerIncompletedCoursesInModule($_GET["farmerid"], $_GET["id"]);
    $row4 = $incompleted_courses_result->fetchAll(PDO::FETCH_ASSOC);

    $not_started_courses_result = $course->getAllFarmerNotStartedCoursesInModule($_GET["farmerid"], $_GET["id"]);
    $row5 = $not_started_courses_result->fetchAll(PDO::FETCH_ASSOC);

    $saved_courses = $course->getAllFarmerSavedCourses($_GET["farmerid"], $_GET["id"]);
    $row6 = $saved_courses->fetchAll(PDO::FETCH_ASSOC);


    

    $row1["courses"] = $row2;
    $row1["numberofcourses"] = count($row2);

    $row1["completedcourses"] = $row3;
    $row1["incompletedcourses"] = $row4;
    $row1["notstartedcourses"] = $row5;

    $row1["savedcourses"] = $row6;
    echo json_encode($row1);
} else {
    
}

?>