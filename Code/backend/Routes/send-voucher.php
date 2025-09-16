<?php
session_start();
require_once(__DIR__ . '/../../database/db.php');

header('Content-Type: application/json');

$data = json_decode(file_get_contents("php://input"), true);

$customer_id = intval($data['customer_id'] ?? 0);
$voucher_code = $data['voucher_code'] ?? '';

if (!$customer_id || !$voucher_code) {
    echo json_encode(['status' => 'error', 'message' => 'Missing customer or voucher code']);
    exit;
}

// Message for the customer
$message = "You received a voucher: $voucher_code. Use it on your next order!";

// Save notification
$stmt = $con->prepare("INSERT INTO notifications (customer_id, message) VALUES (?, ?)");
$stmt->bind_param("is", $customer_id, $message);
$ok = $stmt->execute();
$stmt->close();

if ($ok) {
    echo json_encode(['status' => 'success', 'message' => 'Voucher sent successfully']);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Failed to send voucher']);
}
