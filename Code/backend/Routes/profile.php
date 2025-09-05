<?php
session_start();
require_once(__DIR__ . '/../../Database/db.php');

$action = $_GET['action'] ?? '';
$input = json_decode(file_get_contents("php://input"), true) ?? [];

// Ensure user is logged in
if (!isset($_SESSION['customer_id']) && !isset($_SESSION['admin_id'])) {
    echo json_encode(["status" => "error", "message" => "Not logged in"]);
    exit();
}

$role = $_SESSION['role'] ?? 'customer';
$id = $_SESSION['customer_id'] ?? $_SESSION['admin_id'];

try {
    switch ($action) {
        case "getProfile":
            if ($role === 'customer') {
                $stmt = $con->prepare("SELECT customer_id AS id, first_name, last_name, email, contact FROM customer WHERE customer_id = ?");
            } else {
                $stmt = $con->prepare("SELECT admin_id AS id, first_name, last_name, email, NULL AS contact FROM admin WHERE admin_id = ?");
            }
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $result = $stmt->get_result()->fetch_assoc();
            $result['role'] = $role;

            echo json_encode(["status" => "success", "user" => $result]);
            break;

        case "updateProfile":
            if ($role === 'customer') {
                $sql = "UPDATE customer SET first_name=?, last_name=?, email=?, contact=?";
                $params = [$input['first_name'], $input['last_name'], $input['email'], $input['contact']];
                $types = "ssss";
            } else {
                $sql = "UPDATE admin SET first_name=?, last_name=?, email=?";
                $params = [$input['first_name'], $input['last_name'], $input['email']];
                $types = "sss";
            }

            if (!empty($input['password'])) {
                $sql .= ", password=?";
                $params[] = password_hash($input['password'], PASSWORD_DEFAULT);
                $types .= "s";
            }

            if ($role === 'customer') {
                $sql .= " WHERE customer_id=?";
            } else {
                $sql .= " WHERE admin_id=?";
            }
            $params[] = $id;
            $types .= "i";

            $stmt = $con->prepare($sql);
            $stmt->bind_param($types, ...$params);
            $stmt->execute();

            echo json_encode(["status" => "success", "message" => "Profile updated successfully"]);
            break;

        case "getAddresses":
            if ($role !== "customer") {
                echo json_encode(["status" => "error", "message" => "Admins don’t have addresses"]);
                exit();
            }
            $stmt = $con->prepare("SELECT address_id, street, city, state, postal_code, country, is_default,
                                          CONCAT(street, ', ', city, ', ', COALESCE(state,''), ' ', postal_code, ', ', country) AS full_address
                                   FROM customer_address WHERE customer_id = ?");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $res = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

            echo json_encode(["status" => "success", "addresses" => $res]);
            break;

        case "addAddress":
            if ($role !== "customer") {
                echo json_encode(["status" => "error", "message" => "Admins cannot add addresses"]);
                exit();
            }

            $stmt = $con->prepare("INSERT INTO customer_address (customer_id, street, city, state, postal_code, country, is_default)
                                   VALUES (?, ?, ?, ?, ?, ?, 0)");
            $stmt->bind_param("isssss", $id, $input['street'], $input['city'], $input['state'], $input['postal_code'], $input['country']);
            $stmt->execute();

            echo json_encode(["status" => "success", "message" => "Address added successfully"]);
            break;

        default:
            echo json_encode(["status" => "error", "message" => "Invalid or missing action"]);
            break;
    }
} catch (Exception $e) {
    echo json_encode(["status" => "error", "message" => $e->getMessage()]);
}
