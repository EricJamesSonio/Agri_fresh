<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

$request = $_GET['request'] ?? '';

switch ($request) {
    case 'login':
        require_once(__DIR__ . '/../Routes/auth.php');
        break;

    default:
        echo json_encode([
            "status" => "error",
            "message" => "Unknown or missing API request"
        ]);
        break;
}
