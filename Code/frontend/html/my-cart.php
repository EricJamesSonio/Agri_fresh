<?php
session_start();
if (!isset($_SESSION['customer_id'])) { 
    header("Location: login.php"); 
    exit(); 
}

require_once(__DIR__ . '/../../database/db.php');

$role = $_SESSION['role'] ?? 'customer';
$customer_id = $_SESSION['customer_id'];

// Fetch notifications for the logged-in customer
$notifQuery = mysqli_query($con, "SELECT * FROM notifications WHERE customer_id=$customer_id ORDER BY created_at DESC");
$notifCountRes = mysqli_query($con, "SELECT COUNT(*) as cnt FROM notifications WHERE customer_id=$customer_id AND is_read=0");
$notifCountRow = mysqli_fetch_assoc($notifCountRes);
$notifCount = $notifCountRow['cnt'] ?? 0;
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
  <link rel="stylesheet" href="../css/modal.css">

  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600&display=swap" rel="stylesheet">
</head>
<body>
<?php include(__DIR__ . '/../components/sidebar.php'); ?>

<header>
  <h1>Agri Fresh Market</h1>
  <nav>
    <a href="index.php">Home</a>
    <a href="index.php#products">Products</a>
    <a href="my-orders.php">Orders</a>

    <!-- 🛒 Cart Icon -->
    <a href="my-cart.php" style="display: inline-block; margin-left: 10px;">
      <img src="../images/cart.jpg" alt="Cart" style="width:32px; height:32px;">
    </a>

    <!-- 🔔 Notification Bell -->
    <div class="notification-wrapper" id="notifWrapper" style="position: relative; display: inline-block; cursor: pointer;">
      🔔
      <span id="notifCount" style="position: absolute; top: -5px; right: -10px; 
           background: red; color: white; font-size: 0.8rem; padding: 2px 6px; 
           border-radius: 50%; <?= $notifCount > 0 ? 'display:inline-block;' : 'display:none;' ?>">
           <?= $notifCount ?>
      </span>
    </div>
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

<!-- 🔔 Notification Modal -->
<div id="notifModal" class="modal hidden">
  <div class="modal-content">
    <span class="close-btn" id="closeNotifBtn">&times;</span>
    <h3>Notifications</h3>
    <ul id="notifList" style="list-style: none; padding: 0;"></ul>
  </div>
</div>

<footer>
  <p>&copy; 2025 AgriFresh Market – Freshness Delivered.</p>
</footer>




<script src="../js/my-cart.js"></script>

<script>
const customerId = <?= json_encode($customer_id) ?>;
const notifWrapper = document.getElementById('notifWrapper');
const notifModal = document.getElementById('notifModal');
const closeNotifBtn = document.getElementById('closeNotifBtn');
const notifList = document.getElementById('notifList');
const notifBadge = document.getElementById('notifCount');

function openNotifModal() {
    notifModal.classList.remove('hidden');
    markNotificationsRead();
}
function closeNotifModal() {
    notifModal.classList.add('hidden');
}

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

</body>
</html>
