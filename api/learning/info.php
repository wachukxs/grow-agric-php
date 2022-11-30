<?php
// Headers
// https://stackoverflow.com/a/17098221
include_once '../../config/globals/header.php';

// Resources
include_once '../../config/Database.php';
include_once '../../model/Farmer.php';



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

    $saved_courses = $farmer->getSavedCoursesForFarmer($_GET["farmerid"]);
    $row2["saved_courses"] = $saved_courses->fetchAll(PDO::FETCH_ASSOC);

    $result = array_merge($row1, $row2);

    echo json_encode($result);
} else {
    
}

?>