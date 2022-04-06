<?php

require __DIR__ . "/../../../vendor/autoload.php"; // https://stackoverflow.com/a/44623787/9259701
file_put_contents('php://stderr', "Hitting upload" . "\n" . "\n", FILE_APPEND | LOCK_EX);

include_once '../../../config/globals/header.php';

$dotenv = Dotenv\Dotenv::createUnsafeImmutable(__DIR__ . "/../../.."); // https://github.com/vlucas/phpdotenv#putenv-and-getenv
$dotenv->safeLoad();

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

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (
        isset($data->description, $data->name, $data->moduleid)
        &&
        !empty($data->name)
        &&
        !empty($data->description)
        &&
        !empty($data->moduleid)
    ) {
        $result = $admin->editExistingModule($data->name, $data->description, $data->moduleid);

        $module_result = $admin->getModuleByID($data->moduleid); // we need to check if there was an id, so we don't use $result which will be true|1 if there was an id, and that would select what we don't want.

        // returns an object, $row is an object
        $row = $module_result->fetch(PDO::FETCH_ASSOC);

        echo json_encode(
            array(
                'message' => 'Module edited',
                'response' => 'OK',
                'response_code' => http_response_code(),
                'new_module_details' => $row
            )
        );
    } else {
    }
}
