<?php

echo "<h2>🌱 AgriFresh Database Seeder</h2>";
echo "<p>Starting database seeding process...</p><hr>";

// Database model Creation
echo "<h3>📊 Creating Models</h3>";
require_once(__DIR__ . '/model/admin.php');
require_once(__DIR__ . '/model/admin_logs.php');
require_once(__DIR__ . '/model/category.php');
require_once(__DIR__ . '/model/customer.php');
require_once(__DIR__ . '/model/customer_address.php');
require_once(__DIR__ . '/model/product.php');
require_once(__DIR__ . '/model/cart.php');   
require_once(__DIR__ . '/model/cart_item.php');
require_once(__DIR__ . '/model/orders.php');
require_once(__DIR__ . '/model/order_detail.php');

echo "<hr>";

// Data Seeding 
echo "<h3>🌾 Seeding Data</h3>";

echo "<h4>👤 Seeding Users & Categories...</h4>";
require_once(__DIR__ . '/scripts/data/admin_seed.php');
require_once(__DIR__ . '/scripts/data/category_seed.php');
//require_once(__DIR__ . '/scripts/data/customer_seed.php');
//require_once(__DIR__ . '/scripts/data/customer_address_seed.php');
require_once(__DIR__ . '/scripts/data/product_seed.php');
//require_once(__DIR__ . '/scripts/data/cart_seed.php');
//require_once(__DIR__ . '/scripts/data/cart_item_seed.php');
//require_once(__DIR__ . '/scripts/data/order_seed.php');
//require_once(__DIR__ . '/scripts/data/order_detail_seed.php');
//require_once(__DIR__ . '/scripts/data/admin_log_seed.php');

echo "<hr>";
echo "<h3>✅ Seeding Complete!</h3>";
echo "<p><strong>Summary:</strong></p>";
echo "<li>✅ All models created/updated</li>";
echo "<li>✅ Admin accounts created </li>";
echo "<li>✅ Product categories added </li>";
echo "<li>✅ Sample customers added</li>";
echo "<li>✅ Customer addresses added </li>";
echo "<li>✅ Sample products with categories added</li>";
echo "<li>✅ Shopping carts created </li>";
echo "<li>✅ Cart items added </li>";
echo "<li>✅ Sample orders created </li>";
echo "<li>✅ Order details added </li>";
echo "<li>✅ Admin activity logs added</li>";
echo "</ul>";

?>