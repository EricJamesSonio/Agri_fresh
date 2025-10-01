<?php

require_once(__DIR__ . '/../db.php');
require_once(__DIR__ . '/../function.php');

createTable($con, 'orders', "
    CREATE TABLE `orders` (
        order_id INT AUTO_INCREMENT PRIMARY KEY,
        customer_id INT NOT NULL,
        address_id INT NOT NULL,
        voucher_code VARCHAR(50) DEFAULT NULL, 
        subtotal DECIMAL(10,2) NOT NULL,         -- total of items only
        shipping_fee DECIMAL(10,2) NOT NULL,    -- usually 50, or 0 if free shipping
        discount_amount DECIMAL(10,2) DEFAULT 0,-- applied discount from voucher
        total_amount DECIMAL(10,2) NOT NULL,    -- final payable (subtotal + shipping - discount)
        order_status ENUM(
            'pending', 
            'approved',       
            'processing', 
            'shipped', 
            'delivered', 
            'completed', 
            'cancelled', 
            'returned', 
            'refunded'
        ) DEFAULT 'pending',
        payment_method VARCHAR(50),
        return_request TINYINT(1) DEFAULT 0,     -- new column for return/refund request
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (customer_id) REFERENCES customer(customer_id) ON DELETE CASCADE,
        FOREIGN KEY (address_id) REFERENCES customer_address(address_id) ON DELETE RESTRICT
    )
");
