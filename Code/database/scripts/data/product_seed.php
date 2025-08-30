<?php

require_once(__DIR__ . '/../../db.php');
require_once(__DIR__ . '/../../function.php');

// Get category IDs
$vegetableId = mysqli_fetch_assoc(mysqli_query($con, "SELECT category_id FROM category WHERE category_name = 'Vegetables'"))['category_id'];
$fruitId = mysqli_fetch_assoc(mysqli_query($con, "SELECT category_id FROM category WHERE category_name = 'Fruits'"))['category_id'];
$herbId = mysqli_fetch_assoc(mysqli_query($con, "SELECT category_id FROM category WHERE category_name = 'Herbs'"))['category_id'];
$grainId = mysqli_fetch_assoc(mysqli_query($con, "SELECT category_id FROM category WHERE category_name = 'Grains'"))['category_id'];
$dairyId = mysqli_fetch_assoc(mysqli_query($con, "SELECT category_id FROM category WHERE category_name = 'Dairy'"))['category_id'];

// Get admin ID for created_by
$adminId = mysqli_fetch_assoc(mysqli_query($con, "SELECT admin_id FROM admin WHERE email = 'admin@farmfresh.com'"))['admin_id'];

insertDataSmart($con, 'product', 
    ['name', 'description', 'category_id', 'price', 'stock_quantity', 'image_url', 'is_seasonal', 'is_organic', 'created_by'], 
    [
        // Vegetables
        ['Fresh Tomatoes', 'Organic red tomatoes, locally grown', $vegetableId, 45.00, 100, 'tomatoes.jpg', 0, 1, $adminId],
        ['Green Lettuce', 'Fresh crispy lettuce leaves', $vegetableId, 35.00, 80, 'lettuce.jpg', 0, 1, $adminId],
        ['Carrots', 'Sweet orange carrots', $vegetableId, 40.00, 120, 'carrots.jpg', 0, 1, $adminId],
        ['Onions', 'Fresh white onions', $vegetableId, 30.00, 150, 'onions.jpg', 0, 0, $adminId],
        ['Bell Peppers', 'Colorful bell peppers mix', $vegetableId, 85.00, 60, 'bell-peppers.jpg', 0, 1, $adminId],
        
        // Fruits
        ['Bananas', 'Sweet ripe bananas', $fruitId, 25.00, 200, 'bananas.jpg', 0, 1, $adminId],
        ['Mangoes', 'Philippine mangoes', $fruitId, 120.00, 50, 'mangoes.jpg', 1, 1, $adminId],
        ['Apples', 'Red delicious apples', $fruitId, 180.00, 75, 'apples.jpg', 0, 0, $adminId],
        ['Oranges', 'Fresh citrus oranges', $fruitId, 60.00, 90, 'oranges.jpg', 1, 1, $adminId],
        
        // Herbs
        ['Basil', 'Fresh basil leaves', $herbId, 15.00, 40, 'basil.jpg', 0, 1, $adminId],
        ['Mint', 'Fresh mint leaves', $herbId, 12.00, 35, 'mint.jpg', 0, 1, $adminId],
        ['Cilantro', 'Fresh cilantro/coriander', $herbId, 18.00, 50, 'cilantro.jpg', 0, 1, $adminId],
        
        // Grains
        ['Brown Rice', 'Organic brown rice', $grainId, 85.00, 200, 'brown-rice.jpg', 0, 1, $adminId],
        ['Quinoa', 'Premium quinoa grains', $grainId, 250.00, 30, 'quinoa.jpg', 0, 1, $adminId],
        
        // Dairy
        ['Fresh Milk', 'Farm fresh cow milk', $dairyId, 65.00, 80, 'fresh-milk.jpg', 0, 1, $adminId],
        ['Cheese', 'Local cheese', $dairyId, 150.00, 45, 'cheese.jpg', 0, 0, $adminId]
    ],
    ['name'] // Unique column to check duplicates
);


echo "Product seeding completed.<br>";

?>