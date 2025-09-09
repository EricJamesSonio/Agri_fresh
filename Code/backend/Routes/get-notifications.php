<?php
session_start(); // Start session to access session variables
require_once(__DIR__ . '/../../Database/db.php');

header('Content-Type: application/json');

// Get customer_id from SESSION instead of GET parameter
$customer_id = $_SESSION['customer_id'] ?? 0;

// Check if user is logged in
if (!$customer_id) {
    echo json_encode([
        'status' => 'error', 
        'message' => 'Not logged in'
    ]);
    exit;
}

// Optional: Also check if user role is customer (if you want to restrict admin access)
$user_role = $_SESSION['role'] ?? '';
if ($user_role !== 'customer') {
    echo json_encode([
        'status' => 'error', 
        'message' => 'Unauthorized access'
    ]);
    exit;
}

// Use prepared statement to prevent SQL injection
$stmt = mysqli_prepare($con, "SELECT * FROM notifications WHERE customer_id = ? ORDER BY created_at DESC");
mysqli_stmt_bind_param($stmt, "i", $customer_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

$notifications = [];
while ($row = mysqli_fetch_assoc($result)) {
    $notifications[] = $row;
}

mysqli_stmt_close($stmt);

echo json_encode($notifications);