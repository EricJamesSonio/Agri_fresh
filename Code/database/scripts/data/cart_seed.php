<?php

require_once(__DIR__ . '/../../db.php');
require_once(__DIR__ . '/../../function.php');

// Get customer IDs
$juanId = mysqli_fetch_assoc(mysqli_query($con, "SELECT customer_id FROM customer WHERE email = 'juan.delacruz@email.com'"))['customer_id'];
$anaId = mysqli_fetch_assoc(mysqli_query($con, "SELECT customer_id FROM customer WHERE email = 'ana.reyes@email.com'"))['customer_id'];

insertDataSmart($con, 'cart', 
    ['customer_id'], 
    [
        [$juanId],
        [$anaId]
    ],
    ['customer_id'] // Unique column to check duplicates (one cart per customer)
);

echo "Cart seeding completed.<br>";

