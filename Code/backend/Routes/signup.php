<?php
require_once(__DIR__ . '/../Controller/CustomerController.php');

$controller = new CustomerController();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $controller->register();
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}
