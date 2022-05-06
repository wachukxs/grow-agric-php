<?php

// this should be in farmer

// Headers
// https://stackoverflow.com/a/17098221

use function PHPSTORM_META\type;

include_once '../../../config/globals/header.php';

// Resources
include_once '../../../config/Database.php';
include_once '../../../model/Admin.php';

// Instantiate Database to get a connection
$database_connection = new Database();
$a_database_connection = $database_connection->connect();

// Instantiate Course object
$admin = new Admin($a_database_connection);

class Message {
    public function __construct() {
        $this->the_message = htmlspecialchars_decode($this->the_message);
    }
}

// WE should def do some authentication
if ($_SERVER["REQUEST_METHOD"] == "GET") {
    
    try {

        $row = array();
        $result1;

        if (isset($_GET["farmeremail"])) { // for farmers

            // unused ... depreciated
            // $result2 = $admin->getAllFarmerMessages($_GET["farmeremail"]);
            // $row["_messages"] = $result2->fetchAll(PDO::FETCH_GROUP);

            
            $result1 = $admin->getAllFarmerMessages($_GET["farmeremail"]);

            $_r = $result1->fetchAll(PDO::FETCH_GROUP); //
            $_q = array();
            $_q['no_of_unreads'] = 0;

            foreach ($_r as $_key => $_item) {
                $_q['msgs'][$_key]['messages'] = $_item;

                foreach ($_item as $key => $item) {
    
                    // file_put_contents('php://stderr', "-whyyyyyy are we not seeing this " . "\n" . "\n", FILE_APPEND | LOCK_EX);
    
                    // file_put_contents('php://stderr', print_r($item, TRUE) , FILE_APPEND | LOCK_EX);
    
                    $item['the_message'] = htmlspecialchars_decode($item['the_message']); // let's not see terrible special chars
                    if (($item['message_seen_by_recipient'] == false || $item['time_read'] == NULL) && stripos($item['_from'], "@growagric.com")) { // and _from fields does have @growagric
                        $_q['no_of_unreads'] = $_q['no_of_unreads'] + 1;
    
                        if (array_key_exists('unreads', $_q['msgs'][$_key])) {
                            $_q['msgs'][$_key]['unreads'] = $_q['msgs'][$_key]['unreads'] + 1 ;
                        } else {
                            $_q['msgs'][$_key]['unreads'] = 1;
                        }
                    } else {
                        if (!array_key_exists('unreads', $_q['msgs'][$_key])) {
                            $_q['msgs'][$_key]['unreads'] = 0;
                        }
                    }
                }
            }
            


            $row["messages"] = $_q;
        } else { // for admin
            $result1 = $admin->getAllFarmersWithMessages();
            $theFarmers = $result1->fetchAll(PDO::FETCH_ASSOC); // the farmer's id, firstname, lastname, and email

            $result2 = $admin->getAllAdminMessages(); // selects all from messages
            $theMessages = $result2->fetchAll(PDO::FETCH_ASSOC);

            for ($i = 0; $i < count($theFarmers); $i++) { // this is filtering only farmers with messages.

                $_farmer_email = $theFarmers[$i]['email'];

                $theFarmers[$i]['messages'] = array_values(array_filter($theMessages, function($_message) use ($_farmer_email)
                {
                    return $_message['_from'] == $_farmer_email || $_message['_to'] == $_farmer_email;
                }));
            }

            $row["messages"] = $theFarmers;

            $result3 = $admin->getAllFarmersWithMessagesV2();
            $_f = $result3->fetchAll(PDO::FETCH_GROUP|PDO::FETCH_ASSOC|PDO::FETCH_GROUP); // grouped by farmerid, and subject

            
            $_d = array();
            // https://stackoverflow.com/a/7575010/9259701
            foreach ($_f as $_key => $_item) {
                $_d[$_key]['no_of_unreads'] = 0;
                foreach ($_item as $key => $item) {
                    $_d[$_key]['messages'][$item['subject']]['msgs'][$key+1] = $item;
                    

                    if ($item['message_seen_by_recipient'] == false || $item['time_read'] == NULL) {
                        $_d[$_key]['no_of_unreads'] = $_d[$_key]['no_of_unreads'] + 1;

                        if (array_key_exists('unreads', $_d[$_key]['messages'][$item['subject']])) {
                            $_d[$_key]['messages'][$item['subject']]['unreads'] = $_d[$_key]['messages'][$item['subject']]['unreads'] + 1 ;
                        } else {
                            $_d[$_key]['messages'][$item['subject']]['unreads'] = 1;
                        }
                    } else {
                        if (!array_key_exists('unreads', $_d[$_key]['messages'][$item['subject']])) {
                            $_d[$_key]['messages'][$item['subject']]['unreads'] = 0;
                        }
                    }
                }
            }


            $row["_messages"] = $_d; // de-commisioned ... we should delete



            $result4 = $admin->getAllFarmersWithMessagesV2();
            $_h = $result4->fetchAll(PDO::FETCH_GROUP|PDO::FETCH_ASSOC);

            $_n = array();
            foreach ($_h as $_key => $_item) {
                $_n[$_key]['no_of_unreads'] = 0;
                foreach ($_item as $key => $item) {
                    $_n[$_key]['messages'][$item['subject']]['msgs'][$key] = $item;

                    file_put_contents('php://stderr', "-whyyyyyy are we not seeing this " . $item['_to'] . "\n" . "\n", FILE_APPEND | LOCK_EX);

                    $item['the_message'] = htmlspecialchars_decode($item['the_message']); // let's not see terrible special chars
                    if (($item['message_seen_by_recipient'] == false || $item['time_read'] == NULL) && stripos($item['_to'], "@growagric.com")) { // and _to fields does have @growagric
                        $_n[$_key]['no_of_unreads'] = $_n[$_key]['no_of_unreads'] + 1;

                        if (array_key_exists('unreads', $_n[$_key]['messages'][$item['subject']])) {
                            $_n[$_key]['messages'][$item['subject']]['unreads'] = $_n[$_key]['messages'][$item['subject']]['unreads'] + 1 ;
                        } else {
                            $_n[$_key]['messages'][$item['subject']]['unreads'] = 1;
                        }
                    } else {
                        if (!array_key_exists('unreads', $_n[$_key]['messages'][$item['subject']])) {
                            $_n[$_key]['messages'][$item['subject']]['unreads'] = 0;
                        }
                    }
                }

                foreach ($_n as $_key => $_item) { // clearning it up
                    foreach ($_item as $key => $item) {

                        if ($key == "messages") {

                            foreach ($item as $key_ => $item_) {

                                $_n[$_key]['messages'][$key_]['msgs'] = array_values($_n[$_key]['messages'][$key_]['msgs']); // make it an array for FrontEnd

                            }
                        }
   
                    }
                    
                }
            }
            
            $row["new_messages"] = $_n;

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