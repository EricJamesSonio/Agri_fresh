<?php
require_once(__DIR__ . '/../../database/db.php');

header("Content-Type: application/json");

$data = json_decode(file_get_contents("php://input"), true);
$order_id = intval($data['order_id'] ?? 0);

if (!$order_id) {
    echo json_encode(["status" => "error", "message" => "Invalid order ID"]);
    exit;
}

try {
    // Prepare query
    $stmt = $con->prepare("UPDATE orders SET return_request = 1 WHERE order_id = ?");
    $stmt->bind_param("i", $order_id);
    $ok = $stmt->execute();

    if ($ok && $stmt->affected_rows > 0) {
        echo json_encode([
            "status" => "success",
            "message" => "Return/Refund request submitted successfully"
        ]);
    } else {
        echo json_encode([
            "status" => "error",
            "message" => "Failed to submit request (order not found or already requested)"
        ]);
    }

    $stmt->close();
} catch (Exception $e) {
    echo json_encode([
        "status" => "error",
        "message" => "Server error: " . $e->getMessage()
    ]);
}
