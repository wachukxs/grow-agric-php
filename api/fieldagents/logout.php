<?php
include_once '../../config/globals/header.php';

unset($_SESSION["email"]);

http_response_code();

echo json_encode(
    array(
        'message' => 'Fieldagent logged out',
        'response' => 'OK',
        'response_code' => http_response_code(),
    )
);