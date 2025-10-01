<?php
require_once(__DIR__ . '/../../database/db.php');

$data = json_decode(file_get_contents("php://input"), true);
$order_id = intval($data['order_id'] ?? 0);

if (!$order_id) {
    echo json_encode(["status" => "error", "message" => "Invalid order ID"]);
    exit;
}

try {
    $stmt = $con->prepare("UPDATE orders SET return_request = 1 WHERE order_id = ?");
    $ok = $stmt->execute([$order_id]);

    if ($ok) {
        echo json_encode([
            "status" => "success",
            "message" => "Return/Refund request submitted"
        ]);
    } else {
        echo json_encode([
            "status" => "error",
            "message" => "Failed to submit request"
        ]);
    }
} catch (Exception $e) {
    echo json_encode([
        "status" => "error",
        "message" => "Server error: " . $e->getMessage()
    ]);
}
