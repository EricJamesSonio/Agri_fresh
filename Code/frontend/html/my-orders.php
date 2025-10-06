<?php
session_start();

// ✅ Require login
if (!isset($_SESSION['customer_id'])) {
    header("Location: login.php");
    exit();
}

// Store role and customer info (unchanged)
$role = $_SESSION['role'] ?? 'customer';
$customer_id = $_SESSION['customer_id'];
$customer_name = $_SESSION['customer_name'] ?? 'Customer';

// ---- ADDED: DB include + notification count (minimal, non-invasive) ----
require_once(__DIR__ . '/../../database/db.php');
$notifCountRes = mysqli_query($con, "SELECT COUNT(*) as cnt FROM notifications WHERE customer_id=$customer_id AND is_read=0");
$notifCountRow = mysqli_fetch_assoc($notifCountRes);
$notifCount = $notifCountRow['cnt'] ?? 0;
// -----------------------------------------------------------------------
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

  <!-- ---- ADDED: modal styles (required for the notification popup) ---- -->
  <link rel="stylesheet" href="../css/modal.css">
  <!-- ------------------------------------------------------------------ -->
</head>
<body>
<?php include(__DIR__ . '/../components/sidebar.php'); ?>

<header>
  <h1>Agri Fresh Market</h1>
  <nav>
    <a href="index.php">Home</a>
    <a href="index.php#products">Product</a>
    <a href="my-orders.php">Orders</a>
    <a href="my-cart.php" style="display: inline-block; margin-left: 10px;">
        <img src="../images/cart.jpg" alt="Cart" style="width:32px; height:32px;">
    </a>

    <!-- ---- ADDED: Notification Bell (minimal markup) ---- -->
    <div class="notification-wrapper" id="notifWrapper" style="position: relative; display: inline-block; cursor: pointer; margin-left:12px;">
      🔔
      <span id="notifCount" style="position: absolute; top: -5px; right: -10px;
           background: red; color: white; font-size: 0.8rem; padding: 2px 6px;
           border-radius: 50%; <?= $notifCount > 0 ? 'display:inline-block;' : 'display:none;' ?>">
           <?= $notifCount ?>
      </span>
    </div>
    <!-- --------------------------------------------------- -->
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

<!-- keep your existing customer_id variable where it is (unchanged) -->
<script>
  window.customer_id = <?php echo intval($customer_id); ?>;
</script>

<!-- keep your orders script exactly as before (unchanged) -->
<script src="../js/my-orders.js"></script>

<!-- ---- ADDED: config + notification script (placed AFTER your orders JS so it won't affect it) ---- -->
<script src="../js/config.js"></script>

<!-- Notification Modal (kept near the bottom, minimal) -->
<div id="notifModal" class="modal hidden" aria-hidden="true">
  <div class="modal-content">
    <span class="close-btn" id="closeNotifBtn" title="Close">&times;</span>
    <h3>Notifications</h3>
    <ul id="notifList" style="list-style: none; padding: 0; margin: 0;"></ul>
  </div>
</div>

<script>
/*
  Minimal notification script:
  - uses window.customer_id (already set above)
  - uses apiUrl() from ../js/config.js (loaded just above)
  - won't run if customer id is missing
  - does not alter any existing variables or functions
*/
(function () {
  if (typeof window === 'undefined') return;
  const customerId = window.customer_id;
  if (!customerId) return;

  const notifWrapper = document.getElementById('notifWrapper');
  const notifModal = document.getElementById('notifModal');
  const closeNotifBtn = document.getElementById('closeNotifBtn');
  const notifList = document.getElementById('notifList');
  const notifBadge = document.getElementById('notifCount');

  function showModal() {
    if (!notifModal) return;
    notifModal.classList.remove('hidden');
    notifModal.setAttribute('aria-hidden', 'false');
    markNotificationsRead();
  }

  function hideModal() {
    if (!notifModal) return;
    notifModal.classList.add('hidden');
    notifModal.setAttribute('aria-hidden', 'true');
  }

  async function loadNotifications() {
    if (typeof apiUrl !== 'function') {
      console.warn('apiUrl() not available — check ../js/config.js');
      return;
    }
    try {
      const res = await fetch(apiUrl(`get-notifications?customer_id=${customerId}`));
      if (!res.ok) {
        console.warn('Failed to fetch notifications', res.status);
        return;
      }
      const notifications = await res.json();
      const unreadCount = Array.isArray(notifications) ? notifications.filter(n => n.is_read == 0).length : 0;
      if (notifBadge) {
        notifBadge.innerText = unreadCount;
        notifBadge.style.display = unreadCount > 0 ? 'inline-block' : 'none';
      }
      if (notifList) {
        notifList.innerHTML = '';
        if (Array.isArray(notifications) && notifications.length) {
          notifications.forEach(n => {
            const li = document.createElement('li');
            li.style.padding = '8px';
            li.style.borderBottom = '1px solid #eee';
            if (n.is_read == 0) li.style.fontWeight = 'bold';
            li.innerHTML = `${n.message}<br><small style="color:#888;">${n.created_at}</small>`;
            notifList.appendChild(li);
          });
        } else {
          const li = document.createElement('li');
          li.style.padding = '8px';
          li.textContent = 'No notifications yet.';
          notifList.appendChild(li);
        }
      }
    } catch (err) {
      console.error('Error loading notifications:', err);
    }
  }

  function markNotificationsRead() {
    if (typeof apiUrl !== 'function') return;
    fetch(apiUrl('mark-notif-read'), {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ customer_id: customerId })
    }).then(() => {
      if (notifBadge) {
        notifBadge.innerText = 0;
        notifBadge.style.display = 'none';
      }
    }).catch(console.error);
  }

  // Attach handlers safely (elements exist because this script is at bottom)
  if (notifWrapper) notifWrapper.addEventListener('click', showModal);
  if (closeNotifBtn) closeNotifBtn.addEventListener('click', hideModal);

  // Load notifications when the page finishes loading (non-invasive)
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', loadNotifications);
  } else {
    loadNotifications();
  }
})();
</script>
<!-- -------------------------------------------------------------------------------------------- -->

</body>
</html>
