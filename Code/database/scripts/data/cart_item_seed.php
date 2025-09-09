<?php

require_once(__DIR__ . '/../../db.php');
require_once(__DIR__ . '/../../function.php');

// Get cart IDs
$juanCartId = mysqli_fetch_assoc(mysqli_query($con, "
    SELECT c.cart_id 
    FROM cart c 
    JOIN customer cu ON c.customer_id = cu.customer_id 
    WHERE cu.email = 'juan.delacruz@email.com'
"))['cart_id'];

$anaCartId = mysqli_fetch_assoc(mysqli_query($con, "
    SELECT c.cart_id 
    FROM cart c 
    JOIN customer cu ON c.customer_id = cu.customer_id 
    WHERE cu.email = 'ana.reyes@email.com'
"))['cart_id'];

// Get product IDs
$tomatoId = mysqli_fetch_assoc(mysqli_query($con, "SELECT product_id FROM product WHERE name = 'Fresh Tomatoes'"))['product_id'];
$bananaId = mysqli_fetch_assoc(mysqli_query($con, "SELECT product_id FROM product WHERE name = 'Bananas'"))['product_id'];
$mangoId = mysqli_fetch_assoc(mysqli_query($con, "SELECT product_id FROM product WHERE name = 'Mangoes'"))['product_id'];
$lettuceId = mysqli_fetch_assoc(mysqli_query($con, "SELECT product_id FROM product WHERE name = 'Green Lettuce'"))['product_id'];
$milkId = mysqli_fetch_assoc(mysqli_query($con, "SELECT product_id FROM product WHERE name = 'Fresh Milk'"))['product_id'];

insertDataSmart($con, 'cart_item', 
    ['cart_id', 'product_id', 'quantity', 'price_each'], 
    [
        // Juan's cart items
        [$juanCartId, $tomatoId, 3, 45.00],
        [$juanCartId, $bananaId, 2, 25.00],
        [$juanCartId, $milkId, 1, 65.00],
        
        // Ana's cart items
        [$anaCartId, $lettuceId, 1, 35.00],
        [$anaCartId, $mangoId, 2, 120.00]
    ]
);

echo "Cart items seeding completed.<br>";

