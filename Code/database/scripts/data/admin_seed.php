<?php

require_once(__DIR__ . '/../../db.php');
require_once(__DIR__ . '/../../function.php');

insertDataSmart($con, 'admin', 
    ['first_name', 'last_name', 'email', 'password', 'contact'], 
    [
        ['Admin', 'FreshCorp', 'admin@agrifresh.com', password_hash('admin123', PASSWORD_DEFAULT), '+639123456789'],
    ],
    ['email'] // Unique column to check duplicates
);

echo "Admin seeding completed.<br>";

?>
