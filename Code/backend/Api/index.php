<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

require_once(__DIR__ . '/../../database/db.php'); // DB connection

$request = $_GET['request'] ?? '';

switch ($request) {
    case 'login':
        require_once(__DIR__ . '/../Routes/auth.php');
        break;

    case 'products':
        require_once(__DIR__ . '/../Routes/product.php');
        break;

    case 'signup':
        require_once(__DIR__ . '/../Routes/signup.php');
        break;

    default:
        echo json_encode([
            "status" => "error",
            "message" => "Unknown or missing API request"
        ]);
        break;
}
