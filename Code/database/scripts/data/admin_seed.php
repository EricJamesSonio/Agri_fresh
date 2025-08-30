<?php

require_once(__DIR__ . '/../../db.php');
require_once(__DIR__ . '/../../function.php');

insertDataSmart($con, 'admin', 
    ['first_name', 'last_name', 'email', 'password', 'contact'], 
    [
        ['John', 'Admin', 'admin@farmfresh.com', password_hash('admin123', PASSWORD_DEFAULT), '+639123456789'],
        ['Maria', 'Santos', 'maria.santos@farmfresh.com', password_hash('maria123', PASSWORD_DEFAULT), '+639987654321'],
        ['Pedro', 'Lopez', 'pedro.lopez@farmfresh.com', password_hash('pedro123', PASSWORD_DEFAULT), '+639555123456']
    ],
    ['email'] // Unique column to check duplicates
);

echo "Admin seeding completed.<br>";

?>
