<?php
// Headers
include_once '../../config/globals/header.php';

// Resources
include_once '../../config/Database.php';
include_once '../../model/Farmer.php';

// Instantiate Database to get a connection
$database_connection = new Database();
$a_database_connection = $database_connection->connect();

// Instantiate food delivery farmer object
$farmer = new Farmer($a_database_connection);

// food delivery farmer query
$results = $farmer->getAllInventory();

// Get total number
$total_number = $results->rowCount();

// Check the number of farmer gotten
if ($total_number > 0) {
    $inventory_array = array();
    $inventory_array['response_code'] = http_response_code(200);
    $inventory_array['message'] = 'good request, no errors';
    $inventory_array['response']= 'OK';
    $inventory_array['data'] = array();
    
    while ($row = $results->fetch(PDO::FETCH_ASSOC)) {
        extract($row);

        $an_item = array(
            'price' => $price,
            'category' => $category,
            'id' => $id,
            'available' => $available,
            'image' => 'https://' .  $_SERVER['HTTP_HOST'] . '/chuks/food_delivery/assets/images/' . rawurlencode($image), // https://www.php.net/manual/en/function.urlencode.php#56426
            'name' => $name,
            'description' => $description
        );

        // Push to data index
        array_push($inventory_array['data'], $an_item);
    }

    // Turn to JSON & output
    echo json_encode($inventory_array);
    
} else {
    // No farmer
    echo json_encode(
        array(
            'message' => 'No farmer available',
            'response' => 'OK',
            'response_code' => http_response_code()
        )
    );

}

?>
