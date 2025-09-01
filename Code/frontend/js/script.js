class ShoppingCart {
  constructor() {
    this.products = [];
    this.cart = [];
    this.activeTags = [];
    this.customer_id = this.initializeCustomerId();
  }

  
  initializeCustomerId() {
    const rawCustomerId = localStorage.getItem("customer_id");
    const customerName = localStorage.getItem("customer_name");

    console.log("Raw customer_id from localStorage:", rawCustomerId);
    console.log("Customer name from localStorage:", customerName);

    // 🚫 If the logged in user is admin, block cart
    if (customerName && customerName.toLowerCase().includes("admin")) {
      console.warn("Admin detected, disabling cart features.");
      return null;
    }

    if (rawCustomerId && rawCustomerId !== "null" && rawCustomerId !== "undefined") {
      const parsedId = parseInt(rawCustomerId, 10);
      console.log("Parsed customer_id:", parsedId);

      if (isNaN(parsedId)) {
        console.warn("customer_id is not a valid number:", rawCustomerId);
        return null;
      }
      return parsedId;
    } else {
      console.warn("No valid customer_id found. Cart actions will be local only.");
      return null;
    }
  }
  async fetchProducts() {
  try {
    console.log("Fetching products from:", apiUrl('products'));
    const res = await fetch(apiUrl('products'));
    if (!res.ok) throw new Error('Failed to fetch products');

    const rawProducts = await res.json();
    console.log("Raw products from API:", rawProducts);

this.products = rawProducts.map(p => {
  let imgSrc = p.img || p.image_url || 'placeholder.jpg';

  // Only prepend CONFIG.IMAGE_PATH if it’s a relative path (does NOT start with http)
  if (!imgSrc.startsWith('http')) {
    imgSrc = imageUrl(imgSrc);
  }

  return {
    ...p,
    id: parseInt(p.product_id || p.id, 10),
    category: p.category ?? p.category_name ?? 'Uncategorized',
    description: p.description ?? '',
    tags: Array.isArray(p.tags) ? p.tags.map(t => t.toLowerCase()) : [],
    img: imgSrc
  };
});



    console.log("Final products array:", this.products);
    this.render(this.products);
  } catch (err) {
    console.error(err);
    const productsEl = document.getElementById('products');
    if (productsEl) {
      productsEl.innerHTML = `<p style="color:red;">Failed to load products</p>`;
    }
  }
}

 async populateCategories() {
  const sel = document.getElementById('category');
  if (!sel) return;

  sel.innerHTML = `<option value="all">All Categories</option>`;

  try {
    // NOTE: use 'category' to match your index.php switch-case
    console.log("Fetching categories from:", apiUrl('category'));
    const res = await fetch(apiUrl('category'));
    const text = await res.text();
    console.log("Raw categories response text:", text);

    if (!res.ok) throw new Error(`Status ${res.status}`);

    let cats;
    try {
      cats = JSON.parse(text);
    } catch (e) {
      console.error('Failed to parse categories JSON', e);
      cats = [];
    }

    if (!Array.isArray(cats) || cats.length === 0) {
      const opt = document.createElement('option');
      opt.value = 'none';
      opt.textContent = '— No categories —';
      opt.disabled = true;
      sel.appendChild(opt);
      return;
    }

    cats.forEach(c => {
      const rawName = c.name ?? c.category_name ?? null;
      if (!rawName) {
        console.warn('Skipping category with no name field:', c);
        return;
      }
      const opt = document.createElement('option');
      opt.value = String(rawName).toLowerCase();
      opt.textContent = rawName;
      sel.appendChild(opt);
    });
  } catch (err) {
    console.error('Could not load categories', err);
    const opt = document.createElement('option');
    opt.value = 'all';
    opt.textContent = 'All Categories';
    sel.appendChild(opt);
  }
}



render(list = this.products) {
  const grid = document.getElementById('products');

  if (!grid) {
    console.warn("Products grid element not found, skipping render.");
    return; // exit gracefully
  }

  grid.innerHTML = list.map(p => `
    <div class="card">
      <img src="${p.img}" alt="${p.name}" loading="lazy" onerror="this.src='../images/placeholder.jpg'">
      <div class="card-body">
        <h4>${p.name}</h4>
        <span class="price">₱${p.price}</span>
        <button class="add-to-cart-btn" onclick="addToCart(${p.id})">Add to Cart</button>
      </div>
    </div>
  `).join('');
}


  async addToCart(product_id) {
  console.log("=== ADD TO CART DEBUG ===");
  console.log("addToCart called with product_id:", product_id, typeof product_id);
  console.log("Current customer_id:", this.customer_id, typeof this.customer_id);

  // 🚫 Prevent admin from adding to cart
  const customerName = localStorage.getItem("customer_name");
  if (customerName && customerName.toLowerCase().includes("admin")) {
    alert("Admins are not allowed to add items to the cart.");
    return;
  }

  // Ensure product_id is an integer
  product_id = parseInt(product_id, 10);

  // Force refresh customer_id from localStorage
  let fresh_customer_id = localStorage.getItem("customer_id");
  if (fresh_customer_id && fresh_customer_id !== "null") {
    fresh_customer_id = parseInt(fresh_customer_id, 10);
    if (!isNaN(fresh_customer_id)) {
      this.customer_id = fresh_customer_id;
      console.log("Refreshed customer_id:", this.customer_id);
    }
  }

  // If user is not logged in
  if (!this.customer_id || isNaN(this.customer_id)) {
    alert("You are not logged in yet. Please login to add items to your cart.");
    window.location.href = 'login.html'; // redirect to login page
    return;
  }

  try {
    // Existing code to add to cart if logged in...
    const found = this.cart.find(i => parseInt(i.product_id, 10) === product_id);
    const currentQty = found ? parseInt(found.qty, 10) : 0;
    const totalQty = currentQty + 1;

    const payload = {
      customer_id: parseInt(this.customer_id, 10),
      product_id: parseInt(product_id, 10),
      quantity: totalQty
    };

    const response = await fetch(apiUrl('cart'), {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json'
      },
      body: JSON.stringify(payload)
    });

    const result = await response.json();
    if (!response.ok || result.status === 'error') {
      throw new Error(result.message || 'Failed to add to cart');
    }

    await this.fetchCart();
  } catch (err) {
    console.error('Failed to sync cart:', err);
    alert('Failed to add item to cart: ' + err.message);
  }
}


  addToLocalCart(product_id) {
    const product = this.products.find(p => p.id === product_id);
    const found = this.cart.find(i => parseInt(i.product_id, 10) === product_id);
    
    if (found) {
      found.qty = parseInt(found.qty, 10) + 1; // FIXED: Ensure proper integer addition
    } else {
      this.cart.push({ 
        name: product.name, 
        price: product.price, 
        qty: 1, 
        product_id: product_id ,img: product.img ?? '../images/placeholder.jpg'
      });
    }
    this.updateCart();
  }

