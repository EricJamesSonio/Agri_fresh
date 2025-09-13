<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$role = $_SESSION['role'] ?? 'customer';
?>

<!-- Sidebar Trigger -->
<div class="sidebar-trigger"></div>

<!-- Sidebar Menu -->
<div class="sidebar-menu">
  <a href="signup.php">Sign Up</a>
  <a href="profile.php">Profile</a>
  <a href="about.php">About Us</a>
  <?php if ($role === 'admin'): ?>
    <a href="../admin/">Admin</a>
  <?php elseif ($role === 'cashier'): ?>
    <a href="../cashier/">Cashier Panel</a>
  <?php endif; ?>
  <a href="#" onclick="logout()" class="logout">Logout</a>
</div>
