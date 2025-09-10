<?php

require_once(__DIR__ . '/../../db.php');
require_once(__DIR__ . '/../../function.php');

// Insert multiple customers
insertDataSmart($con, 'customer', 
    ['first_name', 'last_name', 'email', 'password', 'contact'], 
    [
        ['Dummy', 'Customer', 'customer@gmail.com', password_hash('customer123', PASSWORD_DEFAULT), '+639111222333'],
        ['Alice', 'Smith', 'dum1@gmail.com', password_hash('alice123', PASSWORD_DEFAULT), '+639123456789'],
        ['Bob', 'Johnson', 'dum2@gmail.com', password_hash('bob123', PASSWORD_DEFAULT), '+639987654321'],
        ['Carol', 'Williams', 'dum3@gmail.com', password_hash('carol123', PASSWORD_DEFAULT), '+639112233445'],
        ['David', 'Brown', 'dum4@gmail.com', password_hash('david123', PASSWORD_DEFAULT), '+639556677889']
    ],
    ['email'] // Unique column to check duplicates
);

echo "Customer seeding completed.<br>";