updateCart() {
  // Floating cart (index.php)
  const list = document.getElementById('cart-items');
  const total = document.getElementById('cart-total');
  const count = document.getElementById('cart-count');

  if (list && total && count) {
    list.innerHTML = this.cart.map(
      (item, idx) =>
        `<li>
           ${item.name} – ₱${item.price} × ${item.qty}
           <span class="item-controls">
             <button class="minus" onclick="cartInstance.changeQty(${idx},-1)">-</button>
             <button class="plus"  onclick="cartInstance.changeQty(${idx},1)">+</button>
             <button class="remove" onclick="cartInstance.removeItem(${idx})">×</button>
           </span>
         </li>`
    ).join('');

    const grand = this.cart.reduce((t, i) => t + (parseFloat(i.price) * parseInt(i.qty, 10)), 0);
    total.textContent = '₱' + grand;
    count.textContent = this.cart.reduce((c, i) => c + parseInt(i.qty, 10), 0);

    document.getElementById('cart').classList.toggle('show', this.cart.length > 0);
  }

  // Main cart table (my-orders.html)
  if (typeof renderOrderTable === 'function') renderOrderTable();
}


  async changeQty(idx, delta) {
    const item = this.cart[idx];
    const currentQty = parseInt(item.qty, 10);
    const newQty = currentQty + delta;

    if (!this.customer_id) {
      if (newQty < 1) {
        this.cart.splice(idx, 1);
      } else {
        this.cart[idx].qty = newQty;
      }
      this.updateCart();
      return;
    }

    try {
      const payload = {
        customer_id: parseInt(this.customer_id, 10),
        product_id: parseInt(item.product_id, 10),
        quantity: Math.max(newQty, 0)
      };

      const response = await fetch(apiUrl('cart'), {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(payload)
      });

      const result = await response.json();
      if (!response.ok || result.status === 'error') {
        throw new Error(result.message || 'Failed to update cart');
      }

      await this.fetchCart();
    } catch (err) {
      console.error('Failed to update cart item:', err);
    }
  }

  async removeItem(idx) {
    const product_id = this.cart[idx].product_id;

    if (!this.customer_id) {
      this.cart.splice(idx, 1);
      this.updateCart();
      return;
    }

    try {
      const payload = {
        customer_id: parseInt(this.customer_id, 10),
        product_id: parseInt(product_id, 10),
        quantity: 0
      };

      const response = await fetch(apiUrl('cart'), {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(payload)
      });

      const result = await response.json();
      if (!response.ok || result.status === 'error') {
        throw new Error(result.message || 'Failed to remove item');
      }

      await this.fetchCart();
    } catch (err) {
      console.error('Failed to remove cart item:', err);
    }
  }

  async fetchCart() {
    if (!this.customer_id) return;

    try {
      const res = await fetch(apiUrl(`cart?customer_id=${this.customer_id}`));
      if (!res.ok) throw new Error('Failed to fetch cart');
      const data = await res.json();
this.cart = data.map(i => {
  const prod = this.products.find(p => p.id === parseInt(i.product_id, 10));
  return {
    name: i.name,
    price: parseFloat(i.price_each),
    qty: parseInt(i.quantity, 10),
    product_id: parseInt(i.product_id, 10),
    img: prod ? prod.img : imageUrl('placeholder.jpg')
  };
});


      this.updateCart();
    } catch (err) {
      console.error(err);
    }
  }
  logout() {
    console.log("Logging out customer_id:", this.customer_id);
    
    // Remove customer identifier
    localStorage.removeItem("customer_id");

    // Reset internal cart
    this.cart = [];
    this.customer_id = null;

    // Update UI
    this.updateCart();

    // Optionally redirect to homepage/login
    window.location.href = location.origin + "/agrifresh/code/frontend/html/login.html";

  }
