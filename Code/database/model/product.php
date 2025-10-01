<?php

require_once(__DIR__ . '/../db.php');
require_once(__DIR__ . '/../function.php');

createTable($con, 'product', "
    CREATE TABLE product (
        product_id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL,
        description TEXT,
        category_id INT,
        size_value DECIMAL(10,2) NOT NULL, 
        size_unit ENUM('kg','g','liter','ml','bunch') NOT NULL, 
        price DECIMAL(10,2) NOT NULL,
        stock_quantity DECIMAL(10,2) DEFAULT 0,
        image_url VARCHAR(255),
        is_seasonal BOOLEAN DEFAULT FALSE,
        is_organic BOOLEAN DEFAULT FALSE,
        created_by INT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (category_id) REFERENCES category(category_id) ON DELETE SET NULL,
        FOREIGN KEY (created_by) REFERENCES admin(admin_id) ON DELETE SET NULL
    )
");