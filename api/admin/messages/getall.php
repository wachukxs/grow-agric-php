<?php

// this should be in farmer

// Headers
// https://stackoverflow.com/a/17098221
include_once '../../../config/globals/header.php';

// Resources
include_once '../../../config/Database.php';
include_once '../../../model/Admin.php';

// Instantiate Database to get a connection
$database_connection = new Database();
$a_database_connection = $database_connection->connect();

// Instantiate Course object
$admin = new Admin($a_database_connection);

// WE should def do some authentication
if ($_SERVER["REQUEST_METHOD"] == "GET") {
    
    try {

        $row = array();
        $result1;

        if (isset($_GET["farmeremail"])) { // for farmers
            $result1 = $admin->getAllFarmerMessages($_GET["farmeremail"]);

            $result2 = $admin->getAllFarmerMessages($_GET["farmeremail"]);

            $row["messages"] = $result1->fetchAll(PDO::FETCH_ASSOC); //
            $row["_messages"] = $result2->fetchAll(PDO::FETCH_GROUP);
        } else { // for admin
            $result1 = $admin->getAllFarmersWithMessages();
            $theFarmers = $result1->fetchAll(PDO::FETCH_ASSOC); // the farmer's id, firstname, lastname, and email

            $result2 = $admin->getAllAdminMessages(); // selects all from messages
            $theMessages = $result2->fetchAll(PDO::FETCH_ASSOC);

            for ($i = 0; $i < count($theFarmers); $i++) { // this is filtering only farmers with messages.

                $_farmer_email = $theFarmers[$i]['email'];
                file_put_contents('php://stderr', "CHECKING messages for: " . $_farmer_email . "\n" . "\n", FILE_APPEND | LOCK_EX);

                $theFarmers[$i]['messages'] = array_values(array_filter($theMessages, function($_message) use ($_farmer_email)
                {
                    return $_message['_from'] == $_farmer_email || $_message['_to'] == $_farmer_email;
                }));
            }

            $row["messages"] = $theFarmers;

            $result3 = $admin->getAllFarmersWithMessagesV2();
            $_f = $result3->fetchAll(PDO::FETCH_GROUP|PDO::FETCH_ASSOC|PDO::FETCH_GROUP); // grouped by farmerid, and subject

            

            foreach ($_f as $_key => $_item) {
                $arr = array();
                foreach ($_item as $key => $item) {
                    file_put_contents('php://stderr', print_r("\n\n" . 'filtering' . "key: $key\n", TRUE));
                    file_put_contents('php://stderr', print_r($_item, TRUE));
                    $arr[$item['subject']][$key] = $item;
                }
                $_f[$_key] = $arr;
            }


            $row["_messages"] = $_f;

            $result0 = $admin->getAllFarmersWithoutMessages();
            $row["no_messages"] = $result0->fetchAll(PDO::FETCH_ASSOC);
        }

        if ($result1) {
            http_response_code();
            echo json_encode($row);
        } else { // if error occured
            echo json_encode([]);
        }
        
        

        
        
    } catch (\Throwable $err) {
        //throw $th;
        $result = array();

        file_put_contents('php://stderr', "ERR getting all messages: " . $err->getMessage() . "\n" . "\n", FILE_APPEND | LOCK_EX);

        http_response_code(400);
        $result = array();
        $result["status"] = http_response_code();
        $result["message"] = $err->getMessage();

        echo json_encode($result);
    }
}