<?php

require_once(__DIR__ . '/../../db.php');
require_once(__DIR__ . '/../../function.php');

insertDataSmart($con, 'category', 
    ['category_name'], 
    [
        ['Vegetables'],
        ['Fruits'],
        ['Herbs'],
        ['Grains'],
        ['Dairy'],
        ['Meat'],
        ['Seafood']
    ],
    ['category_name'] // Unique column to check duplicates
);

echo "Category seeding completed.<br>";

?>