filterProducts() {
  const search = (document.getElementById('search').value || '').toLowerCase().trim();
  const category = (document.getElementById('category').value || '').toLowerCase();
  const activeTags = this.activeTags.map(t => t.toLowerCase());

  const filtered = this.products.filter(p => {
    const nameMatch = (p.name || '').toLowerCase().includes(search);
    const descMatch = (p.description || '').toLowerCase().includes(search);
    const searchMatch = nameMatch || descMatch;

    const productCategory = (p.category || 'uncategorized').toLowerCase();
    const categoryMatch = category === 'all' || productCategory === category;

    const productTags = Array.isArray(p.tags) ? p.tags.map(t => t.toLowerCase()) : [];

    // single-select behavior: activeTags is [] or [tag]
    // but use some() to keep it flexible (works if you later allow multiselect)
    const tagMatch = activeTags.length === 0 || activeTags.some(t => productTags.includes(t));

    return searchMatch && categoryMatch && tagMatch;
  });

  this.render(filtered);
}


 // SINGLE-SELECT toggleTag: clicking a tag deactivates others; clicking same tag toggles it off
toggleTag(btn) {
  // read tag from data attribute or fallback to button text
  const tag = (btn.dataset && btn.dataset.tag) ? String(btn.dataset.tag).toLowerCase() : btn.textContent.trim().toLowerCase();

  // deactivate all other tag buttons
  document.querySelectorAll('.tags button').forEach(b => {
    if (b !== btn) b.classList.remove('active');
  });

  // toggle clicked button (this allows clicking again to clear)
  btn.classList.toggle('active');

  // set activeTags to either [] or [tag]
  this.activeTags = btn.classList.contains('active') ? [tag] : [];

  // re-apply filters
  this.filterProducts();
}


  closeCart() {
    document.getElementById('cart').classList.remove('show');
  }

 checkout() {
  if (this.cart.length === 0) {
    alert('Your cart is empty!');
    return;
  }


  
  // Instead of redirecting to checkout-address.html
  window.location.href = 'my-orders.html';
}

