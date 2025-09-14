<?php

require_once(__DIR__ . '/../../db.php');
require_once(__DIR__ . '/../../function.php');

function getCategoryId($con, $categoryName) {
    $result = mysqli_query($con, "SELECT category_id FROM category WHERE category_name = '$categoryName'");
    $row = mysqli_fetch_assoc($result);
    if (!$row) die("Error: Category '$categoryName' not found in database!<br>");
    return $row['category_id'];
}

function getAdminId($con, $email) {
    $result = mysqli_query($con, "SELECT admin_id FROM admin WHERE email = '$email'");
    $row = mysqli_fetch_assoc($result);
    if (!$row) die("Error: Admin with email '$email' not found in database!<br>");
    return $row['admin_id'];
}

$vegetableId = getCategoryId($con, 'Vegetables');
$fruitId     = getCategoryId($con, 'Fruits');
$herbId      = getCategoryId($con, 'Herbs');
$grainId     = getCategoryId($con, 'Grains');

$adminId = getAdminId($con, 'admin@agrifresh.com');

// Insert products with 2 size options
insertDataSmart($con, 'product', 
    [
        'name', 'description', 'category_id',
        'size1_value', 'size1_unit', 'price1',
        'size2_value', 'size2_unit', 'price2',
        'stock_quantity', 'image_url',
        'is_seasonal', 'is_organic', 'created_by'
    ], 
    [
        // Vegetables
        ['Tomatoes', 'Organic red tomatoes, locally grown', $vegetableId, 1.00, 'kg', 105.00, 0.50, 'kg', 60.00, 220, 'tomatoes.jpg', 0, 1, $adminId],
        ['Lettuce', 'Fresh crispy lettuce leaves', $vegetableId, 1.00, 'kg', 115.00, 0.50, 'kg', 65.00, 170, 'lettuce.jpg', 0, 1, $adminId],
        ['Carrots', 'Sweet orange carrots', $vegetableId, 1.00, 'kg', 140.00, 0.50, 'kg', 75.00, 260, 'carrots.jpg', 0, 1, $adminId],
        ['Onions', 'Fresh white onions', $vegetableId, 1.00, 'kg', 100.00, 0.50, 'kg', 55.00, 310, 'onions.jpg', 0, 0, $adminId],
        ['Bell Peppers', 'Colorful bell peppers mix', $vegetableId, 1.00, 'kg', 155.00, 0.50, 'kg', 85.00, 130, 'bell-peppers.jpg', 0, 1, $adminId],

        // Fruits
        ['Bananas', 'Sweet ripe bananas', $fruitId, 1.00, 'kg', 125.00, 0.50, 'kg', 70.00, 420, 'bananas.jpg', 0, 1, $adminId],
        ['Mangoes', 'Philippine mangoes', $fruitId, 1.00, 'kg', 120.00, 0.50, 'kg', 65.00, 110, 'mangoes.jpg', 1, 1, $adminId],
        ['Apples', 'Red delicious apples', $fruitId, 1.00, 'kg', 180.00, 0.50, 'kg', 95.00, 165, 'apples.jpg', 0, 0, $adminId],
        ['Oranges', 'Fresh citrus oranges', $fruitId, 1.00, 'kg', 160.00, 0.50, 'kg', 85.00, 190, 'oranges.jpg', 1, 0, $adminId],

        // Herbs
        ['Basil', 'Fresh basil leaves', $herbId, 1.00, 'bunch', 50.00, 0.50, 'bunch', 30.00, 90, 'basil.jpg', 0, 1, $adminId],
        ['Mint', 'Fresh mint leaves', $herbId, 1.00, 'bunch', 70.00, 0.50, 'bunch', 40.00, 80, 'mint.jpg', 0, 1, $adminId],
        ['Cilantro', 'Fresh cilantro/coriander', $herbId, 1.00, 'bunch', 80.00, 0.50, 'bunch', 45.00, 110, 'cilantro.jpg', 0, 1, $adminId],

        // Grains
        ['Brown Rice', 'Organic brown rice', $grainId, 1.00, 'kg', 75.00, 0.50, 'kg', 40.00, 450, 'brown-rice.jpg', 0, 1, $adminId],
        ['Quinoa', 'Premium quinoa grains', $grainId, 1.00, 'kg', 100.00, 0.50, 'kg', 55.00, 70, 'quinoa.jpg', 0, 1, $adminId],
    ],
    ['name'] // unique check only on product name now
);

echo "✅ Product seeding completed with 2 size options per product.<br>";
