<?php
include_once '../../config/globals/header.php';

unset($_SESSION["email"]); // https://www.php.net/manual/en/function.unset.php#119711

// what about https://www.php.net/manual/en/function.session-destroy.php ?

http_response_code();

echo json_encode(
    array(
        'message' => 'Farmer logged out',
        'response' => 'OK',
        'response_code' => http_response_code(),
    )
);

