<?php
require __DIR__ . "/../../../vendor/autoload.php"; // https://stackoverflow.com/a/44623787/9259701

// Headers
// https://stackoverflow.com/a/17098221
include_once '../../../config/globals/header.php';

$dotenv = Dotenv\Dotenv::createUnsafeImmutable(__DIR__ . "/../../.."); // https://github.com/vlucas/phpdotenv#putenv-and-getenv
$dotenv->safeLoad();

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
    file_put_contents('php://stderr', "\nHitting upload v22222" . "\n" . "\n", FILE_APPEND | LOCK_EX);

    try {
        // set up basic connection
        $ftp = ftp_connect(getenv("GROW_AGRIC_HOST_NAME"));
        $ftp_user_name = getenv("FTP_USERNAME") . "@" . getenv("GROW_AGRIC_HOST_NAME");
        // login with username and password
        $login_result = ftp_login($ftp, $ftp_user_name, getenv("FTP_PASSWORD"));

        // check connection
        if ((!$ftp) || (!$login_result)) {
            file_put_contents('php://stderr', "FTP connection has failed!" . "\n" . "\n", FILE_APPEND | LOCK_EX);

            file_put_contents('php://stderr', "Attempted to connect to " . getenv("GROW_AGRIC_HOST_NAME") . " for user $ftp_user_name" . "\n" . "\n", FILE_APPEND | LOCK_EX);

            // if we get here, we should exit ... return status code
            exit;
        } else {
            file_put_contents('php://stderr', "Connected to" . getenv("GROW_AGRIC_HOST_NAME") . " for user $ftp_user_name" . "\n" . "\n", FILE_APPEND | LOCK_EX);
        }

        $ext = explode('/', mime_content_type($data->file))[1]; // https://stackoverflow.com/a/52463011/9259701

        file_put_contents('php://stderr', "\nfile ext is: " . $ext . "\n" . "\n", FILE_APPEND | LOCK_EX);

        $new_file_name = preg_replace('/[[:space:]]+/', '+', ucwords($data->name)) . '.' . $ext; // https://stackoverflow.com/a/14600743/9259701
        file_put_contents('php://stderr', $new_file_name, FILE_APPEND | LOCK_EX);

        $target_path = './' . $new_file_name;

        // https://stackoverflow.com/a/39384867/9259701
        $content = base64_decode(preg_replace("/^data:[a-z]+\/[a-z]+;base64,/i", "", $data->file)); 
        
        $file = fopen($target_path, 'w');    
        fwrite($file, $content);
        fclose($file);

        $destination_file = getenv("SERVER_UPLOAD_PATH") . $new_file_name;

        $upload = ftp_put($ftp, $destination_file, $target_path, FTP_BINARY);

        $url = substr_replace($destination_file, "https://" . getenv("GROW_AGRIC_HOST_NAME"), 0, strlen(explode('/', $destination_file)[0])); // not tryna hardcode

        if ($upload) {
            unlink($target_path);

            $courseid = $admin->addNewCourse($ext, ucwords($data->name), $data->description, $url, $data->moduleid);

            if ($courseid instanceof Throwable) {
                http_response_code(400);
                echo json_encode(
                    array(
                        'message' => 'Badd request, there are errors',
                        'response' => 'NOT OK',
                        'response_code' => http_response_code(),
                        'message_details' => $courseid->getMessage()
                    )
                );
            } else {
                $course_result = $admin->getCourseByID($courseid); // we need to check if there was an id, so we don't use $result which will be true|1 if there was an id, and that would select what we don't want.

                // returns an object, $row is an object
                $row = $course_result->fetch(PDO::FETCH_ASSOC);

                file_put_contents('php://stderr', "Uploaded $target_path to" . getenv("GROW_AGRIC_HOST_NAME") . " as $destination_file" . "\n" . "\n", FILE_APPEND | LOCK_EX);

                file_put_contents('php://stderr', "URL is as $url" . "\n" . "\n", FILE_APPEND | LOCK_EX);

                echo json_encode(
                    array(
                        'message' => 'Course added to module learning list',
                        'response' => 'OK',
                        'response_code' => http_response_code(),
                        'course_details' => $row
                    )
                );
            }
            

            
        } else {
            file_put_contents('php://stderr', "FTP upload has failed!" . "\n" . "\n", FILE_APPEND | LOCK_EX);

            http_response_code(400);
        }

        
    } catch (\Throwable $err) {
        //throw $th;
        file_put_contents('php://stderr', "\nupload ERR: " . $err->getMessage() . "\n" . "\n", FILE_APPEND | LOCK_EX);

        http_response_code(400);
    }
}
