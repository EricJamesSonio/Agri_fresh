<?php

require_once(__DIR__ . '/../../db.php');
require_once(__DIR__ . '/../../function.php');

// Get order IDs
$orderResult = mysqli_query($con, "SELECT order_id FROM `orders` ORDER BY order_id");
$orderIds = [];
while ($row = mysqli_fetch_assoc($orderResult)) {
    $orderIds[] = $row['order_id'];
}

// Get product IDs
$tomatoId = mysqli_fetch_assoc(mysqli_query($con, "SELECT product_id FROM product WHERE name = 'Fresh Tomatoes'"))['product_id'];
$bananaId = mysqli_fetch_assoc(mysqli_query($con, "SELECT product_id FROM product WHERE name = 'Bananas'"))['product_id'];
$mangoId = mysqli_fetch_assoc(mysqli_query($con, "SELECT product_id FROM product WHERE name = 'Mangoes'"))['product_id'];
$lettuceId = mysqli_fetch_assoc(mysqli_query($con, "SELECT product_id FROM product WHERE name = 'Green Lettuce'"))['product_id'];
$milkId = mysqli_fetch_assoc(mysqli_query($con, "SELECT product_id FROM product WHERE name = 'Fresh Milk'"))['product_id'];
$carrotId = mysqli_fetch_assoc(mysqli_query($con, "SELECT product_id FROM product WHERE name = 'Carrots'"))['product_id'];
$appleId = mysqli_fetch_assoc(mysqli_query($con, "SELECT product_id FROM product WHERE name = 'Apples'"))['product_id'];

insertDataSmart($con, 'order_detail', 
    ['order_id', 'product_id', 'quantity', 'price_each'], 
    [
        // Order 1 details (Juan - delivered)
        [$orderIds[0], $tomatoId, 2, 45.00],
        [$orderIds[0], $bananaId, 4, 25.00],
        [$orderIds[0], $mangoId, 1, 120.00],
        
        // Order 2 details (Juan - processing)
        [$orderIds[1], $lettuceId, 2, 35.00],
        [$orderIds[1], $milkId, 2, 65.00],
        
        // Order 3 details (Juan - pending)
        [$orderIds[2], $carrotId, 1, 40.00],
        [$orderIds[2], $bananaId, 2, 25.00],
        
        // Order 4 details (Ana - shipped)
        [$orderIds[3], $mangoId, 2, 120.00],
        [$orderIds[3], $lettuceId, 1, 35.00],
        
        // Order 5 details (Ana - delivered)
        [$orderIds[4], $appleId, 1, 180.00],
        [$orderIds[4], $tomatoId, 2, 45.00]
    ]
);

echo "Order details seeding completed.<br>";

