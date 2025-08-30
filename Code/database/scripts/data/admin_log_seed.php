<?php

require_once(__DIR__ . '/../../db.php');
require_once(__DIR__ . '/../../function.php');

// Get admin IDs
$johnId = mysqli_fetch_assoc(mysqli_query($con, "SELECT admin_id FROM admin WHERE email = 'admin@farmfresh.com'"))['admin_id'];
$mariaId = mysqli_fetch_assoc(mysqli_query($con, "SELECT admin_id FROM admin WHERE email = 'maria.santos@farmfresh.com'"))['admin_id'];
$pedroId = mysqli_fetch_assoc(mysqli_query($con, "SELECT admin_id FROM admin WHERE email = 'pedro.lopez@farmfresh.com'"))['admin_id'];

insertDataSmart($con, 'admin_logs', 
    ['admin_id', 'action'], 
    [
        // John's actions
        [$johnId, 'Created product: Fresh Tomatoes'],
        [$johnId, 'Updated product: Bananas stock quantity'],
        [$johnId, 'Created category: Vegetables'],
        [$johnId, 'Login attempt - successful'],
        [$johnId, 'Updated inventory for Fresh Milk'],
        [$johnId, 'Created product: Mangoes'],
        [$johnId, 'Updated order status: Order #1 to delivered'],
        
        // Maria's actions
        [$mariaId, 'Login attempt - successful'],
        [$mariaId, 'Created product: Green Lettuce'],
        [$mariaId, 'Updated product: Carrots price'],
        [$mariaId, 'Created category: Fruits'],
        [$mariaId, 'Updated order status: Order #2 to processing'],
        
        // Pedro's actions
        [$pedroId, 'Login attempt - successful'],
        [$pedroId, 'Created product: Fresh Milk'],
        [$pedroId, 'Updated inventory for multiple products'],
        [$pedroId, 'Created category: Dairy'],
        [$pedroId, 'Updated customer information'],
        [$pedroId, 'Generated sales report']
    ]
);

echo "Admin logs seeding completed.<br>";

?>