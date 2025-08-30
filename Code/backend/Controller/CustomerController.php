<?php
require_once(__DIR__ . '/../Model/CustomerModel.php');
require_once(__DIR__ . '/../../database/db.php');

class CustomerController {
    private $model;

    public function __construct() {
        global $con; // from db.php
        $this->model = new CustomerModel($con);
    }

    public function register() {
        // Get POST data
        $data = json_decode(file_get_contents("php://input"), true);

        $firstName = $data['firstName'] ?? '';
        $lastName  = $data['lastName'] ?? '';
        $email     = $data['email'] ?? '';
        $password  = $data['password'] ?? '';
        $contact   = $data['contact'] ?? '';

        // Check if email exists
        if ($this->model->getCustomerByEmail($email)) {
            echo json_encode(['success' => false, 'message' => 'Email already exists']);
            return;
        }

        // Create customer
        $result = $this->model->createCustomer($firstName, $lastName, $email, $password, $contact);

        echo json_encode($result);
    }
}
