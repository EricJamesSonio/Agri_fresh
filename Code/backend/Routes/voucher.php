<?php
header('Content-Type: application/json');

// Prevent HTML output from warnings/notices
error_reporting(E_ALL);
ini_set('display_errors', 0);

// Global error/exception handler
set_error_handler(function($severity, $message, $file, $line){
    http_response_code(500);
    echo json_encode(['status'=>'error','message'=>$message]);
    exit;
});

set_exception_handler(function($e){
    http_response_code(500);
    echo json_encode(['status'=>'error','message'=>$e->getMessage()]);
    exit;
});

require_once(__DIR__ . '/../../Database/db.php');

try {
    $data = json_decode(file_get_contents("php://input"), true);
    $voucherCode = trim($data['voucher_code'] ?? '');
    $customerId  = intval($data['customer_id'] ?? 0);

    if (!$voucherCode || !$customerId) {
        throw new Exception('Voucher code and customer are required.');
    }

    $stmt = $con->prepare("SELECT * FROM voucher WHERE code = ? AND is_active = 1");
    $stmt->bind_param("s", $voucherCode);
    $stmt->execute();
    $voucher = $stmt->get_result()->fetch_assoc();

    if (!$voucher) throw new Exception('Invalid or expired voucher code.');

    $today = date('Y-m-d');
    if (($voucher['start_date'] && $today < $voucher['start_date']) ||
        ($voucher['end_date'] && $today > $voucher['end_date'])) {
        throw new Exception('Voucher not valid at this time.');
    }

    $checkUsage = $con->prepare("SELECT COUNT(*) as cnt FROM orders WHERE customer_id = ? AND voucher_code = ?");
    $checkUsage->bind_param("is", $customerId, $voucherCode);
    $checkUsage->execute();
    $usage = $checkUsage->get_result()->fetch_assoc();
    if ($usage['cnt'] > 0) throw new Exception('You have already used this voucher.');

    // Return voucher info
    echo json_encode([
        'status' => 'success',
        'voucher' => [
            'type' => $voucher['type'],
            'discount_type' => $voucher['discount_type'],
            'discount_value' => floatval($voucher['discount_value'])
        ]
    ]);
} catch (Exception $e) {
    echo json_encode(['status'=>'error','message'=>$e->getMessage()]);
}
