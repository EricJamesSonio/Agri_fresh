<?php
header('Content-Type: application/json');
require_once(__DIR__ . '/../../Database/db.php');

$data = json_decode(file_get_contents("php://input"), true);

try {
    $voucher_id     = intval($data['voucher_id']);
    $code           = trim($data['code']);
    $type           = $data['type'];
    $discount_type  = !empty($data['discount_type']) ? $data['discount_type'] : null;
    $discount_value = ($data['discount_value'] !== "" && $data['discount_value'] !== null) ? floatval($data['discount_value']) : 0;
    $usage_limit    = ($data['usage_limit'] !== "" && $data['usage_limit'] !== null) ? intval($data['usage_limit']) : null;

    // ✅ Convert empty string or 0000-00-00 to NULL
    $start_date     = (!empty($data['start_date']) && $data['start_date'] !== "0000-00-00") ? $data['start_date'] : null;
    $end_date       = (!empty($data['end_date']) && $data['end_date'] !== "0000-00-00") ? $data['end_date'] : null;

    $is_active      = isset($data['is_active']) ? intval($data['is_active']) : 1;

    $sql = "
        UPDATE voucher 
        SET 
            code = ?,
            type = ?,
            discount_type = ?,
            discount_value = ?,
            usage_limit = ?,
            start_date = ?,
            end_date = ?,
            is_active = ?
        WHERE voucher_id = ?
    ";

    $stmt = $con->prepare($sql);
    if (!$stmt) {
        throw new Exception("Prepare failed: " . $con->error);
    }

    // ✅ FIXED: Use 's' (string) for date fields, 'i' for integers
    // Order: code, type, discount_type, discount_value, usage_limit, start_date, end_date, is_active, voucher_id
    // Types: s     s     s              d               i            s           s         i          i
    $stmt->bind_param(
        "sssdissii",  // ✅ Changed from "sssdisiii" to "sssdissii"
        $code,
        $type,
        $discount_type,
        $discount_value,
        $usage_limit,
        $start_date,      // These are strings (DATE format "YYYY-MM-DD") or NULL
        $end_date,        // These are strings (DATE format "YYYY-MM-DD") or NULL
        $is_active,
        $voucher_id
    );

    $stmt->execute();

    echo json_encode(['status'=>'success','message'=>'Voucher updated successfully.']);
} catch (Exception $e) {
    echo json_encode(['status'=>'error','message'=>$e->getMessage()]);
}