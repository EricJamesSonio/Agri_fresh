<?php

require_once(__DIR__ . '/../Controller/ProductController.php');

$controller = new ProductController($con);

// Get the HTTP method
$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    // Handle GET requests (fetch products)
    $request_id = $_GET['id'] ?? null;
    
    if ($request_id) {
        $controller->get(intval($request_id));
    } else {
        $controller->index();
    }
} elseif ($method === 'POST' || $method === 'PUT') {
    // Handle POST/PUT requests (create/update products)
    $controller->createOrUpdate();
} else {
    // Handle unsupported methods
    http_response_code(405);
    echo json_encode([
        "status" => "error",
        "message" => "Method not allowed"
    ]);
}