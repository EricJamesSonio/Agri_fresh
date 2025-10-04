<?php
header('Content-Type: application/json');

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

// Get the email from POST data
$data = json_decode(file_get_contents("php://input"), true);
$email = $data['email'] ?? '';

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['success' => false, 'message' => 'Invalid email']);
    exit;
}

// Generate a 6-digit OTP
$otp = rand(100000, 999999);

// Start session and store OTP for verification
session_start();
$_SESSION['otp'] = $otp;
$_SESSION['otp_email'] = $email;
$_SESSION['otp_expiry'] = time() + 300; // 5 minutes

// Absolute path to Python
$python = 'C:\\Users\\TBPPH\\AppData\\Local\\Microsoft\\WindowsApps\\python.exe';


// Path to your Python script
$pythonScript = realpath(__DIR__ . '/../../../scripts/send-otp.py');

// Build the command
$cmd = escapeshellarg($python) . ' ' . escapeshellarg($pythonScript) . ' ' 
       . escapeshellarg($email) . ' ' . escapeshellarg($otp);

// Execute the Python script
exec($cmd . ' 2>&1', $output, $return_var);

// Return result
if ($return_var === 0) {
    echo json_encode(['success' => true, 'message' => 'OTP sent']);
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Failed to send OTP',
        'debug' => $output
    ]);
}
