<?php

require_once(__DIR__ . '/../../db.php');
require_once(__DIR__ . '/../../function.php');

insertDataSmart($con, 'customer', 
    ['first_name', 'last_name', 'email', 'password', 'contact'], 
    [
        ['Dummy', 'Customer', 'customer@gmail.com', password_hash('customer123', PASSWORD_DEFAULT), '+639111222333'],
    ],
    ['email'] // Unique column to check duplicates
);

echo "Customer seeding completed.<br>";

?>