<?php
require_once(__DIR__ . '/../../Database/db.php');

class OrderModel {
    private $con;

    public function __construct($con) {
        $this->con = $con;
    }

    public function createOrder(
        $customer_id,
        $address_id,
        $subtotal,
        $shipping_fee,
        $discount_amount,
        $total_amount,
        $payment_method = 'COD',
        $voucher_code = null
    ) {
       $stmt = $this->con->prepare("
    INSERT INTO `orders` 
    (customer_id, address_id, subtotal, shipping_fee, discount_amount, total_amount, payment_method, voucher_code, order_status) 
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'pending')
");

$stmt->bind_param(
    "iiddddss",
    $customer_id,
    $address_id,
    $subtotal,
    $shipping_fee,
    $discount_amount,
    $total_amount,
    $payment_method,
    $voucher_code
);


        if ($stmt->execute()) {
            return $this->con->insert_id;
        }
        return false;
    }

    public function addOrderDetail($order_id, $product_id, $quantity, $price_each) {
        $stmt = $this->con->prepare("
            INSERT INTO order_detail (order_id, product_id, quantity, price_each) 
            VALUES (?, ?, ?, ?)
        ");
        $stmt->bind_param("iidd", $order_id, $product_id, $quantity, $price_each);
        return $stmt->execute();
    }

    public function getOrder($order_id) {
        $stmt = $this->con->prepare("
            SELECT o.*, ca.street, ca.city, ca.state, ca.postal_code, ca.country,
                   c.first_name, c.last_name, c.email
            FROM `orders` o
            JOIN customer_address ca ON o.address_id = ca.address_id
            JOIN customer c ON o.customer_id = c.customer_id
            WHERE o.order_id = ?
        ");
        $stmt->bind_param("i", $order_id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    public function getOrderDetails($order_id) {
        $stmt = $this->con->prepare("
            SELECT od.*, 
                p.name as product_name, 
                p.description,
                p.size_value, p.size_unit,
                p.price
            FROM order_detail od
            JOIN product p ON od.product_id = p.product_id
            WHERE od.order_id = ?
        ");

        $stmt->bind_param("i", $order_id);
        $stmt->execute();
        $result = $stmt->get_result();

        $details = [];
        while ($row = $result->fetch_assoc()) {
            $details[] = $row;
        }
        return $details;
    }


    public function getCustomerOrders($customer_id) {
        $stmt = $this->con->prepare("
            SELECT o.*, ca.street, ca.city, ca.state, ca.country
            FROM `orders` o
            JOIN customer_address ca ON o.address_id = ca.address_id
            WHERE o.customer_id = ?
            ORDER BY o.created_at DESC
        ");
        $stmt->bind_param("i", $customer_id);
        $stmt->execute();
        $result = $stmt->get_result();

        $orders = [];
        while ($row = $result->fetch_assoc()) {
            $orders[] = $row;
        }
        return $orders;
    }

    public function updateOrderStatus($order_id, $status) {
        $valid_statuses = ['pending','approved','processing','shipped','delivered','cancelled','completed','refunded','returned'];
        if (!in_array($status, $valid_statuses)) {
            return false;
        }

        $stmt = $this->con->prepare("UPDATE `orders` SET order_status = ? WHERE order_id = ?");
        $stmt->bind_param("si", $status, $order_id);
        return $stmt->execute();
    }

    public function updateOrderTotal($order_id, $subtotal = 0, $shipping_fee = 0, $discount_amount = 0, $total_amount = 0) {
        $stmt = $this->con->prepare("
            UPDATE orders 
            SET subtotal = ?, shipping_fee = ?, discount_amount = ?, total_amount = ? 
            WHERE order_id = ?
        ");
        $stmt->bind_param("ddddi", $subtotal, $shipping_fee, $discount_amount, $total_amount, $order_id);
        return $stmt->execute();
    }



    public function getAllOrders() {
        $result = $this->con->query("SELECT * FROM orders ORDER BY created_at DESC");
        $orders = [];
        while ($row = $result->fetch_assoc()) {
            $orders[] = $row;
        }
        return $orders;
    }

    public function deleteOrderItem($order_id, $product_id) {
        $stmt = $this->con->prepare("DELETE FROM order_detail WHERE order_id = ? AND product_id = ?");
        $stmt->bind_param("ii", $order_id, $product_id);
        $success = $stmt->execute();

        if ($success) {
            // recalc subtotal
            $stmt2 = $this->con->prepare("SELECT SUM(quantity * price_each) as subtotal FROM order_detail WHERE order_id = ?");
            $stmt2->bind_param("i", $order_id);
            $stmt2->execute();
            $result = $stmt2->get_result()->fetch_assoc();
            $subtotal = $result['subtotal'] ?? 0;

            // fetch current shipping & discount
            $stmt3 = $this->con->prepare("SELECT shipping_fee, discount_amount FROM orders WHERE order_id = ?");
            $stmt3->bind_param("i", $order_id);
            $stmt3->execute();
            $row = $stmt3->get_result()->fetch_assoc();
            $shipping_fee = $row['shipping_fee'] ?? 0;
            $discount_amount = $row['discount_amount'] ?? 0;

            $total_amount = $subtotal + $shipping_fee - $discount_amount;

            $this->updateOrderTotal($order_id, $subtotal, $shipping_fee, $discount_amount, $total_amount);

            if ($subtotal == 0) {
                $this->updateOrderStatus($order_id, 'cancelled');
            }

            return true;
        }

        return false;
    }
}
