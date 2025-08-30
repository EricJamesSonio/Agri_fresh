<?php
require_once(__DIR__ . '/../controller/AuthController.php');
require_once(__DIR__ . '/../../Database/db.php');

header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");

$data = json_decode(file_get_contents("php://input"), true);

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = $data["email"] ?? '';
    $password = $data["password"] ?? '';

    $auth = new AuthController($con);
    $response = $auth->login($email, $password);

    echo json_encode($response);
} else {
    echo json_encode(["status" => "error", "message" => "Invalid request"]);
}
