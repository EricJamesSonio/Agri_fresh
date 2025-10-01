<?php

require_once(__DIR__ . '/../db.php');
require_once(__DIR__ . '/../function.php');

createTable($con, 'voucher', "
    CREATE TABLE voucher (
        voucher_id INT AUTO_INCREMENT PRIMARY KEY,
        code VARCHAR(50) NOT NULL UNIQUE,         -- Voucher code (e.g., SAVE10, FREESHIP)
        type ENUM('discount', 'free_shipping') NOT NULL, -- Voucher type
        discount_type ENUM('percent', 'fixed') DEFAULT NULL, -- For discount vouchers
        discount_value DECIMAL(10,2) DEFAULT 0.00, -- % or amount depending on discount_type
        usage_limit INT DEFAULT NULL,             -- Max times the voucher can be used (NULL = unlimited)
        used_count INT DEFAULT 0,                 -- Track how many times it was used
        is_active TINYINT(1) DEFAULT 1,           -- 1 = active, 0 = inactive
        start_date DATE NULL DEFAULT NULL,        -- When voucher becomes valid
        end_date DATE NULL DEFAULT NULL,          -- When voucher expires
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )
");
