<?php
ini_set('display_errors', 0); // Turn off direct HTML errors
error_reporting(E_ALL);

require_once(__DIR__ . '/../Model/CustomerModel.php');
require_once(__DIR__ . '/../../Database/db.php');

header("Content-Type: application/json");

// Make sure DB connection exists
if (!isset($con) || !$con) {
    echo json_encode(["status" => "error", "message" => "Database connection failed"]);
    exit;
}

// Initialize model
$customerModel = new CustomerModel($con);

// Get HTTP method
$method = $_SERVER['REQUEST_METHOD'];

// -------------------------
// GET: fetch all customers
// -------------------------
if ($method === "GET") {
    try {
        $stmt = $con->prepare("SELECT customer_id, first_name, last_name, email, contact, created_at, updated_at FROM customer ORDER BY created_at DESC");
        $stmt->execute();
        $result = $stmt->get_result();
        $customers = [];
        while ($row = $result->fetch_assoc()) {
            $customers[] = $row;
        }
        echo json_encode($customers);
    } catch (Exception $e) {
        echo json_encode(["status" => "error", "message" => $e->getMessage()]);
    }
    exit;
}

// -------------------------
// DELETE: deactivate customer (set email/password to NULL)
// -------------------------
if ($method === "DELETE") {
    $input = file_get_contents("php://input");
    $data = json_decode($input, true);

    // Validate input
    if (!$data || !isset($data['customer_id'])) {
        echo json_encode(["status" => "error", "message" => "Missing or invalid customer_id"]);
        exit;
    }

    try {
        // Deactivate by setting email and password to NULL
        $stmt = $con->prepare("UPDATE customer SET email = NULL, password = NULL WHERE customer_id = ?");
        if (!$stmt) {
            throw new Exception($con->error);
        }

        $stmt->bind_param("i", $data['customer_id']);
        if ($stmt->execute()) {
            if ($stmt->affected_rows > 0) {
                echo json_encode(["status" => "success", "message" => "Customer deactivated"]);
            } else {
                echo json_encode(["status" => "error", "message" => "Customer not found"]);
            }
        } else {
            throw new Exception($stmt->error);
        }
    } catch (Exception $e) {
        echo json_encode(["status" => "error", "message" => $e->getMessage()]);
    }
    exit;
}

// -------------------------
// Invalid request
// -------------------------
echo json_encode(["status" => "error", "message" => "Invalid request"]);
exit;
