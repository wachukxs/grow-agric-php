<?php
// Headers
// https://stackoverflow.com/a/17098221
include_once '../../../config/globals/header.php';

// Resources
include_once '../../../config/Database.php';
include_once '../../../model/Admin.php';

// Instantiate Database to get a connection
$database_connection = new Database();
$a_database_connection = $database_connection->connect();

// Instantiate new farmer object
$admin = new Admin($a_database_connection);

// get data
$data = json_decode(file_get_contents('php://input'));
file_put_contents('php://stderr', print_r("\n\n" . 'Trying to send email' . "\n", TRUE));
$admin->sendMail();