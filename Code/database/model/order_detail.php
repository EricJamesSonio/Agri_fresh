<?php

require_once(__DIR__ . '/../db.php');
require_once(__DIR__ . '/../function.php');

createTable($con, 'order_detail', "
    CREATE TABLE order_detail (
        order_detail_id INT AUTO_INCREMENT PRIMARY KEY,
        order_id INT NOT NULL,
        product_id INT NOT NULL,
        quantity INT NOT NULL,
        price_each DECIMAL(10,2) NOT NULL,
        FOREIGN KEY (order_id) REFERENCES `orders`(order_id) ON DELETE CASCADE,
        FOREIGN KEY (product_id) REFERENCES product(product_id) ON DELETE CASCADE
    )
");
