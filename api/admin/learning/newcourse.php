<?php
require __DIR__ . "/../../../vendor/autoload.php"; // https://stackoverflow.com/a/44623787/9259701
file_put_contents('php://stderr', "Hitting upload" . "\n" . "\n", FILE_APPEND | LOCK_EX);

// Headers
// https://stackoverflow.com/a/17098221
include_once '../../../config/globals/header.php';

$dotenv = Dotenv\Dotenv::createUnsafeImmutable(__DIR__ . "/../../.."); // https://github.com/vlucas/phpdotenv#putenv-and-getenv
$dotenv->safeLoad();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
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
            exit;
        } else {
            file_put_contents('php://stderr', "Connected to " . getenv("GROW_AGRIC_HOST_NAME") . " for user $ftp_user_name" . "\n" . "\n", FILE_APPEND | LOCK_EX);

            ftp_pasv($ftp, true);
        }

        //catch indivizual key-value pair info
        //from form data
        $tz = $_POST["tz"];

        //catch and convert json object info
        $info = $_POST["info"];
        $info = json_decode($info);


        //get the file
        $ori_fname = $_FILES['file']['name'];

        //replace special chars in the file name
        $actual_fname = $_FILES['file']['name'];
        $actual_fname = preg_replace('/[^A-Za-z0-9\-]/', '', $actual_fname);

        //get file extension
        $ext = pathinfo($ori_fname, PATHINFO_EXTENSION);
        file_put_contents('php://stderr', "fil extention " . $ext . "\n" . "\n", FILE_APPEND | LOCK_EX);

        //set random unique name why because file name duplicate will replace
        //the existing files
        $modified_fname = uniqid(rand(10, 200)) . '-' . rand(1000, 1000000) . '-' . $actual_fname;

        //set target file path
        $target_path = basename($modified_fname) . "." . $ext;

        $result = array();
        // move the file to target folder
        if (move_uploaded_file($_FILES['file']['tmp_name'], $target_path)) {

            $result["status"] = 1;
            $result["message"] = "Uploaded file successfully.";

            $destination_file = getenv("SERVER_UPLOAD_PATH") . "new" . $target_path;

            // upload the file // or maybe use ftp_fput() or ftp_nb_fput()
            $upload = ftp_put($ftp, $destination_file, $target_path, FTP_BINARY);

            $result["upload"] = $upload;
            // check upload status
            if ($upload) {
                unlink($target_path);
                file_put_contents('php://stderr', "Uploaded $target_path to" . getenv("GROW_AGRIC_HOST_NAME") . " as $destination_file" . "\n" . "\n", FILE_APPEND | LOCK_EX);
            } else {
                file_put_contents('php://stderr', "FTP upload has failed!" . "\n" . "\n", FILE_APPEND | LOCK_EX);
            }
        } else {

            $result["status"] = 0;
            $result["message"] = "File upload failed. Please try again.";
        }
        // close the FTP connection 
        ftp_close($ftp);

        http_response_code();
        echo json_encode($result);
        
    } catch (\Throwable $err) {
        //throw $th;
        file_put_contents('php://stderr', "ERR: " . $err->getMessage() . "\n" . "\n", FILE_APPEND | LOCK_EX);

        http_response_code(400);
        $result = array();
        $result["status"] = 0;
        $result["message"] = $err->getMessage();
    }
}
