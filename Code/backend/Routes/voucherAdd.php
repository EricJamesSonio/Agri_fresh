<?php
header('Content-Type: application/json');
require_once(__DIR__ . '/../../Database/db.php');

$data = json_decode(file_get_contents("php://input"), true);

try {
    $stmt = $con->prepare("INSERT INTO voucher (code, type, discount_type, discount_value, usage_limit, start_date, end_date) VALUES (?, ?, ?, ?, ?, ?, ?)");
   $code = $data['code'];
$type = $data['type'];
$discount_type = $data['discount_type'] ?? null;
$discount_value = isset($data['discount_value']) && $data['discount_value'] !== "" ? floatval($data['discount_value']) : 0;
$usage_limit = isset($data['usage_limit']) && $data['usage_limit'] !== "" ? intval($data['usage_limit']) : null;
$start_date = $data['start_date'] ?: null;
$end_date = $data['end_date'] ?: null;

// Prepare statement
if($usage_limit === null){
    // NULL for usage_limit
    $stmt = $con->prepare("
        INSERT INTO voucher (code, type, discount_type, discount_value, usage_limit, start_date, end_date)
        VALUES (?, ?, ?, ?, NULL, ?, ?)
    ");
    $stmt->bind_param("sssdss", $code, $type, $discount_type, $discount_value, $start_date, $end_date);
} else {
    $stmt = $con->prepare("
        INSERT INTO voucher (code, type, discount_type, discount_value, usage_limit, start_date, end_date)
        VALUES (?, ?, ?, ?, ?, ?, ?)
    ");
    $stmt->bind_param("sssddss", $code, $type, $discount_type, $discount_value, $usage_limit, $start_date, $end_date);
}

    $stmt->execute();
    echo json_encode(['status'=>'success','message'=>'Voucher added successfully.']);
} catch (Exception $e) {
    echo json_encode(['status'=>'error','message'=>$e->getMessage()]);
}
