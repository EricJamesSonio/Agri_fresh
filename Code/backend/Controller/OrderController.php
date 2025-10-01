<?php
require_once(__DIR__ . '/../Model/OrderModel.php');
require_once(__DIR__ . '/../Model/AddressModel.php');
require_once(__DIR__ . '/../Model/VoucherModel.php');
require_once(__DIR__ . '/CartController.php');

class OrderController {
    private $orderModel;
    private $addressModel;
    private $voucherModel;
    private $cartController;

    public function __construct() {
        global $con;
        $this->orderModel = new OrderModel($con);
        $this->addressModel = new AddressModel($con);
        $this->voucherModel = new VoucherModel($con);
        $this->cartController = new CartController();
    }

    public function createOrder($customer_id, $address_id, $payment_method = 'COD', $voucher_code = null) {
    global $con;
    try {
        $cartItems = $this->cartController->getCart($customer_id);
        if (empty($cartItems)) throw new Exception('Your cart is empty.');

        $address = $this->addressModel->getAddress($address_id);
        if (!$address || $address['customer_id'] != $customer_id) {
            throw new Exception('Invalid address selected.');
        }

        $con->begin_transaction();

        // Stock check
        foreach ($cartItems as $item) {
            $stmt = $con->prepare("SELECT stock_quantity, name FROM product WHERE product_id = ?");
            $stmt->bind_param("i", $item['product_id']);
            $stmt->execute();
            $prod = $stmt->get_result()->fetch_assoc();

            if (!$prod) throw new Exception("Product not found: {$item['name']}");
$deductionQty = $item['quantity'] * $item['size_value'];

if ($prod['stock_quantity'] < $deductionQty) {
    throw new Exception("Insufficient stock for {$prod['name']}. 
        Requested: {$deductionQty} {$item['size_unit']} 
        Available: {$prod['stock_quantity']} {$item['size_unit']}");
}

        }

        // 1️⃣ Subtotal (items only)
        $subtotal = 0;
        foreach ($cartItems as $item) {
            $subtotal += $item['price_each'] * $item['quantity'];
        }

        // 2️⃣ Shipping fee (fixed 50 for example, could also depend on rules)
        $shipping_fee = 50.00;

        // 3️⃣ Discount
        $discount_amount = 0;
        if ($voucher_code) {
    $voucherCheck = $this->voucherModel->validateVoucher($voucher_code, $customer_id, $subtotal + $shipping_fee);
    if ($voucherCheck['valid']) {
        $discount_amount = $voucherCheck['discount'];
    } else {
        throw new Exception($voucherCheck['message']);
    }
}
if ($voucher_code) {
            $voucherCheck = $this->voucherModel->validateVoucher($voucher_code, $customer_id, $subtotal);
            if ($voucherCheck['valid']) {
                $discount_amount = $voucherCheck['discount'];
            } else {
                throw new Exception($voucherCheck['message']);
            }
        }

        // 4️⃣ Final total
        $total_amount = $subtotal + $shipping_fee - $discount_amount;

        // 5️⃣ Create order
        $order_id = $this->orderModel->createOrder(
            $customer_id,
            $address_id,
            $subtotal,
            $shipping_fee,
            $discount_amount,
            $total_amount,
            $payment_method,
            $voucher_code
        );

        if (!$order_id) throw new Exception('Failed to create order.');

        // Add order details & update stock
        foreach ($cartItems as $item) {
            $success = $this->orderModel->addOrderDetail($order_id, $item['product_id'], $item['quantity'], $item['price_each']);
            if (!$success) throw new Exception('Failed to add order details.');

// Use size_value from cart item, multiplied by quantity
$deductionQty = $item['quantity'] * $item['size_value'];

$stmt = $con->prepare("UPDATE product SET stock_quantity = stock_quantity - ? WHERE product_id = ?");
$stmt->bind_param("di", $deductionQty, $item['product_id']);
$stmt->execute();


        }

        if ($voucher_code) $this->voucherModel->markUsed($voucher_code, $customer_id);

        $this->clearCart($customer_id);
        $con->commit();

        return [
            'status' => 'success',
            'message' => 'Order created successfully!',
            'order_id' => $order_id,
            'subtotal' => $subtotal,
            'shipping_fee' => $shipping_fee,
            'discount_amount' => $discount_amount,
            'total_amount' => $total_amount,
            'voucher_applied' => $voucher_code
        ];

    } catch (Exception $e) {
        $con->rollback();
        return ['status'=>'error','message'=>$e->getMessage()];
    }
}

    public function getOrder($order_id, $customer_id = null) {
        try {
            $order = $this->orderModel->getOrder($order_id);
            
            if (!$order) {
                return [
                    'status' => 'error',
                    'message' => 'Order not found'
                ];
            }

            // If customer_id provided, verify ownership
            if ($customer_id && $order['customer_id'] != $customer_id) {
                return [
                    'status' => 'error',
                    'message' => 'Access denied'
                ];
            }

            $orderDetails = $this->orderModel->getOrderDetails($order_id);

            return [
                'status' => 'success',
                'data' => [
                    'order' => $order,
                    'details' => $orderDetails
                ]
            ];

        } catch (Exception $e) {
            return [
                'status' => 'error',
                'message' => 'Error fetching order: ' . $e->getMessage()
            ];
        }
    }

  public function getCustomerOrders($customer_id) {
    try {
        $orders = $this->orderModel->getCustomerOrders($customer_id);

        // Add details for each order
        foreach ($orders as &$order) {
            $order['details'] = $this->orderModel->getOrderDetails($order['order_id']);
        }

        return [
            'status' => 'success',
            'data' => $orders
        ];

    } catch (Exception $e) {
        return [
            'status' => 'error',
            'message' => 'Error fetching orders: ' . $e->getMessage()
        ];
    }
}

   public function updateOrderStatus($order_id, $status) {
    global $con;

    try {
        $status = strtolower($status);

        // 1️⃣ Get customer_id for this order
        $orderRes = mysqli_query($con, "SELECT customer_id FROM orders WHERE order_id = " . intval($order_id));
        $order = mysqli_fetch_assoc($orderRes);
        if (!$order) {
            return [
                'status' => 'error',
                'message' => 'Order not found'
            ];
        }
        $customer_id = $order['customer_id'];

        // 2️⃣ Update order status
        $success = $this->orderModel->updateOrderStatus($order_id, $status);

        // 3️⃣ If refunded or returned, zero out total
        if ($success && ($status === 'refunded' || $status === 'returned')) {
            $this->orderModel->updateOrderTotal($order_id, 0);
        }

        // 4️⃣ Insert notification for the customer
        if ($success) {
            $message = "Your order #$order_id status has been updated to $status.";
            $stmt = $con->prepare("INSERT INTO notifications (customer_id, order_id, message) VALUES (?, ?, ?)");
            $stmt->bind_param("iis", $customer_id, $order_id, $message);
            $stmt->execute();
            $stmt->close();
        }

        return $success
            ? ['status' => 'success', 'message' => 'Order status updated and notification sent.']
            : ['status' => 'error', 'message' => 'Failed to update order status'];

    } catch (Exception $e) {
        return [
            'status' => 'error',
            'message' => 'Error updating order: ' . $e->getMessage()
        ];
    }
}


    private function clearCart($customer_id) {
        global $con;
        // Get cart_id
        $cart_query = "SELECT cart_id FROM cart WHERE customer_id = " . intval($customer_id);
        $cart_result = mysqli_query($con, $cart_query);
        $cart = mysqli_fetch_assoc($cart_result);
        
        if ($cart) {
            // Clear all items from cart
            $clear_query = "DELETE FROM cart_item WHERE cart_id = " . intval($cart['cart_id']);
            mysqli_query($con, $clear_query);
        }
    }

   public function getAllOrders() {
    try {
        $orders = $this->orderModel->getAllOrders();

        // Add details (items) for each order
        foreach ($orders as &$order) {
            $order['details'] = $this->orderModel->getOrderDetails($order['order_id']);
        }

        return [
            'status' => 'success',
            'data' => $orders
        ];
    } catch (Exception $e) {
        return [
            'status' => 'error',
            'message' => 'Error fetching all orders: ' . $e->getMessage()
        ];
    }
}

// Cancel a single product in an order
public function cancelOrderItem($order_id, $product_id) {
    try {
        $success = $this->orderModel->deleteOrderItem($order_id, $product_id);
        if ($success) {
            return [
                'status' => 'success',
                'message' => 'Product removed from order'
            ];
        } else {
            return [
                'status' => 'error',
                'message' => 'Failed to remove product'
            ];
        }
    } catch (Exception $e) {
        return [
            'status' => 'error',
            'message' => 'Error removing product: ' . $e->getMessage()
        ];
    }
}


}
