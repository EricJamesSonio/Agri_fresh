<?php

require_once(__DIR__ . '/../../db.php');
require_once(__DIR__ . '/../../function.php');

insertDataSmart($con, 'customer', 
    ['first_name', 'last_name', 'email', 'password', 'contact'], 
    [
        ['Juan', 'Dela Cruz', 'juan.delacruz@email.com', password_hash('customer123', PASSWORD_DEFAULT), '+639111222333'],
        ['Ana', 'Reyes', 'ana.reyes@email.com', password_hash('ana123', PASSWORD_DEFAULT), '+639444555666']
    ],
    ['email'] // Unique column to check duplicates
);

echo "Customer seeding completed.<br>";

?>