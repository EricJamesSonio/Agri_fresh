<?php

require_once(__DIR__ . '/../../db.php');
require_once(__DIR__ . '/../../function.php');

// Get customer IDs
$juanId = mysqli_fetch_assoc(mysqli_query($con, "SELECT customer_id FROM customer WHERE email = 'juan.delacruz@email.com'"))['customer_id'];
$anaId = mysqli_fetch_assoc(mysqli_query($con, "SELECT customer_id FROM customer WHERE email = 'ana.reyes@email.com'"))['customer_id'];

insertDataSmart($con, 'customer_address', 
    ['customer_id', 'street', 'city', 'state', 'postal_code', 'country', 'is_default'], 
    [
        // Juan's addresses
        [$juanId, '123 Rizal Street, Barangay San Jose', 'Tarlac City', 'Tarlac', '2300', 'Philippines', 1],
        [$juanId, '456 Magsaysay Avenue, Barangay Central', 'Tarlac City', 'Tarlac', '2300', 'Philippines', 0],
        
        // Ana's addresses
        [$anaId, '789 Del Pilar Street, Barangay Poblacion', 'San Jose', 'Tarlac', '2334', 'Philippines', 1],
        [$anaId, '321 Luna Street, Barangay Santo Cristo', 'Capas', 'Tarlac', '2315', 'Philippines', 0]
    ]
);

echo "Customer addresses seeding completed.<br>";

?>