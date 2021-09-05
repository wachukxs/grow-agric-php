<?php
// Headers
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');

// Resources
include_once '../../config/Database.php';
include_once '../../model/Orders.php';

// Instantiate Database to get a connection
$database_connection = new Database();
$a_database_connection = $database_connection->connect();

// Instantiate green homes orders object
$order = new Orders($a_database_connection);

// get data
$data = json_decode(file_get_contents('php://input'));

if (isset($data->id)) {
    $order_id =  $data->id;
        
    // Get the order [details]
    $result = $order->getSingleOrderByID($order_id);

    // Get total number
    $total_number = $result->rowCount();

    if ($total_number > 0) {
        // returns an array, $row is an array
        $row = $result->fetch(PDO::FETCH_ASSOC);

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
                'message' => 'Good request, no errors',
                'response' => 'OK',
                'response_code' => http_response_code(),
                'order_details' => $order_details_arr
            )
        );
    } else {

        echo json_encode(
            array(
                'message' => 'No such order in our records',
                'response' => 'NOT OK',
                'response_code' => http_response_code()
            )
        );

        // $order_details_arr['message'] = 'Bad request, errors';
        // $order_details_arr['response_code'] = http_response_code();
    }


    // Make json and output
    // print_r(json_encode($order_details_arr));
} else {
    echo json_encode(
        array(
            'message' => 'Bad data provided',
            'response' => 'NOT OK',
            'response_code' => http_response_code()
        )
    );
}

?>
