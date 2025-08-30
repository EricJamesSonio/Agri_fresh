<?php

require_once(__DIR__ . '/../db.php');
require_once(__DIR__ . '/../function.php');


createTable($con, 'customer_address', "
    CREATE TABLE customer_address (
        address_id INT AUTO_INCREMENT PRIMARY KEY,
        customer_id INT NOT NULL,
        street VARCHAR(255) NOT NULL,
        city VARCHAR(100) NOT NULL,
        state VARCHAR(100),
        postal_code VARCHAR(20),
        country VARCHAR(100) NOT NULL,
        is_default BOOLEAN DEFAULT FALSE,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (customer_id) REFERENCES customer(customer_id) ON DELETE CASCADE
    )
");
