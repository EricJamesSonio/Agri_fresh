<?php
header('Content-Type: application/json');
require_once(__DIR__ . '/../../Database/db.php');
$data = json_decode(file_get_contents("php://input"), true);

try {
    $stmt = $con->prepare("UPDATE voucher SET code = ?, is_active = ? WHERE voucher_id = ?");
    $stmt->bind_param("sii", $data['code'], $data['is_active'], $data['voucher_id']);
    $stmt->execute();
    echo json_encode(['status'=>'success','message'=>'Voucher updated successfully.']);
} catch (Exception $e) {
    echo json_encode(['status'=>'error','message'=>$e->getMessage()]);
}
