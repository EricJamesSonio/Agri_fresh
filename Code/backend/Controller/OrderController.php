<?php
require_once(__DIR__ . '/../Model/OrderModel.php');
require_once(__DIR__ . '/../Model/AddressModel.php');
require_once(__DIR__ . '/CartController.php');

class OrderController {
    private $orderModel;
    private $addressModel;
    private $cartController;

    public function __construct() {
        global $con;
        $this->orderModel = new OrderModel($con);
        $this->addressModel = new AddressModel($con);
        $this->cartController = new CartController();
    }

    public function createOrder($customer_id, $address_id, $payment_method = 'COD') {
        global $con;
        try {
            // Validate customer has items in cart
            $cartItems = $this->cartController->getCart($customer_id);
            if (empty($cartItems)) {
                return [
                    'status' => 'error',
                    'message' => 'Cart is empty'
                ];
            }

            // Validate address belongs to customer
            $address = $this->addressModel->getAddress($address_id);
            if (!$address || $address['customer_id'] != $customer_id) {
                return [
                    'status' => 'error',
                    'message' => 'Invalid address selected'
                ];
            }

            // Calculate total
            $total_amount = 0;
            foreach ($cartItems as $item) {
                $total_amount += $item['price_each'] * $item['quantity'];
            }

            // Start transaction
            $con->begin_transaction();

            // Create order
            $order_id = $this->orderModel->createOrder($customer_id, $address_id, $total_amount, $payment_method);
            
            if (!$order_id) {
                $con->rollback();
                return [
                    'status' => 'error',
                    'message' => 'Failed to create order'
                ];
            }

            // Add order details
            foreach ($cartItems as $item) {
                $success = $this->orderModel->addOrderDetail(
                    $order_id,
                    $item['product_id'],
                    $item['quantity'],
                    $item['price_each']
                );
                
                if (!$success) {
                    $con->rollback();
                    return [
                        'status' => 'error',
                        'message' => 'Failed to add order details'
                    ];
                }
            }

            // Clear cart after successful order
            $this->clearCart($customer_id);

            // Commit transaction
            $con->commit();

            return [
                'status' => 'success',
                'message' => 'Order created successfully',
                'order_id' => $order_id,
                'total_amount' => $total_amount
            ];

        } catch (Exception $e) {
            $con->rollback();
            return [
                'status' => 'error',
                'message' => 'Order creation failed: ' . $e->getMessage()
            ];
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
    try {
        $status = strtolower($status);

        $success = $this->orderModel->updateOrderStatus($order_id, $status);

        if ($success) {
            // if refunded or returned, zero out total
            if ($status === 'refunded' || $status === 'returned') {
                $this->orderModel->updateOrderTotal($order_id, 0);
            }

            return [
                'status' => 'success',
                'message' => 'Order status updated'
            ];
        } else {
            return [
                'status' => 'error',
                'message' => 'Failed to update order status'
            ];
        }

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


}
