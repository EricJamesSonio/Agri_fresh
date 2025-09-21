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

        $cart_id = intval($cart['cart_id']);
        $res = mysqli_query($this->con, "
            SELECT 
                ci.cart_item_id, 
                ci.product_id, 
                p.name, 
                ci.size_value, 
                ci.size_unit, 
                ci.quantity, 
                ci.price_each 
            FROM cart_item ci 
            JOIN product p ON p.product_id = ci.product_id
            WHERE ci.cart_id=$cart_id
        ");

        $items = [];
        while ($row = mysqli_fetch_assoc($res)) {
            $row['size']       = number_format((float)$row['size_value'], 2) . ' ' . $row['size_unit'];
            $row['size_value'] = (float)$row['size_value'];
            $row['quantity']   = (float)$row['quantity'];   // force float
            $row['price_each'] = (float)$row['price_each']; // force float
            $items[] = $row;
        }

        return $items;
    }

    // Add or update cart items (set exact quantity)
    public function addToCart($customer_id, $product_id, $size_value, $size_unit, $quantity) {
        $customer_id = intval($customer_id);
        $product_id  = intval($product_id);
        $size_value  = floatval($size_value);
        $size_unit   = mysqli_real_escape_string($this->con, $size_unit);
        $quantity    = floatval($quantity);

        // DEBUG
        error_log("addToCart called: cust=$customer_id, prod=$product_id, size=$size_value $size_unit, qty=$quantity");

        // Ensure cart exists
        $cart = mysqli_fetch_assoc(mysqli_query(
            $this->con,
            "SELECT cart_id FROM cart WHERE customer_id=$customer_id"
        ));
        if (!$cart) {
            $stmt = $this->con->prepare("INSERT INTO cart (customer_id) VALUES (?)");
            $stmt->bind_param("i", $customer_id);
            $stmt->execute();
            $cart_id = $stmt->insert_id;
        } else {
            $cart_id = intval($cart['cart_id']);
        }

        // If quantity is 0 or less, remove item
        if ($quantity <= 0) {
            $stmt = $this->con->prepare("
                DELETE FROM cart_item 
                WHERE cart_id=? AND product_id=? AND size_value=? AND size_unit=?");
            $stmt->bind_param("iids", $cart_id, $product_id, $size_value, $size_unit);
            $stmt->execute();
            return;
        }

        // Check if item already exists
        $exists = mysqli_fetch_assoc(mysqli_query(
            $this->con,
            "SELECT cart_item_id FROM cart_item 
             WHERE cart_id=$cart_id AND product_id=$product_id 
             AND size_value=$size_value AND size_unit='$size_unit'"
        ));

        if ($exists) {
            // Update exact quantity (decimal-safe)
            $stmt = $this->con->prepare("
                UPDATE cart_item 
                SET quantity=? 
                WHERE cart_id=? AND product_id=? AND size_value=? AND size_unit=?");
            // d = double (float), i = int, s = string
            $stmt->bind_param("diids", $quantity, $cart_id, $product_id, $size_value, $size_unit);
            $stmt->execute();
        } else {
            // Get correct price from product table
            $priceRow = mysqli_fetch_assoc(mysqli_query(
                $this->con,
                "SELECT 
                    CASE
                        WHEN size1_value = $size_value AND size1_unit = '$size_unit' THEN price1
                        WHEN size2_value = $size_value AND size2_unit = '$size_unit' THEN price2
                        ELSE NULL
                    END AS matched_price
                 FROM product 
                 WHERE product_id = $product_id"
            ));

            $price = $priceRow && $priceRow['matched_price'] !== null
                ? floatval($priceRow['matched_price'])
                : 0.0;

            // Insert new item (decimal-safe)
            $stmt = $this->con->prepare("
                INSERT INTO cart_item (cart_id, product_id, size_value, size_unit, quantity, price_each) 
                VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("iidsdd", $cart_id, $product_id, $size_value, $size_unit, $quantity, $price);
            $stmt->execute();
        }
    }
}
