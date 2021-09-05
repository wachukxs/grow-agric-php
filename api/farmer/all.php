<?php
// Headers
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');
header('Content-Control-Allow-Methods: GET, POST');
header('Content-Control-Allow-Headers: Content-Control-Allow-Methods, Content-Type, Content-Control-Allow-Headers, Authorization, X-Requested-With');

// Resources
include_once '../../config/Database.php';
include_once '../../model/Orders.php';

// Instantiate Database to get a connection
$database_connection = new Database();
$a_database_connection = $database_connection->connect();

// Instantiate food-delivery order object
$order = new Orders($a_database_connection);

// food delivery order query
$results = $order->getAllOrders();

// Get total number
$total_number = $results->rowCount();

// Check the number of orders gotten
if ($total_number > 0) {
    $orders_array = array();
    $orders_array['response_code'] = http_response_code(200);
    $orders_array['message'] = 'good request, no errors';
    $orders_array['response']= 'OK';
    $orders_array['data'] = array();
    
    while ($row = $results->fetch(PDO::FETCH_ASSOC)) {
        extract($row);

        $an_item = array(
            'price' => $price,
            'quantity' => $quantity,
            'address' => $address,
            'time_of_order' => $time_of_order,
            'total' => $total,
            'customer_name' => $customer_name,
            'image' => 'https://' .  $_SERVER['HTTP_HOST'] . '/chuks/food_delivery/assets/images/' . rawurlencode($image), // https://www.php.net/manual/en/function.urlencode.php#56426
            'name' => $name
        );

        // Push to data index
        array_push($orders_array['data'], $an_item);
    }

    // Turn to JSON & output
    echo json_encode($orders_array);

    // Make json and output
    // print_r(json_encode($orders_array));
    
} else {
    // No orders
    echo json_encode(
        array(
            'message' => 'No orders available',
            'response' => 'OK',
            'response_code' => http_response_code()
        )
    );

}

?>
