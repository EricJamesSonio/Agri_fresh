<?php

require_once(__DIR__ . '/../db.php');
require_once(__DIR__ . '/../function.php');

createTable($con, 'cart_item', "
    CREATE TABLE cart_item (
        cart_item_id INT AUTO_INCREMENT PRIMARY KEY,
        cart_id INT NOT NULL,
        product_id INT NOT NULL,
        size_value DECIMAL(10,2) NOT NULL,
        size_unit ENUM('kg','g','liter','ml','bunch') NOT NULL,
        quantity INT NOT NULL CHECK (quantity > 0),
        price_each DECIMAL(10,2) NOT NULL,
        added_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (cart_id) REFERENCES cart(cart_id) ON DELETE CASCADE,
        FOREIGN KEY (product_id) REFERENCES product(product_id) ON DELETE CASCADE,
        UNIQUE(cart_id, product_id, size_value, size_unit) -- allow same product with different sizes
    )
");
?>
