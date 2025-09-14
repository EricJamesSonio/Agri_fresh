<?php
session_start();

// ✅ Require login
if (!isset($_SESSION['customer_id'])) {
    header("Location: login.php");
    exit();
}

// Store role and customer info
$role = $_SESSION['role'] ?? 'customer';
$customer_id = $_SESSION['customer_id'];
$customer_name = $_SESSION['customer_name'] ?? 'Customer';
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>My Orders - Agri-Fresh</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="../css/style.css">
  <link rel="stylesheet" href="../css/sidebar.css">
  <link rel="stylesheet" href="../css/my_orders.css">
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
  <h2>Hello, <?php echo htmlspecialchars($customer_name); ?>! Here are your orders:</h2>
  <div id="orders-container">
    <p>Loading orders...</p>
  </div>
</main>

<footer style="text-align:center; padding:1rem; color:#666;">
  &copy; 2025 AgriFresh Market – Freshness Delivered.
</footer>
<script>
  window.customer_id = <?php echo intval($customer_id); ?>;
</script>
<script src="../js/my-orders.js"></script>


</body>
</html>
