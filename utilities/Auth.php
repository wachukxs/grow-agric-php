<?php

// file_put_contents('php://stderr', "\n\nAuth.phpchecking if they are logged in\n\n\n", FILE_APPEND | LOCK_EX);


// file_put_contents('php://stderr', "\nSESSION\n", FILE_APPEND | LOCK_EX);
// file_put_contents('php://stderr', print_r($_SESSION, TRUE), FILE_APPEND | LOCK_EX);


// file_put_contents('php://stderr', "POST\n", FILE_APPEND | LOCK_EX);
// file_put_contents('php://stderr', print_r($_POST, TRUE), FILE_APPEND | LOCK_EX);
// file_put_contents('php://stderr', "GET\n", FILE_APPEND | LOCK_EX);
// file_put_contents('php://stderr', print_r($_GET, TRUE), FILE_APPEND | LOCK_EX);
// file_put_contents('php://stderr', "SERVER\n", FILE_APPEND | LOCK_EX);
// file_put_contents('php://stderr', print_r($_SERVER, TRUE), FILE_APPEND | LOCK_EX);


// if (isset($_SESSION["email"])) { // don't use session_id()

//     file_put_contents('php://stderr', session_id() . "\n\nAuth.php session should be set " . $_SESSION["email"], FILE_APPEND | LOCK_EX);
    
//     file_put_contents('php://stderr', "\n\nthey are logged in", FILE_APPEND | LOCK_EX);
// } else {

//     file_put_contents('php://stderr', "\n\nthey are NOTT loggged in", FILE_APPEND | LOCK_EX);
//     http_response_code(403);

//     echo json_encode(
//         array(
//             'message' => 'Log in to continue',
//             'response' => 'OK',
//             'response_code' => http_response_code(),
//         )
//     );

//     exit();
// }
