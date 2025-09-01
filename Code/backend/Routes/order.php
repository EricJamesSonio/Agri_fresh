<?php
require_once(__DIR__ . '/../Controller/OrderController.php');

$method = $_SERVER['REQUEST_METHOD'];
$controller = new OrderController(); // ✅ no need to pass $con anymore

switch($method) {
    case 'GET':
    $customer_id = $_GET['customer_id'] ?? null;
    $order_id = $_GET['order_id'] ?? null;

    if ($order_id) {
        // Get specific order
        $response = $controller->getOrder($order_id, $customer_id);
    } elseif ($customer_id) {
        // Get customer's orders
        $response = $controller->getCustomerOrders($customer_id);
    } else {
        // ✅ Admin request: get all orders
        $response = $controller->getAllOrders();
    }

    echo json_encode($response);
    break;


    case 'POST':
        $data = json_decode(file_get_contents('php://input'), true);
        $customer_id = $data['customer_id'] ?? null;
        $address_id = $data['address_id'] ?? null;
        $payment_method = $data['payment_method'] ?? 'COD';

        if (!$customer_id || !$address_id) {
            echo json_encode(["status"=>"error","message"=>"Missing customer_id or address_id"]);
            exit;
        }

        $response = $controller->createOrder($customer_id, $address_id, $payment_method);
        echo json_encode($response);
        break;

case 'PUT':
    $data = json_decode(file_get_contents('php://input'), true);
    $order_id = $data['order_id'] ?? null;
    $status = $data['status'] ?? null;
    $product_id = $data['product_id'] ?? null;

    if ($order_id && $product_id) {
        // Cancel single product
        $response = $controller->cancelOrderItem($order_id, $product_id);
    } elseif ($order_id && $status) {
        // Update entire order status
        $response = $controller->updateOrderStatus($order_id, $status);
    } else {
        echo json_encode(["status"=>"error","message"=>"Missing order_id or status/product_id"]);
        exit;
    }

    echo json_encode($response);
    break;
}
