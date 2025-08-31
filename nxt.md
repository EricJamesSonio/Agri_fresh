ido have checkout here and its not workign yet to process with saing in backend database <!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Agri-Fresh</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <!-- Google Font -->
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="../css/style.css">
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
      <li><a href="admin.html">Admin</a></li>
    </ul>
  </nav>

  <!-- existing horizontal nav -->
  <nav>
    <a href="#" onclick="openOrders()">My Orders</a>
    <a href="about.html">About Us</a>
    <a href="#products">Products</a>
    <a href="#contact">Contact</a>
  </nav>
</header>

  <!-- 🎞️ Hero with slideshow -->
  <section id="home" class="hero">
    <div class="hero-slides">
      <!-- AVIF image from Unsplash (premium) -->
      <img src="https://plus.unsplash.com/premium_photo-1724129050570-669724edcffc?q=80&w=687&auto=format&fit=crop"
           alt="Slide 1">
      <img src="https://images.unsplash.com/photo-1542838132-92c53300491e?auto=format&fit=crop&w=1600&q=80"
           alt="Slide 2">
      <img src="https://images.unsplash.com/photo-1597362925123-77861d3fbac7?auto=format&fit=crop&w=1170&q=80"
           alt="Slide 3">
      <img src="https://images.unsplash.com/photo-1557844352-761f2565b576?auto=format&fit=crop&w=2070&q=80"
           alt="Slide 4">
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
      <button class="mega-cta" onclick="scrollToProducts()">
        🛒 SHOP NOW
      </button>
    </div>
  </section>

  <!-- 🔍 Filters -->
  <div class="filters">
    <input id="search" type="text" placeholder="Search produce…" onkeyup="filterProducts()" autocomplete="off">

    <select id="category" onchange="filterProducts()">
      <option value="all">All Categories</option>
      <option>Leafy Greens</option><option>Roots</option><option>Fruits</option><option>Herbs</option>
    </select>
    <div class="tags">
      <button onclick="toggleTag(this,'organic')">Organic</button>
      <button onclick="toggleTag(this,'seasonal')">Seasonal</button>
    </div>
  </div>

  <!-- 🛍️ Product Grid -->
  <main id="products" class="grid"></main>

  <!-- 🧺 FIXED Floating Cart -->
  <aside id="cart" class="sidebar">
    <div style="display:flex;justify-content:space-between;align-items:center">
      <h4>Your Cart&nbsp;&nbsp;&nbsp;&nbsp; --><span id="cart-count">0</span></h4>
      <span onclick="closeCart()" style="cursor:pointer;font-size:1.3rem;">×</span>
    </div>
    <ul id="cart-items"></ul>
    <div class="cart-summary">
      <strong>Total:</strong> <span id="cart-total">₱0</span>
    </div>
    <button onclick="checkout()">Checkout</button>
  </aside>

  <!-- 🔐 Login Modal (optional) -->
  <div id="login-modal" class="modal">
    <div class="modal-content">
      <span onclick="closeModal()" class="close">&times;</span>
      <h3>Login / Sign Up</h3>
      <input placeholder="Email">
      <input placeholder="Password" type="password">
      <button>Go</button>
    </div>
  </div>

    <footer>
    <p style="text-align:center;padding:1.5rem 0;color:#666;">
      &copy; 2025 AgriFresh Market – Freshness Delivered.
    </p>
  </footer>

  <script src="../js/config.js"></script>
  <script src="../js/script.js"></script>

</body>
</html> so here is teh js let products = []; 
let cart = [];
let activeTags = [];

async function fetchProducts() {
  try {
    const res = await fetch(apiUrl('products')); // <-- uses config.js
    if (!res.ok) throw new Error('Failed to fetch products');

    products = await res.json();
    products = products.map(p => ({
      ...p,
      img: `${CONFIG.IMAGE_PATH}/${p.img}` // use config.js IMAGE_PATH
    }));

    render(products);
  } catch (err) {
    console.error(err);
    document.getElementById('products').innerHTML = `<p style="color:red;">Failed to load products</p>`;
  }
}


// Render cards
function render(list = products) {
  const grid = document.getElementById('products');
  grid.innerHTML = list.map(p => `
    <div class="card">
      <img src="${p.img}" alt="${p.name}" loading="lazy">
      <div class="card-body">
        <h4>${p.name}</h4>
        <span class="price">₱${p.price}</span>
        <button onclick="addToCart('${p.name}',${p.price})">Add to Cart</button>
      </div>
    </div>
  `).join('');
}

