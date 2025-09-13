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
  <style>
    .order-card {
        border: 1px solid #ddd;
        padding: 1rem;
        margin-bottom: 1rem;
        border-radius: 8px;
        background: #fafafa;
    }
    .order-header {
        display: flex;
        justify-content: space-between;
        margin-bottom: 0.5rem;
    }
    .order-details {
        margin-top: 0.5rem;
    }
    .order-item {
        border-top: 1px solid #eee;
        padding: 0.5rem 0;
    }
    .status-pending { color: orange; }
    .status-processing { color: blue; }
    .status-shipped { color: purple; }
    .status-delivered { color: green; }
    .status-cancelled { color: red; }
  </style>
</head>
<body>
<?php include(__DIR__ . '/../components/sidebar.php'); ?>


<header>
  <h1>Agri Fresh Market</h1>
  <nav>
    <a href="index.php">Home</a>
    <a href="about.php">About Us</a>
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
const customer_id = <?php echo intval($customer_id); ?>;

async function fetchOrders() {
    const container = document.getElementById('orders-container');
    container.innerHTML = '<p>Loading orders...</p>';

    try {
        const res = await fetch(`../../backend/api/index.php?request=orders&customer_id=${customer_id}`);
        const data = await res.json();

        if (data.status !== 'success') {
            container.innerHTML = `<p>${data.message}</p>`;
            return;
        }

        const orders = data.data;
        if (!orders.length) {
            container.innerHTML = '<p>No orders found.</p>';
            return;
        }

        container.innerHTML = '';
       orders.forEach(order => {
    const orderCard = document.createElement('div');
    orderCard.className = 'order-card';

    const statusClass = 'status-' + order.order_status.toLowerCase();

    orderCard.innerHTML = `
        <div class="order-header">
            <div>
                <strong>Order #${order.order_id}</strong> 
                - <span class="${statusClass}">${order.order_status}</span>
            </div>
            <div>Total: ${parseFloat(order.total_amount).toLocaleString('en-PH', { style: 'currency', currency: 'PHP' })}</div>
        </div>
        <div><small>Ordered on: ${new Date(order.created_at).toLocaleString()}</small></div>
        <div><strong>Shipping Address:</strong> ${order.street}, ${order.city}, ${order.state || ''}, ${order.country}</div>
        <div class="order-details">
            ${order.details.map(item => `
                <div class="order-item">
                    ${item.product_name} - Qty: ${item.quantity} - ${parseFloat(item.price_each).toLocaleString('en-PH', { style: 'currency', currency: 'PHP' })}
                </div>
            `).join('')}
        </div>
    `;
    container.appendChild(orderCard);
});


    } catch (error) {
        container.innerHTML = `<p>Error fetching orders: ${error.message}</p>`;
    }
}

// Fetch orders on page load
fetchOrders();
</script>

</body>
</html>
