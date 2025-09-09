<?php
session_start();
require_once(__DIR__ . '/../../database/db.php');

header('Content-Type: application/json');

$customer_id = $_SESSION['customer_id'] ?? 0;
if (!$customer_id) {
    echo json_encode(['status' => 'error', 'message' => 'Not logged in']);
    exit;
}

mysqli_query($con, "UPDATE notifications SET is_read=1 WHERE customer_id=$customer_id AND is_read=0");

echo json_encode(['status' => 'success']);
