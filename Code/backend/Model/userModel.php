<?php
require_once(__DIR__ . '/../../Database/db.php');

class UserModel {
    private $con;

    public function __construct($con) {
        $this->con = $con;
    }

    public function findUser($email, $password) {
        // First check admin
        $stmt = $this->con->prepare("SELECT 'admin' as role, admin_id as id, first_name, last_name, email, password FROM admin WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $admin = $stmt->get_result()->fetch_assoc();

        if ($admin && password_verify($password, $admin['password'])) {
            return $admin;
        }

        // Check customer
        $stmt = $this->con->prepare("SELECT 'customer' as role, customer_id as id, first_name, last_name, email, password FROM customer WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $customer = $stmt->get_result()->fetch_assoc();

        if ($customer && password_verify($password, $customer['password'])) {
            return $customer;
        }

        return null;
    }
}
