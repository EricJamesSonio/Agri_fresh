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
        session_start(); // make sure session is available

        $data = json_decode(file_get_contents("php://input"), true);

        $firstName = $data['firstName'] ?? '';
        $lastName  = $data['lastName'] ?? '';
        $email     = $data['email'] ?? '';
        $password  = $data['password'] ?? '';
        $contact   = $data['contact'] ?? '';
        $userOtp   = $data['otp'] ?? '';

        // --- OTP Verification ---
        if (!isset($_SESSION['otp'], $_SESSION['otp_email'], $_SESSION['otp_expiry'])) {
            echo json_encode(['success' => false, 'message' => 'No OTP session found']);
            return;
        }

        if (time() > $_SESSION['otp_expiry']) {
            echo json_encode(['success' => false, 'message' => 'OTP expired']);
            return;
        }

        if ($_SESSION['otp_email'] !== $email) {
            echo json_encode(['success' => false, 'message' => 'OTP email mismatch']);
            return;
        }

        if ($_SESSION['otp'] != $userOtp) {
            echo json_encode(['success' => false, 'message' => 'Invalid OTP']);
            return;
        }

        // --- Clear OTP after successful validation ---
        unset($_SESSION['otp'], $_SESSION['otp_email'], $_SESSION['otp_expiry']);

        // --- Check if email already exists ---
        if ($this->model->getCustomerByEmail($email)) {
            echo json_encode(['success' => false, 'message' => 'Email already exists']);
            return;
        }

        // --- Create customer ---
        $result = $this->model->createCustomer($firstName, $lastName, $email, $password, $contact);

        echo json_encode($result);
    }
}
