<?php
// Headers
// https://stackoverflow.com/a/17098221
include_once '../../config/globals/header.php';

// Resources
include_once '../../config/Database.php';
include_once '../../model/Course.php';

// Instantiate Database to get a connection
$database_connection = new Database();
$a_database_connection = $database_connection->connect();

// Instantiate Course object
$course = new Course($a_database_connection);

// get data
$data = json_decode(file_get_contents('php://input'));

/**
 * check if $_GET["id"] is set
 * also check that that module id exist in db
 */
// echo $_GET["id"];
// course id ...(might later add course and module id, not necessary though)
if (isset($_GET["courseid"])) {
    // Get the course [details]

    $course_result = $course->getSingleCourseByCourseID($_GET["courseid"], $_GET["farmerid"]);
    $row1 = $course_result->fetch(PDO::FETCH_ASSOC);

    echo json_encode($row1);
} else {
    
}

?>