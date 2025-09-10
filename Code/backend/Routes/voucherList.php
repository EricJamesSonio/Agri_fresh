<?php
header('Content-Type: application/json');
require_once(__DIR__ . '/../../Database/db.php');

try {
    $stmt = $con->prepare("SELECT * FROM voucher");
    $stmt->execute();
    $vouchers = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    echo json_encode(['status'=>'success','vouchers'=>$vouchers]);
} catch (Exception $e) {
    echo json_encode(['status'=>'error','message'=>$e->getMessage()]);
}
