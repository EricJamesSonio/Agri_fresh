<?php
session_start();

// ✅ Require login
if (!isset($_SESSION['customer_id'])) {
    header("Location: login.php");
    exit();
}

$role = $_SESSION['role'] ?? 'customer';
$customer_id = $_SESSION['customer_id'];
$customer_name = $_SESSION['customer_name'] ?? 'Customer';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Order Confirmation | Agri-Fresh</title>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="../css/style.css">
  <link rel="stylesheet" href="../css/sidebar.css">
  <link rel="stylesheet" href="../css/order-confirmation.css">
</head>
<body>
<?php include(__DIR__ . '/../components/sidebar.php'); ?>

<header>
  <h1>Agri Fresh Market</h1>
  <nav>
    <a href="index.php">Home</a>
    <a href="my-cart.php" style="display: inline-block; margin-left: 10px;">
        <img src="../images/cart.jpg" alt="Cart" style="width:32px; height:32px;">
    </a>
  </nav>
</header>

<main style="padding: 1rem;">
  <div class="confirmation-container">
    <div class="success-icon">✅</div>
    <h1>Order Confirmed!</h1>
    <p>Thank you, <?php echo htmlspecialchars($customer_name); ?>. We've received your order and will start processing it soon.</p>

    <div id="order-details" class="order-details">
      <div class="loading">Loading order details...</div>
    </div>

    <div class="action-buttons">
      <a href="index.php" class="btn">Continue Shopping</a>
      <button onclick="viewOrders()" class="btn btn-outline">View My Orders</button>
    </div>
  </div>
</main>

<footer style="text-align:center; padding:1rem; color:#666;">
  &copy; 2025 AgriFresh Market – Freshness Delivered.
</footer>

<script src="../js/config.js"></script>
<script src="../js/order-confirmation.js"></script>
</body>
</html>
