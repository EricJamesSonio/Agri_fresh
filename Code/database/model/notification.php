<?php

require_once(__DIR__ . '/../db.php');
require_once(__DIR__ . '/../function.php');

createTable($con, 'notifications', "
    CREATE TABLE notifications (
        id INT AUTO_INCREMENT PRIMARY KEY,
        customer_id INT NOT NULL,
        order_id INT DEFAULT NULL,
        message VARCHAR(255) NOT NULL,
        is_read TINYINT(1) DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (customer_id) REFERENCES customer(customer_id) ON DELETE CASCADE,
        FOREIGN KEY (order_id) REFERENCES orders(order_id) ON DELETE SET NULL
    )
");