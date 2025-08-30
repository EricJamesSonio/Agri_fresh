<?php
require_once(__DIR__ . '/../../database/db.php');

class CustomerModel {
    private $con;

    public function __construct($con) {
        $this->con = $con;
    }

    public function createCustomer($firstName, $lastName, $email, $password, $contact) {
        // Hash the password
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        $stmt = $this->con->prepare("INSERT INTO customer (first_name, last_name, email, password, contact) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("sssss", $firstName, $lastName, $email, $hashedPassword, $contact);

        if ($stmt->execute()) {
            return ['success' => true, 'customer_id' => $stmt->insert_id];
        } else {
            return ['success' => false, 'error' => $stmt->error];
        }
    }

    public function getCustomerByEmail($email) {
        $stmt = $this->con->prepare("SELECT * FROM customer WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }
}
