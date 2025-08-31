<?php
require_once(__DIR__ . '/../Controller/OrderController.php');

$input = json_decode(file_get_contents("php://input"), true);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($input['order_id']) || !isset($input['status'])) {
        echo json_encode([
            'status' => 'error',
            'message' => 'Missing order_id or status'
        ]);
        exit;
    }

    $orderController = new OrderController();
    $result = $orderController->updateOrderStatus($input['order_id'], $input['status']);
    echo json_encode($result);
}
