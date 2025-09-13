<?php
session_start();
if (!isset($_SESSION['customer_id'])) { 
    header("Location: login.php"); 
    exit(); 
}
$role = $_SESSION['role'] ?? 'customer';
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>My Cart – AgriFresh</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="stylesheet" href="../css/style.css">
  <link rel="stylesheet" href="../css/my_orders.css">
  <link rel="stylesheet" href="../css/sidebar.css">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600&display=swap" rel="stylesheet">
</head>
<body>
<?php include(__DIR__ . '/../components/sidebar.php'); ?>

<header>
  <h1>Agri Fresh Market</h1>
  <nav>
    <a href="index.php">Home</a>
    <a href="index.php#products">Products</a>
  </nav>
</header>

<main class="orders-wrapper">
  <h1>Shopping Cart</h1>

  <div class="bulk-bar">
    <label>
      <input type="checkbox" id="selectAll" onchange="toggleAll(this)">
      Select All
    </label>
    <button id="bulkDeleteBtn" onclick="bulkDelete()">Delete Selected</button>
  </div>

  <table class="order-table">
    <thead>
      <tr>
        <th style="width:30px"></th>
        <th style="width:70px">Product</th>
        <th></th>
        <th>Unit Price</th>
        <th>Quantity</th>
        <th>Total Price</th>
        <th>Actions</th>
      </tr>
    </thead>
    <tbody id="orderBody"></tbody>
    <tfoot>
      <tr id="grandTotalRow">
        <td colspan="5">Grand Total</td>
        <td id="grandTotal">₱0</td>
        <td></td>
      </tr>
    </tfoot>
  </table>

  <div class="cart-footer">
    <div class="voucher-line">
      <label>Platform Voucher</label>
      <input type="text" id="voucherCode" placeholder="Enter code">
      <button onclick="applyVoucher()">Apply</button>
      <span id="voucherDiscount">-₱0</span>
    </div>

    <div class="checkout-bar">
      <div class="grand-total">
        Total (<span id="itemCount">0</span> item): <span id="finalTotal">₱0</span>
      </div>
      <button class="checkout-btn" onclick="checkout()">Check Out</button>
    </div>
  </div>

  <div id="emptyState" class="empty-state">
    <p>No orders yet. <a href="index.php" style="color:#2e7d32">Shop now</a></p>
  </div>
</main>

<footer>
  <p>&copy; 2025 AgriFresh Market – Freshness Delivered.</p>
</footer>

<script src="../js/config.js"></script>
<script src="../js/script.js"></script>
<script src="../js/my-cart.js"></script> <!-- Separated JS -->
</body>
</html>
