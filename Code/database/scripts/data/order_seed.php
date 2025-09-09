<?php

require_once(__DIR__ . '/../../db.php');
require_once(__DIR__ . '/../../function.php');

// Get customer IDs and their default address IDs
$juanData = mysqli_fetch_assoc(mysqli_query($con, "
    SELECT c.customer_id, ca.address_id 
    FROM customer c 
    JOIN customer_address ca ON c.customer_id = ca.customer_id 
    WHERE c.email = 'juan.delacruz@email.com' AND ca.is_default = 1
"));

$anaData = mysqli_fetch_assoc(mysqli_query($con, "
    SELECT c.customer_id, ca.address_id 
    FROM customer c 
    JOIN customer_address ca ON c.customer_id = ca.customer_id 
    WHERE c.email = 'ana.reyes@email.com' AND ca.is_default = 1
"));

insertDataSmart($con, 'orders', 
    ['customer_id', 'address_id', 'total_amount', 'order_status', 'payment_method'], 
    [
        // Juan's orders
        [$juanData['customer_id'], $juanData['address_id'], 245.00, 'delivered', 'Cash on Delivery'],
        [$juanData['customer_id'], $juanData['address_id'], 180.00, 'processing', 'GCash'],
        [$juanData['customer_id'], $juanData['address_id'], 95.00, 'pending', 'Bank Transfer'],
        
        // Ana's orders
        [$anaData['customer_id'], $anaData['address_id'], 275.00, 'shipped', 'GCash'],
        [$anaData['customer_id'], $anaData['address_id'], 155.00, 'delivered', 'Cash on Delivery']
    ]
);

echo "Orders seeding completed.<br>";

