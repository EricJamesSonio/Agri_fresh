<?php
// 🔒 Secure admin page
require_once('../../backend/routes/authGuard.php');
requireAdmin(); // redirects non-admins to frontend/html/index.php
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel</title>
    <link rel="stylesheet" href="css/index.css">
</head>
<body>
    <div class="admin-panel">
        <!-- Sidebar -->
        <aside class="sidebar">
            <h2>Admin Panel</h2>
            <ul>
                <li onclick="loadSection('orders')">Order Management</li>
                <li onclick="loadSection('products')">Product Management</li>
                <li onclick="loadSection('sales')">Sales Report</li>
                <li onclick="loadSection('vouchers')">Voucher Management</li>
                 <li onclick="loadSection('stock')">Stock Management</li>

            </ul>

            <!-- 🔙 Back Button -->
            <button onclick="window.location.href='../html/'" class="back-btn">
                ⬅ Back
            </button>
        </aside>

        <!-- Main content -->
        <main class="main-content" id="main-content">
            <p>Welcome to Admin Panel! Select a section from the sidebar.</p>
        </main>
    </div>

    <!-- Load config.js first -->
    <script src="../js/config.js"></script>
    <!-- Then load your admin JS -->
    <script src="js/index.js"></script>
</body>
</html>
