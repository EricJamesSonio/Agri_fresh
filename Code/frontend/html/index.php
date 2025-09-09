<?php
session_start();

// ✅ Require login
if (!isset($_SESSION['customer_id'])) {
    header("Location: login.html");
    exit();
}

// Include database connection
require_once(__DIR__ . '/../../database/db.php'); // Adjust the path as needed

// Store role for later use
$role = $_SESSION['role'] ?? 'customer';
$customer_id = $_SESSION['customer_id'];

// Fetch all notifications for this customer
$notifQuery = mysqli_query($con, "SELECT * FROM notifications WHERE customer_id=$customer_id ORDER BY created_at DESC");

// Count unread notifications
$notifCountRes = mysqli_query($con, "SELECT COUNT(*) as cnt FROM notifications WHERE customer_id=$customer_id AND is_read=0");
$notifCountRow = mysqli_fetch_assoc($notifCountRes);
$notifCount = $notifCountRow['cnt'] ?? 0;
?>

<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Agri-Fresh</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <!-- Google Font -->
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="../css/style.css">
  <link rel="stylesheet" href="../css/modal.css">
</head>
<body>

  <!-- 🔝 Header -->
<header>
  <h1>Agri Fresh Market</h1>

  <!-- 3-dot menu -->
  <nav class="dots-menu">
    <input type="checkbox" id="dots-toggle">
    <label for="dots-toggle">⋮</label>
    <ul>
      <li><a href="signup.html">Sign up</a></li>
      <li><a href="profile.html">Profile</a></li>

      <!-- ✅ Show only if admin -->
      <?php if ($role === 'admin'): ?>
        <li id="admin-link"><a href="../admin/">Admin</a></li>
      <?php endif; ?>

      <li><a href="#" onclick="logout()">Logout</a></li>
    </ul>
  </nav>

  <!-- existing horizontal nav -->
  <nav>
    <a href="#products">Products</a>
    <a href="my-orders.php">My Orders</a>
    <a href="about.html">About Us</a>

    
    
   <div class="notification-wrapper" id="notifWrapper" style="position: relative; display: inline-block; cursor: pointer;">
    🔔
    <span id="notifCount" style="position: absolute; top: -5px; right: -10px; 
         background: red; color: white; font-size: 0.8rem; padding: 2px 6px; 
         border-radius: 50%; display:none;"></span>
    <a href="my-cart.html" style="display: inline-block; margin-left: 10px;">
        <img src="../images/cart.jpg" alt="Cart" style="width:24px; height:24px;">
    </a>
</div>

  <a href="#"></a>

  </nav>
</header>

  <!-- 🎞️ Hero with slideshow -->
  <section id="home" class="hero">
    <div class="hero-slides">
      <img src="https://plus.unsplash.com/premium_photo-1724129050570-669724edcffc?q=80&w=687&auto=format&fit=crop" alt="Slide 1">
      <img src="https://images.unsplash.com/photo-1542838132-92c53300491e?auto=format&fit=crop&w=1600&q=80" alt="Slide 2">
      <img src="https://images.unsplash.com/photo-1597362925123-77861d3fbac7?auto=format&fit=crop&w=1170&q=80" alt="Slide 3">
      <img src="https://images.unsplash.com/photo-1557844352-761f2565b576?auto=format&fit=crop&w=2070&q=80" alt="Slide 4">
    </div>

    <!-- Right-aligned mega text -->
    <div class="hero-text">
      <h2 class="mega-title">
        <span class="line-1">Organic</span>
        <span class="line-2">food</span>
        <span class="line-3">everyday</span>
      </h2>
      <p class="mega-desc">
        Handpicked daily,<br>
        straight from local farms<br>
        to your kitchen.
        <small>No traffic, no hassle, just greens at one click!</small>
      </p>
      <button class="mega-cta" onclick="scrollToProducts()">🛒 SHOP NOW</button>
    </div>
  </section>

  <!-- 🔍 Filters -->
  <div class="filters">
    <input id="search" type="text" placeholder="Search produce…" onkeyup="filterProducts()" autocomplete="off">

    <select id="category" onchange="filterProducts()">
      <option value="all">All Categories</option>
      <option>Leafy Greens</option>
      <option>Roots</option>
      <option>Fruits</option>
      <option>Herbs</option>
    </select>
    <div class="tags">
  <button type="button" data-tag="organic" onclick="toggleTag(this)">Organic</button>
  <button type="button" data-tag="seasonal" onclick="toggleTag(this)">Seasonal</button>
</div>

  </div>

  <!-- 🛍️ Product Grid -->
  <main id="products" class="grid"></main>


  <aside id="cart" class="sidebar">
    <div style="display:flex;justify-content:space-between;align-items:center">
      <h4>Cart (<span id="cart-count">0</span>)</h4>
      <span onclick="closeCart()" style="cursor:pointer;font-size:1.3rem;">×</span>
    </div>
    <ul id="cart-items"></ul>
    <div class="cart-summary">
      <strong>Total:</strong> <span id="cart-total">₱0</span>
    </div>
   
    <button onclick="goToCart()" class="go-to-cart-btn">Go to Cart</button>
  </aside>
  <footer>
    <p style="text-align:center;padding:1.5rem 0;color:#666;">
      &copy; 2025 AgriFresh Market – Freshness Delivered.
    </p>
  </footer>


  <!-- Product Size Modal -->
<div id="sizeModal" class="modal hidden">
  <div class="modal-content">
    <span class="close-btn" onclick="closeSizeModal()">&times;</span>
    <h3 id="modalProductName"></h3>
    <img id="modalProductImg" src="" alt="" style="max-width:120px; margin:10px 0;">
    <p id="modalProductDesc"></p>

    <label for="sizeSelect">Choose size:</label>
    <select id="sizeSelect"></select>

    <label for="modalQty">Quantity:</label>
    <input type="number" id="modalQty" value="1" min="1" style="width:60px;">

    <button id="confirmAddBtn">Add to Cart</button>
  </div>
</div>

 <div id="notifModal" class="modal hidden">
  <div class="modal-content">
    <span class="close-btn" id="closeNotifBtn">&times;</span>
    <h3>Notifications</h3>
    <ul id="notifList" style="list-style: none; padding: 0;">
      <!-- Notifications will be injected here by JS -->
    </ul>
  </div>
</div>

<script src="../js/config.js"></script>
<script>
const customerId = localStorage.getItem('customer_id');
const notifWrapper = document.getElementById('notifWrapper');
const notifModal = document.getElementById('notifModal');
const closeNotifBtn = document.getElementById('closeNotifBtn');
const notifList = document.getElementById('notifList');
const notifBadge = document.getElementById('notifCount');

// Open modal
function openNotifModal() {
    notifModal.classList.remove('hidden');
    markNotificationsRead();
}

// Close modal
function closeNotifModal() {
    notifModal.classList.add('hidden');
}

// Fetch notifications from backend
async function loadNotifications() {
    if (!customerId) return;

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
        console.error(err);
    }
}

// Mark notifications as read
function markNotificationsRead() {
    fetch(apiUrl('mark-notif-read'), {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ customer_id: customerId })
    })
    .then(() => {
        notifBadge.innerText = 0;
        notifBadge.style.display = 'none';
    })
    .catch(console.error);
}

// Event listeners
notifWrapper.addEventListener('click', openNotifModal);
closeNotifBtn.addEventListener('click', closeNotifModal);

// Load notifications on page load
document.addEventListener('DOMContentLoaded', loadNotifications);
</script>

  <script src="../js/script.js"></script>

  
</body>
</html>
