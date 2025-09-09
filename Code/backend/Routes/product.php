<?php

require_once(__DIR__ . '/../Controller/ProductController.php');

$controller = new ProductController($con);

// Get the HTTP method
$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    $request_id = $_GET['id'] ?? null;
    if ($request_id) {
        $controller->get(intval($request_id));
    } else {
        $controller->index();
    }
} elseif ($method === 'POST' || $method === 'PUT') {
    $controller->createOrUpdate();
} elseif ($method === 'DELETE') {
    $request_id = $_GET['id'] ?? null;
    $request_name = $_GET['name'] ?? null;

    if ($request_id) {
        $controller->delete(intval($request_id));
    } elseif ($request_name) {
        $controller->deleteByName($request_name);
    } else {
        http_response_code(400);
        echo json_encode(["status" => "error", "message" => "Product ID or name required"]);
    }
}

