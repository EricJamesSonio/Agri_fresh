<?php

require_once(__DIR__ . '/../../db.php');
require_once(__DIR__ . '/../../function.php');

// Get category IDs
$vegetableId = mysqli_fetch_assoc(mysqli_query($con, "SELECT category_id FROM category WHERE category_name = 'Vegetables'"))['category_id'];
$fruitId = mysqli_fetch_assoc(mysqli_query($con, "SELECT category_id FROM category WHERE category_name = 'Fruits'"))['category_id'];
$herbId = mysqli_fetch_assoc(mysqli_query($con, "SELECT category_id FROM category WHERE category_name = 'Herbs'"))['category_id'];
$grainId = mysqli_fetch_assoc(mysqli_query($con, "SELECT category_id FROM category WHERE category_name = 'Grains'"))['category_id'];
$dairyId = mysqli_fetch_assoc(mysqli_query($con, "SELECT category_id FROM category WHERE category_name = 'Dairy'"))['category_id'];
$seafoodId = mysqli_fetch_assoc(mysqli_query($con, "SELECT category_id FROM category WHERE category_name = 'Seafood'"))['category_id'];

// Get admin ID for created_by
$adminId = mysqli_fetch_assoc(mysqli_query($con, "SELECT admin_id FROM admin WHERE email = 'admin@farmfresh.com'"))['admin_id'];

insertDataSmart($con, 'product', 
    ['name', 'description', 'category_id', 'price', 'stock_quantity', 'image_url', 'is_seasonal', 'is_organic', 'created_by'], 
    [
        // Vegetables
        ['Fresh Tomatoes - 1 kg', 'Organic red tomatoes, locally grown', $vegetableId, 105.00, 100, 'tomatoes.jpg', 0, 1, $adminId],
        ['Green Lettuce - 1 kg', 'Fresh crispy lettuce leaves', $vegetableId, 115.00, 80, 'lettuce.jpg', 0, 1, $adminId],
        ['Carrots - 1 kg', 'Sweet orange carrots', $vegetableId, 140.00, 120, 'carrots.jpg', 0, 1, $adminId],
        ['Onions - 1 kg', 'Fresh white onions', $vegetableId, 100.00, 150, 'onions.jpg', 0, 0, $adminId],
        ['Bell Peppers - 1 kg', 'Colorful bell peppers mix', $vegetableId, 155.00, 60, 'bell-peppers.jpg', 0, 1, $adminId],
        
        // Fruits
        ['Bananas - 1 kg', 'Sweet ripe bananas', $fruitId, 125.00, 200, 'bananas.jpg', 0, 1, $adminId],
        ['Mangoes - 1 kg', 'Philippine mangoes', $fruitId, 120.00, 50, 'mangoes.jpg', 1, 1, $adminId],
        ['Apples - 1 kg', 'Red delicious apples', $fruitId, 180.00, 75, 'apples.jpg', 0, 0, $adminId],
        ['Oranges - 1 kg', 'Fresh citrus oranges', $fruitId, 160.00, 90, 'oranges.jpg', 1, 1, $adminId],
        
        // Herbs
        ['Basil - 1 bunch', 'Fresh basil leaves', $herbId, 50.00, 40, 'basil.jpg', 0, 1, $adminId],
        ['Mint - 1 bunch', 'Fresh mint leaves', $herbId, 70.00, 35, 'mint.jpg', 0, 1, $adminId],
        ['Cilantro - 1 bunch', 'Fresh cilantro/coriander', $herbId, 80.00, 50, 'cilantro.jpg', 0, 1, $adminId],
        
        // Grains
        ['Brown Rice - 1 kg', 'Organic brown rice', $grainId, 75.00, 200, 'brown-rice.jpg', 0, 1, $adminId],
        ['Quinoa - 1 kg', 'Premium quinoa grains', $grainId, 100.00, 30, 'quinoa.jpg', 0, 1, $adminId],
        
        // Dairy
        ['Fresh Milk - 1 liter', 'Farm fresh cow milk', $dairyId, 120.00, 80, 'fresh-milk.jpg', 0, 1, $adminId],
        ['Fresh Milk - 500 ml', 'Farm fresh cow milk', $dairyId, 65.00, 80, 'fresh-milk2.jpg', 0, 1, $adminId],
        ['Cheese - 1 kg', 'Local cheese', $dairyId, 150.00, 45, 'cheese.jpg', 0, 0, $adminId],
        ['Cheese - 500 g', 'Local cheese', $dairyId, 80.00, 45, 'cheese2.jpg', 0, 0, $adminId],

        // Seafood
        ['Shrimp - 1 kg', 'Juicy prawns, medium size', $seafoodId, 180.00, 60, 'shrimp.jpg', 0, 1, $adminId],
        ['Tilapia - 1 kg', 'Locally sourced tilapia', $seafoodId, 100.00, 80, 'tilapia.jpg', 0, 1, $adminId],
        ['Crab - 1 kg', 'Fresh blue crabs', $seafoodId, 120.00, 30, 'crab.jpg', 1, 0, $adminId],
        ['Squid - 1 kg', 'Fresh squid, cleaned', $seafoodId, 150.00, 50, 'squid.jpg', 0, 1, $adminId]
    ],
    ['name'] // Unique column to check duplicates
);

echo "Product seeding completed including Seafood.<br>";

?>
