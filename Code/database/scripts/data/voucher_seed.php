<?php

require_once(__DIR__ . '/../../db.php');
require_once(__DIR__ . '/../../function.php');

insertDataSmart(
    $con,
    'voucher',
    ['code', 'type', 'discount_type', 'discount_value', 'usage_limit', 'is_active', 'start_date', 'end_date'],
    [
        // Percentage discount voucher
        ['SAVE10', 'discount', 'percent', 10.00, 100, 1, '2025-09-01', '2025-09-30'],

        // Fixed discount voucher
        ['LESS100', 'discount', 'fixed', 100.00, 50, 1, '2025-09-01', '2025-09-15'],

        // Free shipping voucher
        ['FREESHIP', 'free_shipping', null, 0.00, null, 1, null, null],
    ],
    ['code'] // Unique column to check duplicates
);

echo "✅ Voucher seeding completed.<br>";


