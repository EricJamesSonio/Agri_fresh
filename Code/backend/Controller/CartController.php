<?php
require_once(__DIR__ . '/../../database/db.php');

class CartController {

    private $con;

    public function __construct() {
        global $con;
        $this->con = $con;
    }

    // Get all cart items for a customer
    public function getCart($customer_id) {
        $cart = mysqli_fetch_assoc(mysqli_query(
            $this->con,
            "SELECT cart_id FROM cart WHERE customer_id=" . intval($customer_id)
        ));

        if (!$cart) return [];

        $cart_id = $cart['cart_id'];
        $res = mysqli_query($this->con, "
            SELECT ci.cart_item_id, ci.product_id, p.name, ci.quantity, ci.price_each 
            FROM cart_item ci 
            JOIN product p ON p.product_id = ci.product_id
            WHERE ci.cart_id=" . intval($cart_id)
        );

        $items = [];
        while ($row = mysqli_fetch_assoc($res)) {
            $items[] = $row;
        }
        return $items;
    }

    // Add or update cart items (standardized: set exact quantity)
    public function addToCart($customer_id, $product_id, $quantity) {
        $customer_id = intval($customer_id);
        $product_id  = intval($product_id);
        $quantity    = intval($quantity);

        // Ensure cart exists
        $cart = mysqli_fetch_assoc(mysqli_query(
            $this->con,
            "SELECT cart_id FROM cart WHERE customer_id=$customer_id"
        ));
        if (!$cart) {
            mysqli_query($this->con, "INSERT INTO cart (customer_id) VALUES ($customer_id)");
            $cart_id = mysqli_insert_id($this->con);
        } else {
            $cart_id = $cart['cart_id'];
        }

        // If quantity is 0 or less, remove item
        if ($quantity <= 0) {
            mysqli_query($this->con,
                "DELETE FROM cart_item WHERE cart_id=$cart_id AND product_id=$product_id"
            );
            return;
        }

        // Check if item already exists
        $exists = mysqli_fetch_assoc(mysqli_query(
            $this->con,
            "SELECT cart_item_id FROM cart_item WHERE cart_id=$cart_id AND product_id=$product_id"
        ));

        if ($exists) {
            // Update to the exact quantity sent by frontend
            mysqli_query($this->con,
                "UPDATE cart_item SET quantity=$quantity WHERE cart_id=$cart_id AND product_id=$product_id"
            );
        } else {
            // Insert new with price from product table
            $priceRow = mysqli_fetch_assoc(mysqli_query(
                $this->con,
                "SELECT price FROM product WHERE product_id=$product_id"
            ));
            $price = $priceRow ? $priceRow['price'] : 0;

            mysqli_query($this->con,
                "INSERT INTO cart_item (cart_id, product_id, quantity, price_each) 
                 VALUES ($cart_id, $product_id, $quantity, $price)"
            );
        }
    }
}
