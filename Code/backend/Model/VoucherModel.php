<?php
require_once(__DIR__ . '/../../Database/db.php');

class VoucherModel {
    private $con;

    public function __construct($con) {
        $this->con = $con;
    }

    // ✅ Validate voucher against rules
    // VoucherModel.php
public function validateVoucher($voucher_code, $customer_id, $total_amount) {
    $stmt = $this->con->prepare("SELECT * FROM voucher WHERE code = ? AND is_active = 1");
    $stmt->bind_param("s", $voucher_code);
    $stmt->execute();
    $voucher = $stmt->get_result()->fetch_assoc();

    if (!$voucher) {
        return ['valid' => false, 'message' => 'Invalid or inactive voucher.'];
    }

    $today = date('Y-m-d');
    if (($voucher['start_date'] && $today < $voucher['start_date']) ||
        ($voucher['end_date'] && $today > $voucher['end_date'])) {
        return ['valid' => false, 'message' => 'Voucher not valid at this time.'];
    }

    // Check if this customer already used this voucher
    $check = $this->con->prepare("SELECT COUNT(*) as cnt FROM orders WHERE customer_id = ? AND voucher_code = ?");
    $check->bind_param("is", $customer_id, $voucher_code);
    $check->execute();
    $used = $check->get_result()->fetch_assoc();

    if ($used['cnt'] > 0) {
        return ['valid' => false, 'message' => 'You have already used this voucher.'];
    }

    // Usage limit
    if ($voucher['usage_limit'] !== null && $voucher['used_count'] >= $voucher['usage_limit']) {
        return ['valid' => false, 'message' => 'This voucher has reached its usage limit.'];
    }

    // ✅ Calculate discount & new total
    $discount = 0;
    $newTotal = $total_amount;

    if ($voucher['type'] === 'discount') {
        if ($voucher['discount_type'] === 'percent') {
            $discount = $total_amount * ($voucher['discount_value'] / 100);
        } elseif ($voucher['discount_type'] === 'fixed') {
            $discount = $voucher['discount_value'];
        }
    } elseif ($voucher['type'] === 'free_shipping') {
        // assume shipping fee = 50 (or pass it in from controller)
        $discount = 50.00;
    }

    if ($discount > $total_amount) $discount = $total_amount; // no negative totals
    $newTotal = $total_amount - $discount;

    return [
        'valid' => true,
        'message' => 'Voucher applied successfully.',
        'discount' => $discount,       // ✅ return discount amount
        'new_total' => $newTotal
    ];
}


    // ✅ Update global usage count
    public function markUsed($voucher_code, $customer_id) {
        $stmt = $this->con->prepare("UPDATE voucher SET used_count = used_count + 1 WHERE code = ?");
        $stmt->bind_param("s", $voucher_code);
        return $stmt->execute();
    }
}