// Cart helpers
function addToCart(name, price) {
  const found = cart.find(i => i.name === name);
  found ? found.qty++ : cart.push({ name, price, qty: 1 });
  updateCart();
}
function updateCart() {
  const list   = document.getElementById('cart-items');
  const total  = document.getElementById('cart-total');
  const count  = document.getElementById('cart-count');

  list.innerHTML = cart.map(
    (item, idx) =>
      `<li>
         ${item.name} – ₱${item.price} × ${item.qty}
         <span class="item-controls">
           <button class="minus" onclick="changeQty(${idx},-1)">-</button>
           <button class="plus"  onclick="changeQty(${idx},1)">+</button>
           <button class="remove" onclick="removeItem(${idx})">×</button>
         </span>
       </li>`
  ).join('');

  const grand = cart.reduce((t, i) => t + i.price * i.qty, 0);
  total.textContent = '₱' + grand;
  count.textContent = cart.reduce((c, i) => c + i.qty, 0);

  document.getElementById('cart').classList.toggle('show', cart.length > 0);
}
function changeQty(idx, delta) {
  cart[idx].qty += delta;
  if (cart[idx].qty < 1) cart.splice(idx, 1);
  updateCart();
}
function removeItem(idx) {
  cart.splice(idx, 1);
  updateCart();
}
function closeCart() {
  document.getElementById('cart').classList.remove('show');
}

// Filters
function filterProducts() {
  const search = document.getElementById('search').value.toLowerCase();
  const category = document.getElementById('category').value;
  let filtered = products.filter(p => {
    const matchSearch = p.name.toLowerCase().includes(search);
    const matchCat = category === 'all' || p.category === category;
    const matchTag = activeTags.length === 0 || activeTags.every(t => p.tags.includes(t));
    return matchSearch && matchCat && matchTag;
  });
  render(filtered);
}
function toggleTag(btn, tag) {
  btn.classList.toggle('active');
  activeTags = [...document.querySelectorAll('.tags .active')].map(b => b.textContent.trim());
  filterProducts();
}

// Checkout
function checkout() {
  alert('Proceeding to payment (COD / Card coming soon)');
}

// Scroll
function scrollToProducts() {
  document.getElementById('products').scrollIntoView({ behavior: 'smooth' });
}

// Init
fetchProducts(); // uses API now  then here structure sop u need to add case swithc in idnex. and have routes, controller andmodel if needed code/ 
---> backend/                   # Backend logics to manipulate and control the data from database to frontend
---> ----> api/                 # Api entry point connection of frontend & Backend
---> ----> ----> index.php      # main api entry point (calls routes)
---> ----> ----> .htaccess      # security for the backend

---> ----> Controller/      # functions / logic
---> ----> Model/           # model of data table
---> ----> Routes/          # entrance

---> database/          # Database creation & seedings
---> ----> Model/
---> ----> Scripts/
---> ----> db.php          # database creation to xampp
---> ----> function.php    # main function used by the models and scripts
---> ----> seed.php        # seeder of data to database

---> frontend/          # designs and structure
---> ----> Html/
---> ----> Css/

---> ----> Js/
---> ----> --> config.js    # based helper for connecting backend to js

---> ----> Images/
  then here is some database table <?php

require_once(__DIR__ . '/../db.php');
require_once(__DIR__ . '/../function.php');

createTable($con, 'order_detail', "
    CREATE TABLE order_detail (
        order_detail_id INT AUTO_INCREMENT PRIMARY KEY,
        order_id INT NOT NULL,
        product_id INT NOT NULL,
        quantity INT NOT NULL,
        price_each DECIMAL(10,2) NOT NULL,
        FOREIGN KEY (order_id) REFERENCES `orders`(order_id) ON DELETE CASCADE,
        FOREIGN KEY (product_id) REFERENCES product(product_id) ON DELETE CASCADE
    )
");  <?php

require_once(__DIR__ . '/../db.php');
require_once(__DIR__ . '/../function.php');

createTable($con, 'orders', "
    CREATE TABLE `orders` (
        order_id INT AUTO_INCREMENT PRIMARY KEY,
        customer_id INT NOT NULL,
        address_id INT NOT NULL,
        total_amount DECIMAL(10,2) NOT NULL,
        order_status ENUM('pending', 'processing', 'shipped', 'delivered', 'cancelled') DEFAULT 'pending',
        payment_method VARCHAR(50),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (customer_id) REFERENCES customer(customer_id) ON DELETE CASCADE,
        FOREIGN KEY (address_id) REFERENCES customer_address(address_id) ON DELETE RESTRICT
    )
");

?> 