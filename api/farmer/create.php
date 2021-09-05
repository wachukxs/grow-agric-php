<?php

// Headers
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');
header('Content-Control-Allow-Methods: POST');
header('Content-Control-Allow-Headers: Content-Control-Allow-Methods, Content-Type, Content-Control-Allow-Headers, Authorization, X-Requested-With');

// Resources
include_once '../../config/Database.php';
include_once '../../model/Orders.php';

// Instantiate Database to get a connection
$database_connection = new Database();
$a_database_connection = $database_connection->connect();

// Instantiate food-delivery order object
$order = new Orders($a_database_connection);

// get data
$data = json_decode(file_get_contents('php://input'));

if (isset($data->customer_name, $data->quantity, $data->address, $data->id_of_food)
    &&
    !empty($data->customer_name)
    &&
    !empty($data->quantity)
    &&
    !empty($data->address)
    &&
    !empty($data->id_of_food)
) { // if good data was provided
    // Create the order [details]
    $result = $order->createOrder($data->customer_name, $data->quantity, $data->address, $data->id_of_food);
    if ($result) {
        // Get the order [details]
        $order_result = $order->getSingleOrderByID($result);
        // returns an array, $row is an array
        $row = $order_result->fetch(PDO::FETCH_ASSOC);

        extract($row);

        // Create array
        $order_details_arr = array(
            'customer_name' => $customer_name,
            'quantity' => $quantity,
            'address' => $address,
            'price' => $price,
            'image' => 'https://' .  $_SERVER['HTTP_HOST'] . '/chuks/food_delivery/assets/images/' . rawurlencode($image), // https://www.php.net/manual/en/function.urlencode.php#56426
            'time_of_order' => $time_of_order,
            'total' => $total,
            'name' => $name
        );

        echo json_encode(
            array(
                'message' => 'Order created',
                'response' => 'OK',
                'response_code' => http_response_code(),
                'order_details' => $order_details_arr
            )
        );
    } else {
        echo json_encode(
            array(
                'message' => 'Order not created',
                'response' => 'OK',
                'response_code' => http_response_code()
            )
        );
    }
} else { // if bad or empty data was provided
    echo json_encode(
        array(
            'message' => 'Bad data provided',
            'response' => 'NOT OK',
            'response_code' => http_response_code()
        )
    );
}

?>
