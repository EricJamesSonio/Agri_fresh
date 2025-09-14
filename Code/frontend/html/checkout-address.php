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
  <title>Checkout - Select Address | Agri-Fresh</title>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="../css/style.css">
  <link rel="stylesheet" href="../css/sidebar.css">
  <link rel="stylesheet" href="../css/checkout-address.css">
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
  <div class="checkout-container">
    <!-- Order Summary -->
    <div class="order-summary">
      <h3>Order Summary</h3>
      <div id="order-items"></div>
      <div style="font-weight: bold; margin-top: 1rem;">
        Total: <span id="order-total">₱0</span>
      </div>
    </div>

    <!-- Address Selection -->
    <div class="address-section">
      <h3>Delivery Address</h3>
      <div id="address-list">
        <div class="loading">Loading addresses...</div>
      </div>
      <button type="button" class="btn-outline btn" onclick="toggleAddressForm()">
        + Add New Address
      </button>
    </div>

    <!-- Add Address Form -->
    <div id="add-address-form" class="add-address-form">
      <h4>Add New Address</h4>
      <form id="address-form">
        <div class="form-row">
          <div class="form-group">
            <label for="street">Street Address *</label>
            <input type="text" id="street" name="street" required>
          </div>
        </div>
        <div class="form-row">
          <div class="form-group">
            <label for="city">City *</label>
            <input type="text" id="city" name="city" required>
          </div>
          <div class="form-group">
            <label for="state">State/Province</label>
            <input type="text" id="state" name="state">
          </div>
        </div>
        <div class="form-row">
          <div class="form-group">
            <label for="postal_code">Postal Code</label>
            <input type="text" id="postal_code" name="postal_code">
          </div>
          <div class="form-group">
            <label for="country">Country *</label>
            <select id="country" name="country" required>
              <option value="Philippines">Philippines</option>
              <option value="Other">Other</option>
            </select>
          </div>
        </div>
        <div class="form-group">
          <label>
            <input type="checkbox" id="is_default" name="is_default"> 
            Set as default address
          </label>
        </div>
        <button type="submit" class="btn">Save Address</button>
        <button type="button" class="btn btn-secondary" onclick="toggleAddressForm()">Cancel</button>
      </form>
      <div id="address-message"></div>
    </div>

    <!-- Payment Method -->
    <div class="payment-section" style="margin-top: 2rem;">
      <h3>Payment Method</h3>
      <div class="form-group">
        <label>
          <input type="radio" name="payment_method" value="COD" checked> 
          Cash on Delivery (COD)
        </label>
      </div>
      <div class="form-group">
        <label>
          <input type="radio" name="payment_method" value="Card" disabled> 
          Credit/Debit Card (Coming Soon)
        </label>
      </div>
    </div>

    <!-- Action Buttons -->
    <div class="action-buttons" style="margin-top: 2rem;">
      <button class="btn btn-secondary" onclick="goBack()">Back to Cart</button>
      <button id="place-order-btn" class="btn" onclick="placeOrder()" disabled>Place Order</button>
    </div>
  </div>
</main>

<footer style="text-align:center; padding:1rem; color:#666;">
  &copy; 2025 AgriFresh Market – Freshness Delivered.
</footer>

<!-- Load global config -->
<script src="../js/config.js"></script>

<!-- Inject PHP session values into CONFIG -->
<script>
  CONFIG.CUSTOMER_ID = "<?= htmlspecialchars($customer_id) ?>";
  CONFIG.CUSTOMER_NAME = "<?= htmlspecialchars($customer_name) ?>";
  CONFIG.ROLE = "<?= htmlspecialchars($role) ?>";
</script>

<!-- Page-specific JS -->
<script src="../js/checkout-address.js"></script>
</body>
</html>