goToCart() {
  // Navigate to the full cart page (my-orders.html)
  window.location.href = 'my-orders.html';
}

  scrollToProducts() {
    document.getElementById('products').scrollIntoView({ behavior: 'smooth' });
  }

  // Debug functions
  debugLocalStorage() {
    console.log("=== LOCALSTORAGE DEBUG ===");
    console.log("All localStorage keys:", Object.keys(localStorage));
    for (let i = 0; i < localStorage.length; i++) {
      const key = localStorage.key(i);
      const value = localStorage.getItem(key);
      console.log(`${key}: ${value} (type: ${typeof value})`);
    }
  }

  async testCartAPI() {
    const testPayload = {
      customer_id: parseInt(localStorage.getItem("customer_id"), 10),
      product_id: 1,
      quantity: 1
    };
    
    console.log("Testing with payload:", testPayload);
    
    try {
      const response = await fetch(apiUrl('cart'), {
        method: 'POST',
        headers: { 
          'Content-Type': 'application/json',
          'Accept': 'application/json'
        },
        body: JSON.stringify(testPayload)
      });
      
      const result = await response.json();
      console.log("Test response:", result);
    } catch (err) {
      console.error("Test failed:", err);
    }
  }
}


// Create global instance to maintain compatibility with existing HTML

const cartInstance = new ShoppingCart();
// Expose logout globally so HTML can call it
function logout() {cartInstance.logout();}window.logout = logout;
function goToCart() { return cartInstance.goToCart(); }

// Keep original function names for HTML compatibility
let products = cartInstance.products;
let cart = cartInstance.cart;
let activeTags = cartInstance.activeTags;
let customer_id = cartInstance.customer_id;

// Wrapper functions to maintain HTML compatibility
function fetchProducts() { return cartInstance.fetchProducts(); }
function render(list) { return cartInstance.render(list); }
function addToCart(product_id) { return cartInstance.addToCart(product_id); }
function updateCart() { return cartInstance.updateCart(); }
function changeQty(idx, delta) { return cartInstance.changeQty(idx, delta); }
function removeItem(idx) { return cartInstance.removeItem(idx); }
function closeCart() { return cartInstance.closeCart(); }
function filterProducts() { return cartInstance.filterProducts(); }
function toggleTag(btn, tag) { return cartInstance.toggleTag(btn, tag); }
function fetchCart() { return cartInstance.fetchCart(); }
function checkout() { return cartInstance.checkout(); }
function scrollToProducts() { return cartInstance.scrollToProducts(); }
function debugLocalStorage() { return cartInstance.debugLocalStorage(); }
function testCartAPI() { return cartInstance.testCartAPI(); }

// Make debug functions available globally
window.debugLocalStorage = debugLocalStorage;
window.testCartAPI = testCartAPI;
window.goToCart = goToCart;

// Initialize the application
cartInstance.populateCategories();
cartInstance.fetchProducts();
cartInstance.fetchCart();