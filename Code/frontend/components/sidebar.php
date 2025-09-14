<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// fallback role (in case CONFIG injection fails)
$role = $_SESSION['role'] ?? 'customer';
?>

<!-- Sidebar Trigger -->
<div class="sidebar-trigger"></div>

<!-- Sidebar Menu -->
<div class="sidebar-menu">
  <a href="signup.php">Sign Up</a>
  <a href="profile.php">Profile</a>
  <a href="about.php">About Us</a>

  <!-- Role-based links -->
  <?php if ($role === 'admin'): ?>
    <a href="../admin/">Admin</a>
  <?php elseif ($role === 'cashier'): ?>
    <a href="../cashier/">Cashier Panel</a>
  <?php endif; ?>

  <a href="#" onclick="logout()" class="logout">Logout</a>
</div>

<!-- Global scripts (loaded once here) -->
<script src="../js/config.js"></script>

<!-- Inject PHP session values into CONFIG -->
<script>
  CONFIG.CUSTOMER_ID = "<?= htmlspecialchars($_SESSION['customer_id'] ?? '') ?>";
  CONFIG.CUSTOMER_NAME = "<?= htmlspecialchars($_SESSION['customer_name'] ?? 'Customer') ?>";
  CONFIG.ROLE = "<?= htmlspecialchars($role) ?>";
</script>

<script src="../js/script.js"></script>
