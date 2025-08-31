<?php

require_once(__DIR__ . '/../Controller/CartController.php');

$method = $_SERVER['REQUEST_METHOD'];

$controller = new CartController();

switch($method) {
    case 'GET':
        $customer_id = $_GET['customer_id'] ?? null;
        if (!$customer_id) {
            echo json_encode(["status"=>"error","message"=>"Missing customer_id"]);
            exit;
        }
        $items = $controller->getCart($customer_id);
        echo json_encode($items);
        break;

    case 'POST':
        $data = json_decode(file_get_contents('php://input'), true);
        $customer_id = $data['customer_id'] ?? null;
        $product_id  = $data['product_id'] ?? null;
        $quantity    = $data['quantity'] ?? 1;

        if (!$customer_id || !$product_id) {
            echo json_encode(["status"=>"error","message"=>"Missing customer_id or product_id"]);
            exit;
        }

        $controller->addToCart($customer_id, $product_id, $quantity);
        echo json_encode(["status"=>"success","message"=>"Cart updated"]);
        break;

    default:
        echo json_encode(["status"=>"error","message"=>"Method not allowed"]);
        break;
}
