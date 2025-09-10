<?php
header('Content-Type: application/json');
require_once(__DIR__ . '/../../Database/db.php');

$data = json_decode(file_get_contents("php://input"), true);

try {
    $stmt = $con->prepare("DELETE FROM voucher WHERE voucher_id = ?");
    $stmt->bind_param("i", $data['voucher_id']);
    $stmt->execute();
    echo json_encode(['status'=>'success','message'=>'Voucher deleted successfully.']);
} catch (Exception $e) {
    echo json_encode(['status'=>'error','message'=>$e->getMessage()]);
}
