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

// Include database connection
require_once(__DIR__ . '/../../database/db.php');

// Fetch notification count
$notifCountRes = mysqli_query($con, "SELECT COUNT(*) as cnt FROM notifications WHERE customer_id=$customer_id AND is_read=0");
$notifCountRow = mysqli_fetch_assoc($notifCountRes);
$notifCount = $notifCountRow['cnt'] ?? 0;
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
  <link rel="stylesheet" href="../css/modal.css">
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
    
    <!-- 🔔 Notification Icon -->
    <div class="notification-wrapper" id="notifWrapper" style="position: relative; display: inline-block; cursor: pointer;">
        🔔
        <span id="notifCount" style="position: absolute; top: -5px; right: -10px; 
             background: red; color: white; font-size: 0.8rem; padding: 2px 6px; 
             border-radius: 50%; <?php echo ($notifCount > 0 ? '' : 'display:none;'); ?>">
             <?php echo $notifCount; ?>
        </span>
    </div>
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

<!-- 🧾 Notifications Modal -->
<div id="notifModal" class="modal hidden">
  <div class="modal-content">
    <span class="close-btn" id="closeNotifBtn">&times;</span>
    <h3>Notifications</h3>
    <ul id="notifList" style="list-style: none; padding: 0;"></ul>
  </div>
</div>

<script>
  // ✅ Notification System (Same as index.php)
  const customerId = <?php echo intval($customer_id); ?>;
  const notifWrapper = document.getElementById('notifWrapper');
  const notifModal = document.getElementById('notifModal');
  const closeNotifBtn = document.getElementById('closeNotifBtn');
  const notifList = document.getElementById('notifList');
  const notifBadge = document.getElementById('notifCount');

  function apiUrl(path) {
      // 👇 Adjust to your real backend API path
      return `../../backend/api/${path}.php`;
  }

  function openNotifModal() {
      notifModal.classList.remove('hidden');
      markNotificationsRead();
  }
  function closeNotifModal() { notifModal.classList.add('hidden'); }

  async function loadNotifications() {
      try {
          const res = await fetch(apiUrl(`get-notifications?customer_id=${customerId}`));
          const notifications = await res.json();
          const unreadCount = notifications.filter(n => n.is_read == 0).length;
          notifBadge.innerText = unreadCount;
          notifBadge.style.display = unreadCount > 0 ? 'inline-block' : 'none';
          notifList.innerHTML = '';
          notifications.forEach(n => {
              const li = document.createElement('li');
              li.style.padding = '8px';
              li.style.borderBottom = '1px solid #ddd';
              if (n.is_read == 0) li.style.fontWeight = 'bold';
              li.innerHTML = `${n.message}<br><small style="color:#888;">${n.created_at}</small>`;
              notifList.appendChild(li);
          });
      } catch (err) {
          console.error('Error loading notifications:', err);
      }
  }

  function markNotificationsRead() {
      fetch(apiUrl('mark-notif-read'), {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({ customer_id: customerId })
      }).then(() => {
          notifBadge.innerText = 0;
          notifBadge.style.display = 'none';
      }).catch(console.error);
  }

  notifWrapper.addEventListener('click', openNotifModal);
  closeNotifBtn.addEventListener('click', closeNotifModal);
  document.addEventListener('DOMContentLoaded', loadNotifications);
</script>

<script src="../js/my-orders.js"></script>
</body>
</html>
