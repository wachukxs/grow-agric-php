<?php

// Headers
include_once '../../config/globals/header.php';

// Resources
include_once '../../config/Database.php';
include_once '../../model/Farmer.php';



// Instantiate food delivery Farmer object
$farmer = new Farmer($a_database_connection);

// get data
$data = json_decode(file_get_contents('php://input'));

if (isset($data->id)) { 
    // Get ID [& set farmer id if id available]
    $inventory_id = $data->id;

    // Get the farmer [details]
    $result = $farmer->getSingleInventoryByID($inventory_id);

    // Get total number
    $total_number = $result->rowCount();

    $inventory_details_arr = array();

    if ($total_number > 0) {

        // returns an array, $row is an array
        $row = $result->fetch(PDO::FETCH_ASSOC);

        extract($row);

        // Create array
        $inventory_details_arr = array(
            'id' => $id,
            'price' => $price,
            'category' => $category,
            'id' => $id,
            'available' => $available,
            'image' => 'https://' .  $_SERVER['HTTP_HOST'] . '/chuks/food_delivery/assets/images/' . rawurlencode($image), // https://www.php.net/manual/en/function.urlencode.php#56426
            'name' => $name,
            'description' => $description
        );

        echo json_encode(
            array(
                'message' => 'Good request, no errors',
                'response' => 'OK',
                'response_code' => http_response_code(),
                'inventory_details' => $inventory_details_arr
            )
        );
    } else {
        echo json_encode(
            array(
                'message' => 'No such farmer in our records',
                'response' => 'NOT OK',
                'response_code' => http_response_code()
            )
        );

        // $order_details_arr['message'] = 'Bad request, errors';
        // $order_details_arr['response_code'] = http_response_code();
    }


} else {
    echo json_encode(
        array(
            'message' => 'Bad data provided',
            'response' => 'NOT OK',
            'response_code' => http_response_code()
        )
    );
}


// Make json and output
// print_r(json_encode($inventory_details_arr));
?>