<?php
require_once(__DIR__ . '/../../Database/db.php');

class OrderModel {
    private $con;

    public function __construct($con) {
        $this->con = $con;
    }

    public function createOrder($customer_id, $address_id, $total_amount, $payment_method = 'COD') {
        $stmt = $this->con->prepare("
            INSERT INTO `orders` (customer_id, address_id, total_amount, payment_method, order_status) 
            VALUES (?, ?, ?, ?, 'pending')
        ");
        $stmt->bind_param("iids", $customer_id, $address_id, $total_amount, $payment_method);
        
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
        $stmt->bind_param("iiid", $order_id, $product_id, $quantity, $price_each);
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
            SELECT od.*, p.name as product_name, p.description
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
        // allow statuses including refunded/returned
        $valid_statuses = ['pending', 'processing', 'shipped', 'delivered', 'cancelled', 'completed', 'refunded', 'returned'];
        if (!in_array($status, $valid_statuses)) {
            return false;
        }

        $stmt = $this->con->prepare("UPDATE `orders` SET order_status = ? WHERE order_id = ?");
        $stmt->bind_param("si", $status, $order_id);
        return $stmt->execute();
    }

    public function updateOrderTotal($order_id, $newTotal) {
        $stmt = $this->con->prepare("UPDATE orders SET total_amount = ? WHERE order_id = ?");
        $stmt->bind_param("di", $newTotal, $order_id);
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

// Delete/cancel a single product from an order
public function deleteOrderItem($order_id, $product_id) {
    $stmt = $this->con->prepare("
        DELETE FROM order_detail 
        WHERE order_id = ? AND product_id = ?
    ");
    $stmt->bind_param("ii", $order_id, $product_id);
    $success = $stmt->execute();

    if ($success) {
        // Recalculate total
        $stmt2 = $this->con->prepare("
            SELECT SUM(quantity * price_each) as total 
            FROM order_detail 
            WHERE order_id = ?
        ");
        $stmt2->bind_param("i", $order_id);
        $stmt2->execute();
        $result = $stmt2->get_result()->fetch_assoc();
        $newTotal = $result['total'] ?? 0;

        $this->updateOrderTotal($order_id, $newTotal);

        // If no items left, mark order as cancelled
        if ($newTotal == 0) {
            $this->updateOrderStatus($order_id, 'cancelled');
        }

        return true;
    }

    return false;
}


}
