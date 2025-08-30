<?php

require_once(__DIR__ . '/../db.php');
require_once(__DIR__ . '/../function.php');


createTable($con, 'cart', "
    CREATE TABLE cart (
        cart_id INT AUTO_INCREMENT PRIMARY KEY,
        customer_id INT NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (customer_id) REFERENCES customer(customer_id) ON DELETE CASCADE,
        UNIQUE(customer_id) -- one active cart per customer
    )
");
