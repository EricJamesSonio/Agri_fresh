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
$dairyId     = getCategoryId($con, 'Dairy');
$seafoodId   = getCategoryId($con, 'Seafood');

$adminId = getAdminId($con, 'admin@agrifresh.com');

// Insert products with multiple sizes
insertDataSmart($con, 'product', 
    ['name', 'description', 'category_id', 'size_value', 'size_unit', 'price', 'stock_quantity', 'image_url', 'is_seasonal', 'is_organic', 'created_by'], 
    [
        // Vegetables
        ['Tomatoes', 'Organic red tomatoes, locally grown', $vegetableId, 1.00, 'kg', 105.00, 100, 'tomatoes.jpg', 0, 1, $adminId],
        ['Tomatoes', 'Organic red tomatoes, locally grown', $vegetableId, 0.50, 'kg', 60.00, 120, 'tomatoes2.jpg', 0, 1, $adminId],
        ['Lettuce', 'Fresh crispy lettuce leaves', $vegetableId, 1.00, 'kg', 115.00, 80, 'lettuce.jpg', 0, 1, $adminId],
        ['Lettuce', 'Fresh crispy lettuce leaves', $vegetableId, 0.50, 'kg', 65.00, 90, 'lettuce2.jpg', 0, 1, $adminId],
        ['Carrots', 'Sweet orange carrots', $vegetableId, 1.00, 'kg', 140.00, 120, 'carrots.jpg', 0, 1, $adminId],
        ['Carrots', 'Sweet orange carrots', $vegetableId, 0.50, 'kg', 75.00, 140, 'carrots2.jpg', 0, 1, $adminId],
        ['Onions', 'Fresh white onions', $vegetableId, 1.00, 'kg', 100.00, 150, 'onions.jpg', 0, 0, $adminId],
        ['Onions', 'Fresh white onions', $vegetableId, 0.50, 'kg', 55.00, 160, 'onions2.jpg', 0, 0, $adminId],
        ['Bell Peppers', 'Colorful bell peppers mix', $vegetableId, 1.00, 'kg', 155.00, 60, 'bell-peppers.jpg', 0, 1, $adminId],
        ['Bell Peppers', 'Colorful bell peppers mix', $vegetableId, 0.50, 'kg', 85.00, 70, 'bell-peppers2.jpg', 0, 1, $adminId],

        // Fruits
        ['Bananas', 'Sweet ripe bananas', $fruitId, 1.00, 'kg', 125.00, 200, 'bananas.jpg', 0, 1, $adminId],
        ['Bananas', 'Sweet ripe bananas', $fruitId, 0.50, 'kg', 70.00, 220, 'bananas2.jpg', 0, 1, $adminId],
        ['Mangoes', 'Philippine mangoes', $fruitId, 1.00, 'kg', 120.00, 50, 'mangoes.jpg', 1, 1, $adminId],
        ['Mangoes', 'Philippine mangoes', $fruitId, 0.50, 'kg', 65.00, 60, 'mangoes2.jpg', 1, 1, $adminId],
        ['Apples', 'Red delicious apples', $fruitId, 1.00, 'kg', 180.00, 75, 'apples.jpg', 0, 0, $adminId],
        ['Apples', 'Red delicious apples', $fruitId, 0.50, 'kg', 95.00, 90, 'apples2.jpg', 0, 0, $adminId],
        ['Oranges', 'Fresh citrus oranges', $fruitId, 1.00, 'kg', 160.00, 90, 'oranges.jpg', 1, 1, $adminId],
        ['Oranges', 'Fresh citrus oranges', $fruitId, 0.50, 'kg', 85.00, 100, 'oranges2.jpg', 1, 1, $adminId],

        // Herbs
        ['Basil', 'Fresh basil leaves', $herbId, 1.00, 'bunch', 50.00, 40, 'basil.jpg', 0, 1, $adminId],
        ['Basil', 'Fresh basil leaves', $herbId, 0.50, 'bunch', 30.00, 50, 'basil2.jpg', 0, 1, $adminId],
        ['Mint', 'Fresh mint leaves', $herbId, 1.00, 'bunch', 70.00, 35, 'mint.jpg', 0, 1, $adminId],
        ['Mint', 'Fresh mint leaves', $herbId, 0.50, 'bunch', 40.00, 45, 'mint2.jpg', 0, 1, $adminId],
        ['Cilantro', 'Fresh cilantro/coriander', $herbId, 1.00, 'bunch', 80.00, 50, 'cilantro.jpg', 0, 1, $adminId],
        ['Cilantro', 'Fresh cilantro/coriander', $herbId, 0.50, 'bunch', 45.00, 60, 'cilantro2.jpg', 0, 1, $adminId],

        // Grains
        ['Brown Rice', 'Organic brown rice', $grainId, 1.00, 'kg', 75.00, 200, 'brown-rice.jpg', 0, 1, $adminId],
        ['Brown Rice', 'Organic brown rice', $grainId, 0.50, 'kg', 40.00, 250, 'brown-rice2.jpg', 0, 1, $adminId],
        ['Quinoa', 'Premium quinoa grains', $grainId, 1.00, 'kg', 100.00, 30, 'quinoa.jpg', 0, 1, $adminId],
        ['Quinoa', 'Premium quinoa grains', $grainId, 0.50, 'kg', 55.00, 40, 'quinoa2.jpg', 0, 1, $adminId],

        // Dairy
        ['Fresh Milk', 'Farm fresh cow milk', $dairyId, 1.00, 'liter', 120.00, 80, 'fresh-milk.jpg', 0, 1, $adminId],
        ['Fresh Milk', 'Farm fresh cow milk', $dairyId, 0.50, 'liter', 65.00, 80, 'fresh-milk2.jpg', 0, 1, $adminId],
        ['Cheese', 'Local cheese', $dairyId, 1.00, 'kg', 150.00, 45, 'cheese.jpg', 0, 0, $adminId],
        ['Cheese', 'Local cheese', $dairyId, 0.50, 'kg', 80.00, 45, 'cheese2.jpg', 0, 0, $adminId],

        // Seafood
        ['Shrimp', 'Juicy prawns, medium size', $seafoodId, 1.00, 'kg', 180.00, 60, 'shrimp.jpg', 0, 1, $adminId],
        ['Shrimp', 'Juicy prawns, medium size', $seafoodId, 0.50, 'kg', 100.00, 70, 'shrimp2.jpg', 0, 1, $adminId],
        ['Tilapia', 'Locally sourced tilapia', $seafoodId, 1.00, 'kg', 100.00, 80, 'tilapia.jpg', 0, 1, $adminId],
        ['Tilapia', 'Locally sourced tilapia', $seafoodId, 0.50, 'kg', 55.00, 90, 'tilapia2.jpg', 0, 1, $adminId],
        ['Crab', 'Fresh blue crabs', $seafoodId, 1.00, 'kg', 120.00, 30, 'crab.jpg', 1, 0, $adminId],
        ['Crab', 'Fresh blue crabs', $seafoodId, 0.50, 'kg', 65.00, 40, 'crab2.jpg', 1, 0, $adminId],
        ['Squid', 'Fresh squid, cleaned', $seafoodId, 1.00, 'kg', 150.00, 50, 'squid.jpg', 0, 1, $adminId],
        ['Squid', 'Fresh squid, cleaned', $seafoodId, 0.50, 'kg', 80.00, 60, 'squid2.jpg', 0, 1, $adminId],
    ],
    ['name', 'size_value', 'size_unit'] // composite unique check
);

echo "✅ Product seeding completed with all size variants.<br>";

?>
